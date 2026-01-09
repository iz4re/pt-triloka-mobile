CV Triloka - Financial & Operational System

Sistem terpadu untuk manajemen keuangan dan operasional perusahaan dengan Laravel backend dan Flutter frontend.

 Project Overview

**Timeline:** 3 minggu (Target: 21 Desember 2024)

**Stack:**
- **Backend:** Laravel 11 + MySQL + Sanctum Auth
- **Frontend Mobile:** Flutter (untuk klien)
- **Frontend Web:** Laravel/Vue (untuk admin) - *Coming soon*

---

##  Project Structure

```
pt-triloka-mobile/
├── cv-triloka-backend/    # Laravel API Backend
└── lib/                   # Flutter Mobile App
```

---

##  Quick Start

**New team member?** Follow → [**Team Setup Guide**](.gemini/antigravity/brain/69ebc38b-c0ee-46ff-9230-836538ba3b08/team_setup_guide.md)

**Short version:**

```bash
# 1. Backend setup
cd cv-triloka-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

# 2. Flutter setup
cd ..
flutter pub get
flutter run -d chrome
```

**Test Login:**
- Email: `mitra@example.com`
- Password: `password`

---

##  Documentation

- **[Database Sync Guide](cv-triloka-backend/DATABASE_SYNC.md)** - How to keep database in sync with team
- [Team Setup Guide](.gemini/antigravity/brain/69ebc38b-c0ee-46ff-9230-836538ba3b08/team_setup_guide.md) - Complete setup for new members

---


##  Features

### Implemented
- User authentication (Login/Register)
- API integration (Flutter ↔ Laravel)
- CORS configuration
- Dashboard (basic)

###  In Progress
- Profile management
- Notification system
- Dashboard with real data

### Planned
- Project request submission
- Quotation view & approval
- Payment with proof upload
- Document management
- Invoice tracking

---

## Development

Start servers:

```bash
# Terminal 1: Laravel backend
cd cv-triloka-backend
php artisan serve

# Terminal 2: Flutter
flutter run -d chrome
```

**Useful commands:**

```bash
# Laravel
php artisan migrate:fresh    # Reset database
php artisan route:list       # View all routes

# Flutter
flutter clean               # Clean build
flutter pub get            # Install packages
```

---

## Team
Ahmad Naufal Alghiffari
Yudha Harwanto
abizar
Hafidz


---
