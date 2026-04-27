<?php

// Neste projeto usamos apenas rotas web (monolito Laravel).
// Este arquivo existe para satisfazer o bootstrap/app.php mas não define rotas de API separadas.
// As rotas JSON do calendário (ex: /api/schedules/{id}/appointments) estão em routes/web.php
// sob o grupo middleware(['auth', 'tenant']).
