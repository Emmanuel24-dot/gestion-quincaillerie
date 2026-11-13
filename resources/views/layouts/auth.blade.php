<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f4b70">
    <title>@yield('title') · QuincaPro</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/quincapro-icon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/quincapro-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    @vite(['resources/css/icons.css', 'resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-visual" aria-label="Bienvenue chez QuincaPro">
            <img class="auth-visual-image" src="{{ asset('images/quincapro-login-hero.jpg') }}" alt="Rayons bien organisés d’une quincaillerie" fetchpriority="high">
            <div class="auth-visual-shade"></div>
            <div class="auth-visual-content">
                <a class="auth-brand" href="{{ route('login') }}" aria-label="QuincaPro, connexion">
                    <span class="auth-brand-icon"><img src="{{ asset('images/quincapro-icon.svg') }}" alt=""></span>
                    <span>Quinca<span>Pro</span></span>
                </a>

                <div class="auth-welcome-copy">
                    <span class="auth-eyebrow"><span></span> @yield('visual_eyebrow', 'GESTION COMMERCIALE')</span>
                    <h1>@yield('visual_title')</h1>
                    <p>@yield('visual_text')</p>
                </div>

                <div class="auth-visual-footer">
                    <div class="auth-feature-icon"><i class="bi @yield('visual_feature_icon', 'bi-box-seam')" aria-hidden="true"></i></div>
                    <div><strong>@yield('visual_feature_title')</strong><span>@yield('visual_feature_text')</span></div>
                    <i class="bi bi-arrow-up-right auth-feature-arrow" aria-hidden="true"></i>
                </div>
            </div>
            <span class="auth-image-caption"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Votre espace de gestion</span>
        </section>

        <section class="auth-form-panel" aria-labelledby="auth-title">
            <div class="auth-form-inner">
                <div class="auth-mobile-brand">
                    <span class="auth-brand-icon"><img src="{{ asset('images/quincapro-icon.svg') }}" alt=""></span>
                    <span>Quinca<span>Pro</span></span>
                </div>

                <button class="auth-install-button" type="button" data-pwa-install hidden>
                    <i class="bi bi-download" aria-hidden="true"></i> Installer QuincaPro
                </button>

                <div class="auth-form-heading">
                    <span class="auth-kicker">@yield('kicker')</span>
                    <h2 id="auth-title">@yield('heading')</h2>
                    <p>@yield('intro')</p>
                </div>

                @if (session('status'))
                    <div class="auth-success" role="status">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-error" role="alert">
                        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @yield('content')

                <footer class="auth-copyright">© {{ now()->year }} QuincaPro <span>·</span> Gestion de votre activité</footer>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const password = button.closest('.auth-input-wrap').querySelector('input');
                const isVisible = password.type === 'text';
                password.type = isVisible ? 'password' : 'text';
                button.setAttribute('aria-pressed', String(!isVisible));
                button.setAttribute('aria-label', isVisible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
                button.innerHTML = `<i class="bi bi-eye${isVisible ? '' : '-slash'}" aria-hidden="true"></i>`;
            });
        });
    </script>
</body>
</html>
