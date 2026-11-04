---
tags: [ui, design-system, personal-taste, minimalism, clean-ui]
aliases: [PERSONAL_UI_TASTE.md, User Personal UI Rules, Aesthetic Codex]
related: ["[[AGENTS.md]]", "[[UI_RULES]]", "[[FRONTEND_RULES]]"]
---

# PERSONAL_UI_TASTE.md — Personal UI Design Codex & Minimalist Standards

> Dokumen ini memuat **preferensi estetika dan standar desain antarmuka personal pengguna** (*Single Source of Truth* untuk UI/UX pada proyek ini).
> Setiap AI Agent wajib mematuhi standar di bawah ini saat merancang, mengubah, atau merefaktor tampilan antarmuka (Blade, Tailwind CSS, daisyUI, dan komponen terkait).

---

## 🎯 1. Filosofi Utama: Signal-Over-Noise & Extreme Minimalism

* **Zero Unnecessary Text**: Hilangkan segala bentuk teks panduan berlebih, kalimat tutorial di dalam kartu kerja, atau catatan kaki yang menyatakan hal yang sudah jelas dari konteks interaksi (contoh: *hapus "1 operator dapat ditugaskan ke...", "Hijau: Terisi · Oranye: Belum Ada Operator"*).
* **Self-Evident Workflows**: Komponen dan formulir harus intuitif tanpa membutuhkan paragraf petunjuk.
* **Tabel Kerja Ramping**: Lebar kolom dimaksimalkan untuk data esensial yang dapat ditindaklanjuti (*actionable*), bukan teks deskriptif pasif.

---

## 🛡️ 2. Proteksi Arsitektur Teknis (Invisible Stack)

* **Dilarang Menampilkan Detail Internal Engine**: Pengguna akhir adalah operator & leader pabrik, bukan sysadmin.
* **Haram**: Menampilkan label stack seperti `Penyimpanan: SQLite Lokal`, `OFFLINE LOCAL`, atau nama database/storage di antarmuka publik.
* **Fokus Domain Nyata**: Tampilan antarmuka hanya memuat terminologi operasional riil (Line, Shift, Jam Kerja, Roster, Stasiun, Leader).

---

## 🏷️ 3. Terminologi Domain yang Konsisten & Tunggal

* **Satu Entitas, Satu Nama**: Jangan menduplikasi atau membuat sinonim panjang dalam antarmuka.
* **Leader**: Gunakan istilah tunggal **`Leader`** di seluruh form, label, tabel, maupun dokumen PDF. Dilarang menggunakan istilah `Penanggung Jawab` atau `Penanggung Jawab (Leader)`.
* **Satuan MP (Manpower)**: Gunakan singkatan baku **`MP`** untuk seluruh penyebutan kuantitas personil/operator (contoh: `[1 MP]`, `14 / 15 MP`, `MP/Line`). **Dilarang** menggunakan istilah percakapan seperti `org` atau `orang`.

---

## 📊 4. Indikator Metrik Bersih & Tenang (Anti-Badge Soup)

* **Angka / Counter Netral Tanpa Background**:
  * Indikator jumlah total manpower, kapasitas shift, atau status pengisian (contoh: `14 / 15 MP`, `12 Org`) **WAJIB** menggunakan teks abu-abu netral monospaced (`font-mono text-base-content/60` atau `text-gray-500`).
  * **DILARANG KERAS** membungkus angka standar dengan badge berwarna-warni (`badge badge-success`, `badge badge-warning`, `badge badge-primary`) yang menciptakan visual noise seperti lampu pohon natal.
  * Warna aksen hanya dicadangkan untuk status abnormalitas kritis atau tombol tindakan utama.
* **Tanpa Ringkasan Sub-Header Redundan**:
  * Hapus teks duplikat di header shift seperti `Terisi: 14 operator (Standar: 15 org)` karena angka `14 / 15 MP` sudah jelas terlihat di sisi kanan.
