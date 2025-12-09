# 🔄 Database Synchronization Guide

Panduan lengkap untuk sinkronisasi database antar anggota tim.

---

## 📚 Table of Contents

1. [Overview](#overview)
2. [What Gets Synced](#what-gets-synced)
3. [First Time Setup](#first-time-setup)
4. [Daily Workflow](#daily-workflow)
5. [Making Database Changes](#making-database-changes)
6. [Troubleshooting](#troubleshooting)

---

## Overview

Database synchronization menggunakan **Laravel Migrations** dan **Database Seeders**:

- **Migrations** = Database schema (struktur tabel, kolom, dll)
- **Seeders** = Test data (user accounts, sample data)

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  Developer   │  push   │     Git      │  pull   │  Team Member │
│    (You)     │────────▶│  Repository  │────────▶│   (Others)   │
└──────────────┘         └──────────────┘         └──────────────┘
       │                                                  │
       │ php artisan migrate                              │
       ▼                                                  ▼
┌──────────────┐                                  ┌──────────────┐
│  Local MySQL │                                  │  Local MySQL │
│   Database   │                                  │   Database   │
└──────────────┘                                  └──────────────┘
```

---

## What Gets Synced

### ✅ **Pushed to Git (Synced)**

```
cv-triloka-backend/
├── database/
│   ├── migrations/          ✅ Schema changes
│   │   └── 2025_12_02_*.php
│   └── seeders/             ✅ Test data generators
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
├── .env.example             ✅ Config template
└── composer.json            ✅ Dependencies
```

### ❌ **NOT in Git (Local Only)**

```
cv-triloka-backend/
├── .env                     ❌ Your credentials
├── vendor/                  ❌ Dependencies (install via composer)
└── storage/                 ❌ Logs, cache, uploads
```

**MySQL Database:** ❌ NOT synced (each developer has own local database)

---

## First Time Setup

Team member yang baru clone repository:

### **Step 1: Clone Repository**

```bash
git clone <repository-url>
cd pt-triloka-mobile/cv-triloka-backend
```

### **Step 2: Install Dependencies**

```bash
composer install
```

### **Step 3: Setup Environment**

```bash
# Copy environment template
cp .env.example .env

# Generate app key
php artisan key:generate
```

### **Step 4: Configure Database**

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cv_triloka_db
DB_USERNAME=root
DB_PASSWORD=           # Your MySQL password
```

### **Step 5: Create Database**

Via phpMyAdmin atau MySQL client:

```sql
CREATE DATABASE cv_triloka_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **Step 6: Run Migrations**

```bash
php artisan migrate
```

**Output:**
```
Migrating: 2025_12_02_000000_create_users_table
Migrated:  2025_12_02_000000_create_users_table (45.23ms)
Migrating: 2025_12_02_000001_create_activity_logs_table
Migrated:  2025_12_02_000001_create_activity_logs_table (30.15ms)
...
```

### **Step 7: Seed Test Data**

```bash
php artisan db:seed
```

**Output:**
```
Seeding database.
Test users seeded successfully!
```

### **Step 8: Verify**

Check database in phpMyAdmin:
- ✅ All tables created
- ✅ Test users exist in `users` table

---

## Daily Workflow

### **When You Pull Latest Code:**

```bash
# Pull from git
git pull origin main

# Run any new migrations
php artisan migrate

# (Optional) Update test data
php artisan db:seed
```

### **If Someone Added a New Table:**

Their changes:
```bash
# They created migration
php artisan make:migration create_projects_table

# They ran it locally
php artisan migrate

# They pushed to git
git add database/migrations/
git commit -m "Add projects table"
git push
```

Your actions:
```bash
# Pull their changes
git pull

# Run the new migration
php artisan migrate
```

**Result:** Your database now has the `projects` table! ✅

---

## Making Database Changes

### **Creating a New Table**

```bash
# Create migration
php artisan make:migration create_projects_table

# Edit the migration file
# database/migrations/2025_12_02_xxxxx_create_projects_table.php
```

Example migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
```

**Run & Test:**

```bash
# Run migration locally
php artisan migrate

# Verify in phpMyAdmin
# Table 'projects' should exist

# If OK, commit & push
git add database/migrations/
git commit -m "Add projects table"
git push
```

### **Modifying Existing Table**

```bash
# Create migration
php artisan make:migration add_status_to_projects_table

# Edit to add column
```

Example:

```php
public function up(): void
{
    Schema::table('projects', function (Blueprint $table) {
        $table->string('status')->default('pending')->after('description');
    });
}

public function down(): void
{
    Schema::table('projects', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
```

**Run, test, commit:**

```bash
php artisan migrate
git add database/migrations/
git commit -m "Add status column to projects"
git push
```

### **Creating Test Data Seeder**

```bash
# Create seeder
php artisan make:seeder ProjectSeeder
```

Edit `database/seeders/ProjectSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::create([
            'name' => 'Sample Project',
            'description' => 'Test project for development',
            'status' => 'active',
        ]);
    }
}
```

Register in `DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        ProjectSeeder::class,  // Add this
    ]);
}
```

**Test & Push:**

```bash
php artisan db:seed --class=ProjectSeeder
git add database/seeders/
git commit -m "Add project seeder"
git push
```

---

## Useful Commands

### **Migration Commands**

```bash
# Run all pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Rollback specific steps
php artisan migrate:rollback --step=2

# Reset and re-run all migrations
php artisan migrate:fresh

# Fresh + seed (⚠️ DELETES ALL DATA!)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### **Seeder Commands**

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder

# Refresh & seed
php artisan migrate:fresh --seed
```

### **Database Info**

```bash
# List all migrations
php artisan migrate:status

# Show database info
php artisan db:show

# List all tables
php artisan db:table --database=mysql
```

---

## Troubleshooting

### **Problem: "Nothing to migrate"**

**Cause:** No new migrations to run.

**Solution:** You're up to date! ✅

---

### **Problem: "SQLSTATE[42S01]: Base table or view already exists"**

**Cause:** Table already exists in your database.

**Solution:**

```bash
# Option 1: Skip migrations that already ran
# (migrations track themselves automatically)

# Option 2: Fresh start (⚠️ DELETES DATA!)
php artisan migrate:fresh --seed
```

---

### **Problem: "Class UserSeeder does not exist"**

**Cause:** Seeder file not found or not autoloaded.

**Solution:**

```bash
# Regenerate autoload files
composer dump-autoload

# Then try again
php artisan db:seed
```

---

### **Problem: Migration runs locally but fails for team**

**Cause:** Migration depends on data or tables that don't exist for them.

**Solution:** 

1. Check migration order (older migrations run first)
2. Ensure migrations are self-contained
3. Use foreign keys properly with `onDelete('cascade')`

---

### **Problem: "Database connection refused"**

**Cause:** MySQL not running or wrong credentials.

**Solution:**

```bash
# 1. Start MySQL (XAMPP/Laragon)
# 2. Check .env credentials
# 3. Test connection:
php artisan db:show
```

---

## Best Practices

### **✅ DO:**

- ✅ Always test migrations locally before pushing
- ✅ Write `down()` method for rollbacks
- ✅ Use descriptive migration names
- ✅ Keep seeders idempotent (can run multiple times safely)
- ✅ Commit migration and model together

### **❌ DON'T:**

- ❌ Edit old migrations that already ran on production
- ❌ Commit `.env` file
- ❌ Use `Schema::drop()` in production migrations
- ❌ Put actual user data in seeders (only test data)
- ❌ Hardcode IDs in seeders

---

## Test Credentials

After running `php artisan db:seed`:

### **Admin Account**
- Email: `admin@triloka.com`
- Password: `password`
- Role: `admin`

### **Client Account**
- Email: `mitra@example.com`
- Password: `password`
- Role: `klien`

---

## Quick Reference

**New team member:**
```bash
git clone <url>
cd cv-triloka-backend
composer install
cp .env.example .env
php artisan key:generate
# Edit .env with DB credentials
php artisan migrate
php artisan db:seed
php artisan serve
```

**Pull latest changes:**
```bash
git pull
php artisan migrate
php artisan db:seed  # Optional
```

**Create new table:**
```bash
php artisan make:migration create_xyz_table
# Edit migration
php artisan migrate
git add database/migrations/ && git commit -m "Add xyz table" && git push
```

---

**Questions?** Check Laravel docs: https://laravel.com/docs/migrations
