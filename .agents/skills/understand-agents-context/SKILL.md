---
name: understand-agents-context
description: Gunakan skill ini ketika memulai sesi kerja baru, saat onboarding ke proyek yang menggunakan .agents, atau saat user meminta untuk membaca, memuat, dan memahami aturan serta konteks master agents.
---

# Skill: Understand Agents Context (`understand-agents-context`)

Playbook eksekusi terstruktur bagi AI coding agent untuk membaca, memetakan, dan menyerap seluruh konteks sistem, aturan rekayasa, standar arsitektur, dan batasan mutlak yang tersimpan di dalam `.agents-v2/` sebelum melakukan perubahan atau menulis baris kode apa pun.

---

## 1. Trigger Conditions (Kapan Skill Ini Digunakan)

Skill ini **WAJIB** dijalankan pada kondisi:
1. Sesi percakapan baru dimulai pada proyek yang terhubung dengan `.agents` atau `.agents-v2`.
2. Pengguna meminta: *"pahami konteks agents"*, *"baca rules yang ada"*, *"onboarding agents"*, *"load context rules"*, atau *"baca panduan saya"*.
3. Sebelum memulai proyek baru dari nol atau sebelum merancang arsitektur fitur besar.
4. Ketika agen mendeteksi ketidakpastian mengenai aturan konvensi (penamaan folder, standar styling, atau kontrak API).

---

## 2. Alur Eksekusi 4 Tahap Pemahaman Konteks

```
┌────────────────────────────────────────────────────────────────────────┐
│               ALUR PEMAHAMAN KONTEKS MASTER AGENTS                     │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Discovery & Path Check ──> 2. Domain Deep Reading                   │
│                                           │                            │
│ 4. Confirmation Report    <── 3. Constraint Locking (P0 Guardrails)    │
└────────────────────────────────────────────────────────────────────────┘
```

---

### Tahap 1: Discovery & Path Verification

1. **Periksa Keberadaan Master Hub**:
   - Cari berkas `AGENTS.md` di direktori `.agents-v2/` atau tautan `.agents/` di root proyek.
2. **Pahami Peta Modul Aturan**:
   Pastikan seluruh dokumen aturan modular terdeteksi:
   - `AGENTS.md` (Hub Utama)
   - `PRINCIPLES.md` (Fondasi Rekayasa)
   - `ARCHITECTURE.md` (Struktur Folder & Layering)
   - `CODE_STYLE.md` (Standar Penulisan Kode)
   - `FRONTEND_RULES.md` (Vite & Tailwind v4)
   - `UI_RULES.md` (Anti-AI Slop & Tipografi)
   - `API_RULES.md` (RESTful API Contract)
   - `ENV_STANDARDS.md` (Variabel Lingkungan .env)

---

### Tahap 2: Domain Deep Reading & Absorption

Agen **DILARANG BERASUMSI**. Agen wajib membaca berkas-berkas aturan sesuai lingkup pekerjaan yang akan dihadapi:

#### A. Jika Menyangkut Struktur Proyek & Rekayasa:
- **Baca [[ARCHITECTURE]]**:
  - Konvensi folder: **WAJIB** `client/` (Frontend) dan `server/` (Backend).
  - Aliran dependensi satu arah: `HTTP Request ──> Controller ──> Service ──> Repository ──> Database`.
  - Kontrak antar layer wajib menggunakan DTO, dilarang tembus layer.
- **Baca [[PRINCIPLES]]**:
  - Terapkan Clean Code, KISS, YAGNI, DRY, dan SOLID.
  - Selesaikan task dengan solusi paling sederhana (*smallest viable change*).

#### B. Jika Menyangkut Penulisan Kode & Tooling:
- **Baca [[CODE_STYLE]]**:
  - Naming deskriptif dan domain-specific (tanpa `data`, `temp`, `obj`, `x`).
  - Fungsi terfokus (maksimal 3–4 parameter, *no hidden side effects*).
  - Type safety eksplisit (tanpa `any`), magic values diekstrak ke `UPPER_SNAKE_CASE`.
  - Komentar menjelaskan *kenapa*, bukan *apa*.
