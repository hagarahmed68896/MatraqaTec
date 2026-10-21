<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/API-Sanctum-38B2AC?style=for-the-badge&logo=laravel&logoColor=white" alt="Sanctum">
  <img src="https://img.shields.io/badge/Payments-MyFatoorah-1A73E8?style=for-the-badge" alt="MyFatoorah">
  <img src="https://img.shields.io/badge/Notifications-Firebase_FCM-FFCA28?style=for-the-badge&logo=firebase&logoColor=black" alt="Firebase FCM">
</p>

# 🔧 MatraqaTec — Maintenance Services Platform

**MatraqaTec** is a comprehensive maintenance services management platform that connects **clients** with **maintenance companies** and **technicians**. It provides a full-featured backend (Admin Dashboard + RESTful API) to manage the entire lifecycle of maintenance orders — from service requests to payments and contracts.

---

## 📌 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Architecture](#-system-architecture)
- [Installation](#-installation)
- [API Overview](#-api-overview)
- [Admin Dashboard](#-admin-dashboard)
- [Scheduled Tasks](#-scheduled-tasks)
- [Project Structure](#-project-structure)
- [License](#-license)

---

## ✨ Features

### 👤 Multi-Role Authentication
- **Clients** (Individual & Corporate customers)
- **Maintenance Companies** with company profiles & schedules
- **Technicians** with skill-based profiles & location tracking
- **Admins & Supervisors** with role-based permissions
- OTP verification via **Taqnyat SMS**
- Token-based API auth via **Laravel Sanctum**

### 📋 Order Management
- Full order lifecycle (create → assign → in-progress → complete)
- Appointment scheduling & rescheduling
- Order attachments & tracking
- Automatic expiry handling for stale orders

### 📄 Contracts
- Contract creation & management
- Payment receipt tracking
- Contract-linked invoices

### 💳 Payments & Wallet
- **MyFatoorah** payment gateway integration
- Wallet system with transaction history
- Platform profit tracking
- Financial settlements & refunds

### 🔔 Notifications
- **Firebase Cloud Messaging (FCM)** push notifications
- Scheduled notification system
- Appointment reminders

### 📊 Admin Dashboard
- Comprehensive reports & analytics
- Service management (CRUD)
- Customer management (Individual & Corporate)
- Complaint handling & resolution
- Review moderation
- Inventory management
- Content & FAQ management
- Privacy policy & terms management

### 🌍 Localization
- Multi-language support (Arabic & English)

---

## 🛠 Tech Stack

| Layer            | Technology                        |
|------------------|-----------------------------------|
| **Framework**    | Laravel 11                        |
| **Language**     | PHP 8.2+                          |
| **Database**     | MySQL                             |
| **Auth**         | Laravel Sanctum                   |
| **Payments**     | MyFatoorah Payment Gateway        |
| **Notifications**| Firebase Cloud Messaging (FCM)    |
| **OTP / SMS**    | Taqnyat SMS Service               |
| **Excel Export** | Maatwebsite/Excel                 |
| **Frontend**     | Blade Templates + Vite            |

---

## 🏗 System Architecture

```
MatraqaTec/
├── app/
│   ├── Console/Commands/       # Scheduled tasks (expired orders, reminders)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # 36 Admin dashboard controllers
│   │   │   └── Api/            # 37 RESTful API controllers
│   │   └── Middleware/
│   ├── Models/                 # 43 Eloquent models
│   └── Services/               # FCM, MyFatoorah, Taqnyat OTP
├── database/migrations/        # Database schema & migrations
├── lang/                       # Localization files (ar/en)
├── resources/views/admin/      # Blade templates for admin panel
├── routes/
│   ├── api.php                 # API routes
│   └── web.php                 # Web/admin routes
└── config/                     # App configuration
```

---

## 🚀 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL database
- Firebase project (for FCM)
- MyFatoorah account (for payments)

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/hagarahmed68896/MatraqaTec.git
cd MatraqaTec

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure your .env file
#    - Database credentials
#    - MyFatoorah API keys
#    - Firebase credentials
#    - Taqnyat SMS credentials

# 5. Run migrations
php artisan migrate

# 6. Build frontend assets
npm run build

# 7. Start the development server
php artisan serve
```

Or use the built-in setup script:

```bash
composer setup
```

For development with all services (server + queue + vite):

```bash
composer dev
```

---

## 📡 API Overview

The API is organized around the following main resources:

| Module                  | Description                                      |
|-------------------------|--------------------------------------------------|
| **Auth**                | Login, register, OTP, password reset              |
| **Orders**              | Create, track, assign, reschedule orders          |
| **Appointments**        | Schedule & manage service appointments            |
| **Contracts**           | Contract management & payment receipts            |
| **Services**            | Browse & search available services                |
| **Payments / Wallet**   | Online payments, wallet transactions              |
| **Technicians**         | Technician profiles, requests, locations          |
| **Companies**           | Maintenance company profiles & schedules          |
| **Notifications**       | FCM push notifications                            |
| **Complaints**          | File & track complaints                           |
| **Reviews**             | Submit & view service reviews                     |
| **Chat**                | In-app messaging                                  |
| **Inventory**           | Parts & supplies management                       |

> All API routes use **Laravel Sanctum** for authentication. Include the token in the `Authorization: Bearer {token}` header.

---

## 🖥 Admin Dashboard

The admin panel provides a full management interface with:

- 📊 **Reports & Analytics** — Revenue, orders, and company performance
- 👥 **User Management** — Clients, companies, technicians, supervisors
- 🔐 **Roles & Permissions** — Fine-grained access control
- 📦 **Order Management** — View, assign, and track all orders
- 💰 **Financial Management** — Invoices, payments, refunds, settlements
- ⚙️ **Settings** — App configuration, content, FAQs, terms & privacy

---

## ⏰ Scheduled Tasks

| Command                        | Description                              |
|--------------------------------|------------------------------------------|
| `HandleExpiredOrders`          | Auto-cancel orders past expiry           |
| `SendAppointmentReminders`    | Remind users of upcoming appointments    |
| `SendScheduledNotifications`  | Dispatch queued notifications            |

---

## 📄 License

This project is proprietary software. All rights reserved.

---

<p align="center">
  Built with ❤️ by <strong>Hagar Ahmed</strong>
</p>