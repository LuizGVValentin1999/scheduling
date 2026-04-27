/**
 * Componente Calendar — montado em schedules/show.blade.php
 *
 * Usa FullCalendar para exibir os agendamentos.
 * Comunicação com Laravel via axios (rota /api/schedules/{id}/appointments).
 * Abre modal MUI para criar/editar agendamentos.
 */

import React, { useCallback, useEffect, useRef, useState } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

import AppointmentModal from './AppointmentModal';

// Mapeia status → cor no calendário
const STATUS_COLORS = {
    confirmed: '#10b981',
    pending:   '#f59e0b',
    cancelled: '#ef4444',
};

export default function Calendar({ scheduleId, canEdit, csrf }) {
    const calendarRef = useRef(null);
    const [modal, setModal]               = useState({ open: false, appointment: null, defaultStart: null });
    const [loading, setLoading]           = useState(false);

    // Botão externo "Novo Agendamento" dispara este evento
    useEffect(() => {
        const handler = () => {
            if (!canEdit) return;
            const now = new Date();
            now.setMinutes(0, 0, 0);
            const pad = n => String(n).padStart(2, '0');
            const localStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:00:00`;
            setModal({ open: true, appointment: null, defaultStart: localStr });
        };
        window.addEventListener('calendar:new-appointment', handler);
        return () => window.removeEventListener('calendar:new-appointment', handler);
    }, [canEdit]);

    // -------------------------------------------------------
    // Carrega eventos do servidor para o range visível
    // -------------------------------------------------------
    const fetchEvents = useCallback(async (fetchInfo, successCallback, failureCallback) => {
        try {
            const res = await window.axios.get(`/api/schedules/${scheduleId}/appointments`, {
                params: {
                    start: fetchInfo.startStr,
                    end:   fetchInfo.endStr,
                },
            });

            const events = res.data.map(appt => ({
                id:              String(appt.id),
                title:           appt.title,
                start:           appt.starts_at,
                end:             appt.ends_at,
                backgroundColor: STATUS_COLORS[appt.status] ?? '#6c7086',
                borderColor:     STATUS_COLORS[appt.status] ?? '#6c7086',
                extendedProps:   appt,
            }));

            successCallback(events);
        } catch (err) {
            failureCallback(err);
        }
    }, [scheduleId]);

    // -------------------------------------------------------
    // Handlers de interação
    // -------------------------------------------------------
    const handleDateClick = (info) => {
        if (!canEdit) return;
        setModal({ open: true, appointment: null, defaultStart: info.dateStr });
    };

    const handleEventClick = (info) => {
        setModal({ open: true, appointment: info.event.extendedProps, defaultStart: null });
    };

    // Drag & drop — atualiza horário via PATCH
    const handleEventDrop = async (info) => {
        if (!canEdit) { info.revert(); return; }

        try {
            await window.axios.put(`/api/appointments/${info.event.id}`, {
                starts_at: info.event.startStr,
                ends_at:   info.event.endStr || info.event.startStr,
                _method:   'PUT',
            });
        } catch {
            info.revert();
        }
    };

    const handleSaved = () => {
        setModal({ open: false, appointment: null, defaultStart: null });
        calendarRef.current?.getApi().refetchEvents();
    };

    // -------------------------------------------------------
    // Render
    // -------------------------------------------------------
    return (
        <div>
            <FullCalendar
                ref={calendarRef}
                plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                initialView="timeGridWeek"
                locale={ptBrLocale}
                headerToolbar={{
                    left:   'prev,next today',
                    center: 'title',
                    right:  'dayGridMonth,timeGridWeek,timeGridDay',
                }}
                height="auto"
                allDaySlot={false}
                slotMinTime="06:00:00"
                slotMaxTime="22:00:00"
                slotDuration="00:30:00"
                nowIndicator
                editable={canEdit}
                selectable={canEdit}
                events={fetchEvents}
                dateClick={handleDateClick}
                eventClick={handleEventClick}
                eventDrop={handleEventDrop}
                eventTimeFormat={{ hour: '2-digit', minute: '2-digit', meridiem: false }}
                buttonText={{ today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia' }}
            />

            <AppointmentModal
                open={modal.open}
                scheduleId={scheduleId}
                appointment={modal.appointment}
                defaultStart={modal.defaultStart}
                canEdit={canEdit}
                csrf={csrf}
                onClose={() => setModal({ open: false, appointment: null, defaultStart: null })}
                onSaved={handleSaved}
            />
        </div>
    );
}
