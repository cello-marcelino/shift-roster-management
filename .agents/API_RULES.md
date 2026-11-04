---
tags: [rules, server, api, rest, backend, security]
aliases: [API Rules, REST Standards, Server API Specification]
---

# API Rules & Backend Standards

Seluruh endpoint yang dibuat atau dimodifikasi pada layer `server/` **WAJIB** mematuhi aturan ini secara konsisten.

---

## 1. Konvensi Endpoint & Protokol

- **Arsitektur**: RESTful over HTTP dengan payload format **JSON**.
- **Versioning**: Wajib menggunakan prefix versi `/api/v1/...`. Setiap perubahan yang merusak kontrak sistem (*breaking changes*) wajib menaikkan nomor versi (`v2`, `v3`).
- **Penamaan Path**:
  - Menggunakan **kata benda jamak (*plural nouns*)**, huruf kecil (*lowercase*), dan dipisahkan tanda strip (**`kebab-case`**), contoh: `/api/v1/rekomendasi-dosen`, `/api/v1/mahasiswa`.
  - **Dilarang** memuat kata kerja (*verbs*) di dalam path URL (contoh salah: `/api/v1/get-users`, `/api/v1/create-order`).
  - **Nesting maksimal 2 level** untuk relasi resource:
    - ✅ `/api/v1/dosen/{id}/jadwal-sidang`
    - ❌ `/api/v1/fakultas/{id}/prodi/{id}/dosen/{id}/jadwal` *(terlalu dalam)*
- **Penamaan Field JSON**: Seluruh key pada request dan response JSON **wajib** menggunakan format **`snake_case`**.
- **Separasi Layer**: Seluruh logika bisnis berada di **Service Layer**, dilarang menaruh business logic di controller/handler HTTP.

### HTTP Methods & Semantik

| Method | Semantik Aksi | Sifat Idempoten |
|---|---|:---:|
| `GET` | Mengambil satu atau sekumpulan resource (tanpa *side-effect*) | Ya |
| `POST` | Membuat resource baru | Tidak |
| `PUT` | Menggantikan (*replace*) seluruh atribut resource secara penuh | Ya |
| `PATCH` | Memperbarui sebagian field/atribut resource (*partial update*) | Ya |
| `DELETE` | Menghapus resource | Ya |

---

## 2. Standar HTTP Status Codes

Gunakan status code sesuai kondisi nyata. **DILARANG KERAS** mengembalikan status `200 OK` untuk respons error!

| Status Code | Makna Semantik | Kondisi Penggunaan |
|---|---|---|
| **200 OK** | Sukses dengan payload | `GET`, `PUT`, `PATCH` berhasil mengembalikan data |
| **201 Created** | Resource berhasil dibuat | `POST` pembuatan resource baru |
| **204 No Content** | Sukses tanpa body | `DELETE` atau update yang tidak perlu mengembalikan data |
| **400 Bad Request** | Request salah format | Format payload rusak, JSON syntax error |
| **401 Unauthorized** | Belum terotentikasi | Token tidak disertakan, token kedaluwarsa, atau tidak valid |
| **403 Forbidden** | Terotentikasi tapi tanpa izin | User tidak memiliki hak akses/role ke resource tersebut |
| **404 Not Found** | Resource tidak ditemukan | Endpoint atau ID resource tidak ada di database |
| **409 Conflict** | Terjadi konflik data | Duplikasi data unik (misal: email/NIM sudah terdaftar) |
| **422 Unprocessable** | Validasi input gagal | Format benar tapi aturan validasi data gagal |
| **429 Too Many Req** | Terkena rate limit | Terlalu banyak request dalam jendela waktu tertentu |
| **500 Server Error** | Kesalahan internal server | Unhandled exception di server (sembunyikan detail error) |

---

## 3. Format Response Terstandarisasi

Semua response wajib konsisten menggunakan amplop struktur seragam.

### A. Response Sukses (Single / Object)
```json
{
  "success": true,
  "message": "Data dosen berhasil diperbarui",
  "data": {
    "id": 102,
    "nama_lengkap": "Dr. Eng. Budi Santoso",
    "status_aktif": true,
    "created_at": "2026-10-24T09:00:00Z"
  },
  "meta": {}
}
```

