@extends('layouts.auth')

@section('title', 'Configuration initiale')
@section('visual_eyebrow', 'PREMIÈRE UTILISATION')
@section('visual_title')Bienvenue,<br>configurons votre espace.@endsection
@section('visual_text', 'Créez le compte administrateur qui gérera les vendeurs, le catalogue, les achats et les rapports.')
@section('visual_feature_icon', 'bi-shield-check')
@section('visual_feature_title', 'Accès sécurisé')
@section('visual_feature_text', 'Ce formulaire se désactive dès que le premier compte est créé.')
@section('kicker', 'CONFIGURATION INITIALE')
@section('heading', 'Créer l’administrateur')
@section('intro', 'Ce compte disposera de tous les droits sur l’application.')

@section('content')
    <form method="POST" action="{{ route('setup.store') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="name">Nom complet</label>
            <div class="auth-input-wrap @error('name') has-error @enderror">
                <i class="bi bi-person" aria-hidden="true"></i>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Votre nom" maxlength="255" autocomplete="name" required autofocus>
            </div>
        </div>

        <div class="auth-field">
            <label for="email">Adresse courriel</label>
            <div class="auth-input-wrap @error('email') has-error @enderror">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nom@exemple.com" maxlength="255" autocomplete="username" required>
            </div>
        </div>

        <div class="auth-field">
            <label for="password">Mot de passe</label>
            <div class="auth-input-wrap @error('password') has-error @enderror">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input id="password" name="password" type="password" placeholder="12 caractères minimum" autocomplete="new-password" required>
                <button class="auth-password-toggle" type="button" data-password-toggle aria-label="Afficher le mot de passe" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            <small class="auth-field-hint">Au moins 12 caractères, avec majuscule, minuscule et chiffre.</small>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <div class="auth-input-wrap">
                <i class="bi bi-lock-fill" aria-hidden="true"></i>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Saisissez-le à nouveau" autocomplete="new-password" required>
            </div>
        </div>

        <button class="auth-submit" type="submit"><span>Créer le compte administrateur</span><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="auth-setup-link"><span>Un compte existe déjà ?</span><a href="{{ route('login') }}">Se connecter <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
@endsection
