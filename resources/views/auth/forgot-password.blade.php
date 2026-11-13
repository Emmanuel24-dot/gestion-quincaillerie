@extends('layouts.auth')

@section('title', 'Mot de passe oublié')
@section('visual_eyebrow', 'ACCÈS AU COMPTE')
@section('visual_title')Pas de panique,<br>on s’en occupe.@endsection
@section('visual_text', 'Recevez un lien sécurisé par courriel pour choisir un nouveau mot de passe.')
@section('visual_feature_icon', 'bi-envelope-check')
@section('visual_feature_title', 'Lien valable 60 minutes')
@section('visual_feature_text', 'Il ne peut être utilisé qu’une seule fois.')
@section('kicker', 'MOT DE PASSE OUBLIÉ')
@section('heading', 'Réinitialiser')
@section('intro', 'Indiquez l’adresse courriel de votre compte. Nous vous enverrons un lien pour choisir un nouveau mot de passe.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="email">Adresse courriel</label>
            <div class="auth-input-wrap @error('email') has-error @enderror">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nom@exemple.com" autocomplete="username" required autofocus>
            </div>
        </div>

        <button class="auth-submit" type="submit"><span>Envoyer le lien</span><i class="bi bi-send" aria-hidden="true"></i></button>
    </form>

    <div class="auth-setup-link"><span>Vous vous en souvenez ?</span><a href="{{ route('login') }}">Retour à la connexion <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
@endsection
