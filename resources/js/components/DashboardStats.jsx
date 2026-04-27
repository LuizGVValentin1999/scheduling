/**
 * DashboardStats — montado em dashboard/index.blade.php
 *
 * Exibe os cards de métricas usando dados iniciais do servidor (SSR via data-props).
 * Pode fazer polling para atualizar os números sem reload de página.
 */

import React from 'react';
import {
    Grid, Card, CardContent, Typography, Box, List,
    ListItem, ListItemText, ListItemIcon, Chip,
} from '@mui/material';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import AccessTimeIcon    from '@mui/icons-material/AccessTime';
import PendingIcon       from '@mui/icons-material/Pending';
import ViewWeekIcon      from '@mui/icons-material/ViewWeek';

const STAT_CARDS = [
    {
        key:   'total_appointments_today',
        label: 'Agendamentos hoje',
        icon:  <CalendarMonthIcon />,
        color: '#3b82f6',
        bg:    '#eff6ff',
    },
    {
        key:   'total_appointments_week',
        label: 'Esta semana',
        icon:  <ViewWeekIcon />,
        color: '#8b5cf6',
        bg:    '#f5f3ff',
    },
    {
        key:   'pending_count',
        label: 'Pendentes',
        icon:  <PendingIcon />,
        color: '#f59e0b',
        bg:    '#fef3c7',
    },
    {
        key:   'total_schedules',
        label: 'Minhas agendas',
        icon:  <AccessTimeIcon />,
        color: '#10b981',
        bg:    '#f0fdf4',
    },
];

const STATUS_CHIP = {
    confirmed: { label: 'Confirmado', color: 'success' },
    pending:   { label: 'Pendente',   color: 'warning' },
    cancelled: { label: 'Cancelado',  color: 'error'   },
};

export default function DashboardStats({ stats, upcoming }) {
    return (
        <Box>
            {/* Cards de métricas */}
            <Grid container spacing={2} sx={{ mb: 3 }}>
                {STAT_CARDS.map(card => (
                    <Grid item xs={12} sm={6} md={3} key={card.key}>
                        <Card>
                            <CardContent>
                                <Box display="flex" alignItems="center" justifyContent="space-between">
                                    <Box>
                                        <Typography
                                            variant="h3"
                                            fontWeight={700}
                                            sx={{ color: card.color, lineHeight: 1.1 }}
                                        >
                                            {stats[card.key] ?? 0}
                                        </Typography>
                                        <Typography variant="body2" color="text.secondary" mt={0.5}>
                                            {card.label}
                                        </Typography>
                                    </Box>
                                    <Box
                                        sx={{
                                            background: card.bg,
                                            color: card.color,
                                            borderRadius: 2,
                                            p: 1,
                                            display: 'flex',
                                        }}
                                    >
                                        {card.icon}
                                    </Box>
                                </Box>
                            </CardContent>
                        </Card>
                    </Grid>
                ))}
            </Grid>

            {/* Próximos agendamentos */}
            {upcoming?.length > 0 && (
                <Card>
                    <CardContent>
                        <Typography variant="subtitle1" fontWeight={600} mb={1}>
                            Próximos agendamentos
                        </Typography>
                        <List dense disablePadding>
                            {upcoming.map(appt => (
                                <ListItem
                                    key={appt.id}
                                    disablePadding
                                    sx={{ py: 0.5, borderBottom: '1px solid #f1f5f9' }}
                                >
                                    <ListItemIcon sx={{ minWidth: 32 }}>
                                        <Box
                                            sx={{
                                                width: 8, height: 8, borderRadius: '50%',
                                                bgcolor: STATUS_CHIP[appt.status]?.color === 'success' ? '#10b981'
                                                       : STATUS_CHIP[appt.status]?.color === 'warning' ? '#f59e0b'
                                                       : '#ef4444',
                                            }}
                                        />
                                    </ListItemIcon>
                                    <ListItemText
                                        primary={appt.title}
                                        secondary={`${appt.client?.name ?? appt.client_name ?? '—'} · ${new Date(appt.starts_at).toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' })}`}
                                        primaryTypographyProps={{ fontSize: 14, fontWeight: 500 }}
                                        secondaryTypographyProps={{ fontSize: 12 }}
                                    />
                                    <Chip
                                        label={STATUS_CHIP[appt.status]?.label ?? appt.status}
                                        color={STATUS_CHIP[appt.status]?.color ?? 'default'}
                                        size="small"
                                        variant="outlined"
                                    />
                                </ListItem>
                            ))}
                        </List>
                    </CardContent>
                </Card>
            )}
        </Box>
    );
}
