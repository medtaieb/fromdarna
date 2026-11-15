<?php

namespace App\Http\Controllers;

use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierController extends Controller
{
    /**
     * Dashboard livreur
     */
    public function dashboard()
    {
        $user = Auth::user() ?? User::where('role', 'courier')->first();

        // Récupérer la course en cours assignée au livreur
        $activeOrder = Order::with(['store.zone', 'user'])
            ->where('courier_id', $user?->id)
            ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
            ->latest()
            ->first();

        // Si aucune course assignée, chercher une commande prête à être prise
        if (!$activeOrder) {
            $availableOrder = Order::with(['store.zone', 'user'])
                ->whereNull('courier_id')
                ->whereIn('status', ['PLACED', 'ACCEPTED', 'READY'])
                ->latest()
                ->first();
        } else {
            $availableOrder = null;
        }

        $courierLocation = $user?->courierLocation;

        return view('courier.dashboard', compact('user', 'activeOrder', 'availableOrder', 'courierLocation'));
    }

    /**
     * Étapes de livraison & Tracé GPS
     */
    public function delivery($id = null)
    {
        $user = Auth::user() ?? User::where('role', 'courier')->first();

        $order = null;
        if ($id) {
            $order = Order::with(['store.zone', 'items.product', 'user'])->find($id);
        }

        if (!$order) {
            $order = Order::with(['store.zone', 'items.product', 'user'])
                ->where('courier_id', $user?->id)
                ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
                ->latest()
                ->first();
        }

        if (!$order) {
            $order = Order::with(['store.zone', 'items.product', 'user'])->latest()->first();
        }

        $courierLocation = $user?->courierLocation;

        return view('courier.delivery', compact('order', 'courierLocation', 'user'));
    }

    /**
     * Mettre à jour la position GPS du livreur (appelé toutes les 5-10s en JS)
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'courier')->first();

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $validated = $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'heading'   => 'nullable|numeric',
            'speed'     => 'nullable|numeric',
            'battery'   => 'nullable|integer',
        ]);

        $loc = CourierLocation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'latitude'     => $validated['latitude'],
                'longitude'    => $validated['longitude'],
                'heading'      => $validated['heading'] ?? 0,
                'speed'        => $validated['speed'] ?? 0,
                'battery'      => $validated['battery'] ?? 80,
                'is_online'    => true,
                'last_ping_at' => now(),
            ]
        );

        return response()->json([
            'success'   => true,
            'timestamp' => now()->toIso8601String(),
            'location'  => $loc,
        ]);
    }

    /**
     * Progression du statut de la course par le livreur
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $user = Auth::user() ?? User::where('role', 'courier')->first();

        $status = $request->input('status'); // ACCEPTED, PICKED_UP, DELIVERED

        if ($status === 'ACCEPTED') {
            $order->update([
                'courier_id'  => $user->id,
                'status'      => 'ACCEPTED',
                'accepted_at' => now(),
            ]);
            return back()->with('success', 'Course acceptée ! Rendez-vous au restaurant.');
        }

        if ($status === 'PICKED_UP') {
            $order->update([
                'status'       => 'PICKED_UP',
                'picked_up_at' => now(),
            ]);
            return back()->with('success', 'Commande récupérée ! En route vers le client.');
        }

        if ($status === 'DELIVERED') {
            $code = $request->input('otp');
            if ($order->delivery_code && $code !== $order->delivery_code) {
                return back()->with('warning', 'Code de livraison incorrect.');
            }

            $order->update([
                'status'         => 'DELIVERED',
                'payment_status' => 'paid',
                'delivered_at'   => now(),
            ]);
            return redirect()->route('courier.dashboard')->with('success', "Commande #{$order->ref} livrée avec succès !");
        }

        return back();
    }

    public function earnings() { return view('courier.earnings'); }
    public function incident() { return view('courier.incident'); }
}
