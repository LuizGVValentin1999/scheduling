<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Painel administrativo — acesso restrito a usuários com role=admin no tenant atual.
 * Autorização feita via middleware no route group.
 */
class AdminController extends Controller
{
    public function dashboard(): View
    {
        $tenant = auth()->user()->currentTenant;

        $stats = [
            'total_users'    => $tenant->users()->count(),
            'total_schedules'=> Schedule::count(), // tenant scope ativo
            'total_clients'  => \App\Models\Client::count(),
        ];

        $users = $tenant->users()->withPivot('role')->get();

        return view('admin.dashboard', compact('tenant', 'stats', 'users'));
    }

    public function users(): View
    {
        $tenant = auth()->user()->currentTenant;
        $users  = $tenant->users()->withPivot('role')->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Altera o papel de um usuário no tenant atual.
     * PATCH /admin/users/{user}/role
     */
    public function updateUserRole(User $user, Request $request): RedirectResponse
    {
        $data = $request->validate(['role' => 'required|in:admin,member']);

        auth()->user()->currentTenant
              ->users()
              ->updateExistingPivot($user->id, ['role' => $data['role']]);

        return back()->with('success', 'Papel atualizado.');
    }

    /**
     * Remove usuário do tenant.
     * DELETE /admin/users/{user}
     */
    public function removeUser(User $user): RedirectResponse
    {
        // Impede remoção de si mesmo
        abort_if($user->id === auth()->id(), 403, 'Você não pode remover a si mesmo.');

        auth()->user()->currentTenant->users()->detach($user->id);

        return back()->with('success', "{$user->name} removido do workspace.");
    }

    /**
     * Convida usuário pelo e-mail (cria se não existir).
     * POST /admin/users/invite
     */
    public function inviteUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'role'  => 'required|in:admin,member',
        ]);

        $tenant = auth()->user()->currentTenant;

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            ['name'  => explode('@', $data['email'])[0]]
        );

        // Adiciona ao tenant se ainda não pertence
        if (! $tenant->users()->where('users.id', $user->id)->exists()) {
            $tenant->users()->attach($user->id, ['role' => $data['role']]);
        }

        return back()->with('success', "{$user->email} adicionado ao workspace.");
    }
}
