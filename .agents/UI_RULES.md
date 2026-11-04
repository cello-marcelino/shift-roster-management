---
tags: [rules, frontend, ui, anti-slop, design-system, tailwind-v4]
aliases: [UI Rules, Anti-AI Slop Specification, Design Standards]
related: ["[[AGENTS]]", "[[FRONTEND_RULES]]", "[[API_RULES]]"]
---

# UI Rules & Anti-AI Slop Specification

Pedoman desain antarmuka pengguna untuk membasmi estetika generik "vibe-coded" AI (*AI slop*) dan menghasilkan antarmuka web yang matang, bersih, berorientasi fungsional, dan berkarakter manusia.

---

## 1. Katalog Pola Klise AI Web Design (The AI Slop Blacklist)

Daftar berikut adalah pola-pola paling sering muncul dari hasil generate AI yang **DILARANG KERAS** digunakan secara sembarangan:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   THE AI SLOP HALL OF SHAME (BLACKLIST)                │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Warna & Tampilan : Gradien neon ungu/indigo, glow blob, glassmorphism│
│ 2. Tipografi        : Headline teks bergradien (clip-text), font klise │
│ 3. Formula Layout   : Hero pill ✨ + 2 button, 3 kartu fitur, fake stats│
│ 4. Detail UI        : rounded-2xl di semua sudut, hover translate-y-1  │
│ 5. Copywriting      : "Supercharge", "Seamless", "Transform workflow"  │
└────────────────────────────────────────────────────────────────────────┘
```

---

### A. Warna & Efek Tampilan
- ❌ **Gradien Neon AI (Ungu / Indigo / Biru-Violet)**: Dilarang menggunakan gradien 2-stop generik (`purple → blue`, `indigo → violet`, `violet → pink`) baik di tema terang maupun dark mode.
- ❌ **Glow Neon & Floating Blur Blobs**: Dilarang menaruh lingkaran blur warna-warni mengambang (`blur-3xl opacity-30 bg-purple-500 rounded-full`) atau radial grid background di dark theme tanpa kebutuhan fungsional.
- ❌ **Glassmorphism Klise**: Dilarang membuat kartu semi-transparan dengan efek blur berlebihan (`bg-white/10 backdrop-blur-md border-white/20`).
- ❌ **Palet Mentah Default Tailwind**: Dilarang menggunakan kombinasi mentah tanpa penyesuaian (misal: background `slate-950` dipadu `indigo-600` untuk semua tombol).
- ❌ **Tren Klise Baru (Pseudo-Editorial)**: Dilarang mengekor tren generik baru: background krem/beige (`#fdfbf7`) dipaksakan dengan font serif ala majalah "editorial" jika tidak relevan dengan esensi produk.

> ✅ **Solusi**: Gunakan canvas netral yang tenang (`bg-slate-50` / `#f8fafc` atau `zinc-900`), solid card surface putih/gelap bersih, dan garis pembatas halus 1px (*hairline border* `border-slate-200/80`). Kustomisasi warna brand secara eksplisit di `@theme`.

---

### B. Tipografi
- ❌ **Headline Teks Bergradien**: Dilarang keras teks judul bergradien mengkilap (`bg-gradient-to-r from-purple-600 to-blue-500 bg-clip-text text-transparent`).
- ❌ **Tipografi Monoton Tanpa Hirarki**: Dilarang memakai font yang sama di segala konteks tanpa pertimbangan bobot atau membiarkan font monospace bocor ke judul/tombol.

> ✅ **Solusi**: Judul teks solid dengan kontras tinggi (`text-slate-900` / `text-white`), ukuran proporsional, dan font monospace HANYA untuk angka tabular numerik (`tabular-nums font-mono`) atau kode ID teknis.

---

### C. Formula Layout Klise (Template AI Slop)
- ❌ **Hero Rata Tengah Formulaik**:
  - Hindari pola kaku: Badge pill kecil di atas (*"✨ Baru: Fitur AI v2..."*) ➔ Headline raksasa rata tengah ➔ Subjudul 2 baris ➔ Dua tombol (satu solid, satu outline/ghost).
- ❌ **Tiga Kartu Fitur Sejajar (3-Up Cards)**:
  - Hindari 3 kartu identik berjejer yang masing-masing hanya berisi icon di dalam kotak pastel + judul singkat + deskripsi 2 baris.
