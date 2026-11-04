# PRD Sistem Penjadwalan Manpower Line Operator

Oct 24, 2026 · @Christian Marcelino Sinaga

## 1. Ringkasan dan Latar Belakang

Sistem Penjadwalan Manpower Line Operator adalah aplikasi web desktop yang membuat leader menyusun roster mingguan operator secara digital dan menghasilkan PDF siap dibagikan ke grup koordinasi lapangan. Sistem ini menggantikan alur lama: jadwal disusun di spreadsheet, layar monitor difoto dengan ponsel, lalu foto dikirim ke grup.

**Masalah operasional**

- **Distribusi tidak efisien.** Foto layar monitor sulit dibaca dan kualitasnya bergantung pada kamera dan sudut pengambilan.
- **Risiko duplikasi.** Penyusunan manual dapat menempatkan badge ID yang sama di dua line atau shift berbeda dalam satu periode roster.
- **Aturan penempatan tidak terkontrol.** Kecocokan operator dengan level station dicek dari ingatan leader, bukan oleh sistem.

## 2. Tujuan dan Indikator Keberhasilan

Tujuan versi 1: leader dapat menyusun satu roster mingguan untuk satu line tanpa mengetik nama, hanya dengan klik, dan mengunduhnya sebagai PDF yang terbaca jelas.

| Tujuan | Indikator keberhasilan |
| --- | --- |
| Menghapus foto layar sebagai media distribusi | 100% roster dibagikan lewat PDF unduhan |
| Mencegah duplikasi badge ID | Setiap duplikasi dalam 1 periode roster memunculkan peringatan sebelum PDF diunduh |
| Mempercepat penyusunan | Roster satu line selesai tanpa input teks manual (target waktu disepakati bersama leader saat pilot) |
| Menjaga kecocokan penempatan | Penempatan operator tidak relevan di station level basic-trained atau trained selalu ditandai |

## 3. Pengguna dan Ruang Lingkup Versi 1

Pengguna akhir adalah leader. Versi 1 dipakai satu pengguna tunggal secara offline, tanpa login dan tanpa pembatasan akses per leader. Beberapa leader tetap berkoordinasi secara lisan, lalu satu orang menyusun roster dan menjadi penanggung jawab modul.

**Dalam cakupan versi 1**

- Manajemen data master: manpower, line, leader, station.
- Penyusunan roster mingguan untuk satu atau lebih line, dengan status draft dan final.
- Peringatan duplikasi badge ID dan ketidakcocokan level station.
- Ekspor roster ke PDF.
- Impor data master awal dari file JSON yang sudah disiapkan.

## 4. Kebutuhan Fungsional

Modul terdiri dari empat master data dan satu fitur utama, yaitu roster mingguan. Semua master mendukung tambah, ubah, hapus, dan pencarian.

**4.1 Manajemen Manpower**

| Field | Aturan |
| --- | --- |
| Badge ID | Unik, tidak boleh kosong |
| Nama | Wajib diisi |
| Station utama | Satu station utama, ditampilkan sebagai 3 pilihan station |
| Status | Aktif atau Cuti, berupa toggle tanpa rentang tanggal |

Operator berstatus Cuti otomatis tidak muncul sebagai pilihan saat menyusun roster.

**4.2 Manajemen Line**

| Field | Aturan |
| --- | --- |
| Nama line | Wajib diisi |
| Jumlah proses/station | Dihitung dari daftar station milik line |
| MP standard | Jumlah operator standar untuk seluruh line, bukan per station |
| Pola kerja | 5 hari kerja (2 shift) atau 6 hari kerja (3 shift) |
| Jam kerja per shift | Dapat berbeda untuk setiap line |
| Status | Aktif, Off, atau Waiting Material |

Line berstatus Off atau Waiting Material tetap dapat dijadwalkan.

**4.3 Manajemen Leader**

| Field | Aturan |
| --- | --- |
| Badge ID | Unik, tidak boleh kosong |
| Nama | Wajib diisi, tercetak di PDF |
| Status | Aktif atau tidak aktif |

**4.4 Manajemen Station**

| Field | Aturan |
| --- | --- |
| Nama station | Wajib diisi |
| Line pemilik | Setiap station hanya milik satu line. Station bernama sama di line lain adalah data terpisah |
| Level | Basic, Basic-Trained, atau Trained |
| Cycle time | Informasi referensi, tidak dipakai untuk validasi di versi 1 |

