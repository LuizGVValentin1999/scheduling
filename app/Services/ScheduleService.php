<?php

namespace App\Services;

use App\Models\PublicBookingLink;
use App\Models\Schedule;
use App\Models\ScheduleShare;
use App\Models\User;
use Illuminate\Support\Collection;

class ScheduleService
{
    /**
     * Retorna as agendas visíveis para o usuário:
     *  - agendas próprias
     *  - agendas compartilhadas com ele
     *  - se admin: todas as agendas do tenant
     */
    public function visibleFor(User $user): Collection
    {
        if ($user->isAdminOfCurrentTenant()) {
            return Schedule::with('user')->get();
        }

        $ownIds    = $user->schedules()->pluck('id');
        $sharedIds = $user->sharedSchedules()->pluck('schedules.id');
        $ids       = $ownIds->merge($sharedIds)->unique();

        return Schedule::with('user')->whereIn('id', $ids)->get();
    }

    public function create(User $user, array $data): Schedule
    {
        $data['user_id'] = $user->id;
        return Schedule::create($data);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update($data);
        return $schedule->fresh();
    }

    public function delete(Schedule $schedule): void
    {
        $schedule->delete();
    }

    // --------------------------------------------------------
    // Compartilhamento
    // --------------------------------------------------------

    public function share(Schedule $schedule, User $targetUser, string $permission): ScheduleShare
    {
        return ScheduleShare::updateOrCreate(
            ['schedule_id' => $schedule->id, 'shared_with_user_id' => $targetUser->id],
            ['permission'  => $permission]
        );
    }

    public function unshare(Schedule $schedule, User $targetUser): void
    {
        ScheduleShare::where('schedule_id', $schedule->id)
                     ->where('shared_with_user_id', $targetUser->id)
                     ->delete();
    }

    // --------------------------------------------------------
    // Links públicos
    // --------------------------------------------------------

    public function createPublicLink(Schedule $schedule, array $data): PublicBookingLink
    {
        return $schedule->publicLinks()->create($data);
    }

    public function revokePublicLink(PublicBookingLink $link): void
    {
        $link->update(['is_active' => false]);
    }
}