- ❌ **Bento Grid Kosong**:
  - Hindari bento grid dengan ukuran kotak asimetris acak jika hanya digunakan untuk memajang visual hiasan tanpa data fungsional riil.
- ❌ **Metrik & Statistik Palsu**:
  - Dilarang membuat baris angka statistik karangan (*"10K+ pengguna aktif"*, *"99.9% uptime"*, *"5x produktivitas"*).
- ❌ **Elemen Kepercayaan Palsu (*Fake Trust*)**:
  - Dilarang barisan logo *"Trusted by"* dari perusahaan fiktif atau logo abu-abu tanpa izin resmi.
  - Dilarang kartu testimoni fiktif dengan foto avatar dummy stock dan rating 5 bintang template.
- ❌ **Pricing 3-Tier Formulaik**:
  - Hindari kartu harga 3 kolom di mana tier tengah sengaja dibuat lebih besar dengan badge *"Most Popular"* / *"Best Value"*, kecuali memang struktur bisnis produk memintanya.
- ❌ **Footer 4-Kolom Template**:
  - Hindari footer raksasa 4 kolom berisi link dummy (*Product*, *Company*, *Resources*, *Legal*) yang tidak memiliki halaman tujuan nyata.

> ✅ **Solusi**: Rancang layout berdasarkan **kebutuhan nyata alur tugas pengguna (User Task Flow)**. Tampilkan data fungsional, antarmuka tabel berkepadatan tinggi, atau formulir interaktif langsung.

---

### D. Detail UI & Mikro-Interaksi
- ❌ **`rounded-2xl` & Shadow Berlebih**:
  - Dilarang membulatkan semua kartu dan kontainer secara ekstrem (`rounded-2xl` / `rounded-3xl`) disertai bayangan mengambang tebal (`shadow-xl`).
- ❌ **Hover Naik Mengambang Klise**:
  - Dilarang memberikan efek hover melayang seragam di setiap kartu (`hover:-translate-y-1 hover:shadow-lg transition-all`).
- ❌ **Emoji sebagai Pengganti Ikon UI**:
  - Dilarang keras menggunakan emoji Unicode (🚀, ✨, 🎯, ⚡, 🔥, 💡) di judul, tombol, atau navigasi.

> ✅ **Solusi**: Gunakan radius wajar dan matang (`rounded-lg` atau `rounded-md`), batas visual dengan border hairline 1px solid, dan ikon vektor monoline SVG seragam (Lucide / Heroicons).

---

### E. Copywriting & Blacklist Kata AI
Dilarang menggunakan kata-kata marketing generik dan hiperbolis berikut pada UI copy:

| Kata Terlarang (Blacklist) | Karakteristik Masalah | Solusi Pengganti |
|---|---|---|
| *"Supercharge"* | Hiperbola klise AI | Sebutkan peningkatan fungsi secara spesifik |
| *"Seamless"* / *"Seamlessly"* | Basa-basi tanpa arti teknis | Langsung jelaskan cara kerja fiturnya |
| *"Transform your workflow"* | Janji marketing kosong | Tampilkan alat/fitur kerja nyatanya |
| *"Unlock the power of..."* | Slogan generik AI | Hapus total; langsung ke inti manfaat |
| *"Effortless"* / *"Tanpa usaha"* | Klaim berlebihan | Tunjukkan melalui alur form yang simpel |
| *"Next-generation platform"* | Jargon tanpa bukti | Tuliskan jenis aplikasi secara jujur |
| *"Empower your team"* | Buzzword korporat | Ganti dengan aksi nyata yang dapat dilakukan |

---

## 2. Arsitektur Alternatif: Desain Web yang Bersih & Fungsional

Alih-alih menggunakan formula AI slop, terapkan prinsip arsitektur antarmuka berikut:

### 1. Dual-Density Typography System (Skala Ukuran Teks)

