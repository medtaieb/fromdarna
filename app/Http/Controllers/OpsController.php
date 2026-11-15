<?php

namespace App\Http\Controllers;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Support\Demo;
use Illuminate\Http\Request;

class OpsController extends Controller
{
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
                    'total_tnd'        => \App\Support\Money::tnd($order->total_millimes),
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

        return back()->with('success', "Commande #{$order->ref} assignée au livreur.");
    }

    public function dashboard() { return view('ops.dashboard'); }
    public function restaurants() { return view('ops.stores', ['stores' => Store::all()]); }
    public function couriers() { return view('ops.couriers', ['couriers' => Demo::couriers()]); }
    public function ledger() { return view('ops.ledger', ['lines' => Demo::ledger()]); }
}
