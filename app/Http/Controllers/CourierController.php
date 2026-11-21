<?php

namespace App\Http\Controllers;

use App\Models\CourierCashDrop;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\OrderIncident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierController extends Controller
{
    protected function getActiveCourier(): User
    {
        return Auth::user() ?? User::where('role', 'courier')->first() ?? User::first();
    }

    /**
     * Dashboard livreur
     */
    public function dashboard()
    {
        $user = $this->getActiveCourier();

        // Récupérer la course en cours assignée au livreur
        $activeOrder = Order::with(['store.zone', 'user'])
            ->where('courier_id', $user->id)
            ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
            ->latest()
            ->first();

        // Si aucune course assignée, chercher une commande prête à être prise
        $availableOrder = null;
        if (!$activeOrder) {
            $availableOrder = Order::with(['store.zone', 'user'])
                ->whereNull('courier_id')
                ->whereIn('status', ['PLACED', 'ACCEPTED', 'READY'])
                ->latest()
                ->first();
        }

        $courierLocation = $user->courierLocation;

        // Calculs réels de caisse et plafond 150 TND
        $heldCash = $user->getHeldCashMillimes();
        $cashLimit = 150000; // 150,000 TND
        $cashPercent = min(100, round(($heldCash / $cashLimit) * 100));
        $isLimitExceeded = $user->isCashLimitExceeded();
        $todayEarnings = $user->getTodayEarningsMillimes();

        return view('courier.dashboard', compact(
            'user',
            'activeOrder',
            'availableOrder',
            'courierLocation',
            'heldCash',
            'cashLimit',
            'cashPercent',
            'isLimitExceeded',
            'todayEarnings'
        ));
    }

    /**
     * Étapes de livraison & Tracé GPS
     */
    public function delivery($id = null)
    {
        $user = $this->getActiveCourier();

        $order = null;
        if ($id) {
            $order = Order::with(['store.zone', 'items.product', 'user'])->find($id);
        }

        if (!$order) {
            $order = Order::with(['store.zone', 'items.product', 'user'])
                ->where('courier_id', $user->id)
                ->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])
                ->latest()
                ->first();
        }

        if (!$order) {
            $order = Order::with(['store.zone', 'items.product', 'user'])->latest()->first();
        }

        $courierLocation = $user->courierLocation;

        return view('courier.delivery', compact('order', 'courierLocation', 'user'));
    }

    /**
     * Mettre à jour la position GPS du livreur (appelé toutes les 5-10s en JS)
     */
    public function updateLocation(Request $request)
    {
        $user = $this->getActiveCourier();

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
        $user = $this->getActiveCourier();
        $status = $request->input('status'); // ACCEPTED, PICKED_UP, DELIVERED

        if ($status === 'ACCEPTED') {
            // Règle de sécurité : Bloquer si le plafond de 150 TND est dépassé
            if ($user->isCashLimitExceeded()) {
                return back()->with('warning', '⚠️ Impossible d’accepter une nouvelle course en cash : vous avez dépassé le plafond de 150 TND de cash en main. Veuillez déposer votre cash au bureau FromDarna de Grombalia.');
            }

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
                return back()->with('warning', 'Code de livraison incorrect. Demandez les 4 chiffres au client.');
            }

            $order->update([
                'status'         => 'DELIVERED',
                'payment_status' => 'paid',
                'delivered_at'   => now(),
            ]);

            return redirect()->route('courier.dashboard')->with('success', "Commande #{$order->ref} livrée avec succès ! Cash encaissé : " . \App\Support\Money::tnd($order->total_millimes));
        }

        return back();
    }

    /**
     * Relevé des gains et suivi de la caisse cash
     */
    public function earnings()
    {
        $user = $this->getActiveCourier();

        $heldCash = $user->getHeldCashMillimes();
        $cashLimit = 150000;
        $isLimitExceeded = $user->isCashLimitExceeded();

        // Courses terminées par ce coursier
        $deliveredOrders = Order::with(['store', 'user'])
            ->where('courier_id', $user->id)
            ->where('status', 'DELIVERED')
            ->latest('delivered_at')
            ->get();

        $todayDeliveries = $deliveredOrders->filter(fn ($o) => $o->delivered_at && $o->delivered_at->isToday());
        $todayEarnings = $todayDeliveries->count() * 2500; // 2.500 TND par course

        $weekDeliveries = $deliveredOrders->filter(fn ($o) => $o->delivered_at && $o->delivered_at->greaterThanOrEqualTo(now()->startOfWeek()));
        $weekEarnings = $weekDeliveries->count() * 2500;

        // Historique des remises de cash au QG
        $cashDrops = CourierCashDrop::where('user_id', $user->id)->latest()->get();

        return view('courier.earnings', compact(
            'user',
            'heldCash',
            'cashLimit',
            'isLimitExceeded',
            'todayEarnings',
            'todayDeliveries',
            'weekEarnings',
            'deliveredOrders',
            'cashDrops'
        ));
    }

    /**
     * Écran de remise de cash au QG de Grombalia
     */
    public function cash()
    {
        $user = $this->getActiveCourier();
        $heldCash = $user->getHeldCashMillimes();
        $cashLimit = 150000;
        $cashPercent = min(100, round(($heldCash / $cashLimit) * 100));

        $cashDrops = CourierCashDrop::where('user_id', $user->id)->latest()->take(5)->get();

        return view('courier.cash', compact('user', 'heldCash', 'cashLimit', 'cashPercent', 'cashDrops'));
    }

    /**
     * Action : Remettre le cash détenu au bureau FromDarna
     */
    public function dropCash(Request $request)
    {
        $user = $this->getActiveCourier();
        $heldCash = $user->getHeldCashMillimes();

        if ($heldCash <= 0) {
            return back()->with('info', 'Vous n’avez aucun cash à déposer actuellement.');
        }

        $drop = CourierCashDrop::create([
            'user_id'         => $user->id,
            'amount_millimes' => $heldCash,
            'status'          => 'validated', // Validé automatiquement pour la démo / bureau
            'reference'       => CourierCashDrop::generateRef(),
            'notes'           => $request->input('notes', 'Remise de cash effectuée au bureau FromDarna Grombalia'),
            'validated_at'    => now(),
        ]);

        return redirect()->route('courier.earnings')->with('success', "Remise de cash de " . \App\Support\Money::tnd($drop->amount_millimes) . " validée ! Référence : #{$drop->reference}. Votre plafond de 150 TND est remis à zéro.");
    }

    /**
     * Écran de signalement d'incident
     */
    public function incident()
    {
        $user = $this->getActiveCourier();

        // Récupérer les commandes récentes pour la sélection
        $recentOrders = Order::with('store')
            ->where('courier_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $incidents = OrderIncident::where('courier_id', $user->id)->latest()->get();

        return view('courier.incident', compact('user', 'recentOrders', 'incidents'));
    }

    /**
     * Action : Enregistrer un incident de livraison
     */
    public function storeIncident(Request $request)
    {
        $user = $this->getActiveCourier();

        $validated = $request->validate([
            'order_id'    => 'nullable|exists:orders,id',
            'type'        => 'required|string|max:50',
            'description' => 'required|string|max:1000',
        ]);

        $incident = OrderIncident::create([
            'courier_id'  => $user->id,
            'order_id'    => $validated['order_id'] ?? null,
            'type'        => $validated['type'],
            'description' => $validated['description'],
            'status'      => 'open',
        ]);

        return redirect()->route('courier.incident')->with('success', "Incident #{$incident->id} transmis en direct à l'équipe Ops. Vous serez contacté(e) rapidement.");
    }
}
