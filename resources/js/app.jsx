/**
 * Ponto de entrada React — "React Islands" pattern.
 *
 * Cada página Blade pode ter um ou mais <div id="..."> mount points.
 * Este arquivo detecta quais estão presentes no DOM e monta
 * o componente React correto em cada um.
 *
 * Vantagens sobre SPA completa:
 *  - Laravel gerencia rotas e auth (sem duplicidade)
 *  - React só carrega onde é necessário
 *  - Server-side rendering dos dados via data-* attributes
 *  - Fallback HTML garantido antes do JS hidratar
 */

import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { LocalizationProvider } from '@mui/x-date-pickers';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFnsV3';
import { ptBR } from 'date-fns/locale';

import theme from './theme';
import Calendar from './components/Calendar';
import DashboardStats from './components/DashboardStats';

// -------------------------------------------------------
// Mount: Calendário de agenda (schedule/show.blade.php)
// -------------------------------------------------------
const calendarRoot = document.getElementById('calendar-root');
if (calendarRoot) {
    const scheduleId = calendarRoot.dataset.scheduleId;
    const canEdit    = calendarRoot.dataset.canEdit === 'true';
    const csrf       = calendarRoot.dataset.csrf;

    createRoot(calendarRoot).render(
        <ThemeProvider theme={theme}>
            <CssBaseline />
            <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={ptBR}>
                <Calendar
                    scheduleId={scheduleId}
                    canEdit={canEdit}
                    csrf={csrf}
                />
            </LocalizationProvider>
        </ThemeProvider>
    );
}

// -------------------------------------------------------
// Mount: Stats do dashboard (dashboard/index.blade.php)
// -------------------------------------------------------
const dashboardRoot = document.getElementById('dashboard-stats');
if (dashboardRoot) {
    const stats    = JSON.parse(dashboardRoot.dataset.stats    || '{}');
    const upcoming = JSON.parse(dashboardRoot.dataset.upcoming || '[]');

    createRoot(dashboardRoot).render(
        <ThemeProvider theme={theme}>
            <CssBaseline />
            <DashboardStats stats={stats} upcoming={upcoming} />
        </ThemeProvider>
    );
}
