@extends('layouts.auth')

@section('title', 'Connexion')
@section('visual_title')Votre activité,<br>bien en main.@endsection
@section('visual_text', 'Ventes, stock et partenaires : retrouvez l’essentiel de votre quincaillerie au même endroit.')
@section('visual_feature_title', 'Simple au quotidien')
@section('visual_feature_text', 'Une gestion claire, du comptoir au stock.')
@section('kicker', 'HEUREUX DE VOUS REVOIR')
@section('heading', 'Connectez-vous')
@section('intro', 'Entrez vos identifiants pour accéder à votre espace.')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="email">Adresse courriel</label>
            <div class="auth-input-wrap @error('email') has-error @enderror">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nom@exemple.com" autocomplete="username" required autofocus>
            </div>
        </div>

        <div class="auth-field">
            <div class="auth-label-row"><label for="password">Mot de passe</label><a href="{{ route('password.request') }}" class="auth-forgot-link">Mot de passe oublié ?</a></div>
            <div class="auth-input-wrap @error('password') has-error @enderror">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input id="password" name="password" type="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                <button class="auth-password-toggle" type="button" data-password-toggle aria-label="Afficher le mot de passe" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>

        <label class="auth-remember" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1">
            <span class="auth-checkbox" aria-hidden="true"><i class="bi bi-check"></i></span>
            <span>Rester connecté sur cet appareil</span>
        </label>

        <button class="auth-submit" type="submit"><span>Se connecter</span><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="auth-secure-note"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i><span>Connexion sécurisée à votre espace professionnel</span></div>

    @if (\App\Models\User::query()->count() === 0 || (\App\Models\User::query()->count() === 1 && \App\Models\User::query()->where('email', 'test@example.com')->where('name', 'Test User')->exists()))
        <div class="auth-setup-link"><span>Première utilisation ?</span><a href="{{ route('setup') }}">Configurer l’administrateur <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
    @endif
@endsection
