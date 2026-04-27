<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Policies\AppointmentPolicy;
use App\Policies\SchedulePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Policies registradas explicitamente
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Schedule::class,    SchedulePolicy::class);

        // Gate simples para verificar se o usuário é admin do tenant atual
        // Usado em: Route::middleware('can:admin')
        Gate::define('admin', fn($user) => $user->isAdminOfCurrentTenant());
    }
}
