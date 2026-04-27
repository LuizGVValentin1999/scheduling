<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\PublicBookingLink;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Desliga verificação de FK durante seed conforme o driver
        match (config('database.default')) {
            'pgsql'  => DB::statement('SET session_replication_role = replica;'),
            'mysql'  => DB::statement('SET FOREIGN_KEY_CHECKS=0;'),
            default  => null, // SQLite não precisa
        };

        // -------------------------------------------------------
        // Tenant: Barbearia Demo
        // -------------------------------------------------------
        $barbershop = Tenant::create([
            'name' => 'Barbearia do João',
            'slug' => 'barbearia-do-joao',
            'plan' => 'pro',
            'settings' => ['timezone' => 'America/Sao_Paulo'],
        ]);

        $admin = User::create([
            'name'              => 'João Admin',
            'email'             => 'admin@barbearia.demo',
            'provider'          => 'google',
            'provider_id'       => 'demo_admin_001',
            'avatar'            => 'https://ui-avatars.com/api/?name=Joao&background=7c3aed&color=fff',
            'current_tenant_id' => $barbershop->id,
        ]);
        $barbershop->users()->attach($admin->id, ['role' => 'admin']);

        $member = User::create([
            'name'              => 'Maria Barbeira',
            'email'             => 'maria@barbearia.demo',
            'provider'          => 'microsoft',
            'provider_id'       => 'demo_member_002',
            'avatar'            => 'https://ui-avatars.com/api/?name=Maria&background=0ea5e9&color=fff',
            'current_tenant_id' => $barbershop->id,
        ]);
        $barbershop->users()->attach($member->id, ['role' => 'member']);

        // -------------------------------------------------------
        // Agenda principal do Admin
        // -------------------------------------------------------
        $schedule = Schedule::withoutGlobalScopes()->create([
            'tenant_id'     => $barbershop->id,
            'user_id'       => $admin->id,
            'name'          => 'Agenda João',
            'slot_duration' => 45,
            'is_public'     => true,
            'working_hours' => [
                'mon' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
                'tue' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
                'wed' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
                'thu' => ['active' => true,  'start' => '09:00', 'end' => '18:00'],
                'fri' => ['active' => true,  'start' => '09:00', 'end' => '17:00'],
                'sat' => ['active' => true,  'start' => '09:00', 'end' => '13:00'],
                'sun' => ['active' => false, 'start' => null,    'end' => null   ],
            ],
        ]);

        // -------------------------------------------------------
        // Clientes
        // -------------------------------------------------------
        $clients = collect([
            ['name' => 'Carlos Oliveira', 'email' => 'carlos@email.com', 'phone' => '(11)99999-0001'],
            ['name' => 'Ana Souza',       'email' => 'ana@email.com',    'phone' => '(11)99999-0002'],
            ['name' => 'Pedro Costa',     'email' => 'pedro@email.com',  'phone' => '(11)99999-0003'],
        ])->map(fn($d) => Client::withoutGlobalScopes()->create(
            array_merge($d, ['tenant_id' => $barbershop->id])
        ));

        // -------------------------------------------------------
        // Agendamentos para os próximos 7 dias
        // -------------------------------------------------------
        $statuses = ['confirmed', 'confirmed', 'pending', 'cancelled', 'confirmed'];

        for ($day = 0; $day < 7; $day++) {
            $client = $clients->get($day % $clients->count());
            $base   = now()->startOfDay()->addDays($day);

            Appointment::withoutGlobalScopes()->create([
                'tenant_id'        => $barbershop->id,
                'schedule_id'      => $schedule->id,
                'client_id'        => $client->id,
                'title'            => "Corte — {$client->name}",
                'starts_at'        => $base->copy()->addHours(10),
                'ends_at'          => $base->copy()->addHours(10)->addMinutes(45),
                'duration_minutes' => 45,
                'status'           => $statuses[$day % count($statuses)],
            ]);

            Appointment::withoutGlobalScopes()->create([
                'tenant_id'        => $barbershop->id,
                'schedule_id'      => $schedule->id,
                'client_name'      => 'Walk-in',
                'title'            => 'Barba e cabelo',
                'starts_at'        => $base->copy()->addHours(14),
                'ends_at'          => $base->copy()->addHours(14)->addMinutes(45),
                'duration_minutes' => 45,
                'status'           => 'confirmed',
            ]);
        }

        // -------------------------------------------------------
        // Link público
        // -------------------------------------------------------
        PublicBookingLink::create([
            'schedule_id' => $schedule->id,
            'label'       => 'Agendar na Barbearia do João',
            'settings'    => ['primary_color' => '#7c3aed', 'title' => 'Agendar Horário'],
            'is_active'   => true,
        ]);

        // -------------------------------------------------------
        // Tenant: Clínica Demo
        // -------------------------------------------------------
        $clinic = Tenant::create(['name' => 'Clínica Saúde', 'slug' => 'clinica-saude', 'plan' => 'free']);
        $dr     = User::create([
            'name'              => 'Dra. Beatriz',
            'email'             => 'beatriz@clinica.demo',
            'provider'          => 'google',
            'provider_id'       => 'demo_clinic_001',
            'avatar'            => 'https://ui-avatars.com/api/?name=Beatriz&background=10b981&color=fff',
            'current_tenant_id' => $clinic->id,
        ]);
        $clinic->users()->attach($dr->id, ['role' => 'admin']);

        match (config('database.default')) {
            'pgsql'  => DB::statement('SET session_replication_role = DEFAULT;'),
            'mysql'  => DB::statement('SET FOREIGN_KEY_CHECKS=1;'),
            default  => null,
        };

        $this->command->info('✅ Seeds criados!');
        $this->command->table(['Tenant', 'E-mail', 'Role'], [
            [$barbershop->name, $admin->email,  'admin'],
            [$barbershop->name, $member->email, 'member'],
            [$clinic->name,     $dr->email,     'admin'],
        ]);
    }
}
