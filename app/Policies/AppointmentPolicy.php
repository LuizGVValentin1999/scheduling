<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Pode ver um agendamento se:
     * - é dono da agenda
     * - é admin do tenant
     * - a agenda foi compartilhada com ele (view ou edit)
     */
    public function view(User $user, Appointment $appointment): bool
    {
        $schedule = $appointment->schedule;

        return $user->id === $schedule->user_id
            || $user->isAdminOfCurrentTenant()
            || $schedule->isSharedWith($user);
    }

    /**
     * Pode criar/editar agendamentos se:
     * - é dono da agenda
     * - é admin do tenant
     * - a agenda foi compartilhada com permissão "edit"
     */
    public function create(User $user, \App\Models\Schedule $schedule): bool
    {
        return $user->id === $schedule->user_id
            || $user->isAdminOfCurrentTenant()
            || $schedule->permissionFor($user) === 'edit';
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->create($user, $appointment->schedule);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->schedule->user_id
            || $user->isAdminOfCurrentTenant();
    }
}
