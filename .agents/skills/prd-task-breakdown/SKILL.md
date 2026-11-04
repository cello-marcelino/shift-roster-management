---
name: prd-task-breakdown
description: Gunakan skill ini ketika user memberikan dokumen PRD (Product Requirements Document), meminta untuk menganalisis PRD, atau meminta menyusun roadmap tasks step-by-step production yang terstruktur dan anti-scope creep.
---

# Skill: PRD Understanding & Production Task Breakdown (`prd-task-breakdown`)

Playbook eksekusi terstruktur bagi AI coding agent untuk menganalisis Product Requirements Document (PRD), mengisolasi batasan ruang lingkup (*scope containment*), dan menyusun **Roadmap Tasks Produksi Bertahap** yang terorganisir, terarah, dan kebal terhadap pelebaran fitur (*anti-scope creep*).

---

## 1. Trigger Conditions (Kapan Skill Ini Digunakan)

Skill ini **WAJIB** dijalankan pada kondisi:
1. Pengguna menyediakan dokumen PRD (misal: `PRD.md`, `docs/prd.md`, dokumen spesifikasi fitur) untuk dianalisis.
2. Pengguna meminta: *"baca PRD ini dan buat tasks"*, *"susun task step-by-step dari PRD"*, *"breakdown PRD untuk production"*, atau *"buat rencana pengerjaan agar tidak melebar"*.
3. Sebelum sebuah proyek baru dieksekusi ke tahap penulisan kode produksi.

---

## 2. Alur 4 Fase Eksekusi: Dari PRD ke Production Tasks

```
┌────────────────────────────────────────────────────────────────────────┐
│                   ALUR PRD-TO-PRODUCTION BREAKDOWN                     │
├────────────────────────────────────────────────────────────────────────┤
│ 1. PRD Anatomy & Scope Quarantine (In-Scope vs Out-of-Scope)           │
│                                           │                            │
│ 2. Architecture Mapping to .agents-v2 Standard                         │
│                                           │                            │
│ 3. Dependency-Ordered Task Decomposition (Milestone 0 to 5)            │
│                                           │                            │
│ 4. Task Sheet Generation with Anti-Scope Creep Guardrails              │
└────────────────────────────────────────────────────────────────────────┘
```

---

### Fase 1: Bedah PRD & Karantina Ruang Lingkup (*Scope Containment*)

Langkah awal adalah mencegah proyek melebar (*scope creep*). Ekstraksi 4 elemen kunci dari PRD:

1. **Core Problem & Value Proposition**: Masalah riil apa yang diselesaikan untuk pengguna akhir?
2. **Actor & User Roles**: Siapa saja peran pengguna sistem (misal: Mahasiswa, Dosen, Admin)?
3. **Karantina Ruang Lingkup (Scope Boundary Matrix)**:
   - **IN-SCOPE (Wajib & Kritis)**: Fitur dan fungsionalitas yang secara eksplisit diminta untuk rilis ini.
   - **OUT-OF-SCOPE (Sengaja Ditunda / Dilarang Dikerjakan Sekarang)**: Ide-ide tambahan, fitur sekunder, integrasi pihak ketiga opsional, atau animasi kompleks yang tidak ada di PRD.
4. **Celah Ambiguitas & Pertanyaan Terbuka**:
   - Jika terdapat requirement ambigu yang berdampak pada arsitektur data atau keamanan, **tanyakan kepada pengguna sebelum mulai coding**.

---

### Fase 2: Pemetaan Kebutuhan PRD ke Standar `.agents-v2`

Petakan seluruh kebutuhan teknis PRD ke berkas aturan yang telah ditetapkan:

| Kebutuhan PRD | Modul Aturan Terkait | Standar yang Harus Dipenuhi |
|---|---|---|
| Direktori & Layout Proyek | [[ARCHITECTURE]] | Folder `client/` (Frontend) dan `server/` (Backend) |
| Kebutuhan Data & Migrasi | [[ARCHITECTURE]] & [[API_RULES]] | Migrasi skema berversi, relational constraints, zero SQL injection |
| Endpoints & Kontrak Data | [[API_RULES]] | Prefix `/api/v1/...`, REST semantic, format response baku |
| Antarmuka Pengguna (UI) | [[UI_RULES]] | Anti-AI Slop, Single-layer section cards, Dual-Density Typography |
| Aksesibilitas Form Mobile | [[UI_RULES]] | Input & textarea font minimal **16px** di mobile (anti auto-zoom) |
| Tooling & Styling | [[FRONTEND_RULES]] | Vite mandatory, Tailwind CSS v4 Zero-Config (`@theme`) |
| Variabel Konfigurasi & Kunci | [[ENV_STANDARDS]] | Konfigurasi `.env` terisolasi, prefix `VITE_` di client, zero hardcoded secrets |

