@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Gestion de ' . $store->name)

@section('content')
<x-title :title="$store->name" :sub="'Fiche technique, commission et gestion complète du menu · Zone ' . ($store->zone?->name ?? 'Grombalia')">
    <x-slot name="actions">
        <form method="POST" action="{{ route('ops.stores.toggle', $store->id) }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn sm {{ $store->is_open ? 'olive' : 'ghost' }}">
                {{ $store->is_open ? '● Restaurant Ouvert' : '○ Restaurant Fermé' }}
            </button>
        </form>
        <x-btn variant="ghost" size="sm" :href="route('ops.stores')">← Liste des restaurants</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="al r mb">
        <ul style="margin:0;padding-left:16px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── 1. MODIFICATION DES INFORMATIONS DU RESTAURANT ── --}}
<form method="POST" action="{{ route('ops.stores.update', $store->id) }}">
    @csrf
    <div class="grid g2 mb">
        <x-card>
            <h2>Paramètres & Identité</h2>
            <x-field label="Nom de l'établissement *" name="name" :value="old('name', $store->name)" required />
            
            <div class="grid g2">
                <x-field label="Spécialité / Type *" name="kind" :value="old('kind', $store->kind)" required />
                <x-field label="Emoji" name="emoji" :value="old('emoji', $store->emoji)" />
            </div>

            <div class="grid g2">
                <x-field label="Téléphone professionnel" name="phone" :value="old('phone', $store->phone ?? '+216 72 000 000')" />
                <x-field label="Adresse physique" name="address" :value="old('address', $store->address ?? 'Centre-ville')" />
            </div>
            
            <div class="grid g2">
                <x-field label="Commission (%) *" name="commission_percent" type="number" step="0.5" :value="old('commission_percent', ($store->commission_bps ?? 1200) / 100)" helper="Ex: 12%" required />
                <x-field label="Préparation moyenne (minutes) *" name="avg_prep_minutes" type="number" :value="old('avg_prep_minutes', $store->avg_prep_minutes ?? 15)" required />
            </div>

            <label class="f">
                Zone de rattachement :
                <select name="zone_id" required>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ $store->zone_id == $z->id ? 'selected' : '' }}>
                            {{ $z->name }} ({{ $z->ar_name }})
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="ck" style="margin-top:12px;">
                <input type="checkbox" name="is_open" value="1" {{ $store->is_open ? 'checked' : '' }}>
                <strong>Restaurant actuellement ouvert aux commandes en ligne</strong>
            </label>

            <div style="margin-top:16px;">
                <button class="btn block" type="submit">Enregistrer les paramètres</button>
            </div>
        </x-card>

        <x-card>
            <h2>Localisation & Coordonnées GPS</h2>
            <div class="grid g2">
                <x-field label="Latitude GPS" name="latitude" :value="old('latitude', $store->latitude ?? 36.6175)" required />
                <x-field label="Longitude GPS" name="longitude" :value="old('longitude', $store->longitude ?? 10.4990)" required />
            </div>
            
            <div style="padding:14px;background:#f5efe6;border-radius:8px;border:1px dashed #b08861;text-align:center;margin-top:10px;">
                <div style="font-size:1.8rem;margin-bottom:4px;">📍</div>
                <b>Point de retrait des livreurs</b>
                <p class="mut" style="font-size:0.85rem;margin:4px 0 8px 0;">Coordonnées utilisées par les algorithmes de guidage et de dispatch.</p>
                <a class="btn ghost sm" target="_blank" href="https://www.google.com/maps?q={{ $store->latitude ?? 36.6175 }},{{ $store->longitude ?? 10.4990 }}">
                    Vérifier sur Google Maps ↗
                </a>
            </div>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #ddd;">
                <h3 style="color:#C41317;margin-bottom:6px;">Zone de danger</h3>
                <p class="mut" style="font-size:0.85rem;margin-bottom:10px;">Supprimer définitivement cet établissement ainsi que tous ses menus et options.</p>
                <form method="POST" action="{{ route('ops.stores.destroy', $store->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn red sm" onclick="return confirm('Confirmez-vous la suppression définitive du restaurant « {{ addslashes($store->name) }} » ? Cette action est irréversible.');">
                        🗑️ Supprimer ce restaurant
                    </button>
                </form>
            </div>
        </x-card>
    </div>
</form>

