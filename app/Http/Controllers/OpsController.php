<?php

namespace App\Http\Controllers;

use App\Models\CourierCashDrop;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\OrderIncident;
use App\Models\Store;
use App\Models\User;
use App\Models\Zone;
use App\Support\Demo;
use App\Support\Money;
use Illuminate\Http\Request;

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

    public function restaurants() { return view('ops.stores', ['stores' => Store::with('zone')->get()]); }
    public function ledger() { return view('ops.ledger', ['lines' => Demo::ledger()]); }
}
