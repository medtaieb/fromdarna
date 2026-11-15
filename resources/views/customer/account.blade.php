@extends('layouts.site')
@section('title', 'Mon compte · FromDarna')

@section('content')
<div class="wrap" style="max-width:960px;margin-top:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:24px">
        <div>
            <h1 style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
                Mon compte
                <span class="bd g" style="font-size:13px">Client FromDarna</span>
            </h1>
            <div class="mut" style="font-size:14.5px">
                Gérez vos informations personnelles, vos adresses et vos préférences de livraison.
            </div>
        </div>
        <a href="{{ route('feed') }}" class="btn ghost sm">
            🍲 Commander un plat
        </a>
    </div>

    @if(session('success'))
        <div class="al g" role="status" style="margin-bottom:20px;padding:12px 18px">
            <span>✓ {{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="al" role="status" style="margin-bottom:20px;padding:12px 18px">
            <span>ℹ️ {{ session('info') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="al r" role="alert" style="margin-bottom:20px;padding:12px 18px">
            <div>
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul style="margin:4px 0 0 18px;padding:0;font-size:13.5px">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid g2" style="align-items:start">
        {{-- 1. Carte Profil & Coordonnées --}}
        <x-card>
            <h2>Profil & Contact</h2>
            <form method="POST" action="{{ route('account.profile') }}" style="display:flex;flex-direction:column;gap:14px">
                @csrf
                <div style="display:flex;flex-direction:column;gap:6px">
                    <label for="profileName" style="font-weight:700;font-size:14px;color:var(--tx)">Prénom et nom</label>
                    <input id="profileName" type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Ex: Sana Mansour">
                </div>

                <div style="display:flex;flex-direction:column;gap:6px">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <label style="font-weight:700;font-size:14px;color:var(--tx)">Téléphone de connexion</label>
                        <span class="bd g" style="font-size:11.5px">✓ Vérifié par SMS</span>
                    </div>
                    <input type="tel" value="{{ $user->phone }}" disabled style="background:var(--bg2);cursor:not-allowed;color:var(--mut)">
                    <small class="mut" style="font-size:12px">Le numéro est vérifié par SMS pour sécuriser vos commandes.</small>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px">
                    <label for="profileEmail" style="font-weight:700;font-size:14px;color:var(--tx)">Email (optionnel)</label>
                    <input id="profileEmail" type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="votre.email@exemple.tn">
                </div>

                <h3 style="margin-top:10px;font-size:16px">Langue et alertes</h3>
                <div style="display:flex;gap:14px;flex-wrap:wrap">
                    <label class="ck" style="flex:1">
                        <input type="radio" name="language" value="fr" {{ old('language', $user->language ?? 'fr') === 'fr' ? 'checked' : '' }}>
                        <span>Français</span>
                    </label>
                    <label class="ck" style="flex:1">
                        <input type="radio" name="language" value="ar" {{ old('language', $user->language) === 'ar' ? 'checked' : '' }}>
                        <span class="ar" style="font-size:17px">العربية</span>
                    </label>
                </div>

                <label class="ck">
                    <input type="checkbox" name="notify_orders" value="1" {{ old('notify_orders', $user->notify_orders ?? true) ? 'checked' : '' }}>
                    <span>Être notifié(e) par SMS de l'avancée de mes livraisons</span>
                </label>

                <label class="ck">
                    <input type="checkbox" name="notify_marketing" value="1" {{ old('notify_marketing', $user->notify_marketing ?? false) ? 'checked' : '' }}>
                    <span>Recevoir les plats du jour et offres locales</span>
                </label>

                <button class="btn sm" type="submit" style="align-self:flex-start;margin-top:6px">
                    Enregistrer mon profil
                </button>
            </form>
        </x-card>

        {{-- 2. Carte Carnet d'Adresses --}}
        <div class="stack">
            <x-card>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <h2>Mes adresses de livraison</h2>
                    <button type="button" class="btn ghost sm" onclick="toggleAddressForm()" id="toggleAddressBtn" style="font-size:13px;padding:0 12px">
                        + Nouvelle adresse
                    </button>
                </div>

                <p class="mut" style="font-size:13.5px;margin-top:0">
                    Les livreurs du Cap Bon utilisent le repère visuel pour trouver votre porte sans vous déranger.
                </p>

                {{-- Liste des adresses existantes --}}
                @forelse($addresses as $addr)
                    <div style="background:var(--bg2);border:1.5px solid var(--line);border-radius:16px;padding:16px;position:relative;display:flex;flex-direction:column;gap:8px">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-size:18px">
                                    {{ $addr->label === 'Travail' ? '🏢' : ($addr->label === 'Maison' ? '🏠' : '📍') }}
                                </span>
                                <strong style="font-size:16px">{{ $addr->label }}</strong>
                                @if($addr->is_default)
                                    <span class="bd g" style="font-size:11px">Adresse principale</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('account.address.delete', $addr->id) }}" onsubmit="return confirm('Supprimer cette adresse ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:0;color:var(--red);font-size:12.5px;font-weight:700;cursor:pointer;padding:4px">
                                    ✕ Supprimer
                                </button>
                            </form>
                        </div>

                        <div style="font-size:14px;line-height:1.4">
                            <strong>{{ $addr->city }}</strong>
                            @if($addr->street_address) · {{ $addr->street_address }} @endif
                            @if($addr->building) · Bât. {{ $addr->building }} @endif
                            @if($addr->floor) · Étage {{ $addr->floor }} @endif
                            @if($addr->door) · Porte {{ $addr->door }} @endif
                        </div>

                        <div style="background:var(--card);border-left:3px solid var(--rust);padding:8px 12px;border-radius:8px;font-size:13.5px;color:var(--tx)">
                            <strong>Repère coursier :</strong> « {{ $addr->landmark }} »
                        </div>

                        @if($addr->contact_phone && $addr->contact_phone !== $user->phone)
                            <div class="mut" style="font-size:12.5px">
                                📞 Téléphone de réception : {{ $addr->contact_phone }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="text-align:center;padding:24px;border:1.5px dashed var(--line);border-radius:16px">
                        <span style="font-size:32px">📍</span>
                        <h3 style="font-size:16px;margin:8px 0 4px">Aucune adresse enregistrée</h3>
                        <p class="mut" style="font-size:13.5px;margin:0 0 12px">Ajoutez l'adresse de votre domicile ou de votre bureau pour commander en 1 clic.</p>
                        <button type="button" class="btn sm" onclick="toggleAddressForm()">Ajouter mon adresse</button>
                    </div>
                @endforelse

                {{-- Formulaire d'ajout d'adresse (Toggle) --}}
                <div id="addressFormWrap" style="display:{{ $errors->has('landmark') || $errors->has('label') ? 'block' : 'none' }};margin-top:14px;border-top:1.5px dashed var(--line);padding-top:16px">
                    <h3 style="font-size:16px;margin-bottom:12px;color:var(--rust)">+ Ajouter une adresse à Grombalia</h3>
                    <form method="POST" action="{{ route('account.address.store') }}" style="display:flex;flex-direction:column;gap:12px">
                        @csrf
                        <div class="grid g2" style="gap:10px">
                            <div style="display:flex;flex-direction:column;gap:4px">
                                <label style="font-weight:700;font-size:13.5px">Nom de l'adresse</label>
                                <select name="label" required style="min-height:42px">
                                    <option value="Maison">🏠 Maison</option>
                                    <option value="Travail">🏢 Travail / Bureau</option>
                                    <option value="Famille">👨‍👩‍👧 Famille / Amis</option>
                                    <option value="Autre">📍 Autre</option>
                                </select>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px">
                                <label style="font-weight:700;font-size:13.5px">Ville</label>
                                <select name="city" required style="min-height:42px">
                                    <option value="Grombalia" selected>Grombalia (Zone active)</option>
                                    <option value="Soliman">Soliman</option>
                                    <option value="Béni Khalled">Béni Khalled</option>
                                    <option value="Bou Argoub">Bou Argoub</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:4px">
                            <label style="font-weight:700;font-size:13.5px">Repère indispensable (très important en Tunisie) *</label>
                            <input type="text" name="landmark" required placeholder="Ex: En face de la pharmacie centrale, porte en fer verte, à côté de l'épicier" value="{{ old('landmark') }}">
                            <small class="mut">Ce repère sera lu par le livreur pour vous localiser précisément.</small>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:4px">
                            <label style="font-weight:700;font-size:13.5px">Rue / Quartier (optionnel)</label>
                            <input type="text" name="street_address" placeholder="Ex: Avenue Habib Bourguiba, Cité El Bassatine" value="{{ old('street_address') }}">
                        </div>

                        <div class="grid g3" style="gap:8px">
                            <div style="display:flex;flex-direction:column;gap:4px">
                                <label style="font-size:13px;font-weight:600">Bâtiment / Résidence</label>
                                <input type="text" name="building" placeholder="Ex: Bloc C" value="{{ old('building') }}">
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px">
                                <label style="font-size:13px;font-weight:600">Étage</label>
                                <input type="text" name="floor" placeholder="Ex: 2ème" value="{{ old('floor') }}">
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px">
                                <label style="font-size:13px;font-weight:600">Porte / N°</label>
                                <input type="text" name="door" placeholder="Ex: Appt 5" value="{{ old('door') }}">
                            </div>
                        </div>

                        <label class="ck" style="margin-top:4px">
                            <input type="checkbox" name="is_default" value="1" checked>
                            <span>Définir comme adresse principale de livraison</span>
                        </label>

                        <div style="display:flex;gap:10px;margin-top:6px">
                            <button class="btn sm" type="submit">Enregistrer l'adresse</button>
                            <button type="button" class="btn ghost sm" onclick="toggleAddressForm()">Annuler</button>
                        </div>
                    </form>
                </div>
            </x-card>

            {{-- 3. Sécurité & Suppression du compte (INPDP) --}}
            <x-card>
                <h2 style="color:var(--red)">Supprimer mon compte</h2>
                <p class="mut" style="font-size:13.5px;line-height:1.5;margin:0">
                    Conformément à la protection des données personnelles, vos commandes archivées, numéros de téléphone et adresses enregistrées seront définitivement effacés de FromDarna.
                </p>
                <form method="POST" action="{{ route('account.delete') }}" onsubmit="return confirm('Êtes-vous sûr(e) de vouloir supprimer définitivement votre compte FromDarna ? Cette action est irréversible.')" style="margin-top:10px">
                    @csrf
                    <button class="btn red sm" type="submit">
                        Supprimer définitivement mon compte
                    </button>
                </form>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAddressForm() {
    const wrap = document.getElementById('addressFormWrap');
    const btn = document.getElementById('toggleAddressBtn');
    if (!wrap) return;
    const isHidden = wrap.style.display === 'none';
    wrap.style.display = isHidden ? 'block' : 'none';
    if (btn) btn.textContent = isHidden ? '✕ Fermer' : '+ Nouvelle adresse';
}
</script>
@endpush
@endsection
