---
name: filament-pm-context
description: Konteks lengkap project filament-pm (Laravel 12 + Filament 4 Project Management). Gunakan skill ini setiap kali mengerjakan task apapun di project ini agar memahami arsitektur, konvensi kode, relasi model, dan pola pengembangan yang benar.
---

# Skill: filament-pm Project Context

Skill ini memberikan konteks penuh project `filament-pm` agar setiap pengembangan konsisten dengan arsitektur yang sudah ada.

## Langkah Wajib Sebelum Mengerjakan Task Apapun

1. **Baca knowledge map** di `docs/knowledge-map.md` untuk memahami struktur lengkap project
2. **Identifikasi resource/model yang terlibat** — lihat peta relasi model di knowledge map
3. **Ikuti pola yang sudah ada** — jangan membuat pola baru jika sudah ada konvensi

---

## Konvensi Kode yang WAJIB Diikuti

### 1. Pola Resource Filament (PALING PENTING)
Resource menggunakan **class terpisah** untuk pemisahan concern:

```php
// Resource utama hanya memanggil class Schema:
public static function form(Schema $schema): Schema
{
    return ProjectForm::configure($schema);
}

public static function infolist(Schema $schema): Schema
{
    return ProjectInfolist::configure($schema);
}

public static function table(Table $table): Table
{
    return ProjectsTable::configure($table);
}
```

**Lokasi file schema:**
- Form → `app/Filament/Resources/{Group}/{Resource}Resource/Schemas/{Resource}Form.php`
- Infolist → `app/Filament/Resources/{Group}/{Resource}Resource/Schemas/{Resource}Infolist.php`
- Table → `app/Filament/Resources/{Group}/{Resource}Resource/Tables/{Resource}Table.php`

### 2. Filament 4 API (BUKAN v3)
- Gunakan `Schema` bukan `Form` atau `Infolist`
- Gunakan `Heroicon::OutlinedRectangleStack` bukan string `'heroicon-o-...'`
- Gunakan `BackedEnum|UnitEnum` untuk icon navigation

### 3. Namespace Resource
Resource dikelompokkan dalam subdirektori:
- `App\Filament\Resources\Projects\` — semua yang berkaitan project
- `App\Filament\Resources\Tasks\` — task management
- `App\Filament\Resources\Accounting\` — keuangan
- `App\Filament\Resources\Users\` — user & role management

### 4. Menambah Sub-halaman Project
Jika menambah halaman baru di dalam project (sub-navigasi):
1. Buat file page di `app/Filament/Resources/Projects/Pages/`
2. Daftarkan di `ProjectResource::getPages()`
3. Tambahkan ke `getRecordSubNavigation()` agar muncul di tab nav

### 5. Navigation Project Bersifat Dinamis
Navigation item per-project **TIDAK** dibuat di resource. Dibuat di `AdminPanelProvider::boot()` via `Filament::serving()`.
Jangan tambahkan `$shouldRegisterNavigation = true` di `ProjectResource`.

---

## Pola Model

### Auto-generate Field
- **Task `code`**: Di-generate di `boot()` model: `TK-{project_id|3pad}-{epic_id|3pad}-{id|3pad}`
- **Project `share_token`**: Via method `generateShareToken()` (Str::random(32)), bukan di boot

### Observer Pattern
Setiap perubahan Task yang memengaruhi Google Calendar → sudah ditangani `TaskObserver`.
Jangan buat logic Google Calendar di luar `GoogleCalendarService` dan `TaskObserver`.

### Pivot Tables
- `project_user`: kolom `role`
- `task_users`: kolom `role`, `google_event_id`

---

## Pola Keuangan (Accounting)

Hierarki data akuntansi:
```
PnlCategory / CashflowCategory
    └── Account (COA) [debit/credit]
            └── Transaction
                    ├── belongsTo Project
                    └── belongsTo RekeningBank
```

Saat membuat fitur keuangan, selalu load relasi: `transaction->account->pnlCategory` dan `transaction->account->cashflowCategory`.

---

## Google Calendar Integration

Token OAuth disimpan di `SocialiteUser` (bukan `User`):
- `access_token`, `refresh_token`, `token_expires_at`

Selalu gunakan `GoogleCalendarService::getClientForUser(User $user)` — sudah menangani token refresh otomatis.
Jangan akses Google API secara langsung di luar service ini.

---

## File Penting yang Perlu Dipahami Sebelum Modifikasi

| File | Ukuran | Keterangan |
|---|---|---|
| `ProjectTaskList.php` | 17KB | Terbesar, list task + filter + action |
| `ProjectKanban.php` | 10KB | Kanban board (flowforge) |
| `ProjectFileManagement.php` | 11KB | File management + versioning + approval |
| `ProjectTransactions.php` | 8KB | CRUD transaksi keuangan project |
| `ProjectZoom.php` | 9KB | Integrasi Zoom |
| `AdminPanelProvider.php` | 5KB | Konfigurasi panel + dynamic navigation |

---

## Checklist Sebelum Commit

- [ ] Mengikuti pola Schema class terpisah
- [ ] Namespace sudah sesuai grup resource
- [ ] Kebijakan RBAC sudah dicakup Policy yang relevan
- [ ] Jika ada perubahan Task yang melibatkan assignee, Observer sudah menangani Google Calendar
- [ ] Tidak ada logic bisnis di Controller/Livewire yang seharusnya ada di Service
- [ ] Migration baru menggunakan timestamp yang urut
