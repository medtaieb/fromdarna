<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    /**
     * Récupère le restaurant actif du partenaire connecté (ou le premier par défaut pour démo)
     */
    protected function getActiveStore(): Store
    {
        $user = Auth::user();
        if ($user && $user->store) {
            return $user->store;
        }

        // Si l'utilisateur est le compte démo Dar Zitouna (+21621000001)
        if ($user && $user->phone === '+21621000001') {
            $store = Store::where('name', 'like', '%Dar Zitouna%')->first();
            if ($store) return $store;
        }

        return Store::with(['zone', 'categories.products'])->first();
    }

    /**
     * Tableau de bord restaurant (Partenaire) — Console Cuisine en direct
     */
    public function dashboard()
    {
        $store = $this->getActiveStore();

        // 1. Nouvelles commandes urgentes à accepter (statut PLACED)
        $newOrders = Order::with(['items.product', 'user'])
            ->where('store_id', $store->id)
            ->where('status', 'PLACED')
            ->latest()
            ->get();

        // 2. Commandes en cours de préparation (statut ACCEPTED)
        $preparingOrders = Order::with(['items.product', 'user', 'courier'])
            ->where('store_id', $store->id)
            ->where('status', 'ACCEPTED')
            ->latest()
            ->get();

        // 3. Commandes prêtes (statut READY)
        $readyOrders = Order::with(['items.product', 'user', 'courier'])
            ->where('store_id', $store->id)
            ->where('status', 'READY')
            ->latest()
            ->get();

        // 4. Commandes en livraison (statut PICKED_UP)
        $inTransitOrders = Order::with(['items.product', 'user', 'courier'])
            ->where('store_id', $store->id)
            ->where('status', 'PICKED_UP')
            ->latest()
            ->get();

        // 5. Commandes terminées aujourd'hui (statut DELIVERED)
        $deliveredToday = Order::where('store_id', $store->id)
            ->where('status', 'DELIVERED')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        // Statistiques du jour
        $todayOrdersCount = $deliveredToday->count() + $preparingOrders->count() + $readyOrders->count() + $inTransitOrders->count();
        $todayRevenue = Order::where('store_id', $store->id)
            ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP', 'DELIVERED'])
            ->whereDate('created_at', today())
            ->sum('subtotal_millimes');

        $commissionRate = ($store->commission_bps ?? 1200) / 10000; // 12% = 0.12
        $todayCommission = (int) round($todayRevenue * $commissionRate);
        $todayNet = $todayRevenue - $todayCommission;

        return view('partner.dashboard', compact(
            'store',
            'newOrders',
            'preparingOrders',
            'readyOrders',
            'inTransitOrders',
            'deliveredToday',
            'todayOrdersCount',
            'todayRevenue',
            'todayCommission',
            'todayNet'
        ));
    }

    /**
     * Liste complète des commandes du restaurant avec filtres par statut
     */
    public function orders(Request $request)
    {
        $store = $this->getActiveStore();
        $filter = $request->query('status', 'all');

        $query = Order::with(['items.product', 'user', 'courier'])
            ->where('store_id', $store->id);

        if ($filter === 'placed') {
            $query->where('status', 'PLACED');
        } elseif ($filter === 'accepted') {
            $query->where('status', 'ACCEPTED');
        } elseif ($filter === 'ready') {
            $query->where('status', 'READY');
        } elseif ($filter === 'delivered') {
            $query->where('status', 'DELIVERED');
        } elseif ($filter === 'active') {
            $query->whereIn('status', ['PLACED', 'ACCEPTED', 'READY', 'PICKED_UP']);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('partner.orders', compact('store', 'orders', 'filter'));
    }

    /**
     * Détail d'une commande spécifique pour la cuisine
     */
    public function orderShow($id)
    {
        $store = $this->getActiveStore();

        $order = Order::with(['items.product', 'user', 'courier'])
            ->where('store_id', $store->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('ref', 'like', "%{$id}%");
            })
            ->firstOrFail();

        return view('partner.order-show', compact('store', 'order'));
    }

    /**
     * Accepter une commande et définir le temps de préparation estimé
     */
    public function acceptOrder(Request $request, Order $order)
    {
        $prepMinutes = (int) $request->input('prep_minutes', 15);
        if ($prepMinutes < 5) $prepMinutes = 15;

        $order->update([
            'status'      => 'ACCEPTED',
            'accepted_at' => now(),
            'ready_at'    => now()->addMinutes($prepMinutes),
        ]);

        return back()->with('success', "Commande #{$order->ref} acceptée ! Prête dans environ {$prepMinutes} minutes.");
    }

    /**
     * Marquer une commande comme « Prête » pour le livreur
     */
    public function readyOrder(Request $request, Order $order)
    {
        $order->update([
            'status'   => 'READY',
            'ready_at' => now(),
        ]);

        return back()->with('success', "Commande #{$order->ref} prête ! Le livreur et le dispatch ont été prévenus.");
    }

    /**
     * Refuser une commande (avec motif obligatoire)
     */
    public function rejectOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:200',
        ]);

        $order->update([
            'status'           => 'REJECTED',
            'rejection_reason' => $validated['reason'],
        ]);

        return redirect()->route('partner.dashboard')->with('info', "Commande #{$order->ref} refusée ({$validated['reason']}).");
    }

    /**
     * Relevés financiers et calcul des versements
     */
    public function statements()
    {
        $store = $this->getActiveStore();

        // Récupérer les commandes livrées
        $deliveredOrders = Order::where('store_id', $store->id)
            ->where('status', 'DELIVERED')
            ->latest()
            ->get();

        $commissionRate = ($store->commission_bps ?? 1200) / 10000; // ex: 12%

        // Groupement par semaine (ex: Année-Semaine)
        $weeks = $deliveredOrders->groupBy(function ($o) {
            return $o->created_at->format('Y') . ' · Semaine ' . $o->created_at->format('W');
        })->map(function ($group, $weekName) use ($commissionRate) {
            $count = $group->count();
            $sales = $group->sum('subtotal_millimes');
            $commission = (int) round($sales * $commissionRate);
            $net = $sales - $commission;

            return [
                'period'     => $weekName,
                'count'      => $count,
                'sales'      => $sales,
                'commission' => $commission,
                'net'        => $net,
                'status'     => 'o:En cours',
            ];
        });

        // Totaux globaux
        $totalSales = $deliveredOrders->sum('subtotal_millimes');
        $totalCommission = (int) round($totalSales * $commissionRate);
        $totalNet = $totalSales - $totalCommission;

        return view('partner.statements', compact('store', 'weeks', 'totalSales', 'totalCommission', 'totalNet'));
    }

    /**
     * API JSON appelée toutes les 5s par le dashboard pour les alertes sonores
     */
    public function liveOrders()
    {
        $store = $this->getActiveStore();

        $newOrders = Order::where('store_id', $store->id)
            ->where('status', 'PLACED')
            ->get(['id', 'ref', 'total_millimes', 'created_at'])
            ->map(function ($o) {
                return [
                    'id'              => $o->id,
                    'ref'             => $o->ref,
                    'total_tnd'       => \App\Support\Money::tnd($o->total_millimes),
                    'seconds_elapsed' => $o->created_at->diffInSeconds(now()),
                ];
            });

        return response()->json([
            'new_count'  => $newOrders->count(),
            'new_orders' => $newOrders,
            'is_open'    => (bool) $store->is_open,
        ]);
    }

    /**
     * Gestion du menu et des ruptures de stock
     */
    public function products()
    {
        $store = $this->getActiveStore();
        $store->load('categories.products.options');
        return view('partner.products', compact('store'));
    }

    /**
     * Basculer un produit entre "Disponible" et "En rupture"
     */
    public function toggleProduct(Request $request, Product $product)
    {
        $product->is_available = !$product->is_available;
        $product->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'id'           => $product->id,
                'is_available' => $product->is_available,
                'label'        => $product->is_available ? 'Disponible' : 'En rupture',
            ]);
        }

        $status = $product->is_available ? 'marqué comme disponible' : 'marqué en rupture';
        return back()->with('info', "« {$product->name} » a été {$status}.");
    }

    /**
     * Ouvrir / Fermer le restaurant aux commandes
     */
    public function toggleStoreStatus(Request $request, Store $store)
    {
        $store->is_open = !$store->is_open;
        $store->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_open' => $store->is_open,
                'label'   => $store->is_open ? 'Prend des commandes' : 'En pause',
            ]);
        }

        return back()->with('info', "Statut du restaurant mis à jour : " . ($store->is_open ? 'Ouvert' : 'Fermé'));
    }
}
