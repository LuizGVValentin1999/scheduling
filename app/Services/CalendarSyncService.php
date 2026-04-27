<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CalendarIntegration;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendarSyncService
{
    public function syncAppointment(Appointment $appointment, string $action): void
    {
        $schedule = $appointment->schedule;
        $owner    = $schedule->user;

        foreach ($owner->calendarIntegrations()->where('is_active', true)->get() as $integration) {
            try {
                match ($integration->provider) {
                    'google'  => $this->syncGoogle($integration, $appointment, $action),
                    'outlook' => $this->syncOutlook($integration, $appointment, $action),
                    default   => null,
                };
            } catch (\Throwable $e) {
                Log::warning("Calendar sync failed [{$integration->provider}]", [
                    'appointment' => $appointment->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    // --------------------------------------------------------
    // Google Calendar
    // --------------------------------------------------------

    private function syncGoogle(CalendarIntegration $integration, Appointment $appointment, string $action): void
    {
        $client = new GoogleClient();
        $client->setAccessToken($integration->access_token);

        if ($client->isAccessTokenExpired() && $integration->refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($integration->refresh_token);
            $newToken = $client->getAccessToken();
            $integration->update([
                'access_token'     => $newToken['access_token'],
                'token_expires_at' => now()->addSeconds($newToken['expires_in']),
            ]);
        }

        $service    = new GoogleCalendar($client);
        $calendarId = $integration->calendar_id ?? 'primary';

        match ($action) {
            'create' => $this->googleCreate($service, $calendarId, $appointment, $integration),
            'update' => $this->googleUpdate($service, $calendarId, $appointment),
            'delete' => $this->googleDelete($service, $calendarId, $appointment),
        };
    }

    private function googleCreate(GoogleCalendar $service, string $calendarId, Appointment $appointment, CalendarIntegration $integration): void
    {
        $event = new GoogleEvent([
            'summary'     => $appointment->title,
            'description' => $appointment->description,
            'start'       => new EventDateTime(['dateTime' => $appointment->starts_at->toRfc3339String()]),
            'end'         => new EventDateTime(['dateTime' => $appointment->ends_at->toRfc3339String()]),
        ]);

        $created = $service->events->insert($calendarId, $event);
        $appointment->update(['google_event_id' => $created->getId()]);
    }

    private function googleUpdate(GoogleCalendar $service, string $calendarId, Appointment $appointment): void
    {
        if (! $appointment->google_event_id) {
            return;
        }

        $event = $service->events->get($calendarId, $appointment->google_event_id);
        $event->setSummary($appointment->title);
        $event->setStart(new EventDateTime(['dateTime' => $appointment->starts_at->toRfc3339String()]));
        $event->setEnd(new EventDateTime(['dateTime' => $appointment->ends_at->toRfc3339String()]));

        $service->events->update($calendarId, $appointment->google_event_id, $event);
    }

    private function googleDelete(GoogleCalendar $service, string $calendarId, Appointment $appointment): void
    {
        if ($appointment->google_event_id) {
            $service->events->delete($calendarId, $appointment->google_event_id);
        }
    }

    // --------------------------------------------------------
    // Microsoft Outlook (via Graph API)
    // --------------------------------------------------------

    private function syncOutlook(CalendarIntegration $integration, Appointment $appointment, string $action): void
    {
        $token = $this->refreshOutlookTokenIfNeeded($integration);

        match ($action) {
            'create' => $this->outlookCreate($token, $appointment, $integration),
            'update' => $this->outlookUpdate($token, $appointment),
            'delete' => $this->outlookDelete($token, $appointment),
        };
    }

    private function outlookCreate(string $token, Appointment $appointment, CalendarIntegration $integration): void
    {
        $response = Http::withToken($token)->post('https://graph.microsoft.com/v1.0/me/events', [
            'subject' => $appointment->title,
            'body'    => ['contentType' => 'text', 'content' => $appointment->description ?? ''],
            'start'   => ['dateTime' => $appointment->starts_at->toIso8601String(), 'timeZone' => 'UTC'],
            'end'     => ['dateTime' => $appointment->ends_at->toIso8601String(), 'timeZone' => 'UTC'],
        ]);

        if ($response->successful()) {
            $appointment->update(['outlook_event_id' => $response->json('id')]);
        }
    }

    private function outlookUpdate(string $token, Appointment $appointment): void
    {
        if (! $appointment->outlook_event_id) {
            return;
        }

        Http::withToken($token)->patch("https://graph.microsoft.com/v1.0/me/events/{$appointment->outlook_event_id}", [
            'subject' => $appointment->title,
            'start'   => ['dateTime' => $appointment->starts_at->toIso8601String(), 'timeZone' => 'UTC'],
            'end'     => ['dateTime' => $appointment->ends_at->toIso8601String(), 'timeZone' => 'UTC'],
        ]);
    }

    private function outlookDelete(string $token, Appointment $appointment): void
    {
        if ($appointment->outlook_event_id) {
            Http::withToken($token)->delete("https://graph.microsoft.com/v1.0/me/events/{$appointment->outlook_event_id}");
        }
    }

    private function refreshOutlookTokenIfNeeded(CalendarIntegration $integration): string
    {
        if (! $integration->isExpired()) {
            return $integration->access_token;
        }

        $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'grant_type'    => 'refresh_token',
            'client_id'     => config('services.azure.client_id'),
            'client_secret' => config('services.azure.client_secret'),
            'refresh_token' => $integration->refresh_token,
            'scope'         => 'offline_access Calendars.ReadWrite',
        ]);

        $data = $response->json();
        $integration->update([
            'access_token'     => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }
}
