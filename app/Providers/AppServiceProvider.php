<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::transliterate(Str::lower($request->string('email')->toString()));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
            $expiresInMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Réinitialisation de votre mot de passe QuincaPro')
                ->greeting('Bonjour '.$notifiable->name.',')
                ->line('Vous recevez ce courriel car une réinitialisation du mot de passe a été demandée pour votre compte QuincaPro.')
                ->action('Choisir un nouveau mot de passe', $resetUrl)
                ->line("Ce lien expire dans {$expiresInMinutes} minutes et ne peut servir qu’une seule fois.")
                ->line('Si vous n’êtes pas à l’origine de cette demande, ignorez ce message : votre mot de passe reste inchangé.')
                ->salutation('L’équipe QuincaPro');
        });

        Schema::defaultStringLength(191);

        Paginator::useBootstrapFive();
    }
}
