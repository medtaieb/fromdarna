@extends('layouts.base')

@section('body')
<nav>
    <div class="wrap">
        <a class="brand" href="#top"><span class="logo" role="img" aria-label="Logo Cooked by Us"></span>Cooked by Us</a>
        <ul>
            <li><a href="#villes">Nos villes</a></li>
            <li><a href="#comment">Comment ça marche</a></li>
            <li><a href="#pourquoi">Nos engagements</a></li>
        </ul>
        <a class="btn" href="{{ route('feed') }}">Commander</a>
    </div>
</nav>

<header class="hero" id="top">
    <div class="wrap">
        <div>
            <span class="pill"><i></i>Bientôt dans le Cap Bon ouest</span>
            <h1>Les plats de chez nous, <em>livrés</em> jusqu’à votre porte.</h1>
            <span class="ar ar-line">أكلة من دارنا، توصلك لين دارك</span>
            <p>Restaurants, pâtisseries et cafés de votre ville, réunis au même endroit. Vous voyez le prix total avant de confirmer, et vous payez en cash à la livraison.</p>
            <div class="acts">
                <a class="btn" href="{{ route('feed') }}">Voir les restaurants</a>
                <a class="btn ghost" href="https://wa.me/{{ config('cooked.whatsapp') }}">Commander sur WhatsApp</a>
            </div>
        </div>
        <div class="emblem">
            <div class="logo" role="img" aria-label="Cooked by Us — من دارنا : une marmite fumante sous un toit, entourée de feuilles d’olivier"></div>
            <svg class="steam" viewBox="0 0 80 120" aria-hidden="true"><path d="M20 110c-14-22 14-30 0-52"/><path d="M42 110c-14-22 14-30 0-52"/><path d="M64 110c-14-22 14-30 0-52"/></svg>
            <svg class="leaf a" viewBox="0 0 40 80" aria-hidden="true"><path d="M20 0C40 20 38 56 20 80 2 56 0 20 20 0z"/></svg>
            <svg class="leaf b" viewBox="0 0 40 80" aria-hidden="true"><path d="M20 0C40 20 38 56 20 80 2 56 0 20 20 0z"/></svg>
        </div>
    </div>
</header>

<main>
    <section id="villes">
        <div class="wrap">
            <h2>Quatre villes, une seule cuisine de voisinage.</h2>
            <p class="lead">Nous ouvrons une ville à la fois, en commençant par Grombalia, pour livrer vite et bien.</p>
            <p class="lead ar">من دارنا، لأهل الدار</p>
            <div class="towns">
                <article class="hub">
                    <div>
                        <h3>{{ $hub['name'] }}</h3>
                        <div class="ar">{{ $hub['ar'] }}</div>
                        <p>{{ $hub['text'] }}</p>
                    </div>
                    <span class="tag">{{ $hub['tag'] }}</span>
                </article>
                @foreach ($towns as $town)
                    <article class="town">
                        <h3>{{ $town['name'] }}</h3>
                        <p>{{ $town['text'] }}</p>
                        <span class="km">≈ {{ $town['km'] }} km de Grombalia</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="how" id="comment">
        <div class="wrap">
            <h2>Du menu à votre table en trois gestes.</h2>
            <p class="lead">Pas de surprise : vous savez ce que vous payez avant de dire oui.</p>
            <ol class="steps">
                @foreach ($steps as $step)
                    <li><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="pourquoi">
        <div class="wrap">
            <h2>Ce que nous promettons à nos voisins.</h2>
            <div class="why">
                @foreach ($promises as $promise)
                    <div><h3>{{ $promise[0] }}</h3><p>{{ $promise[1] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="wrap" id="rejoindre">
        <div class="cta">
            <h2>Votre ville est dans la liste ? Dites-le-nous.</h2>
            <p>Écrivez-nous sur WhatsApp pour commander ou pour être prévenu(e) de l’ouverture chez vous. Vous êtes restaurateur ou livreur ? Dites-le aussi.</p>
            <div class="acts" style="justify-content:center">
                <a class="btn" href="https://wa.me/{{ config('cooked.whatsapp') }}">Écrire sur WhatsApp</a>
                <a class="btn ghost" href="tel:{{ config('cooked.phone') }}">Appeler</a>
            </div>
        </div>
    </div>
</main>

@include('partials.footer')
@endsection
