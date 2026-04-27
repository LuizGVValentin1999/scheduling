/**
 * Modal MUI para criar / editar / excluir agendamentos.
 * Chamado pelo componente Calendar.
 */

import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogTitle, DialogContent, DialogActions,
    Button, TextField, MenuItem, Stack, Alert,
    CircularProgress, Divider, Typography, Chip,
} from '@mui/material';
import { DateTimePicker } from '@mui/x-date-pickers';
import { parseISO, addMinutes } from 'date-fns';

const STATUS_LABELS = {
    pending:   { label: 'Pendente',   color: 'warning' },
    confirmed: { label: 'Confirmado', color: 'success' },
    cancelled: { label: 'Cancelado',  color: 'error'   },
};

const EMPTY_FORM = {
    title:        '',
    description:  '',
    client_name:  '',
    client_email: '',
    starts_at:    null,
    ends_at:      null,
    status:       'pending',
};

export default function AppointmentModal({
    open, scheduleId, appointment, defaultStart, canEdit, csrf, onClose, onSaved,
}) {
    const isEdit = Boolean(appointment?.id);

    const [form, setForm]     = useState(EMPTY_FORM);
    const [error, setError]   = useState('');
    const [saving, setSaving] = useState(false);

    // -------------------------------------------------------
    // Inicializa o formulário ao abrir
    // -------------------------------------------------------
    useEffect(() => {
        if (!open) return;

        if (isEdit) {
            setForm({
                title:        appointment.title        ?? '',
                description:  appointment.description  ?? '',
                client_name:  appointment.client_name  ?? appointment.client?.name ?? '',
                client_email: appointment.client_email ?? appointment.client?.email ?? '',
                starts_at:    parseISO(appointment.starts_at),
                ends_at:      parseISO(appointment.ends_at),
                status:       appointment.status       ?? 'pending',
            });
        } else {
            const start = defaultStart ? parseISO(defaultStart) : new Date();
            setForm({ ...EMPTY_FORM, starts_at: start, ends_at: addMinutes(start, 60) });
        }

        setError('');
    }, [open, appointment, defaultStart]);

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    const set = (field) => (e) => setForm(f => ({ ...f, [field]: e?.target?.value ?? e }));

    const validate = () => {
        if (!form.title.trim())   return 'O título é obrigatório.';
        if (!form.starts_at)      return 'A data/hora de início é obrigatória.';
        if (!form.ends_at)        return 'A data/hora de término é obrigatória.';
        if (form.ends_at <= form.starts_at) return 'O término deve ser após o início.';
        return null;
    };

    // -------------------------------------------------------
    // Submissão
    // -------------------------------------------------------
    const handleSubmit = async () => {
        const err = validate();
        if (err) { setError(err); return; }

        setSaving(true);
        setError('');

        try {
            const payload = {
                ...form,
                starts_at: form.starts_at.toISOString(),
                ends_at:   form.ends_at.toISOString(),
            };

            if (isEdit) {
                await window.axios.put(`/api/appointments/${appointment.id}`, payload);
            } else {
                await window.axios.post(`/api/schedules/${scheduleId}/appointments`, payload);
            }

            onSaved();
        } catch (e) {
            const msg = e.response?.data?.message
                ?? Object.values(e.response?.data?.errors ?? {})[0]?.[0]
                ?? 'Erro ao salvar agendamento.';
            setError(msg);
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async () => {
        if (!confirm('Excluir este agendamento?')) return;
        setSaving(true);
        try {
            await window.axios.delete(`/api/appointments/${appointment.id}`);
            onSaved();
        } catch {
            setError('Erro ao excluir.');
        } finally {
            setSaving(false);
        }
    };

    const handleStatusChange = async (newStatus) => {
        if (!isEdit) return;
        setSaving(true);
        try {
            await window.axios.patch(`/api/appointments/${appointment.id}/status`, { status: newStatus });
            onSaved();
        } catch {
            setError('Erro ao atualizar status.');
            setSaving(false);
        }
    };

    // -------------------------------------------------------
    // Render
    // -------------------------------------------------------
    return (
        <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
            <DialogTitle sx={{ pb: 1 }}>
                {isEdit ? 'Editar agendamento' : 'Novo agendamento'}
                {isEdit && (
                    <Typography variant="caption" display="block" color="text.secondary">
                        ID #{appointment.id}
                    </Typography>
                )}
            </DialogTitle>

            <DialogContent>
                <Stack spacing={2} sx={{ mt: 1 }}>

                    {error && <Alert severity="error">{error}</Alert>}

                    {/* Status rápido (apenas em edição) */}
                    {isEdit && (
                        <Stack direction="row" spacing={1}>
                            {Object.entries(STATUS_LABELS).map(([val, cfg]) => (
                                <Chip
                                    key={val}
                                    label={cfg.label}
                                    color={form.status === val ? cfg.color : 'default'}
                                    variant={form.status === val ? 'filled' : 'outlined'}
                                    size="small"
                                    onClick={() => canEdit && handleStatusChange(val)}
                                    sx={{ cursor: canEdit ? 'pointer' : 'default' }}
                                />
                            ))}
                        </Stack>
                    )}

                    <TextField
                        label="Título *"
                        value={form.title}
                        onChange={set('title')}
                        disabled={!canEdit}
                        fullWidth size="small"
                    />

                    <Stack direction="row" spacing={2}>
                        <DateTimePicker
                            label="Início *"
                            value={form.starts_at}
                            onChange={set('starts_at')}
                            disabled={!canEdit}
                            slotProps={{ textField: { size: 'small', fullWidth: true } }}
                            ampm={false}
                        />
                        <DateTimePicker
                            label="Término *"
                            value={form.ends_at}
                            onChange={set('ends_at')}
                            disabled={!canEdit}
                            slotProps={{ textField: { size: 'small', fullWidth: true } }}
                            ampm={false}
                        />
                    </Stack>

                    <Divider><Typography variant="caption" color="text.secondary">Cliente</Typography></Divider>

                    <Stack direction="row" spacing={2}>
                        <TextField
                            label="Nome do cliente"
                            value={form.client_name}
                            onChange={set('client_name')}
                            disabled={!canEdit}
                            fullWidth size="small"
                        />
                        <TextField
                            label="E-mail"
                            value={form.client_email}
                            onChange={set('client_email')}
                            disabled={!canEdit}
                            fullWidth size="small"
                            type="email"
                        />
                    </Stack>

                    <TextField
                        label="Observações"
                        value={form.description}
                        onChange={set('description')}
                        disabled={!canEdit}
                        multiline rows={2}
                        fullWidth size="small"
                    />

                </Stack>
            </DialogContent>

            <DialogActions sx={{ px: 3, pb: 2, gap: 1 }}>
                {isEdit && canEdit && (
                    <Button color="error" onClick={handleDelete} disabled={saving}>
                        Excluir
                    </Button>
                )}
                <div style={{ flex: 1 }} />
                <Button onClick={onClose} disabled={saving}>Fechar</Button>
                {canEdit && (
                    <Button
                        variant="contained"
                        onClick={handleSubmit}
                        disabled={saving}
                        startIcon={saving ? <CircularProgress size={16} /> : null}
                    >
                        {saving ? 'Salvando...' : 'Salvar'}
                    </Button>
                )}
            </DialogActions>
        </Dialog>
    );
}
