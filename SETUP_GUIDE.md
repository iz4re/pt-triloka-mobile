# 🚀 Panduan Setup Proyek CV Triloka

Panduan ini akan membantu Anda menjalankan proyek **CV Triloka** (Backend Laravel & Frontend Flutter) di mesin lokal Anda.

---

## 📋 Prasyarat

Sebelum memulai, pastikan Anda telah menginstal:
- **PHP** (v8.2 atau lebih baru)
- **Composer**
- **Node.js & NPM**
- **MySQL / MariaDB** (atau gunakan XAMPP/Laragon)
- **Flutter SDK** (v3.19 atau lebih baru)

---

## 🛠️ 1. Setup Backend (Laravel)

Ikuti langkah-langkah ini untuk menjalankan API server:

1.  **Masuk ke direktori backend:**
    ```bash
    cd cv-triloka-backend
    ```

2.  **Instal dependensi PHP:**
    ```bash
    composer install
    ```

3.  **Setup Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Konfigurasi Database:**
    - Buka file `.env` dan sesuaikan pengaturan database Anda:
      ```env
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=cv_triloka_db
      DB_USERNAME=root
      DB_PASSWORD=
      ```
    - Buat database baru bernama `cv_triloka_db` di MySQL Anda.

5.  **Jalankan Migrasi & Seeder:**
    ```bash
    php artisan migrate --seed
    ```

6.  **Setup Symlink Storage & Assets:**
    ```bash
    php artisan storage:link
    npm install
    npm run build
    ```

7.  **Jalankan Server:**
    ```bash
    php artisan serve
    ```
    *Server backend akan berjalan di `http://127.0.0.1:8000`*

---

## 📱 2. Setup Frontend Mobile (Flutter)

Buka terminal baru untuk menjalankan aplikasi Flutter:

1.  **Kembali ke root folder (jika masih di folder backend):**
    ```bash
    cd ..
    ```

2.  **Instal dependensi Flutter:**
    ```bash
    flutter pub get
    ```

3.  **Jalankan Aplikasi:**
    - **Untuk Chrome (Web):**
      ```bash
      flutter run -d chrome
      ```
    - **Untuk Android/iOS:**
      Pastikan emulator atau perangkat fisik sudah terhubung, lalu jalankan:
      ```bash
      flutter run
      ```

---

## 🔥 3. Setup Firebase (Penting)

Proyek ini menggunakan Firebase untuk autentikasi dan layanan lainnya. Pastikan file konfigurasi berikut ada di tempatnya:

### **A. Firebase untuk Android**
Pastikan file `google-services.json` ada di direktori:
`android/app/google-services.json`

### **B. Firebase untuk Backend (Laravel)**
Pastikan file kredensial service account (`.json`) ada di direktori:
`cv-triloka-backend/storage/app/firebase_credentials.json`

> [!IMPORTANT]
> Jika teman Anda menggunakan akun Firebase yang baru/berbeda, ia harus:
> 1. Membuat project baru di [Firebase Console](https://console.firebase.google.com/).
> 2. Mengunduh `google-services.json` baru untuk Android.
> 3. Membuat *Service Account Key* (JSON) di Project Settings > Service Accounts dan menyimpannya sebagai `firebase_credentials.json` di folder backend.

---

## 🔑 Akun Uji Coba (Test Accounts)

Gunakan akun berikut setelah menjalankan perintah seed:

| Peran (Role) | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@triloka.com` | `password` |
| **Klien (Mitra)** | `mitra@example.com` | `password` |

---

## ❓ Troubleshooting (Masalah Umum)

- **API Tidak Terhubung (Penting):** 
  Jika aplikasi Flutter tidak bisa konek ke API:
  1. Buka file `lib/services/api_config.dart`.
  2. Jika menggunakan **Emulator Android**, pastikan `usePhysicalDevice = false`. (Default menggunakan `10.0.2.2` untuk akses localhost komputer).
  3. Jika menggunakan **HP Fisik**, set `usePhysicalDevice = true` dan ganti `wifiIp` dengan alamat IP komputer Anda (jalankan `ipconfig` di CMD untuk cek IP).
  4. Pastikan HP dan Komputer berada di **satu jaringan WiFi yang sama**.
- **Gagal Koneksi Database:** Pastikan MySQL (XAMPP/Laragon) sudah menyala dan nama database di `.env` sudah benar.
- **Gambar Tidak Muncul:** Pastikan sudah menjalankan `php artisan storage:link` di folder backend.
- **Flutter Pub Get Error:** Coba jalankan `flutter clean` lalu ulangi `flutter pub get`.

---

**Butuh bantuan lebih lanjut?** Hubungi tim pengembang! 🚀
