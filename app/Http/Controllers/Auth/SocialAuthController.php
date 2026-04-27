<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Fluxo OAuth Social Login:
 *  1. Usuário clica em "Entrar com Google/Microsoft"
 *  2. redirect() manda para o provedor
 *  3. provedor redireciona de volta para callback()
 *  4. callback() cria/encontra o usuário e faz login
 *  5. Se é o primeiro login → cria tenant automaticamente
 */
class SocialAuthController extends Controller
{
    // --------------------------------------------------------
    // Google
    // --------------------------------------------------------

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $socialUser = Socialite::driver('google')->user();
        return $this->loginOrRegister('google', $socialUser);
    }

    // --------------------------------------------------------
    // Microsoft
    // --------------------------------------------------------

    public function redirectToMicrosoft()
    {
        return Socialite::driver('azure')
            ->scopes(['openid', 'profile', 'email', 'offline_access'])
            ->redirect();
    }

    public function handleMicrosoftCallback()
    {
        $socialUser = Socialite::driver('azure')->user();
        return $this->loginOrRegister('microsoft', $socialUser);
    }

    // --------------------------------------------------------
    // Lógica compartilhada
    // --------------------------------------------------------

    private function loginOrRegister(string $provider, $socialUser)
    {
        // Busca por provider_id primeiro (mais preciso) depois por e-mail
        $user = User::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first()
            ?? User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Atualiza avatar e provider_id caso tenha mudado
            $user->update([
                'avatar'      => $socialUser->getAvatar(),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        } else {
            // Primeiro login: cria usuário e tenant pessoal automaticamente
            $user = User::create([
                'name'        => $socialUser->getName(),
                'email'       => $socialUser->getEmail(),
                'avatar'      => $socialUser->getAvatar(),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            $tenant = Tenant::create([
                'name' => $socialUser->getName()."'s Workspace",
            ]);

            $tenant->users()->attach($user->id, ['role' => 'admin']);
            $user->update(['current_tenant_id' => $tenant->id]);
        }

        // Se ainda não tem tenant ativo, define o primeiro disponível
        if (! $user->current_tenant_id) {
            $firstTenant = $user->tenants()->first();
            if ($firstTenant) {
                $user->update(['current_tenant_id' => $firstTenant->id]);
            }
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }
}
