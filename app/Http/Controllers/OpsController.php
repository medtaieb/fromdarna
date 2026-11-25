<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CourierCashDrop;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\OrderIncident;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Zone;
use App\Support\Demo;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OpsController extends Controller
{
    /**
     * Tableau de bord opérationnel & supervision
     */
    public function dashboard()
    {
        // 1. Indicateurs du jour
        $deliveredToday = Order::where('status', 'DELIVERED')
            ->whereDate('delivered_at', today())
            ->count();

        $totalToday = Order::whereDate('created_at', today())->count();
        $rejectedToday = Order::whereIn('status', ['REJECTED', 'CANCELLED', 'FAILED'])
            ->whereDate('created_at', today())
            ->count();
        $acceptanceRate = $totalToday > 0 
            ? round((($totalToday - $rejectedToday) / $totalToday) * 100) 
            : 98;

        // Délai médian de livraison aujourd'hui
        $completedOrders = Order::where('status', 'DELIVERED')
            ->whereNotNull('delivered_at')
            ->whereDate('delivered_at', today())
            ->get();

        $delays = $completedOrders->map(function ($o) {
            return $o->created_at->diffInMinutes($o->delivered_at);
        })->sort()->values();

        $medianDelayMinutes = $delays->isNotEmpty() 
            ? $delays->get((int) floor($delays->count() / 2)) 
            : 32;

        // Cash non encore déposé par la flotte
        $couriers = User::where('role', 'courier')->get();
        $uncollectedCashMillimes = $couriers->sum(fn ($c) => $c->getHeldCashMillimes());

        // 2. Alertes en temps réel (Escalation Alerts)
        // a) Restaurant sans réponse > 60s
        $unresponsiveStoreOrders = Order::with('store')
            ->where('status', 'PLACED')
            ->where('created_at', '<=', now()->subSeconds(60))
            ->latest()
            ->get();

        // b) Prête > 5 min sans livreur
        $unassignedReadyOrders = Order::with('store')
            ->where('status', 'READY')
            ->whereNull('courier_id')
            ->where('ready_at', '<=', now()->subMinutes(5))
            ->latest()
            ->get();

        // c) Livraison en retard > 35 min
        $delayedOrders = Order::with(['store', 'courier'])
            ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
            ->where('created_at', '<=', now()->subMinutes(35))
            ->latest()
            ->get();

        // d) Livreurs ayant dépassé le plafond de cash (150 TND)
        $cashLimitExceededCouriers = $couriers->filter(fn ($c) => $c->isCashLimitExceeded());

        // e) Incidents de terrain ouverts
        $openIncidents = OrderIncident::with(['order.store', 'courier'])
            ->where('status', 'open')
            ->latest()
            ->get();

        // 3. Commandes par tranche horaire (aujourd'hui)
        $hourlyStats = [];
        $hoursRange = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];
        $todayOrders = Order::whereDate('created_at', today())->get();
        $maxPerHour = 1;

        foreach ($hoursRange as $hour) {
            $count = $todayOrders->filter(fn ($o) => (int) $o->created_at->format('H') === $hour)->count();
            if ($count > $maxPerHour) {
                $maxPerHour = $count;
            }
            $hourlyStats[] = [
                'hour'  => $hour . ' h',
                'count' => $count,
            ];
        }

        // 4. Commandes actives en cours
        $activeOrders = Order::with(['store', 'user', 'courier'])
            ->whereIn('status', ['PLACED', 'ACCEPTED', 'READY', 'PICKED_UP'])
            ->latest()
            ->take(8)
            ->get();

        return view('ops.dashboard', compact(
            'deliveredToday',
            'acceptanceRate',
            'medianDelayMinutes',
            'uncollectedCashMillimes',
            'unresponsiveStoreOrders',
            'unassignedReadyOrders',
            'delayedOrders',
            'cashLimitExceededCouriers',
            'openIncidents',
            'hourlyStats',
            'maxPerHour',
            'activeOrders'
        ));
    }

    /**
     * Recherche et liste des commandes
     */
    public function orders(Request $request)
    {
        $query = Order::with(['store.zone', 'user', 'courier']);

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($b) use ($q) {
                $b->where('ref', 'like', "%{$q}%")
                  ->orWhere('delivery_phone', 'like', "%{$q}%")
                  ->orWhere('delivery_landmark', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                  ->orWhereHas('store', fn ($s) => $s->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->input('zone_id'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $stores = Store::orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();

        return view('ops.orders', compact('orders', 'stores', 'zones'));
    }

    /**
     * Détail d'une commande & centre d'intervention
     */
    public function orderShow($id)
    {
        $order = Order::with(['store.zone', 'user', 'courier.courierLocation', 'items.product', 'incidents.courier'])
            ->where('id', $id)
            ->orWhere('ref', $id)
            ->firstOrFail();

        $couriers = User::where('role', 'courier')
            ->with(['courierLocation', 'assignedOrders' => fn ($q) => $q->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])])
            ->get();

        return view('ops.order-show', compact('order', 'couriers'));
    }

    /**
     * Affecter manuellement un livreur à une commande
     */
    public function assignCourier(Request $request, Order $order)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
        ]);

        $order->update([
            'courier_id'  => $validated['courier_id'],
            'status'      => $order->status === 'PLACED' ? 'ACCEPTED' : $order->status,
            'accepted_at' => $order->accepted_at ?? now(),
        ]);

        return back()->with('success', "Commande #{$order->ref} assignée au livreur avec succès.");
    }

    /**
     * Forcer manuellement le statut d'une commande
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:PLACED,ACCEPTED,READY,PICKED_UP,DELIVERED,CANCELLED',
            'reason' => 'nullable|string',
        ]);

        $status = $validated['status'];
        $updates = ['status' => $status];

        if ($status === 'ACCEPTED' && ! $order->accepted_at) {
            $updates['accepted_at'] = now();
        } elseif ($status === 'READY' && ! $order->ready_at) {
            $updates['ready_at'] = now();
        } elseif ($status === 'PICKED_UP' && ! $order->picked_up_at) {
            $updates['picked_up_at'] = now();
        } elseif ($status === 'DELIVERED' && ! $order->delivered_at) {
            $updates['delivered_at'] = now();
            $updates['payment_status'] = 'paid';
        } elseif ($status === 'CANCELLED') {
            $updates['rejection_reason'] = $validated['reason'] ?? 'Annulée manuellement par Ops';
        }

        $order->update($updates);

        return back()->with('success', "Statut de la commande #{$order->ref} mis à jour : {$order->statusLabel()}.");
    }

    /**
     * Annulation ou remboursement d'une commande par Ops
     */
    public function cancelOrRefund(Request $request, Order $order)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'amount' => 'nullable|numeric',
        ]);

        $reason = $validated['reason'];
        if (!empty($validated['amount'])) {
            $reason .= " (Remboursement accordé : " . number_format($validated['amount'], 3, ',', ' ') . " TND)";
        }

        $order->update([
            'status'           => 'CANCELLED',
            'rejection_reason' => $reason,
        ]);

        return back()->with('success', "Commande #{$order->ref} annulée. Motif enregistré : {$reason}");
    }

    /**
     * Vue Dispatch & Carte en temps réel
     */
    public function dispatch()
    {
        $ordersToAssign = Order::with(['store.zone', 'user'])
            ->whereIn('status', ['PLACED', 'ACCEPTED'])
            ->whereNull('courier_id')
            ->latest()
            ->get();

        $activeDeliveries = Order::with(['store.zone', 'user', 'courier.courierLocation'])
            ->whereNotNull('courier_id')
            ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
            ->latest()
            ->get();

        $couriers = User::where('role', 'courier')
            ->with(['courierLocation', 'assignedOrders' => function ($q) {
                $q->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP']);
            }])
            ->get();

        return view('ops.dispatch', compact('ordersToAssign', 'activeDeliveries', 'couriers'));
    }

    /**
     * API JSON appelée toutes les 4-5s par la carte Admin
     */
    public function liveData()
    {
        // 1. Tous les livreurs avec leur dernière position GPS
        $couriers = User::where('role', 'courier')
            ->with('courierLocation')
            ->get()
            ->map(function ($courier) {
                $loc = $courier->courierLocation;
                $activeCount = Order::where('courier_id', $courier->id)
                    ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
                    ->count();

                return [
                    'id'           => $courier->id,
                    'name'         => $courier->name,
                    'phone'        => $courier->phone,
                    'active_orders'=> $activeCount,
                    'battery'      => $loc?->battery ?? 85,
                    'speed'        => $loc?->speed ?? 0,
                    'heading'      => $loc?->heading ?? 0,
                    'is_online'    => $loc?->is_online ?? true,
                    'lat'          => $loc?->latitude ?? 36.6191,
                    'lng'          => $loc?->longitude ?? 10.5012,
                    'last_ping'    => $loc?->last_ping_at?->diffForHumans() ?? 'Récemment',
                ];
            });

        // 2. Toutes les livraisons en cours avec le temps écoulé en temps réel
        $orders = Order::with(['store.zone', 'courier.courierLocation'])
            ->whereIn('status', ['PLACED', 'ACCEPTED', 'READY', 'PICKED_UP'])
            ->latest()
            ->get()
            ->map(function ($order) {
                $elapsedMinutes = $order->created_at->diffInMinutes(now());
                $transitMinutes = $order->picked_up_at ? $order->picked_up_at->diffInMinutes(now()) : null;

                return [
                    'id'               => $order->id,
                    'ref'              => $order->ref,
                    'status'           => $order->status,
                    'status_label'     => $order->statusLabel(),
                    'status_badge'     => $order->statusBadgeType(),
                    'created_at_time'  => $order->created_at->format('H:i'),
                    'elapsed_minutes'  => $elapsedMinutes,
                    'transit_minutes'  => $transitMinutes,
                    'total_tnd'        => Money::tnd($order->total_millimes),
                    'store'            => [
                        'name' => $order->store->name,
                        'lat'  => (float)($order->store->latitude ?? 36.6175),
                        'lng'  => (float)($order->store->longitude ?? 10.4990),
                    ],
                    'customer'         => [
                        'city'     => $order->delivery_city,
                        'landmark' => $order->delivery_landmark,
                        'lat'      => (float)($order->delivery_lat ?? 36.6191),
                        'lng'      => (float)($order->delivery_lng ?? 10.5012),
                    ],
                    'courier'          => $order->courier ? [
                        'id'   => $order->courier->id,
                        'name' => $order->courier->name,
                        'lat'  => (float)($order->courier->courierLocation?->latitude ?? 36.6185),
                        'lng'  => (float)($order->courier->courierLocation?->longitude ?? 10.5020),
                    ] : null,
                ];
            });

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'couriers'  => $couriers,
            'orders'    => $orders,
        ]);
    }

    /**
     * Gestion et supervision des livreurs & remises de cash
     */
    public function couriers()
    {
        $couriers = User::where('role', 'courier')
            ->with(['courierLocation'])
            ->get();

        $pendingDrops = CourierCashDrop::where('status', 'pending')
            ->with('courier')
            ->latest()
            ->get();

        $validatedDrops = CourierCashDrop::where('status', 'validated')
            ->with(['courier', 'validator'])
            ->latest()
            ->take(10)
            ->get();

        return view('ops.couriers', compact('couriers', 'pendingDrops', 'validatedDrops'));
    }

    /**
     * Valider une remise d'espèces au QG
     */
    public function validateCashDrop(Request $request, CourierCashDrop $drop)
    {
        $drop->update([
            'status'       => 'validated',
            'validated_by' => auth()->id() ?? 1,
            'validated_at' => now(),
        ]);

        return back()->with('success', "Remise #{$drop->reference} de " . Money::tnd($drop->amount_millimes) . " validée avec succès.");
    }

    /**
     * Clients & Incidents signalés
     */
    public function customers(Request $request)
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        $incidents = OrderIncident::with(['order.store', 'courier'])
            ->latest()
            ->paginate(15);

        return view('ops.customers', compact('customers', 'incidents'));
    }

    /**
     * Résoudre un incident signalé par un livreur
     */
    public function resolveIncident(Request $request, OrderIncident $incident)
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string|min:3',
        ]);

        $incident->update([
            'status'           => 'resolved',
            'resolution_notes' => $validated['resolution_notes'],
        ]);

        return back()->with('success', "Incident #{$incident->id} marqué comme résolu.");
    }

    /**
     * Liste des restaurants partenaires & état opérationnel
     */
    public function restaurants()
    {
        $stores = Store::with(['zone', 'products'])->get();
        return view('ops.stores', compact('stores'));
    }

    /**
     * Formulaire d'ajout d'un nouveau restaurant
     */
    public function storeCreate()
    {
        $zones = Zone::all();
        return view('ops.store-create', compact('zones'));
    }

    /**
     * Enregistrer un nouveau restaurant
     */
    public function storeStore(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'zone_id'            => 'required|exists:zones,id',
            'kind'               => 'required|string|max:100',
            'emoji'              => 'nullable|string|max:10',
            'commission_bps'     => 'required|integer|min:0|max:5000',
            'avg_prep_minutes'   => 'required|integer|min:5|max:120',
            'min_order_millimes' => 'nullable|integer|min:0',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string|max:255',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'is_open'            => 'nullable|boolean',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $store = Store::create([
            'name'               => $validated['name'],
            'slug'               => $slug,
            'zone_id'            => $validated['zone_id'],
            'kind'               => $validated['kind'],
            'emoji'              => $validated['emoji'] ?? '🍽️',
            'commission_bps'     => $validated['commission_bps'],
            'avg_prep_minutes'   => $validated['avg_prep_minutes'],
            'min_order_millimes' => $validated['min_order_millimes'] ?? 10000,
            'phone'              => $validated['phone'] ?? '+216 72 000 000',
            'address'            => $validated['address'] ?? 'Centre-ville',
            'latitude'           => $validated['latitude'] ?? 36.6175,
            'longitude'          => $validated['longitude'] ?? 10.4990,
            'is_open'            => $request->boolean('is_open'),
        ]);

        // Créer une première catégorie par défaut
        Category::create([
            'store_id'   => $store->id,
            'name'       => 'Plats Principaux',
            'sort_order' => 1,
        ]);

        return redirect()->route('ops.stores.edit', $store->id)->with('success', "Restaurant « {$store->name} » créé avec succès. Vous pouvez maintenant enrichir son menu !");
    }

    /**
     * Édition d'un restaurant (commission, prep, statut, horaires, catégories et produits)
     */
    public function storeEdit($id)
    {
        $store = Store::with(['zone', 'categories.products'])->findOrFail($id);
        $zones = Zone::all();
        return view('ops.store-edit', compact('store', 'zones'));
    }

    /**
     * Sauvegarde des modifications restaurant
     */
    public function storeUpdate(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'kind'             => 'nullable|string|max:100',
            'emoji'            => 'nullable|string|max:10',
            'commission_bps'   => 'required|integer|min:0|max:5000',
            'avg_prep_minutes' => 'required|integer|min:5|max:120',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'zone_id'          => 'required|exists:zones,id',
            'is_open'          => 'nullable|boolean',
        ]);

        $store->update([
            'name'             => $validated['name'],
            'kind'             => $validated['kind'] ?? $store->kind,
            'emoji'            => $validated['emoji'] ?? $store->emoji,
            'commission_bps'   => $validated['commission_bps'],
            'avg_prep_minutes' => $validated['avg_prep_minutes'],
            'phone'            => $validated['phone'],
            'address'          => $validated['address'],
            'latitude'         => $validated['latitude'] ?? $store->latitude,
            'longitude'        => $validated['longitude'] ?? $store->longitude,
            'zone_id'          => $validated['zone_id'],
            'is_open'          => $request->boolean('is_open'),
        ]);

        return back()->with('success', "Restaurant « {$store->name} » mis à jour avec succès.");
    }

    /**
     * Basculer l'ouverture d'un restaurant
     */
    public function storeToggleStatus($id)
    {
        $store = Store::findOrFail($id);
        $store->update(['is_open' => ! $store->is_open]);
        $status = $store->is_open ? 'activé (ouvert)' : 'désactivé (fermé)';
        return back()->with('success', "Le restaurant {$store->name} est maintenant {$status}.");
    }

    /**
     * Supprimer un restaurant
     */
    public function storeDestroy($id)
    {
        $store = Store::findOrFail($id);
        $name = $store->name;
        
        $productIds = $store->products()->pluck('products.id');
        \App\Models\CartItem::whereIn('product_id', $productIds)->delete();

        // Supprimer catégories et produits associés
        foreach ($store->categories as $category) {
            foreach ($category->products as $p) {
                $p->options()->delete();
                $p->delete();
            }
            $category->delete();
        }

        // Supprimer commandes et incidents associés s'il s'agit d'un magasin de test
        foreach ($store->orders as $order) {
            $order->items()->delete();
            $order->incidents()->delete();
            $order->delete();
        }

        $store->delete();

        return redirect()->route('ops.stores')->with('success', "Le restaurant « {$name} » et son menu ont été supprimés.");
    }

    /**
     * Ajouter une catégorie au menu du restaurant
     */
    public function categoryStore(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:80',
        ]);

        $order = Category::where('store_id', $store->id)->count() + 1;

        Category::create([
            'store_id'   => $store->id,
            'name'       => $validated['name'],
            'sort_order' => $order,
        ]);

        return back()->with('success', "Catégorie « {$validated['name']} » ajoutée au menu.");
    }

    /**
     * Supprimer une catégorie et ses produits
     */
    public function categoryDestroy(Category $category)
    {
        $name = $category->name;
        $category->products()->delete();
        $category->delete();

        return back()->with('success', "Catégorie « {$name} » et ses produits ont été supprimés.");
    }

    /**
     * Ajouter un produit/plat au menu du restaurant
     */
    public function productStore(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'price_tnd'    => 'required|numeric|min:0.1',
            'is_available' => 'nullable|boolean',
        ]);

        $priceMillimes = (int) round($validated['price_tnd'] * 1000);
        $sort = Product::where('category_id', $validated['category_id'])->count() + 1;

        Product::create([
            'category_id'    => $validated['category_id'],
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'price_millimes' => $priceMillimes,
            'is_available'   => $request->boolean('is_available', true),
            'sort_order'     => $sort,
        ]);

        return back()->with('success', "Plat « {$validated['name']} » ajouté avec succès au menu (" . number_format($validated['price_tnd'], 3, ',', ' ') . " TND).");
    }

    /**
     * Modifier un plat du menu
     */
    public function productUpdate(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'category_id'  => 'required|exists:categories,id',
            'description'  => 'nullable|string|max:255',
            'price_tnd'    => 'required|numeric|min:0.1',
            'is_available' => 'nullable|boolean',
        ]);

        $priceMillimes = (int) round($validated['price_tnd'] * 1000);

        $product->update([
            'name'           => $validated['name'],
            'category_id'    => $validated['category_id'],
            'description'    => $validated['description'] ?? null,
            'price_millimes' => $priceMillimes,
            'is_available'   => $request->boolean('is_available'),
        ]);

        return back()->with('success', "Plat « {$product->name} » mis à jour avec succès.");
    }

    /**
     * Basculer la disponibilité d'un plat (En stock / En rupture)
     */
    public function productToggle(Product $product)
    {
        $product->update(['is_available' => ! $product->is_available]);
        $status = $product->is_available ? 'disponible en commande' : 'marqué en rupture de stock';
        return back()->with('success', "Le plat « {$product->name} » est maintenant {$status}.");
    }

    /**
     * Supprimer un plat du menu
     */
    public function productDestroy(Product $product)
    {
        $name = $product->name;
        \App\Models\CartItem::where('product_id', $product->id)->delete();
        $product->options()->delete();
        $product->delete();

        return back()->with('success', "Le plat « {$name} » a été retiré du menu.");
    }

    /**
     * Zones de livraison & paramétrage tarifaire
     */
    public function zones()
    {
        $zones = Zone::withCount('stores')->get();
        return view('ops.zones', compact('zones'));
    }

    /**
     * Basculer l'activation d'une zone
     */
    public function zoneToggleStatus(Zone $zone)
    {
        $zone->update(['is_active' => ! $zone->is_active]);
        $state = $zone->is_active ? 'activée' : 'suspendue';
        return back()->with('success', "La zone {$zone->name} est maintenant {$state}.");
    }

    /**
     * Gestion des versements et règlements partenaires
     */
    public function payouts()
    {
        $stores = Store::with('zone')->get()->map(function ($store) {
            $delivered = Order::where('store_id', $store->id)
                ->where('status', 'DELIVERED')
                ->get();

            $sales = $delivered->sum('subtotal_millimes');
            $commRate = ($store->commission_bps ?? 1200) / 10000;
            $comm = (int) round($sales * $commRate);
            $net = $sales - $comm;

            return [
                'store'      => $store,
                'count'      => $delivered->count(),
                'sales'      => $sales,
                'commission' => $comm,
                'net'        => $net,
                'status'     => $net > 0 ? 'À verser' : 'Réglé',
            ];
        });

        $cashDrops = CourierCashDrop::with(['courier', 'validator'])
            ->latest()
            ->take(15)
            ->get();

        return view('ops.payouts', compact('stores', 'cashDrops'));
    }

    /**
     * Annuaire des utilisateurs et attribution des rôles
     */
    public function users(Request $request)
    {
        $query = User::withCount(['orders', 'assignedOrders']);

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('ops.users', compact('users'));
    }

    /**
     * Journal d'audit chronologique
     */
    public function audit()
    {
        $orders = Order::with(['store', 'user', 'courier'])
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($o) {
                return [
                    'date'   => $o->updated_at->format('d/m/Y H:i'),
                    'author' => $o->courier?->name ?? ($o->user?->name ?? 'Système'),
                    'action' => 'Transition statut ' . $o->statusLabel(),
                    'object' => '#' . $o->ref . ' · ' . $o->store->name,
                ];
            });

        $drops = CourierCashDrop::with(['courier', 'validator'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($d) {
                return [
                    'date'   => $d->created_at->format('d/m/Y H:i'),
                    'author' => $d->validator?->name ?? $d->courier?->name ?? 'Ops QG',
                    'action' => 'Dépôt cash physique (' . Money::tnd($d->amount_millimes) . ')',
                    'object' => $d->reference . ' · ' . ($d->courier?->name ?? 'Livreur'),
                ];
            });

        $incidents = OrderIncident::with(['order', 'courier'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($inc) {
                return [
                    'date'   => $inc->created_at->format('d/m/Y H:i'),
                    'author' => $inc->courier?->name ?? 'Livreur',
                    'action' => 'Signalement incident: ' . ucfirst(str_replace('_', ' ', $inc->type)),
                    'object' => '#' . ($inc->order?->ref ?? $inc->order_id),
                ];
            });

        $events = $orders->concat($drops)->concat($incidents)->sortByDesc('date')->values();

        return view('ops.audit', compact('events'));
    }

    public function ledger()
    {
        $couriers = User::where('role', 'courier')->get();
        $fleetCash = $couriers->sum(fn ($c) => $c->getHeldCashMillimes());
        $hqCash = CourierCashDrop::where('status', 'validated')->sum('amount_millimes');

        $deliveredOrders = Order::where('status', 'DELIVERED')->get();
        $deliveredCount = $deliveredOrders->count();
        $couriersDue = $deliveredCount * 2500; // 2.500 TND par course

        $totalSubtotal = $deliveredOrders->sum('subtotal_millimes');
        $restaurantsDue = (int) round($totalSubtotal * 0.88); // 88% net après commission 12%

        return view('ops.ledger', [
            'lines'          => Demo::ledger(),
            'fleetCash'      => $fleetCash,
            'hqCash'         => $hqCash,
            'restaurantsDue' => $restaurantsDue,
            'couriersDue'    => $couriersDue,
        ]);
    }
}
