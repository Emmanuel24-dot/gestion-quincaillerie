@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')
@section('visual_eyebrow', 'ACCÈS AU COMPTE')
@section('visual_title')Un nouveau départ<br>en toute sécurité.@endsection
@section('visual_text', 'Choisissez un mot de passe solide que vous n’utilisez nulle part ailleurs.')
@section('visual_feature_icon', 'bi-shield-check')
@section('visual_feature_title', 'Mot de passe robuste')
@section('visual_feature_text', '12 caractères minimum, avec majuscule, minuscule et chiffre.')
@section('kicker', 'DERNIÈRE ÉTAPE')
@section('heading', 'Nouveau mot de passe')
@section('intro', 'Saisissez votre nouveau mot de passe pour retrouver l’accès à votre espace.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label for="email">Adresse courriel</label>
            <div class="auth-input-wrap @error('email') has-error @enderror">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="username" required>
            </div>
        </div>

        <div class="auth-field">
            <label for="password">Nouveau mot de passe</label>
            <div class="auth-input-wrap @error('password') has-error @enderror">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input id="password" name="password" type="password" placeholder="12 caractères minimum" autocomplete="new-password" required autofocus>
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

        <button class="auth-submit" type="submit"><span>Enregistrer le mot de passe</span><i class="bi bi-check-lg" aria-hidden="true"></i></button>
    </form>

    <div class="auth-setup-link"><a href="{{ route('login') }}">Retour à la connexion <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
@endsection
