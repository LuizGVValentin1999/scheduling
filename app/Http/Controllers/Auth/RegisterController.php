<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'workspace_name'        => 'required|string|max:100',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // cast 'hashed' encripta automaticamente
        ]);

        $tenant = Tenant::create(['name' => $data['workspace_name']]);
        $tenant->users()->attach($user->id, ['role' => 'admin']);
        $user->update(['current_tenant_id' => $tenant->id]);

        Auth::login($user);

        return redirect()->route('dashboard')
                         ->with('success', 'Conta criada com sucesso! Bem-vindo.');
    }
}