- **Baca [[FRONTEND_RULES]]**:
  - Builder **WAJIB** Vite.
  - Styling **WAJIB** Tailwind CSS v4 Zero-Config (`@import "tailwindcss";`, kustomisasi via `@theme` di CSS). Dilarang membuat `tailwind.config.js`.

#### C. Jika Menyangkut Antarmuka Pengguna (UI/UX):
- **Baca [[UI_RULES]]**:
  - Hindari **The 10 Cardinal Sins (Anti-AI Slop)**.
  - Terapkan **Dual-Density Typography System**:
    - Mode Dense (Admin/Dashboard): H1 `2rem`, H2 `1.5rem`, H3 `1rem`, Base `0.8rem`, Tiny `0.7rem`.
    - Mode Clean (Landing Page): Skala dinaikkan **+25%** (Base `1rem`).
  - Single-layer section cards (bebas card soup).
  - Input mobile minimal **16px** (mencegah bug auto-zoom di browser mobile).
  - 100% SVG monoline icons (zero emoji Unicode pada antarmuka).

#### D. Jika Menyangkut Backend, API & Lingkungan:
- **Baca [[API_RULES]]**:
  - REST + JSON dengan prefix wajib `/api/v1/...`.
  - Path: Kata benda jamak, kebab-case (`/api/v1/rekomendasi-dosen`), tanpa kata kerja, nesting maksimal 2 level.
  - Format response baku (`success`, `message`, `data`, `meta` / `errors`, `request_id`).
  - Status codes semantik (dilarang return 200 untuk kondisi error).
- **Baca [[ENV_STANDARDS]]**:
  - Format variabel `UPPER_SNAKE_CASE`.
  - File `.env` wajib masuk `.gitignore`; sediakan `.env.example` dummy.
  - Pada `client/`, hanya variabel ber-prefix `VITE_` yang diekspos.

---

### Tahap 3: Constraint Locking (Mengunci P0 Guardrails)

Kunci 7 batasan mutlak (*Non-Negotiable Constraints*) ke dalam memori kerja sebelum mengeksekusi instruksi apa pun:

1. 🚫 **Zero Hardcoded Secrets**: Dilarang menulis API key, token, atau kredensial langsung di kode; selalu gunakan `.env`.
2. 🚫 **Zero AI Slop**: Dilarang gradien neon 2-stop, card-in-card nesting, glow blobs, emoji UI, dan kata klise marketing (*Supercharge, Seamless*).
3. 🚫 **Strict Directory Naming**: Folder frontend dinamai `client/`, folder backend dinamai `server/`.
4. 🚫 **Tailwind v4 Zero-Config**: Dilarang membuat file `tailwind.config.js` atau `postcss.config.js` gaya v3.
5. 🚫 **Vite Builder Mandatory**: Seluruh aplikasi frontend wajib berjalan di atas Vite.
6. 🚫 **Separation of Layers**: Controller dilarang punya business logic; komponen UI dilarang melakukan query DB atau fetch HTTP langsung di template.
7. 🚫 **Mobile Form A11y**: Seluruh input form di mobile wajib font-size minimal 16px (`text-base sm:text-sm`).

---

### Tahap 4: Confirmation & Readiness Report

Setelah membaca seluruh dokumen relevan, sampaikan status kesiapan kepada pengguna dengan format ringkas:

```markdown
### 🛡️ Agents Context Loaded & Verified

Saya telah membaca dan memetakan seluruh aturan dari `.agents-v2/`:
1. **Arsitektur & Direktori**: Pola `client/` (Vite + Tailwind v4) & `server/` (Layered REST API).
2. **Kualitas Rekayasa**: Clean Code, KISS/YAGNI, penamaan deskriptif, zero hardcoded secrets.
3. **Standar Desain Antarmuka**: Anti-AI Slop aktif, Dual-Density Typography dipatuhi, mobile input &ge;16px.
4. **Kontrak API & Env**: Prefix `/api/v1/`, format JSON `snake_case`, response envelope baku, isolasi `.env`.

Saya siap bekerja dengan kepatuhan penuh terhadap standar ini. Silakan instruksikan tugas yang ingin dieksekusi.
```
