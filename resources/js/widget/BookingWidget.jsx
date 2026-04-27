/**
 * Widget de Agendamento Público — build standalone.
 *
 * Pode ser embutido em qualquer site de duas formas:
 *
 * 1. Via script + div:
 *    <div id="scheduling-widget"
 *         data-token="SEU_TOKEN"
 *         data-base-url="https://seuapp.com">
 *    </div>
 *    <script src="https://seuapp.com/widget/scheduling-widget.iife.js"></script>
 *
 * 2. Via iframe:
 *    <iframe src="https://seuapp.com/book/SEU_TOKEN?embed=1"
 *            style="width:100%; height:600px; border:none;">
 *    </iframe>
 *
 * O widget se auto-instala ao ser incluído na página (auto-init).
 * Configurações de cores são lidas do atributo data-settings (JSON).
 */

import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
    ThemeProvider, createTheme,
    Box, Typography, Button, TextField, Stack,
    Stepper, Step, StepLabel, Alert, CircularProgress,
    Paper, Chip,
} from '@mui/material';
import { LocalizationProvider, DateCalendar, TimeClock } from '@mui/x-date-pickers';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFnsV3';
import { ptBR } from 'date-fns/locale';
import { format, addMinutes, isSameDay, parseISO } from 'date-fns';

const STEPS = ['Escolha o dia', 'Escolha o horário', 'Seus dados', 'Confirmação'];