Daftar station untuk seeder awal (level ditetapkan kemudian, jadi kosong saat seeding):

1. VIFG
2. Packaging
3. Molding
4. Potting
5. Hipot testing
6. Crimping
7. Stripping
8. Ultrasonic welding
9. Laser marking
10. PCBA testing
11. Auto-solder
12. Manual solder
13. Seal ring
14. Insert housing

Pemetaan setiap station ke line pemiliknya belum ditentukan dan diisi saat seeding atau lewat JSON master.

**4.5 Roster Shift Schedule (fitur utama)**

1. Leader memilih line dan periode (satu minggu). Leader dapat menyalin roster minggu sebelumnya sebagai titik awal atau menyusun dari kosong, dan hasil salinan dapat diubah penuh.
2. Sistem menampilkan tabel jadwal berisi shift dan station milik line tersebut.
3. Leader menambahkan operator ke sel jadwal lewat klik dan pilihan dropdown, tanpa mengetik nama.
4. Station ditentukan langsung di tabel jadwal. Satu operator hanya memegang satu station dalam satu shift.
5. Satu station dapat dipegang beberapa operator, dan dua station atau lebih dapat dioperasikan satu operator.
6. Sistem menampilkan peringatan secara langsung (lihat bagian 5). Leader juga dapat melihat gabungan roster beberapa line pada minggu yang sama untuk mengecek duplikasi lintas line.
7. Leader menyimpan sebagai draft, lalu menetapkan final dan mengunduh PDF.

Untuk setiap baris jadwal, sistem menampilkan: nama line, daftar operator beserta badge ID dan station, shift, dan jam kerja.

## 5. Aturan Bisnis dan Validasi

Semua validasi berbentuk peringatan atau info. Sistem tidak memblokir penyimpanan maupun pengunduhan PDF.

**5.1 Pola kerja dan shift**

| Pola | Hari kerja | Jumlah shift | Durasi shift | Rentang waktu |
| --- | --- | --- | --- | --- |
| A | 5 hari | 2 shift | 9 jam | Shift 1: 07.00 sampai 16.00. Shift 2: 16.00 sampai 01.00 |
| B | 6 hari | 3 shift | 8 jam | Shift 1: 07.00 sampai 15.00. Shift 2: 15.00 sampai 23.00. Shift 3: 23.00 sampai 07.00 |

Jam kerja dapat berbeda di setiap line, sehingga jam mulai dan selesai tiap shift disimpan per line.

**5.2 Rotasi shift mingguan**

Periode roster adalah satu minggu. Rotasi berjalan mundur: pagi, malam, siang, lalu kembali ke pagi. Pada line pola 5 hari (2 shift), rotasi tetap mundur sehingga shift 1 dan shift 2 bergantian setiap minggu.

**5.3 Matriks peringatan**

| Kondisi | Respons sistem |
| --- | --- |
| Badge ID sama muncul di line atau shift berbeda dalam satu periode roster | Peringatan duplikasi, menunjukkan lokasi kemunculan lainnya |
| Operator tidak relevan dengan station (station utama berbeda) dan level station Basic | Info saja |
| Operator tidak relevan dengan station dan level station Basic-Trained atau Trained | Peringatan |
| Jumlah operator di roster kurang dari MP standard line | Peringatan kekurangan manpower |
| Operator berstatus Cuti | Tidak tersedia di pilihan operator |

**5.4 Definisi relevan**

Operator dianggap relevan dengan sebuah station jika station utamanya sama dengan station tersebut.

## 6. Output PDF

PDF diunduh langsung dari aplikasi dan tidak dikirim otomatis ke aplikasi pesan. Revisi setelah PDF dibagikan tidak punya fitur khusus: leader mengubah roster lalu mengunduh PDF baru. Secara bawaan satu line menghasilkan satu PDF, dan leader dapat memilih opsi untuk menggabungkan beberapa line dalam satu PDF.

| Elemen | Keterangan |
| --- | --- |
| Line | Nama line |
| Tanggal | Periode roster (satu minggu) |
| Shift | Nama shift beserta waktu kerja |
| Badge dan nama operator | Satu baris per operator |
| Nama station | Station yang dipegang operator pada shift tersebut |
| Nama leader | Penanggung jawab roster |
| Waktu kerja | Jam mulai dan selesai shift |

Peringatan sistem tidak ikut dicetak di PDF. Orientasi landscape, tanpa kop atau logo perusahaan. Ukuran kertas menunggu contoh format spreadsheet saat ini (lihat bagian 11).

