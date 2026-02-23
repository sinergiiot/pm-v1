# Knowledge Map — `filament-pm`

## Stack Teknologi

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 + PHP 8.2+ |
| Admin Panel | Filament 4 (beta) |
| Auth | filament-socialite (Google OAuth) + manual login |
| RBAC | filament-shield (Spatie Permission) |
| Theme | resma/filament-awin-theme (custom Filament theme) |
| Icons | blade-fontawesome + Heroicons bawaan Filament |
| Spreadsheet | phpoffice/phpspreadsheet (import/export Excel) |
| Kanban | relaticle/flowforge |
| External API | Google Calendar API (`google/apiclient`) |
| Queue | Laravel Queue (database driver) |
| Asset | Vite + TailwindCSS |
| Testing | Pest v4 |

---

## Arsitektur Panel

Panel Filament satu: **`admin`** (path: `/admin`), dikonfigurasi di `AdminPanelProvider.php`.

- Primary color: **Amber**
- Navigasi proyek bersifat **dinamis** — setiap project yang `whereHas('users')` milik user yang login akan muncul sebagai navigation item di grup "Projects"
- Navigasi proyek mengarah ke `ProjectResource::getUrl('view', [...])` (ViewProject page)
- SubNavigation project menggunakan `SubNavigationPosition::Top`

---

## Struktur Direktori `app/`

```
app/
├── Console/          # Artisan commands
├── Filament/
│   ├── Imports/      # Import class (TaskImport)
│   ├── Pages/        # Global pages
│   ├── Resources/
│   │   ├── Accounting/   # AccountResource, CashflowCategoryResource, PnlCategoryResource, RekeningBankResource, TransactionResource
│   │   ├── Projects/     # ProjectResource (13 pages sub-nav)
│   │   ├── Tasks/        # TaskResource
│   │   └── Users/        # UserResource, RoleResource
│   └── Widgets/
├── Http/             # Controllers (minimal, sebagian besar pakai Filament)
├── Jobs/             # Queue jobs
├── Listeners/        # Event listeners
├── Livewire/         # Livewire components standalone
├── Models/           # 18 model Eloquent
├── Notifications/    # Notifikasi Laravel
├── Observers/        # 4 observer model
├── Policies/         # 9 Policy (RBAC)
├── Providers/
│   ├── AppServiceProvider.php
│   └── Filament/AdminPanelProvider.php
└── Services/         # 3 service class
```

---

## Peta Model & Relasi

```
User
├── belongsToMany  Project  (pivot: role)
├── belongsToMany  Task     (pivot: role, google_event_id) [table: task_users]
├── hasMany        SocialiteUser (provider OAuth)
└── (auth, roles via Spatie)

Project
├── belongsTo  User         (creator_id)
├── belongsToMany  User     (pivot: role)
├── hasMany    Epic
├── hasMany    Task
├── hasMany    TaskStatus
├── hasMany    TaskPriority  ← dibuat otomatis saat project created (Low/Medium/High/Critical)
├── hasMany    Meeting
├── hasMany    Cluster       (ordered by `order`)
├── hasMany    ProjectDocument
└── hasMany    Transaction

Epic
├── belongsTo  Project
└── hasMany    Task

Task
├── belongsTo  Project
├── belongsTo  Epic
├── belongsTo  TaskStatus
├── belongsTo  TaskPriority
├── belongsToMany  User     (pivot: role, google_event_id)
└── hasMany    TaskComment

ProjectDocument
├── belongsTo  Project
├── belongsTo  Cluster
├── belongsTo  Meeting      (optional)
└── hasMany    ProjectDocumentVersion

ProjectDocumentVersion
└── belongsTo  ProjectDocument

Cluster
└── belongsTo  Project

Meeting
└── belongsTo  Project

Transaction
├── belongsTo  Project
├── belongsTo  Account
└── belongsTo  RekeningBank

Account (COA)
├── belongsTo  PnlCategory
├── belongsTo  CashflowCategory
└── hasMany    Transaction

SocialiteUser
└── belongsTo  User  (via provider + provider_id)
```

---

## Resource Filament

### `ProjectResource` — Inti Aplikasi

Resource ini **tidak menampilkan navigasi mandiri** (`$shouldRegisterNavigation = false`). Item navigasi dibuat dinamis di `AdminPanelProvider`.

**Sub-halaman (SubNavigation Top):**

| Route | File | Deskripsi |
|---|---|---|
| `/{record}` | `ViewProject.php` | Detail project (infolist) |
| `/{record}/task-list` | `ProjectTaskList.php` | List task lengkap (17KB, terbesar) |
| `/{record}/kanban` | `ProjectKanban.php` | Kanban board (flowforge) |
| `/{record}/calendar` | `ProjectCalendar.php` | Kalender task |
| `/{record}/gantt` | `ProjectGantt.php` | Gantt chart |
| `/{record}/zoom` | `ProjectZoom.php` | Integrasi Zoom Meeting |
| `/{record}/files` | `ProjectFileManagement.php` | Manajemen dokumen + versioning |
| `/{record}/transactions` | `ProjectTransactions.php` | Transaksi keuangan project |
| `/{record}/profit-loss` | `ProjectProfitLoss.php` | Laporan P&L |
| `/{record}/cash-flow` | `ProjectCashFlow.php` | Laporan arus kas |
| `/` | `ListProjects.php` | Daftar semua project |
| `/{record}/edit` | `EditProject.php` | Edit project |