* **Tombol Navigasi / Tab Shift Ringkas Tanpa Headcount**:
  * Dilarang mencantumkan keterangan jumlah orang (`n Org`) di dalam tombol pilihan/tab shift.
  * Tombol tab cukup menampilkan nama shift (misal: `SHIFT 1`) dan rentang jam (`07:00 - 15:00`) agar navigasi tetap bersih dan tidak ramai teks.

---

## ⚡ 5. Micro-Interactions Kontekstual vs Kolom Pasif

* **Hapus Kolom Status Pasif yang Memakan Tempat**:
  * Dilarang membuat kolom tabel statis khusus hanya untuk menampilkan badge status umum (contoh: kolom `Status Keahlian` dihapus dari tabel roster).
* **Ikon Peringatan Halus (`exclamation-circle`)**:
  * Anomali atau ketidakcocokan data (misal: operator ditempatkan di luar stasiun utamanya) ditampilkan melalui ikon **`exclamation-circle`** yang halus (`text-warning/70 hover:text-warning`) di samping nama/badge yang bersangkutan.
* **Pesan On-Demand (Klik untuk Tahu)**:
  * Pesan peringatan (misal: `"tidak sesuai station utama"`) hanya muncul ketika tombol ikon diklik/diinteraksi melalui popover/dropdown ringkas, bukan mencemari baris tabel secara permanen.

---

## 🚫 6. Anti-Emoji & Anti-Alarmist Placeholders

* **Bebas Stiker Emoji**: Dilarang menggunakan emoji Unicode (⚠️, 💡, 👥, 🏭, dll.) sebagai ikon status tabel atau penanda navigasi.
* **Placeholder Netral & Lembut**:
  * Kondisi kosong atau belum terpilih (contoh: belum memilih stasiun) disajikan dengan teks placeholder italic netral dan halus (`text-base-content/40 italic: Belum ada station dipilih`), **BUKAN** teks merah menyala dengan stiker peringatan `⚠️`.

---

## 🎛️ 7. Notifikasi Sistem Ramping & Dismissible (Bisa Ditutup)

* **Kompak & Minimal**: Banner peringatan sistem (kekurangan manpower, duplikasi, dll.) disajikan dalam satu bilah ramping (*single-line strip*) yang tidak mendominasi layar.
* **Wajib Tombol Tutup / Sembunyikan**: Selalu sediakan tombol tutup (`✕` / dismiss toggle) agar leader dapat menyembunyikan peringatan dan fokus menyusun jadwal tanpa gangguan visual.

---

## 📋 8. Struktur Baris Tabel yang Bersih

* **Hapus Badge Preferensi Tambahan**: Pada baris operator terpilih, hilangkan badge `Utama: [stasiun]` yang redundan.
* **Fokus Hanya pada Penugasan Aktif**: Baris hanya menampilkan:
  1. `No`
  2. `Badge ID`
  3. `Nama Operator` (+ ikon warning halus jika ada ketidakcocokan)
  4. `Penugasan Station` (badge stasiun aktif dengan tombol `✕` + dropdown tambah cepat)
  5. `Aksi Hapus`
* **Badge Station Terpilih Bersih Tanpa Label Level**:
  * Pada badge stasiun yang telah dipilih/ditugaskan pada tabel roster, **DILARANG** mencantumkan teks level seperti `(Trained)`, `(Basic-Trained)`, atau `(Basic)`.
  * Cukup tampilkan nama stasiun dan tombol silang `✕` (serta indikator jumlah operator tandem jika ≥2 orang).
  * Gunakan styling badge netral (`badge-neutral`) yang tenang dan tidak menimbulkan visual noise warna-warni merah/kuning.
