<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function login() { return view('customer.login'); }
    public function home() 
    { 
        $stores = \App\Models\Store::with('zone')->where('is_open', true)->get();
        return view('customer.home', compact('stores')); 
    }
    public function feed(Request $request)
    {
        $category = $request->query('category', 'all');
        $zoneSlug = $request->query('zone');
        $zones = \App\Models\Zone::orderBy('name')->get();
        $query = \App\Models\Store::with('zone');

        if ($category === 'restaurants') {
            $query->where(function($q) {
                $q->where('kind', 'like', '%Cuisine%')
                  ->orWhere('kind', 'like', '%Grillades%')
                  ->orWhere('kind', 'like', '%Poisson%')
                  ->orWhere('kind', 'like', '%Sandwich%');
            });
        } elseif ($category === 'patisseries') {
            $query->where('kind', 'like', '%Pâtisserie%');
        } elseif ($category === 'cafes') {
            $query->where('kind', 'like', '%Café%');
        }

        if ($zoneSlug) {
            $query->whereHas('zone', fn($q) => $q->where('slug', $zoneSlug));
        }

        $stores = $query->orderByDesc('is_open')->get();

        // Trouver la zone active pour l'affichage
        $activeZone = $zoneSlug ? $zones->firstWhere('slug', $zoneSlug) : null;

        return view('customer.feed', compact('stores', 'category', 'zones', 'zoneSlug', 'activeZone'));
    }

    public function store($id)
    {
        $store = \App\Models\Store::with('categories.products.options')->find($id) 
            ?? \App\Models\Store::with('categories.products.options')->first();

        return view('customer.store', compact('store'));
    }
    public function cart() { return view('customer.cart'); }
    public function track() { return view('customer.track'); }
    public function orders() { return view('customer.orders'); }
    public function help() { return view('customer.help'); }

    /**
     * Affichage du profil client et de ses adresses
     */
    public function account()
    {
        $user = Auth::user();

        // En mode découverte sans connexion, utiliser le client de test par défaut
        if (!$user) {
            $user = User::where('phone', '+21620123456')->first() ?? new User([
                'name' => 'Sana M.',
                'phone' => '+21620123456',
                'language' => 'fr',
                'notify_orders' => true,
                'notify_marketing' => false,
            ]);
        }

        $addresses = $user->exists ? $user->addresses()->latest()->get() : collect();

        return view('customer.account', compact('user', 'addresses'));
    }

    /**
     * Mise à jour des informations de profil
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('warning', 'Veuillez vous connecter pour modifier votre profil.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email,' . $user->id,
            'language' => 'required|in:fr,ar',
            'notify_orders' => 'nullable|boolean',
            'notify_marketing' => 'nullable|boolean',
        ], [
            'name.required' => 'Le prénom et nom sont obligatoires.',
            'email.email' => 'L’adresse email n’est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'language' => $validated['language'],
            'notify_orders' => $request->boolean('notify_orders'),
            'notify_marketing' => $request->boolean('notify_marketing'),
        ]);

        return back()->with('success', 'Votre profil et vos préférences ont été enregistrés.');
    }

    /**
     * Enregistrer une nouvelle adresse de livraison
     */
    public function storeAddress(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('warning', 'Veuillez vous connecter pour enregistrer une adresse.');
        }

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'street_address' => 'nullable|string|max:200',
            'landmark' => 'required|string|max:200',
            'building' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:20',
            'door' => 'nullable|string|max:20',
            'contact_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ], [
            'label.required' => 'Veuillez nommer cette adresse (ex: Maison, Bureau).',
            'landmark.required' => 'Le repère est obligatoire pour orienter le livreur (ex: en face de la mosquée, portail vert).',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'label' => $validated['label'],
            'city' => $validated['city'] ?? 'Grombalia',
            'street_address' => $validated['street_address'] ?? null,
            'landmark' => $validated['landmark'],
            'building' => $validated['building'] ?? null,
            'floor' => $validated['floor'] ?? null,
            'door' => $validated['door'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? $user->phone,
            'latitude' => $validated['latitude'] ?? 36.6191,
            'longitude' => $validated['longitude'] ?? 10.5012,
            'is_default' => $isDefault || $user->addresses()->count() === 0,
        ]);

        return back()->with('success', 'Nouvelle adresse ajoutée avec succès.');
    }

    /**
     * Mettre à jour une adresse existante
     */
    public function updateAddress(Request $request, Address $address)
    {
        $user = Auth::user();
        if (!$user || $address->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'street_address' => 'nullable|string|max:200',
            'landmark' => 'required|string|max:200',
            'building' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:20',
            'door' => 'nullable|string|max:20',
            'contact_phone' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->is_default = true;
        }

        $address->update($validated);

        return back()->with('success', 'Adresse mise à jour.');
    }

    /**
     * Supprimer une adresse
     */
    public function deleteAddress(Address $address)
    {
        $user = Auth::user();
        if (!$user || $address->user_id !== $user->id) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Si l'adresse supprimée était celle par défaut, désigner la plus récente restante
        if ($wasDefault && $firstRemaining = $user->addresses()->latest()->first()) {
            $firstRemaining->update(['is_default' => true]);
        }

        return back()->with('info', 'Adresse supprimée.');
    }

    /**
     * Supprimer définitivement le compte client (Conformité INPDP / RGPD)
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('home');
        }

        Auth::logout();
        $user->addresses()->delete();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Votre compte et vos données ont été définitivement supprimés.');
    }
}