// -------------------------------------------------------
// Widget principal
// -------------------------------------------------------
function BookingWidget({ token, baseUrl, settings = {} }) {
    const primaryColor = settings.primary_color ?? '#7c3aed';
    const title        = settings.title          ?? 'Agendar';

    const theme = createTheme({
        palette: { primary: { main: primaryColor } },
        shape:   { borderRadius: 10 },
        components: {
            MuiButton: {
                defaultProps: { disableElevation: true },
                styleOverrides: { root: { textTransform: 'none', fontWeight: 600 } },
            },
        },
    });

    const [step, setStep]             = useState(0);
    const [schedule, setSchedule]     = useState(null);
    const [slots, setSlots]           = useState([]);
    const [selectedDate, setSelectedDate] = useState(null);
    const [selectedSlot, setSelectedSlot] = useState(null);
    const [form, setForm]             = useState({ name: '', email: '', phone: '' });
    const [loading, setLoading]       = useState(false);
    const [error, setError]           = useState('');
    const [success, setSuccess]       = useState(false);

    // Carrega dados da agenda ao montar
    useEffect(() => {
        if (!token || !baseUrl) return;
        setLoading(true);
        fetch(`${baseUrl}/book/${token}`, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(data => setSchedule(data.schedule))
            .catch(() => setError('Não foi possível carregar a agenda.'))
            .finally(() => setLoading(false));
    }, [token, baseUrl]);

    // Gera slots disponíveis para a data selecionada
    useEffect(() => {
        if (!selectedDate || !schedule) return;

        const dayKey = format(selectedDate, 'EEE').toLowerCase(); // mon, tue, etc.
        const hours  = schedule.working_hours?.[dayKey];

        if (!hours?.active) { setSlots([]); return; }

        const generated = [];
        const [sh, sm]  = hours.start.split(':').map(Number);
        const [eh, em]  = hours.end.split(':').map(Number);
        let   cursor    = new Date(selectedDate);
        cursor.setHours(sh, sm, 0, 0);
        const end = new Date(selectedDate);
        end.setHours(eh, em, 0, 0);

        while (cursor < end) {
            generated.push(new Date(cursor));
            cursor = addMinutes(cursor, schedule.slot_duration ?? 60);
        }

        setSlots(generated);
    }, [selectedDate, schedule]);

    // -------------------------------------------------------
    // Submissão
    // -------------------------------------------------------
    const handleConfirm = async () => {
        setLoading(true);
        setError('');

        const starts = selectedSlot;
        const ends   = addMinutes(starts, schedule.slot_duration ?? 60);

        try {
            const res = await fetch(`${baseUrl}/book/${token}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                },
                credentials: 'include',
                body: JSON.stringify({
                    client_name:  form.name,
                    client_email: form.email,
                    client_phone: form.phone,
                    starts_at:    starts.toISOString(),
                    ends_at:      ends.toISOString(),
                }),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                throw new Error(data.message ?? 'Erro ao confirmar agendamento.');
            }

            setSuccess(true);
            setStep(3);
        } catch (e) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    };

    const getCsrf = () =>
        document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const canNext = () => {
        if (step === 0) return Boolean(selectedDate);
        if (step === 1) return Boolean(selectedSlot);
        if (step === 2) return form.name.trim() && form.email.trim();
        return false;
    };

    // -------------------------------------------------------
    // Render
    // -------------------------------------------------------
    if (loading && !schedule) {
        return (
            <Box display="flex" justifyContent="center" p={4}>
                <CircularProgress />
            </Box>
        );
    }

    if (error && !schedule) {
        return <Alert severity="error">{error}</Alert>;
    }

    return (
        <ThemeProvider theme={theme}>
            <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={ptBR}>
                <Paper elevation={3} sx={{ p: 3, maxWidth: 480, mx: 'auto', borderRadius: 3 }}>

                    <Typography variant="h6" fontWeight={700} mb={2} color="primary">
                        📅 {title}
                    </Typography>

                    {schedule && (
                        <Typography variant="body2" color="text.secondary" mb={2}>
                            {schedule.name} · {schedule.user?.name}
                        </Typography>
                    )}

                    <Stepper activeStep={step} alternativeLabel sx={{ mb: 3 }}>
                        {STEPS.map(label => (
                            <Step key={label}>
                                <StepLabel>{label}</StepLabel>
                            </Step>
                        ))}
                    </Stepper>

                    {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}

                    {/* Step 0 — Selecionar data */}
                    {step === 0 && (
                        <Box display="flex" justifyContent="center">
                            <DateCalendar
                                value={selectedDate}
                                onChange={setSelectedDate}
                                disablePast
                            />
                        </Box>
                    )}

                    {/* Step 1 — Selecionar horário */}
                    {step === 1 && (
                        <Box>
                            <Typography variant="body2" color="text.secondary" mb={2}>
                                {selectedDate && format(selectedDate, "EEEE, d 'de' MMMM", { locale: ptBR })}
                            </Typography>
                            {slots.length === 0 ? (
                                <Alert severity="info">Nenhum horário disponível neste dia.</Alert>
                            ) : (
                                <Box display="flex" flexWrap="wrap" gap={1}>
                                    {slots.map(slot => (
                                        <Chip
                                            key={slot.toISOString()}
                                            label={format(slot, 'HH:mm')}
                                            color={selectedSlot?.toISOString() === slot.toISOString() ? 'primary' : 'default'}
                                            variant={selectedSlot?.toISOString() === slot.toISOString() ? 'filled' : 'outlined'}
                                            onClick={() => setSelectedSlot(slot)}
                                            sx={{ cursor: 'pointer' }}
                                        />
                                    ))}
                                </Box>
                            )}
                        </Box>
                    )}

                    {/* Step 2 — Dados do cliente */}
                    {step === 2 && (
                        <Stack spacing={2}>
                            <TextField
                                label="Seu nome *"
                                value={form.name}
                                onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                                size="small" fullWidth
                            />
                            <TextField
                                label="E-mail *"
                                type="email"
                                value={form.email}
                                onChange={e => setForm(f => ({ ...f, email: e.target.value }))}
                                size="small" fullWidth
                            />
                            <TextField
                                label="Telefone"
                                value={form.phone}
                                onChange={e => setForm(f => ({ ...f, phone: e.target.value }))}
                                size="small" fullWidth
                            />
                        </Stack>
                    )}

                    {/* Step 3 — Confirmação */}
                    {step === 3 && success && (
                        <Box textAlign="center" py={2}>
                            <Typography variant="h2" mb={1}>✅</Typography>
                            <Typography variant="h6" fontWeight={700} mb={1}>
                                Agendamento confirmado!
                            </Typography>
                            <Typography variant="body2" color="text.secondary">
                                {format(selectedSlot, "d 'de' MMMM 'às' HH:mm", { locale: ptBR })}
                            </Typography>
                            <Typography variant="body2" color="text.secondary" mt={1}>
                                Você receberá uma confirmação em {form.email}
                            </Typography>
                        </Box>
                    )}

                    {/* Navegação */}
                    {step < 3 && (
                        <Stack direction="row" justifyContent="space-between" mt={3}>
                            <Button
                                onClick={() => setStep(s => s - 1)}
                                disabled={step === 0 || loading}
                                variant="outlined"
                            >
                                Voltar
                            </Button>
                            <Button
                                onClick={step === 2 ? handleConfirm : () => setStep(s => s + 1)}
                                disabled={!canNext() || loading}
                                variant="contained"
                                startIcon={loading ? <CircularProgress size={16} /> : null}
                            >
                                {step === 2 ? 'Confirmar' : 'Próximo'}
                            </Button>
                        </Stack>
                    )}

                </Paper>
            </LocalizationProvider>
        </ThemeProvider>
    );
}

// -------------------------------------------------------
// Auto-inicialização quando o script é carregado
// -------------------------------------------------------
function init() {
    const container = document.getElementById('scheduling-widget');
    if (!container) return;

    const token    = container.dataset.token   ?? '';
    const baseUrl  = container.dataset.baseUrl ?? window.location.origin;
    const settings = JSON.parse(container.dataset.settings ?? '{}');

    createRoot(container).render(
        <BookingWidget token={token} baseUrl={baseUrl} settings={settings} />
    );
}

// Suporta tanto DOMContentLoaded quanto carregamento tardio
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

export default BookingWidget;