---

### Fase 3: Dekomposisi Task Berbasis Ketergantungan (*Bottom-Up Milestones*)

Susun roadmap pengerjaan dengan urutan ketergantungan logis (dari fondasi ke tampilan):

```
Milestone 0: Foundation & Environment Setup
     └──> Milestone 1: Database Schema & Migrations
           └──> Milestone 2: Server Business Logic & API Endpoints
                 └──> Milestone 3: Client Core Infrastructure & State
                       └──> Milestone 4: Frontend UI Features & Views
                             └──> Milestone 5: Verification & Quality Gate
```

#### Rincian Setiap Milestone:
- **Milestone 0: Foundation & Environment Setup**:
  - Inisialisasi struktur `client/` (Vite + Tailwind v4) dan `server/`.
  - Pembuatan template `.env.example` untuk client dan server.
- **Milestone 1: Database & Data Modeling**:
  - Definisi tabel, relasi foreign key, indeks, dan file migrasi berversi.
  - Seeder data statis awal (roles, admin).
- **Milestone 2: Server Business Logic & API Layer**:
  - Implementasi Repositories (akses database terisolasi).
  - Implementasi Services (business logic murni).
  - Implementasi Controllers & Routing (`/api/v1/...`) disertai validasi input dan auth middleware.
- **Milestone 3: Client Infrastructure & State**:
  - Routing client, HTTP API service client, dan state container/store.
- **Milestone 4: Frontend UI Implementation**:
  - Halaman views utama yang mematuhi Anti-AI Slop (bebas card-in-card, mobile input 16px).
  - Integrasi UI ke backend API endpoints.
- **Milestone 5: Verification & Production Hardening**:
  - Verifikasi flow end-to-end, error handling audit, cross-browser/mobile test, checklist commit.

---

### Fase 4: Format Baku Task Item (Anti-Scope Creep Guardrail)

Setiap task yang dirumuskan **WAJIB** mengikuti format kartu spesifikasi ini:

```markdown
### [TASK-XX] Nama Task yang Singkat & Jelas
- **Milestone**: Milestone X — [Nama Milestone]
- **Target Files**:
  - `server/src/Services/ExampleService.ts`
  - `server/src/Controllers/ExampleController.ts`
- **Dependencies**: [TASK-YY harus selesai terlebih dahulu]
- **Scope Pengerjaan (Hanya ini yang dikerjakan)**:
  1. [Langkah konkret 1]
  2. [Langkah konkret 2]
- **Kriteria Lulus (Acceptance Criteria / DoD)**:
  - [ ] Endpoint merespons dengan format baku: `{ success: true, data: ... }`
  - [ ] Validasi input berjalan jika parameter salah (HTTP 422)
  - [ ] Tidak ada API key yang di-hardcode
- **⛔ STRICTLY OUT-OF-SCOPE (Dilarang Dikerjakan di Task Ini)**:
  - Jangan menambahkan fitur sorting/filter kompleks yang belum diminta di task ini.
  - Jangan mulai membangun antarmuka frontend sebelum API endpoint ini lolos pengujian.
```

---

## 3. Protokol Eksekusi Produksi (*Discipline in Action*)

Untuk memastikan proses pengerjaan tetap terorganisir dan tidak melebar:

1. **One Task at a Time**:
   - Agen hanya boleh mengerjakan **SATU task dalam satu waktu**.
   - Selesaikan task secara tuntas, verifikasi kriteria lulusnya, baru pindah ke task berikutnya.
2. **Strictly No Speculative Features (YAGNI)**:
   - Dilarang menambahkan tombol, modal, atau endpoint "tambahan" yang tidak tercantum di PRD atas inisiatif sendiri.
   - Jika agen merasa suatu fitur tambahan berguna, agen **wajib mengusulkannya kepada user** dan menunggu persetujuan eksplisit.
3. **Check-Off Task Status**:
   - Tandai status task yang telah tuntas (`[x]`) pada dokumen roadmap kerja sebelum melanjutkan ke dependensi berikutnya.
4. **Smallest Viable Change**:
   - Buat perubahan seminimal mungkin yang memenuhi *Acceptance Criteria* tanpa merusak modul yang sudah ada.
