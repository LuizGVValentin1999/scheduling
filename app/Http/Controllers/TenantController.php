<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function create(): View
    {
        return view('tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $tenant = Tenant::create($data);
        $tenant->users()->attach(auth()->id(), ['role' => 'admin']);

        auth()->user()->update(['current_tenant_id' => $tenant->id]);

        return redirect()->route('dashboard')
                         ->with('success', "Workspace \"{$tenant->name}\" criado!");
    }

    /**
     * Troca o tenant ativo na sessão do usuário.
     */
    public function switch(Tenant $tenant): RedirectResponse
    {
        $user = auth()->user();

        // Garante que o usuário pertence ao tenant solicitado
        abort_unless($user->tenants()->where('tenants.id', $tenant->id)->exists(), 403);

        $user->update(['current_tenant_id' => $tenant->id]);

        return redirect()->route('dashboard')
                         ->with('success', "Você entrou no workspace \"{$tenant->name}\".");
    }
}