### B. Response Sukses List (Collection dengan Paginasi)
```json
{
  "success": true,
  "message": "Daftar rekomendasi berhasil diambil",
  "data": [
    { "id": 1, "nama_dosen": "Dr. Ahmad", "skor": 0.94 }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

### C. Response Error
```json
{
  "success": false,
  "message": "Validasi formulir gagal",
  "errors": [
    {
      "field": "email",
      "message": "Format alamat email tidak valid"
    },
    {
      "field": "password",
      "message": "Password minimal terdiri dari 8 karakter"
    }
  ],
  "request_id": "req-98f2b3c1-04e2"
}
```
> **Aturan Pesan**: Pesan error yang ditampilkan ke user (`message` dan `errors.message`) **wajib berbahasa Indonesia yang singkat, jelas, dan solutif**.

---

## 4. Keamanan Sistem & Proteksi Data

1. **Validasi Input Ketat**:
   - Seluruh input dari client wajib divalidasi di sisi server (tipe data, batas panjang karakter, format regex, dan batas rentang numerik).
2. **Parameterized Queries**:
   - Seluruh query database wajib menggunakan parameter binding / parameterized query.
   - **DILARANG KERAS** melakukan penggabungan string (*string concatenation* / interpolation) ke query SQL.
3. **Autentikasi & Otorisasi**:
   - Autentikasi via header standar: `Authorization: Bearer <token>`.
   - Periksa **peran pengguna (*RBAC*)** dan **kepemilikan data (*resource ownership*)** pada setiap pemanggilan endpoint privat (cegah IDOR).
4. **Hashing & Secrets**:
   - Password wajib di-hash menggunakan algoritma modern: **`bcrypt`** atau **`argon2`**.
   - Kunci rahasia (*secrets*), kredensial DB, dan token pihak ketiga HANYA boleh dibaca melalui **environment variable** (`.env`), dilarang di-hardcode (lihat panduan lengkap di [[ENV_STANDARDS]]).
5. **Jaringan & Batasan Akses**:
   - CORS dikonfigurasi secara ketat hanya untuk origin yang diizinkan (*whitelisted*).
   - Wajib HTTPS pada environment staging dan production.
   - Terapkan **Rate Limiting** untuk endpoint auth (login/register) dan endpoint komputasi berat.
6. **Data Leakage Prevention**:
   - Dilarang membocorkan *stack trace*, error internal database, atau path direktori server ke response client.
   - Dilarang mencatat password, secret, token JWT, atau PII sensitif ke dalam server log.

---

## 5. Standar List: Paginasi, Filter & Sorting

- **Paginasi**: Menggunakan parameter `?page=1&per_page=20` (nilai default: 20 item, batas maksimal: 100 item).
- **Filter**: Menggunakan query parameter eksplisit, contoh: `?status=aktif&kategori=akademik`.
- **Sorting**: Menggunakan parameter `?sort=nama` (urutan menaik / *ascending*) atau `?sort=-created_at` (urutan menurun / *descending* dengan prefix minus `-`).

---

## 6. Standar Khusus Endpoint Machine Learning (ML) & NLP

Untuk endpoint yang menjalankan inferensi model AI, NLP, atau algoritma rekomendasi:
1. **Model Lifecycle**: Model wajib di-load **sekali saja saat server pertama kali start**, bukan di-load ulang setiap request datang.
2. **Execution Timeout**: Pasang batas waktu timeout eksekusi yang tegas agar tidak memblokir event loop atau worker pool.
3. **Result Caching**: Terapkan caching (in-memory / Redis) untuk input parameter yang menghasilkan komputasi mahal.
4. **Explainability**: Sertakan rincian komponen skor pada response agar hasil prediksi/rekomendasi dapat dijelaskan secara transparan kepada pengguna.

---

## 7. Checklist Wajib Sebelum Commit (Quality Gate)

Pastikan seluruh checklist ini terverifikasi sebelum melakukan commit/PR:
- [ ] Penamaan path RESTful (`kebab-case`, kata benda jamak), HTTP method tepat, dan status code sesuai semantik.
- [ ] Format amplop response konsisten (`success`, `message`, `data`, `meta` / `errors`, `request_id`).
- [ ] Seluruh input divalidasi di server boundary; auth token dan otorisasi kepemilikan resource telah diverifikasi.
- [ ] Parameterized query digunakan untuk seluruh akses database (zero SQL Injection).
- [ ] Tidak ada credential/secrets yang di-hardcode; response error tidak membocorkan detail teknis internal.
- [ ] Dokumentasi endpoint (path, params, request/response body, error codes) telah diperbarui.
- [ ] Automated tests tersedia dan lolos (skenario sukses, validasi gagal, dan akses ditolak 401/403).
