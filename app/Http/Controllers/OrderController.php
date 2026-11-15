<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ── Panier ───────────────────────────────────────────────────────────

    /**
     * Vue du panier
     */
    public function cart(Request $request)
    {
        $items = $this->getCartItems($request);
        $store = $items->first()?->product?->category?->store;

        $subtotal = $items->sum('line_total');
        $fee      = $store?->zone?->delivery_fee_millimes ?? 3000;
        $total    = $subtotal + $fee;

        return view('customer.cart', compact('items', 'store', 'subtotal', 'fee', 'total'));
    }

    /**
     * Ajouter un produit au panier (JSON ou redirect)
     */
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));

        $cartItem = CartItem::firstOrNew([
            'session_id' => $request->session()->getId(),
            'product_id' => $product->id,
        ]);

        $cartItem->user_id  = Auth::id();
        $cartItem->quantity = ($cartItem->quantity ?? 0) + (int) $request->input('quantity', 1);
        $cartItem->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'cart_count' => $this->cartCount($request),
                'cart_total' => $this->cartTotal($request),
            ]);
        }

        return back()->with('success', "« {$product->name} » ajouté au panier.");
    }

    /**
     * Modifier la quantité d'une ligne du panier
     */
    public function updateCart(Request $request, CartItem $cartItem)
    {
        $qty = (int) $request->input('quantity', 1);

        if ($qty <= 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $qty]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'cart_count' => $this->cartCount($request),
                'cart_total' => $this->cartTotal($request),
            ]);
        }

        return redirect()->route('cart');
    }

    /**
     * Supprimer une ligne du panier
     */
    public function removeFromCart(Request $request, CartItem $cartItem)
    {
        $cartItem->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('info', 'Article retiré du panier.');
    }

    /**
     * Vider le panier
     */
    public function clearCart(Request $request)
    {
        CartItem::where('session_id', $request->session()->getId())->delete();

        return redirect()->route('cart')->with('info', 'Votre panier a été vidé.');
    }

    // ── Commande ─────────────────────────────────────────────────────────

    /**
     * Page de confirmation de commande
     */
    public function checkout(Request $request)
    {
        $items = $this->getCartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('warning', 'Votre panier est vide.');
        }

        $store    = $items->first()->product->category->store;
        $subtotal = $items->sum('line_total');
        $fee      = $store?->zone?->delivery_fee_millimes ?? 3000;
        $total    = $subtotal + $fee;
        $user     = Auth::user();
        $savedAddress = $user?->addresses()->where('is_default', true)->first()
                     ?? $user?->addresses()->latest()->first();

        return view('customer.checkout', compact('items', 'store', 'subtotal', 'fee', 'total', 'user', 'savedAddress'));
    }

    /**
     * Valider la commande et l'enregistrer en BDD
     */
    public function placeOrder(Request $request)
    {
        $items = $this->getCartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('warning', 'Votre panier est vide.');
        }

        $validated = $request->validate([
            'landmark'        => 'required|string|max:200',
            'phone'           => 'required|string|max:30',
            'payment'         => 'required|in:cash',
            'idempotency_key' => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
            'building'        => 'nullable|string|max:50',
            'floor'           => 'nullable|string|max:20',
            'door'            => 'nullable|string|max:20',
            'delivery_lat'    => 'nullable|numeric',
            'delivery_lng'    => 'nullable|numeric',
        ], [
            'landmark.required' => 'Le repère de livraison est obligatoire.',
            'phone.required'    => 'Le numéro de téléphone est obligatoire.',
        ]);

        // Idempotence : éviter les doublons si double-clic
        if ($ikey = $request->input('idempotency_key')) {
            if ($existing = Order::where('idempotency_key', $ikey)->first()) {
                return redirect()->route('orders.show', $existing->id);
            }
        }

        $store    = $items->first()->product->category->store;
        $subtotal = $items->sum('line_total');
        $fee      = $store->zone->delivery_fee_millimes ?? 3000;
        $total    = $subtotal + $fee;

        $order = DB::transaction(function () use ($request, $items, $store, $subtotal, $fee, $total, $validated, $ikey) {
            $order = Order::create([
                'ref'                   => Order::generateRef(),
                'user_id'               => Auth::id(),
                'store_id'              => $store->id,
                'zone_id'               => $store->zone_id,
                'status'                => 'PLACED',
                'payment_method'        => 'cash',
                'payment_status'        => 'pending',
                'subtotal_millimes'     => $subtotal,
                'delivery_fee_millimes' => $fee,
                'total_millimes'        => $total,
                'delivery_landmark'     => $validated['landmark'],
                'delivery_city'         => $store->zone->name ?? 'Grombalia',
                'delivery_building'     => $validated['building'] ?? null,
                'delivery_floor'        => $validated['floor'] ?? null,
                'delivery_door'         => $validated['door'] ?? null,
                'delivery_phone'        => $validated['phone'],
                'delivery_lat'          => !empty($validated['delivery_lat']) ? (float)$validated['delivery_lat'] : ($store->latitude ?? 36.6191),
                'delivery_lng'          => !empty($validated['delivery_lng']) ? (float)$validated['delivery_lng'] : ($store->longitude ?? 10.5012),
                'delivery_code'         => str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
                'customer_note'         => $validated['note'] ?? null,
                'idempotency_key'       => $ikey,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_id'           => $item->product_id,
                    'product_name'         => $item->product->name,
                    'unit_price_millimes'  => $item->product->price_millimes,
                    'quantity'             => $item->quantity,
                    'line_total_millimes'  => $item->line_total,
                    'options_json'         => $item->options_json,
                ]);
            }

            // Vider le panier après commande
            CartItem::where('session_id', $request->session()->getId())->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order->id)
            ->with('success', "Commande {$order->ref} confirmée ! Le restaurant a été prévenu.");
    }

    // ── Historique ───────────────────────────────────────────────────────

    /**
     * Liste des commandes du client connecté
     */
    public function orders(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $orders = Order::with(['store', 'items'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
        } else {
            $orders = collect();
        }

        return view('customer.orders', compact('orders'));
    }

    /**
     * Détail d'une commande
     */
    public function orderShow(Order $order)
    {
        $order->load(['store.zone', 'zone', 'items.product', 'courier.courierLocation']);
        return view('customer.order-show', compact('order'));
    }

    /**
     * API JSON de suivi en direct pour le client
     */
    public function liveTracking(Order $order)
    {
        $order->load(['store', 'courier.courierLocation']);

        return response()->json([
            'status'       => $order->status,
            'status_label' => $order->statusLabel(),
            'status_badge' => $order->statusBadgeType(),
            'store' => [
                'name' => $order->store->name,
                'lat'  => (float)($order->store->latitude ?? 36.6175),
                'lng'  => (float)($order->store->longitude ?? 10.4990),
            ],
            'customer' => [
                'landmark' => $order->delivery_landmark,
                'lat'      => (float)($order->delivery_lat ?? 36.6191),
                'lng'      => (float)($order->delivery_lng ?? 10.5012),
            ],
            'courier' => $order->courier ? [
                'name'      => $order->courier->name,
                'phone'     => $order->courier->phone,
                'lat'       => (float)($order->courier->courierLocation?->latitude ?? 36.6185),
                'lng'       => (float)($order->courier->courierLocation?->longitude ?? 10.5020),
                'heading'   => (float)($order->courier->courierLocation?->heading ?? 0),
                'last_ping' => $order->courier->courierLocation?->last_ping_at?->diffForHumans() ?? 'Récemment',
            ] : null,
        ]);
    }

    // ── Helpers privés ───────────────────────────────────────────────────

    private function getCartItems(Request $request)
    {
        return CartItem::with(['product.category.store.zone'])
            ->where('session_id', $request->session()->getId())
            ->get();
    }

    private function cartCount(Request $request): int
    {
        return CartItem::where('session_id', $request->session()->getId())->sum('quantity');
    }

    private function cartTotal(Request $request): int
    {
        return CartItem::with('product')
            ->where('session_id', $request->session()->getId())
            ->get()
            ->sum('line_total');
    }
}