{{-- ── 2. GESTION DES CATÉGORIES ET AJOUT DE PLATS ── --}}
<div class="cols mb">
    {{-- Formulaire Ajouter une Catégorie --}}
    <x-card>
        <h2>📁 Ajouter une catégorie de menu</h2>
        <form method="POST" action="{{ route('ops.categories.store', $store->id) }}" style="display:flex;gap:8px;align-items:flex-end;">
            @csrf
            <div style="flex:1;">
                <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Nom de la catégorie *</label>
                <input type="text" name="name" placeholder="Ex: Plats tunisiens, Pizze, Boissons..." required style="width:100%;">
            </div>
            <button type="submit" class="btn olive" style="white-space:nowrap;">+ Ajouter catégorie</button>
        </form>

        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;">
            @foreach($store->categories as $c)
                <div style="display:inline-flex;align-items:center;background:#f0e9df;padding:4px 10px;border-radius:20px;font-size:0.85rem;gap:6px;">
                    <b>{{ $c->name }}</b>
                    <span class="mut">({{ $c->products->count() }})</span>
                    <form method="POST" action="{{ route('ops.categories.destroy', $c->id) }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#C41317;cursor:pointer;font-weight:700;padding:0 2px;" onclick="return confirm('Supprimer la catégorie « {{ addslashes($c->name) }} » et tous ses plats ?');" title="Supprimer catégorie">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    </x-card>

    {{-- Formulaire Ajouter un Plat au Menu --}}
    <x-card>
        <h2>🍲 Ajouter un plat au menu</h2>
        <form method="POST" action="{{ route('ops.products.store', $store->id) }}">
            @csrf
            <div class="grid g2">
                <label class="f">
                    Catégorie * :
                    <select name="category_id" required>
                        @foreach($store->categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </label>
                <x-field label="Prix en TND (ex: 14.500) *" name="price_tnd" type="number" step="0.050" placeholder="12.000" required />
            </div>

            <x-field label="Nom du plat *" name="name" placeholder="Ex: Couscous à l'agneau, Ojja merguez, Pizza FromDarna..." required />
            <x-field label="Description ou ingrédients" name="description" placeholder="Ex: Sauce tomate pimentée, œuf fermier, poivrons..." />

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                <label class="ck" style="margin:0;">
                    <input type="checkbox" name="is_available" value="1" checked>
                    <strong>Disponible immédiatement</strong>
                </label>
                <button type="submit" class="btn glow">+ Enregistrer le plat</button>
            </div>
        </form>
    </x-card>
</div>

{{-- ── 3. LISTE DÉTAILLÉE DES PLATS DU MENU PAR CATÉGORIE ── --}}
<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Menu & Carte de {{ $store->name }} ({{ $store->categories->sum(fn($c) => $c->products->count()) }} plats)</h2>
        <x-btn variant="ghost" size="sm" :href="route('store', $store->id)">Voir la carte côté client ↗</x-btn>
    </div>

    @forelse($store->categories as $category)
        <div style="margin-top:20px;border-top:2px solid #e0d8cc;padding-top:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <h3 style="margin:0;color:var(--accent);font-size:1.15rem;">
                    📁 {{ $category->name }} ({{ $category->products->count() }})
                </h3>
            </div>

            @if($category->products->isEmpty())
                <p class="mut" style="font-size:0.85rem;padding:8px 0;">Aucun plat dans cette catégorie pour le moment.</p>
            @else
                <x-table :headers="['Plat & Ingrédients', 'Prix', 'Disponibilité', 'Actions']">
                    @foreach ($category->products as $p)
                        <tr>
                            <td style="max-width:300px;">
                                <b>{{ $p->name }}</b>
                                @if($p->description)
                                    <div class="mut" style="font-size:0.8rem;">{{ $p->description }}</div>
                                @endif
                            </td>
                            <td>
                                <strong style="color:var(--olive);font-size:1.05rem;">
                                    {{ \App\Support\Money::tnd($p->price_millimes) }}
                                </strong>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('ops.products.toggle', $p->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn sm {{ $p->is_available ? 'olive' : 'ghost' }}" style="padding:4px 8px;font-size:0.75rem;">
                                        {{ $p->is_available ? '● Disponible' : '○ En rupture' }}
                                    </button>
                                </form>
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex;gap:6px;align-items:center;">
                                    {{-- Formulaire de modification rapide du prix / nom --}}
                                    <details style="position:relative;display:inline-block;">
                                        <summary class="btn ghost sm" style="cursor:pointer;list-style:none;padding:4px 8px;font-size:0.75rem;">
                                            ✏️ Éditer
                                        </summary>
                                        <div style="position:absolute;right:0;top:100%;z-index:50;background:var(--card);border:1.5px solid var(--line);box-shadow:0 8px 24px rgba(0,0,0,0.15);padding:14px;border-radius:10px;width:280px;text-align:left;">
                                            <form method="POST" action="{{ route('ops.products.update', $p->id) }}" style="display:flex;flex-direction:column;gap:8px;">
                                                @csrf
                                                <input type="hidden" name="category_id" value="{{ $p->category_id }}">
                                                <label style="font-size:0.75rem;font-weight:700;">Nom</label>
                                                <input type="text" name="name" value="{{ $p->name }}" required style="font-size:0.85rem;padding:4px 6px;">
                                                
                                                <label style="font-size:0.75rem;font-weight:700;">Prix (TND)</label>
                                                <input type="number" step="0.050" name="price_tnd" value="{{ $p->price_millimes / 1000 }}" required style="font-size:0.85rem;padding:4px 6px;">
                                                
                                                <label style="font-size:0.75rem;font-weight:700;">Description</label>
                                                <input type="text" name="description" value="{{ $p->description }}" style="font-size:0.85rem;padding:4px 6px;">
                                                
                                                <label class="ck" style="font-size:0.8rem;margin:4px 0;">
                                                    <input type="checkbox" name="is_available" value="1" {{ $p->is_available ? 'checked' : '' }}>
                                                    Disponible
                                                </label>
                                                <button type="submit" class="btn sm olive block">Enregistrer</button>
                                            </form>
                                        </div>
                                    </details>

                                    {{-- Suppression du plat --}}
                                    <form method="POST" action="{{ route('ops.products.destroy', $p->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="btn sm red" 
                                            style="padding:4px 8px;font-size:0.75rem;" 
                                            onclick="return confirm('Retirer « {{ addslashes($p->name) }} » du menu ?');" 
                                            title="Supprimer ce plat"
                                        >
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>
    @empty
        <div style="text-align:center;padding:32px;color:var(--muted);">
            Ce restaurant n'a pas encore de catégorie de menu. Créez-en une ci-dessus pour commencer à ajouter des plats !
        </div>
    @endforelse
</x-card>
@endsection