* **Dropdown Pilihan Station Bersih & Icon-Only**:
  * Trigger penambahan stasiun di dalam baris tabel **WAJIB** diminimalkan berupa tombol ikon plus kecil bulat (`w-5 h-5 btn-circle`) tanpa teks box select yang memakan ruang.
  * Menu dropdown dire-style menggunakan komponen dropdown UI (`menu menu-xs shadow-lg rounded-box`) bukan elemen native select browser.
  * **Tanpa Lebar Fix (`w-max`)**: Dilarang memberi lebar fix/statis (seperti `w-48`, `w-52`) pada menu dropdown yang memotong teks nama stasiun. Gunakan `w-max whitespace-nowrap` agar lebar menu fluid dan proporsional terhadap panjang nama opsi di dalamnya.
  * Dilarang mencantumkan label tingkat keahlian (`(Basic)`, `(Trained)`) di dalam dropdown pilihan stasiun.
  * Dilarang menggunakan stiker atau emoji dekoratif seperti bintang `⭐` di dalam dropdown.
  * Gunakan format indikator operator yang ringkas: `[n MP]` (bukan `[n org]`).

---

## 📐 9. Layout Tabel Antar Shift Horizontal (Multi-Column Side-by-Side)

* **Tata Letak Horizontal Antar Shift**:
  * Karena jumlah kolom tabel roster sangat ringkas (hanya 5 kolom esensial), tabel antar shift **WAJIB** disajikan berdampingan secara horizontal (side-by-side dalam grid multi-kolom):
    * Line dengan 2 Shift: Grid 2 kolom seimbang (50% / 50%).
    * Line dengan 3 Shift: Grid 3 kolom berdampingan pada layar desktop (33% / 33% / 33%).
  * Hindari menumpuk tabel shift secara vertikal ke bawah yang menyisakan ruang kosong lebar dan memaksa scroll panjang.
* **Default View "Semua Shift"**:
  * Mode tampilan awal disetel pada `Semua Shift` agar leader dapat langsung memantau dan mengelola seluruh shift secara simultan dalam satu pandangan mata.
* **Header Kartu Shift Kompak & Anti-Duplikasi (DRY)**:
  * **Hapus Informasi Waktu Shift & Nama Line di Header Tabel**: Dilarang menduplikasi keterangan jam kerja shift (misal `(07.00 - 16.00)`) dan label nama line (misal `Line EV10`) di dalam header kartu tabel shift roster. Informasi waktu shift sudah disajikan di tab navigasi shift, dan nama line sudah dipilih di dropdown line utama.
  * Header kartu shift cukup menampilkan nama shift (misal: `SHIFT 1`), counter kapasitas headcount (`14 / 15 MP`), dan tombol aksi ringkas (misal: `+ Operator`).

---

## 🚫 10. Eliminasi Logo / Badge Kotak Inisial

* **Header Atas Tanpa Kotak Logo**:
  * Dilarang menggunakan kotak inisial (seperti kotak persegi hitam `MP`) pada header atas aplikasi. Cukup gunakan nama aplikasi (`ROSTER APP`) dan subjudul tipografis bersih.
* **Header Shift Tanpa Kotak Inisial Shift**:
  * Dilarang mencantumkan kotak logo/inisial shift (seperti kotak `S1`, `S2`, `S3`) pada kartu shift. Judul teks shift (`SHIFT 1`) dan rentang jam kerja sudah mencukupi secara mandiri tanpa memerlukan dekorasi kotak inisial.

---

## 🔢 11. Standar Pagination Tabel & Kontrol Jumlah Baris

* **Batasan Nomor Pagination Maksimal 4 Angka Navigasi (`< ... 2 3 4 5 ... >`)**:
  * Pagination pada tabel data master dibatasi secara ketat hanya menampilkan maksimal 4 angka navigasi aktif (`< ... 2 3 4 5 ... >`).
  * Tanda elipsis (`...`) ditampilkan secara dinamis di kiri atau kanan saat terdapat halaman tersembunyi di luar jendela 4 angka aktif.
  * Navigasi tetap mendukung tombol panah kiri (`<`), tombol panah kanan (`>`), serta pintasan keyboard panah (`ArrowLeft` / `ArrowRight`).
* **Komponen Pagination DaisyUI (`.join`) & Integritas Livewire**:
  * Tombol pagination dibungkus menggunakan komponen daisyUI `.join` dan tombol mini (`join-item btn btn-xs`).
  * Halaman aktif disorot dengan `btn-neutral font-bold font-mono`.
  * Setiap elemen tombol dan span wajib memiliki atribut `wire:key` deterministik untuk mencegah masalah DOM morphing pada Livewire 3.
