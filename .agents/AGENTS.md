---
tags: [agents, workflow, hub, global-context, v2]
aliases: [AGENTS.md, Global Context 2.0, Master Rules Hub]
related: ["[[PRINCIPLES]]", "[[ARCHITECTURE]]", "[[CODE_STYLE]]", "[[FRONTEND_RULES]]", "[[UI_RULES]]", "[[PERSONAL_UI_TASTE]]", "[[API_RULES]]", "[[ENV_STANDARDS]]"]
---

# AGENTS.md — Global Context & Master Rules Hub 2.0

> Dokumen ini adalah **pintu masuk utama dan Single Source of Truth** bagi AI coding assistant dalam merancang, menulis, merefaktor, dan memvalidasi kode di seluruh proyek.
> Seluruh aturan diatur secara terfokus, modular, dan saling terhubung menggunakan Obsidian wiki-links.

---

## 🗺️ Peta Navigasi Aturan Modular

| Modul Aturan | Ruang Lingkup & Fokus Utama |
|---|---|
| **[[PERSONAL_UI_TASTE]]** | **Preferensi Personal Pengguna**: Extreme minimalism, signal-over-noise, anti-badge soup, no emoji, subtle warning |
| **[[PRINCIPLES]]** | Fondasi Clean Code, SOLID, DRY, KISS, YAGNI, dan hierarki penyelesaian konflik |
| **[[ARCHITECTURE]]** | Arsitektur layered (Controller ➔ Service ➔ Repository), DTO, boundaries, & folder `client/` vs `server/` |
| **[[CODE_STYLE]]** | Standar penamaan deskriptif, batasan fungsi, type safety, structured logging |
| **[[FRONTEND_RULES]]** | Standar builder Vite & Tailwind CSS v4.* Zero-Config architecture |
| **[[UI_RULES]]** | Anti-AI Slop specification, The 10 Cardinal Sins, Dual-Density Typography scale, mobile a11y |
| **[[API_RULES]]** | Standar kontrak REST API, status codes, format amplop respons konsisten, keamanan backend |
| **[[ENV_STANDARDS]]** | Standar baku variabel lingkungan `.env` untuk fullstack monorepo, frontend, dan backend |

---

## 📁 1. Konvensi Struktur Direktori Proyek

Seluruh proyek fullstack maupun monorepo **WAJIB** memisahkan kode menjadi dua direktori utama:

```
project-root/
├── client/     # Frontend application (Vite + Tailwind v4 — lihat [[FRONTEND_RULES]] & [[UI_RULES]])
└── server/     # Backend application (API, DB, Service Layer — lihat [[API_RULES]] & [[ARCHITECTURE]])
```

> **Prinsip**: Dilarang menggunakan nama folder ad-hoc (misal: `frontend/`, `backend/`, `ui/`, `api/`) di level root agar struktur tetap konsisten, terprediksi, dan simetris mengikuti pola *Client-Server Architecture*.

---

## 🔒 2. Zero Hardcoded Secrets Protocol

> **ATURAN MUTLAK**: **JANGAN PERNAH** mencantumkan API key, secret token, password database, atau kredensial apa pun langsung di dalam kode (*hardcoded*). **SELALU** gunakan **Environment Variables (`.env`)**.

- File `.env` **wajib** terdaftar di `.gitignore` dan tidak boleh di-commit.
- Setiap modul wajib menyediakan template `.env.example` dengan nilai dummy (lihat [[ENV_STANDARDS]]).
- Pada `client/`, hanya variabel dengan prefix `VITE_` yang boleh diakses publik.

---

## ⚡ 3. AI Operating Workflow (Siklus 5 Tahap)

Setiap pengerjaan task oleh agen wajib melalui alur kerja terstruktur ini:

```
1. Understand ──> 2. Plan ──> 3. Implement ──> 4. Validate ──> 5. Report
```

1. **Understand**: Pahami konteks, inspeksi kode existing, dan buka dokumen aturan yang relevan di atas.
2. **Plan**: Rancang solusi paling sederhana (*smallest viable change*). Hindari *over-engineering* atau *premature abstraction*.
3. **Implement**: Tulis kode yang mematuhi konvensi penamaan ([[CODE_STYLE]]), arsitektur layer ([[ARCHITECTURE]]), dan antarmuka bersih ([[UI_RULES]]).
4. **Validate**: Jalankan type checking, linter, tests, dan audit kepatuhan sebelum menyatakan task selesai.
5. **Report**: Sampaikan ringkasan perubahan secara padat, transparan, dan profesional.

---

## 🔗 4. Integrasi ke Root Project Lain (Directory Junction)

Agar proyek target dapat memanfaatkan master hub aturan terpusat ini tanpa duplikasi berkas, buat **Directory Junction** di root direktori proyek target:

### Perintah Pembuatan Link (Windows)

```cmd
cmd /c mklink /J .agents "C:\Users\USER\Desktop\ai-workflow\.agents-v2"
```

* **Command Prompt (CMD)**:
  ```cmd
  mklink /J .agents "C:\Users\USER\Desktop\ai-workflow\.agents-v2"
  ```
* **PowerShell**:
  ```powershell
  cmd /c mklink /J .agents "C:\Users\USER\Desktop\ai-workflow\.agents-v2"
  ```

### Melepas Link Secara Aman
Jika ingin melepas tautan di proyek target tanpa menghapus berkas master:
```cmd
cmd /c rmdir .agents
```

---

## 🎯 Siklus Keputusan AI (Decision Budget)
- **Kebenaran Fungsional & Keamanan Sistem** tidak boleh dikompromikan demi apa pun.
- **Anti-Slop Mutlak**: Desain antarmuka harus fungsional dan berkarakter, bebas dari klise template generik AI.
- **Smallest Viable Change**: Selesaikan kebutuhan dengan jumlah baris kode dan abstraksi paling minimal yang bekerja optimal.
