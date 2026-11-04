---
tags: [rules, engineering, principles, clean-code, solid]
aliases: [Engineering Principles, Clean Code, SOLID, KISS, YAGNI, DRY]
related: ["[[ARCHITECTURE]]", "[[CODE_STYLE]]", "[[AGENTS]]"]
---

# Engineering Principles

Prinsip-prinsip ini dipegang sebagai **default**, bukan dogma mutlak. Boleh dilanggar dengan alasan eksplisit yang dikomunikasikan.

---

## 1. Clean Code
Kode harus terbaca dan *self-explanatory* tanpa komentar berlebih. Jika kode butuh banyak komentar untuk dipahami, itu sinyal bahwa kode perlu direfaktor.

---

## 2. KISS (Keep It Simple, Stupid)
Gunakan solusi paling sederhana yang menyelesaikan masalah dengan benar.
- Jangan tambahkan abstraksi tanpa kebutuhan nyata.
- Jangan gunakan design pattern hanya karena ada.

---

## 3. YAGNI (You Aren't Gonna Need It)
- Jangan implementasi fitur yang belum dibutuhkan saat ini.
- Jangan buat abstraksi generik untuk kebutuhan hipotetis.
- Jangan optimasi sebelum ada bottleneck yang terukur.

---

## 4. DRY (Don't Repeat Yourself)
- Hindari duplikasi business logic — harus ada Single Source of Truth.
- **Pengecualian**: Jika dua blok kode mirip tetapi memiliki *alasan perubahan yang berbeda*, duplikasi dapat diterima.

---

## 5. SOLID (Ringkas & Aplikatif)

| Prinsip | Inti Pemahaman | Kapan Diterapkan |
|---|---|---|
| **Single Responsibility** | Satu class/fungsi = satu alasan berubah | Selalu |
| **Open/Closed** | Terbuka untuk ekstensi, tertutup untuk modifikasi | Saat ada variasi behavior |
| **Liskov Substitution** | Subclass harus bisa menggantikan parent tanpa merusak sistem | Saat menggunakan inheritance |
| **Interface Segregation** | Interface kecil, spesifik, dan terfokus | Saat interface mulai membengkak |
| **Dependency Inversion** | Modul high-level tidak bergantung pada detail low-level | Selalu |

---

## 6. Hierarki Saat Konflik

```
KISS vs DRY → KISS menang jika duplikasi hanya 2x dan alasan perubahannya berbeda
DRY vs KISS → DRY menang jika logic yang sama dipakai di 3+ tempat dengan alasan perubahan sama
```

---

## 7. Separation of Concerns
Diterapkan di **tiga level**:
- **Fungsi**: Satu fungsi = satu tugas spesifik (lihat [[CODE_STYLE]]).
- **Class / Module**: Satu module = satu domain tanggung jawab.
- **Layer**: Presentation, business logic, dan data access tidak saling mencampuri (lihat [[ARCHITECTURE]]).

---

## 8. Convention over Configuration
Ikuti konvensi framework/stack yang sudah ada **sebelum** membuat aturan baru. Jika project menggunakan Laravel, ikuti cara Laravel. Jika menggunakan FastAPI/Express, ikuti pola resmi ekosistem tersebut.
