# 📋 absensi_siswa — Aplikasi Manajemen Kehadiran Digital

**absensi_siswa** adalah aplikasi manajemen kehadiran berbasis web yang dirancang untuk mendigitalisasi proses pencatatan kehadiran serta pemantauan disiplin siswa secara real-time.

Proyek ini dikembangkan sebagai bagian dari tugas praktikum **Pengembangan Perangkat Lunak dan Gim (PPLG)** dengan menggunakan PHP, MySQL, HTML, CSS, dan JavaScript.

---

## ✨ Fitur Utama

### 🔐 Multi-Role Authentication

Sistem login dengan pembagian hak akses antara **Admin** dan **Siswa**.

### 📝 Pencatatan Kehadiran Real-Time

Input kehadiran siswa dengan status:

* Hadir
* Sakit
* Izin
* Alpa

### 📈 Dashboard Interaktif

Menampilkan statistik kehadiran siswa dalam bentuk ringkasan data yang mudah dipahami.

---

## 🔑 Akun Demo Pengujian

| Role  | Username     | Password  |
| ----- | ------------ | --------- |
| Admin | adminabsensi | 123456    |
| Siswa | ricky123     | siswa2026 |

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP (OOP & Session Management)
* **Frontend:** HTML5, CSS3, JavaScript
* **Database:** MySQL / MariaDB

---

## 🚀 Instalasi dan Menjalankan Proyek

### 1. Clone atau Unduh Proyek

Letakkan folder proyek ke dalam direktori XAMPP:

```bash
C:\xampp\htdocs\absensi_siswa
```

### 2. Buat Database

Buka **phpMyAdmin**, lalu buat database baru dengan nama:

```sql
db_absensi
```

### 3. Import Database

Import file SQL yang tersedia pada folder proyek ke database **db_absensi**.

### 4. Jalankan Aplikasi

Buka browser dan akses:

```text
http://localhost/absensi_siswa
```