* **Baris Info Footer "Showing of N List" (Murni Teks, Tanpa Dropdown Up-Down)**:
  * Footer tabel master menyajikan counter data: `Showing X-Y of Z list`.
  * **Dilarang keras menyematkan select dropdown dengan panah naik-turun (up-down menu)** di sisi kiri footer yang menimbulkan visual noise dan mengacaukan navigasi keyboard.
  * **Strict Inline (Anti-Wrap)**: Teks counter `Showing X-Y of Z list` **WAJIB** selalu disajikan dalam satu baris utuh (`whitespace-nowrap shrink-0`) dan dilarang patah/wrap ke baris baru.

---

## 📑 12. Standar Navigasi Tabs Border Bottom

* **Navigasi Header & Shift Berbasis `tabs-border`**:
  * Seluruh navigasi horizontal (baik navigasi menu utama di header aplikasi maupun navigasi tab pilihan shift) **WAJIB** menggunakan gaya **`tabs tabs-border`** (border-bottom indicator).
  * **Dilarang** menggunakan tombol bergaya box/pill/btn untuk navigasi menu utama dan tab shift.
  * Tab aktif ditandai secara elegan dengan garis aksen di bagian bawah (`tab-active font-bold text-neutral`), sedangkan tab tidak aktif tampil tenang dengan warna netral (`text-base-content/70 hover:text-base-content`).

---

## 🏷️ 13. Labeling Status & Level Berbasis Colored-Border (No Background)

* **Prinsip "Visual Warna Berbicara Tanpa Perlu Dibaca" (Signal by Color, Zero Verbose Subtext)**:
  * **Dilarang keras menyematkan teks status berlebih** seperti `(TERSIMPAN)` atau `(BELUM TERSIMPAN)` di dalam badge status.
  * Cukup gunakan satu kata tunggal: **`DRAFT`** atau **`FINAL`**. Biarkan perubahan aksen warna dan animasi titik (dot) yang mengomunikasikan siklus data kepada pengguna secara instan tanpa perlu membaca teks keterangan panjang.
* **Model Button Colored-Border Tanpa Background**:
  * Untuk seluruh penanda status dan level:
    - **Status Roster**:
      - `FINAL`: Aksen hijau emerald (`border border-emerald-600 text-emerald-600 bg-transparent`) dengan dot solid hijau.
      - `DRAFT` (Tersimpan / Clean di DB): Aksen kuning amber (`border border-amber-500 text-amber-600 dark:text-amber-500 bg-transparent`) dengan dot solid kuning.
      - `DRAFT` (Perubahan Belum Disimpan / Dirty): Aksen abu-abu slate (`border border-slate-500 text-slate-600 dark:text-slate-400 bg-transparent`) dengan dot pulsing animasi (`bg-slate-500 animate-pulse`).
    - **Status Operator / Leader**: `Aktif` (emerald border & text), `Cuti` (amber border & text), `Nonaktif` (neutral border & text).
    - **Level Station**: `Trained` (rose border & text), `Basic-Trained` (amber border & text), `Basic` (sky border & text).
  * **Format Wajib**:
    - Border berwarna tegas (`border border-{color}`).
    - Latar belakang transparan (`bg-transparent`, no solid fill background).
    - Warna teks identik dengan warna border (`text-{color}`).
    - Model button / rounded rectangle ringkas (`rounded-sm px-2 py-0.5 text-[11px] font-semibold`).

---

## 📖 14. Tampilan Teks Penuh pada Tabel (Anti-Truncate)

* **Dilarang Menyembunyikan Nama Panjang**:
  * Dilarang menggunakan kelas `truncate`, `max-w-[...]`, atau `text-ellipsis` yang memotong nama operator, nama stasiun, atau entitas penting di dalam baris tabel.
  * Teks harus selalu ditampilkan utuh dan dibiarkan membungkus (*wrap*) secara alami bila ruang kolom memerlukan.
