<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Support\Demo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    /**
     * Tableau de bord restaurant (Partenaire)
     */
    public function dashboard()
    {
        $store = Store::find(1) ?? Store::first();
        return view('partner.dashboard', compact('store'));
    }

    /**
     * Commandes en cours
     */
    public function orders()
    {
        return view('partner.orders', ['orders' => Demo::partnerOrders()]);
    }

    /**
     * Gestion du menu et des ruptures de stock
     */
    public function products()
    {
        $store = Store::with('categories.products')->find(1) ?? Store::first();
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
                'success' => true,
                'id' => $product->id,
                'is_available' => $product->is_available,
                'label' => $product->is_available ? 'Disponible' : 'En rupture',
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
                'label' => $store->is_open ? 'Ouvert aux commandes' : 'Fermé temporairement',
            ]);
        }

        return back()->with('info', "Statut du restaurant mis à jour : " . ($store->is_open ? 'Ouvert' : 'Fermé'));
    }

    public function history()
    {
        return view('partner.history', ['rows' => Demo::history()]);
    }
}
