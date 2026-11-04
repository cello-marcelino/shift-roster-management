---
tags: [rules, frontend, tailwind-v4, vite, zero-config, tooling]
aliases: [Frontend Rules, Tooling Rules, Tailwind v4 Zero-Config, Vite Standard]
related: ["[[AGENTS]]", "[[UI_RULES]]", "[[ARCHITECTURE]]", "[[ENV_STANDARDS]]"]
---

# Frontend Tooling Rules (Vite & Tailwind v4)

Pedoman standar builder dan styling engine pada direktori `client/`. Menegakkan penggunaan **Vite** sebagai dev server/bundler utama dan **Tailwind CSS v4.*** dengan arsitektur **Zero-Config**.

---

## 1. Standar Builder: Vite Mandatory

Setiap project frontend di dalam direktori `client/` **WAJIB** menggunakan **Vite**.

### Setup Plugin Vite (`client/vite.config.ts`)
Gunakan plugin resmi `@tailwindcss/vite`:

```typescript
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    tailwindcss(),
    // plugin framework UI (misal: react(), vue(), svelte())
  ],
});
```

> **Aturan**: Dilarang menggunakan bundler warisan lama (Create React App, Webpack manual tanpa alasan arsitektural khusus) untuk frontend baru.

---

## 2. Tailwind CSS v4.*: Zero-Config Architecture

Tailwind CSS v4 mengadopsi arsitektur native CSS tanpa memerlukan file konfigurasi JavaScript terpisah (`tailwind.config.js`).

### Instalasi Dependensi (di dalam `client/`)
```bash
npm install tailwindcss @tailwindcss/vite
```

### Entry Point CSS Utama (`client/src/main.css`)
Cukup impor modul tailwindcss pada baris pertama berkas CSS:

```css
@import "tailwindcss";
```

### Kustomisasi Tema Melalui `@theme` (CSS-First)
Seluruh kustomisasi tema (warna semantik, font, breakpoint, radius) didefinisikan langsung di dalam berkas CSS menggunakan blok `@theme`:

```css
@import "tailwindcss";

@theme {
  /* Font Family */
  --font-sans: 'Inter', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;

  /* Semantic Color Tokens */
  --color-canvas: #f8fafc;
  --color-surface: #ffffff;
  --color-primary: #0f172a;
  --color-primary-hover: #1e293b;
  --color-border-hairline: #e2e8f0;
}
```

---

## 3. Matriks Boleh vs Dilarang (Tooling Constraints)

| ✅ WAJIB / DIIZINKAN | ❌ DILARANG KERAS |
|---|---|
| Menggunakan `@tailwindcss/vite` di `vite.config.*` | Membuat file `tailwind.config.js` atau `tailwind.config.ts` |
| Mengimpor styling via `@import "tailwindcss";` | Menggunakan `@tailwind base; @tailwind components; @tailwind utilities;` (sintaks v3 lama) |
| Kustomisasi token tema via direktif `@theme` di CSS | Menggunakan `postcss.config.js` terpisah hanya untuk Tailwind |
| Menggunakan CSS Variables native untuk tema | Menjalankan perintah lama `npx tailwindcss init -p` |

---

## 4. Checklist Verifikasi Frontend

- [ ] File `client/vite.config.*` memuat plugin `tailwindcss()` dari `@tailwindcss/vite`.
- [ ] Berkas CSS utama memuat `@import "tailwindcss";`.
- [ ] Tidak ada berkas `tailwind.config.*` atau `postcss.config.*` di folder `client/`.
- [ ] Desain antarmuka mematuhi The 10 Cardinal Sins di [[UI_RULES]].
- [ ] Server development (`npm run dev`) berjalan tanpa warning deprecation Tailwind.