## 7. Alur Pengguna

1. Leader mengimpor data master awal dari file JSON (sekali di awal), lalu merapikan data lewat menu master.
2. Leader membuka menu Roster dan memilih line serta minggu yang akan dijadwalkan.
3. Leader mengisi tabel jadwal dengan klik: memilih shift, station, lalu operator dari daftar operator aktif.
4. Sistem menampilkan peringatan atau info langsung di sel yang bermasalah.
5. Leader menyimpan sebagai draft dan boleh melanjutkan di lain waktu.
6. Setelah yakin, leader menetapkan roster sebagai final dan mengunduh PDF.
7. Leader membagikan PDF ke grup koordinasi di luar aplikasi.

## 8. Model Data

Usulan entitas awal. Nama field final menyesuaikan struktur file JSON yang sudah disiapkan.

| Entitas | Field utama | Relasi |
| --- | --- | --- |
| Manpower | badge\_id, nama, station\_utama, status (aktif/cuti) | Dipilih sebagai peserta roster |
| Leader | badge\_id, nama, status | Penanggung jawab roster |
| Line | id, nama, mp\_standard, pola\_kerja, jam\_shift, status | Memiliki banyak Station |
| Station | id, line\_id, nama, level, cycle\_time | Dimiliki tepat satu Line |
| Roster | id, line\_id, leader\_id, minggu\_mulai, status (draft/final) | Memiliki banyak Entri Roster |
| Entri Roster | roster\_id, hari, shift, station\_id, badge\_id | Menghubungkan Roster, Station, dan Manpower |

## 9. Kebutuhan Non-Fungsional dan Teknis

- **Stack:** Laravel untuk backend dan Livewire untuk antarmuka reaktif.
- **Lingkungan:** berjalan offline di jaringan atau mesin lokal, tanpa ketergantungan layanan cloud.
- **Pengguna:** satu pengguna pada satu waktu, tanpa autentikasi di versi 1.
- **Perangkat:** layar desktop, antarmuka dioptimalkan untuk tabel jadwal yang lebar.
- **Penyimpanan data:** lokal. Pilihan penyimpanan (berkas JSON atau database ringan) ditentukan saat desain teknis.
- **PDF:** dibuat tanpa koneksi internet dan tetap terbaca jelas saat dilihat di ponsel.

## 10. Di Luar Cakupan dan Tahap Berikutnya

**Di luar cakupan versi 1**

- Login dan pembatasan akses per leader.
- Pengiriman PDF otomatis ke aplikasi pesan.
- Fitur revisi dengan penandaan versi dan riwayat perubahan.
- Perpindahan station dalam satu shift untuk satu operator.
- Pencatatan jam istirahat.

**Tahap berikutnya**

- Dashboard dan laporan, misalnya rekap jam kerja per operator dan utilisasi line.
- Dukungan beberapa pengguna dan peran lain (supervisor, admin, viewer).

## 11. Asumsi dan Pertanyaan Terbuka

**Pertanyaan terbuka**

- [x] Pola 6 hari: 3 shift x 8 jam. Apakah jam shift 07.00 sampai 15.00, 15.00 sampai 23.00, dan 23.00 sampai 07.00?
- [x] Rotasi mundur pagi, malam, siang: bagaimana urutan rotasi untuk line pola 5 hari yang hanya punya 2 shift?
- [x] Apakah rotasi diterapkan otomatis oleh sistem (misalnya menyalin roster minggu lalu lalu menggeser shift) atau leader tetap mengisi manual tiap minggu?
- [ ] Ukuran kertas PDF (orientasi landscape dan tanpa kop atau logo sudah ditetapkan). Contoh format spreadsheet saat ini diperlukan.
- [x] Satu PDF per line atau gabungan semua line dalam satu minggu?
- [x] Satu roster berlaku untuk satu line saja, atau leader perlu melihat gabungan beberapa line saat mengecek duplikasi lintas line?
- [ ] Struktur final file JSON data master (nama field dan relasi station ke line).

**Asumsi yang dipakai di PRD ini**

- Operator dianggap relevan dengan station jika station utamanya sama dengan station tersebut.
- Peringatan kekurangan MP standard muncul jika jumlah operator unik dalam satu shift kurang dari MP standard line.
- Peringatan sistem tidak dicetak di PDF.
- Leader yang tercetak di PDF dipilih saat membuat roster.
