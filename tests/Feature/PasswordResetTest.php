<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected bool $actsAsAdmin = false;

    public function test_login_page_links_to_the_forgot_password_page(): void
    {
        User::factory()->admin()->create();

        $this->get(route('login'))->assertOk()->assertSee(route('password.request'));
        $this->get(route('password.request'))->assertOk()->assertSee('Envoyer le lien');
    }

    public function test_reset_link_is_emailed_in_french_to_an_existing_account(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'awa@example.test', 'name' => 'Awa']);

        $this->post(route('password.email'), ['email' => 'awa@example.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Réinitialisation de votre mot de passe QuincaPro'
                && str_contains($mail->actionUrl, route('password.reset', ['token' => $notification->token], false));
        });
    }

    public function test_unknown_email_gets_the_same_answer_without_revealing_anything(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'inconnu@example.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_a_valid_token_then_used_to_log_in(): void
    {
        $user = User::factory()->create(['email' => 'awa@example.test']);
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Nouveau mot de passe');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NouveauSecret2026',
            'password_confirmation' => 'NouveauSecret2026',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('NouveauSecret2026', $user->fresh()->password));

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'NouveauSecret2026'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_invalid_token_and_weak_password_are_rejected(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->post(route('password.update'), [
            'token' => 'jeton-invalide',
            'email' => $user->email,
            'password' => 'NouveauSecret2026',
            'password_confirmation' => 'NouveauSecret2026',
        ])->assertSessionHasErrors('email');

        $this->post(route('password.update'), [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => 'faible',
            'password_confirmation' => 'faible',
        ])->assertSessionHasErrors('password');

        $this->assertSame($originalPassword, $user->fresh()->password);
    }

    public function test_logged_in_users_are_redirected_away_from_reset_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('password.request'))->assertRedirect(route('dashboard'));
    }
}