### Pola Penulisan Resource

Resource menggunakan pola **"Schema class terpisah"**:
- `Schemas/ProjectForm.php` → dipanggil `ProjectForm::configure($schema)`
- `Schemas/ProjectInfolist.php` → dipanggil `ProjectInfolist::configure($schema)`
- `Tables/ProjectsTable.php` → dipanggil `ProjectsTable::configure($table)`

Pola yang sama berlaku untuk `TaskResource`, `AccountResource`, dll.

### `TaskResource`

Berisi sub-halaman: List, Create, Edit, View. Ada `RelationManagers` untuk relasi task.

### Accounting Resources

5 resource terpisah: `Account`, `CashflowCategory`, `PnlCategory`, `RekeningBank`, `Transaction`.

---

## Services

### `GoogleCalendarService`
- **`getClientForUser(User)`** — autentikasi Google API via token OAuth yang disimpan di `SocialiteUser`, refresh token otomatis jika expired
- **`createEventForTask(User, Task)`** — buat Google Calendar event dari task
- **`updateEventForTask(User, Task, $eventId)`** — update event
- **`deleteEventForUser(User, $eventId)`** — hapus event
- Google event ID disimpan di pivot `task_users.google_event_id`

### `ZoomService`
- Integrasi Zoom meeting management untuk proyek

### `TaskImportParser`
- Parsing file Excel/CSV untuk bulk import task menggunakan `phpspreadsheet`

---

## Observers

| Observer | Model | Fungsi |
|---|---|---|
| `ProjectObserver` | Project | Lifecycle hooks project |
| `TaskObserver` | Task | Sinkronisasi Google Calendar event saat task berubah |
| `TaskPriorityObserver` | TaskPriority | Lifecycle hooks priority |
| `TaskStatusObserver` | TaskStatus | Lifecycle hooks status |

---

## Fitur Khusus Model

### `Project`
- Auto-generate `share_token` (Str::random(32)) untuk share URL publik: `/share/{token}`
- Method `getProjectStatusLabel()` → 'On Track' / 'Due soon' / 'Overdue' / 'Inactive'
- Scope: `active()`, `inactive()`

### `Task`
- Auto-generate `code` format: `TK-{project_id padded 3}-{epic_id padded 3}-{id padded 3}`
- Pivot dengan User menyimpan `google_event_id`

### `Account`
- Konstanta tipe: `TYPE_DEBIT = 'debit'`, `TYPE_CREDIT = 'credit'`

### `ProjectDocument`
- Tipe dokumen: `risalah_meeting`, `laporan_cluster_a/b/c`, `other`
- Versioning via `ProjectDocumentVersion`, method `getNextVersionNumber()`
- Accessor `type_display` → nama cluster jika ada, fallback ke label tipe

---

## Migrations Timeline

```
Awal    : users, cache, jobs (Laravel default)
Feb 07  : socialite_users, permission_tables, projects
Feb 08  : project_user (pivot), epics, task_statuses, task_priorities, tasks, task_users
Feb 09  : position pada tasks, google_event_id pada task_users, google_tokens pada socialite_users
Feb 10  : start_date tasks, task_comments, meetings, settings, project_documents, clusters
Feb 13  : pnl_categories, cashflow_categories, rekening_banks, accounts, transactions
Feb 13  : imports, exports, failed_import_rows (Filament import/export tables)
Feb 16  : share_token pada projects, notifications table
```

---

## Policies (RBAC via filament-shield)

Policy terdaftar untuk: `Account`, `CashflowCategory`, `PnlCategory`, `Project`, `RekeningBank`, `Role`, `Task`, `Transaction`, `User`

---

## Catatan Penting untuk Pengembangan

> **PENTING:**
> - Filament versi **4** (beta/stable baru), API berbeda dari v3. Gunakan `Schema` bukan `Form`/`Infolist` langsung.
> - Resource pattern menggunakan **class terpisah** untuk Form/Table/Infolist schema — selalu ikuti pola ini.
> - Project navigation bersifat **dinamis** di `AdminPanelProvider::boot()`, bukan di resource.
> - Google tokens disimpan di model `SocialiteUser` (bukan `User`), refresh dilakukan di `GoogleCalendarService`.
> - Task code di-generate di `boot()` model, **bukan di database**.

> **TIP:**
> - `ProjectTaskList.php` adalah file terbesar (17KB) — pahami ini sebelum menambah fitur task baru
> - `ProjectFileManagement.php` (11KB) mengelola dokumen + versioning — ada logic approval di `ProjectDocumentVersion`
> - Untuk fitur keuangan, pahami relasi: `Transaction → Account → PnlCategory / CashflowCategory`
