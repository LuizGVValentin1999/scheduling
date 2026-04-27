<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function view(User $user, Schedule $schedule): bool
    {
        return $user->id === $schedule->user_id
            || $user->isAdminOfCurrentTenant()
            || $schedule->isSharedWith($user);
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->id === $schedule->user_id
            || $user->isAdminOfCurrentTenant();
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->update($user, $schedule);
    }

    public function share(User $user, Schedule $schedule): bool
    {
        return $this->update($user, $schedule);
    }
}