Sistem tipografi memiliki dua mode skala kepadatan (*density scale*) yang disesuaikan dengan jenis halaman:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   DUAL-DENSITY TYPOGRAPHY SYSTEM                       │
├────────────────────────────────────────────────────────────────────────┤
│ MODE DENSE (Dashboard / Admin Panel)   --> Basis Kepadatan Komponen    │
│ MODE CLEAN (Landing Page / Public)     --> Skala Dinaikkan +25%        │
└────────────────────────────────────────────────────────────────────────┘
```

#### Tabel Skala Tipografi Baku

| Token CSS | Mode Dense (Admin & Dashboard) | Mode Clean / Spacious (+25%) | Peruntukan Utama |
|---|:---:|:---:|---|
| `--text-h1` | **`2rem`** (~32px) | **`2.5rem`** (~40px) | Judul halaman utama, angka metrik KPI besar |
| `--text-h2` | **`1.5rem`** (~24px) | **`1.875rem`** (~30px) | Judul seksi kartu container, judul modal dialog |
| `--text-h3` | **`1rem`** (~16px) | **`1.25rem`** (~20px) | Sub-heading item kartu, judul tabel |
| `--text-base` | **`0.8rem`** (~12.8px) | **`1rem`** (~16px) | **Baseline Standar**: Paragraf, tabel, form, tombol |
| `--text-tiny` | **`0.7rem`** (~11.2px) | **`0.875rem`** (~14px) | **Minimal (&ge;11px)**: Micro-badge, eyebrow label, timestamp |

#### Implementasi CSS di Tailwind v4 (`client/src/main.css`)
```css
@import "tailwindcss";

/* 1. Default: Mode Dense untuk Admin Panel & Dashboard (Banyak Komponen) */
:root {
  --text-h1: 2rem;
  --text-h2: 1.5rem;
  --text-h3: 1rem;
  --text-base: 0.8rem;
  --text-tiny: 0.7rem;
}

/* 2. Mode Clean: Halaman Landing Page / Publik (+25% Skala) */
.page-clean,
[data-density="spacious"] {
  --text-h1: 2.5rem;
  --text-h2: 1.875rem;
  --text-h3: 1.25rem;
  --text-base: 1rem;
  --text-tiny: 0.875rem;
}

/* Pemetaan Utility Tailwind v4 */
@theme {
  --text-h1: var(--text-h1);
  --text-h2: var(--text-h2);
  --text-h3: var(--text-h3);
  --text-base: var(--text-base);
  --text-tiny: var(--text-tiny);
}
```

> **Aturan Penggunaan**:
> - Halaman berkepadatan tinggi (Admin Panel, CRM, Dashboard Analitik, Operational Grid) **wajib** menggunakan skala dasar (`0.8rem` base).
> - Halaman yang bersih dan bernapas (Landing Page, Marketing, Halaman Baca Publik) **wajib** dinaikkan 25% (`1rem` base).

---

### 2. Single-Layer Section Cards (Anti-Card Soup)
- Kelompokkan fungsionalitas ke dalam seksi tunggal berlatar putih dengan border hairline tipis.
- Elemen berulang di dalam seksi disusun sebagai baris tabel atau list bersekat (`divide-y divide-slate-100`), **bukan** membuat kartu baru di dalam kartu.

### 3. High-Density & Data-First Presentation
- Utamakan tabel data terstruktur, filter cepat, dan metrik operasional konkret.
- Jarak antar elemen proporsional (8pt grid system: `p-4`, `p-6`, `gap-4`).

### 4. Aksesibilitas Input Mobile (Critical UX)
- Seluruh elemen interaktif input form, select, dan textarea wajib menggunakan font minimal **16px** pada layar mobile (`text-base sm:text-sm` untuk desktop) untuk mencegah bug zoom paksa peramban mobile (iOS Safari / Chrome Android).

---

## 3. Checklist Audit Anti-AI Slop Sebelum Commit

Sebelum menyelesaikan kode antarmuka, verifikasi bahwa:
- [ ] Tidak ada gradien neon 2-stop ungu/indigo di background maupun hero section.
- [ ] Tidak ada teks headline yang menggunakan teks bergradien (`bg-clip-text text-transparent`).
- [ ] Tidak ada efek glow blob blur yang mengambang tanpa fungsi.
- [ ] Tidak ada struktur kartu di dalam kartu (anti-card soup).
- [ ] Bebas dari badge dekoratif palsu (*"✨ Baru: AI..."*, *"High Performance"*).
- [ ] Tidak ada elemen fiktif (logo "Trusted by" palsu, testimoni bintang 5 palsu, metrik bohong).
- [ ] Bebas emoji Unicode pada seluruh komponen antarmuka (100% SVG monoline).
- [ ] Bebas dari kata-kata blacklist (*Supercharge, Seamless, Transform your workflow, Unlock*).
- [ ] Input mobile menggunakan ukuran aman minimal 16px.
