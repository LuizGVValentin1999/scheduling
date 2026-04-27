# Scheduling SaaS

Sistema de agendamento multi-tenant construído como monolito Laravel com ilhas React. Cada workspace (tenant) tem suas próprias agendas, usuários e configurações, isolados por escopo global automático.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 · PHP 8.2 |
| Banco de dados | MySQL 8.4 |
| Cache / Filas | Redis 7 |
| Frontend (componentes) | React 18 · MUI v6 · FullCalendar v6 |
| Build de assets | Vite 5 |
| Autenticação social | Laravel Socialite (Google + Microsoft Azure) |
| Servidor web | Nginx Alpine |
| E-mail (dev) | Mailpit |
| Containerização | Docker Compose |

---

## Funcionalidades

### Multi-tenancy
- Cada usuário pertence a um ou mais **workspaces** (tenants)
- Isolamento automático via `TenantScope` (Global Scope do Eloquent)
- Troca de workspace sem re-login
- Funções por workspace: `admin`, `member`, `viewer`

### Autenticação
- Login e registro com **e-mail + senha**
- OAuth via **Google** e **Microsoft (Azure)**
- Opção "Lembrar de mim"

### Agendas
- Criação e edição de agendas com nome, descrição e duração de slot configurável
- Compartilhamento de agenda com outros usuários do workspace (permissões granulares)
- Geração de links públicos para agendamento externo

### Calendário (React Island)
- Visualização por mês, semana e dia (FullCalendar)
- Criar agendamento clicando num slot livre ou pelo botão "Novo agendamento"
- Editar e cancelar clicando num evento existente
- Drag & drop para reagendar
- Indicador de horário atual em tempo real

### Agendamento público
- Links públicos por agenda com token único
- Página responsiva para o cliente final (`/book/{token}`)
  - Header com dados do profissional e informações da agenda
  - Horários de atendimento configurados exibidos ao cliente
  - Widget de 4 passos: dia → horário disponível → dados pessoais → confirmação
  - Validação: nome obrigatório + pelo menos e-mail **ou** telefone
- Revogação de links a qualquer momento pelo proprietário

### Painel Admin (por workspace)
- Listagem e gerenciamento de usuários do tenant
- Convite de novos membros
- Alteração de papéis e remoção de usuários

---

## Requisitos

- **Docker** e **Docker Compose** (recomendado)
- Ou: PHP 8.2+, Composer, Node.js 20+, MySQL 8, Redis

---

## Instalação com Docker

```bash
# 1. Clone o repositório
git clone <repo-url> scheduling
cd scheduling

# 2. Copie o .env
cp .env.example .env

# 3. Preencha as variáveis obrigatórias no .env
#    (veja a seção Variáveis de Ambiente abaixo)

# 4. Suba os containers
docker compose up -d

# 5. Instale dependências PHP e JS
docker compose exec app composer install
docker compose exec app npm install

# 6. Gere a chave da aplicação
docker compose exec app php artisan key:generate

# 7. Rode as migrations e seeds
docker compose exec app php artisan migrate --seed

# 8. Build dos assets
docker compose exec app npm run build
```

A aplicação estará disponível em **http://localhost:8000**
O Mailpit (captura de e-mails em dev) em **http://localhost:8025**

---

## Variáveis de Ambiente

Copie `.env.example` para `.env` e preencha:

```dotenv
APP_URL=http://localhost:8000
APP_KEY=                        # gerado por: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=db                      # nome do serviço Docker; fora do Docker use 127.0.0.1
DB_PORT=3306
DB_DATABASE=scheduling
DB_USERNAME=scheduling_user
DB_PASSWORD=secret

# OAuth Google (console.cloud.google.com)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# OAuth Microsoft (portal.azure.com)
AZURE_CLIENT_ID=
AZURE_CLIENT_SECRET=
AZURE_REDIRECT_URI=http://localhost:8000/auth/microsoft/callback
AZURE_TENANT_ID=common

# E-mail em desenvolvimento (usa Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

---

## Comandos úteis

```bash
# Ver logs da aplicação
docker compose logs -f app

# Artisan (qualquer comando)
docker compose exec app php artisan <comando>

# Shell interativo
docker compose exec app bash

# Recriar banco do zero
docker compose exec app php artisan migrate:fresh --seed

# Rodar testes
docker compose exec app php artisan test

# Hot-reload de assets (fora do Docker)
npm run dev

# Build de produção
docker compose exec app npm run build
```

---

## Estrutura relevante

```
app/
├── Http/Controllers/
│   ├── Auth/                       # Login, Register, SocialAuth (Google/Microsoft)
│   ├── ScheduleController          # CRUD de agendas + geração de links públicos
│   ├── AppointmentController       # API JSON consumida pelo React Calendar
│   ├── PublicBookingController     # Página e POST de agendamento público
│   ├── AdminController             # Painel admin do workspace
│   └── ScheduleShareController     # Compartilhamento entre usuários
├── Models/
│   ├── Tenant / User / Schedule / Appointment
│   ├── PublicBookingLink / ScheduleShare / Client
│   ├── CalendarIntegration         # Tokens OAuth Google/Outlook (criptografados)
│   └── Concerns/BelongsToTenant    # Trait de multi-tenancy automático
├── Services/
│   ├── ScheduleService
│   └── AppointmentService
└── Policies/                       # Autorização por modelo (Gate/Policy)

resources/
├── js/
│   ├── app.jsx                     # Entry point — monta as ilhas React no DOM
│   ├── components/
│   │   ├── Calendar.jsx            # FullCalendar com modal de agendamento
│   │   ├── AppointmentModal.jsx    # Criar / editar / cancelar agendamento
│   │   └── DashboardStats.jsx      # Cards de métricas do dashboard
│   └── widget/
│       └── BookingWidget.jsx       # Widget standalone para /book/{token}
└── views/
    ├── layouts/app.blade.php       # Shell: sidebar desktop + topbar mobile
    ├── auth/                       # Login e registro
    ├── dashboard/
    ├── schedules/                  # Index, show, create
    └── public/book.blade.php       # Página pública de agendamento
```

---

## Arquitetura: React Islands

O frontend usa o padrão **React Islands**: o Laravel renderiza as páginas via Blade e monta componentes React apenas nos `<div>` que precisam de interatividade.

```
Blade renderiza o HTML
  → app.jsx detecta #calendar-root   → monta <Calendar>
  → app.jsx detecta #dashboard-stats → monta <DashboardStats>
```

Toda navegação é server-side (Laravel). Autenticação, autorização e sessão ficam 100% no servidor, sem duplicidade de lógica no frontend.

---

## Serviços Docker

| Container | Porta local | Descrição |
|---|---|---|
| `scheduling_app` | — | PHP-FPM + Laravel |
| `scheduling_nginx` | **8000** | Entrada HTTP |
| `scheduling_db` | 3306 | MySQL 8.4 |
| `scheduling_redis` | 6379 | Cache e filas |
| `scheduling_queue` | — | Worker `queue:work` |
| `scheduling_mail` | **8025** | Mailpit — UI de e-mails |

---

## Licença

Uso pessoal / privado.
