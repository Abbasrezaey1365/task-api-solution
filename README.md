# Collaborative Task Management API (Laravel)

Senior PHP Backend Developer – Coding Assessment solution.

A REST API for collaborative task management:
- Users (Auth)
- Projects (owned by user)
- Tasks (CRUD + filtering + pagination)
- Comments (CRUD per task)
- Notifications (triggered on task changes)

Built with **Laravel (PHP 8.2+)**, **Sanctum** for API authentication, layered architecture (Controllers → Services → Repositories), caching for task listings, standardized API responses, and automated tests.

---

## Live Demo

### Frontend (Angular)
- **https://journal-index.org/login**

You can **register** using email + password, or use demo credentials:

- **Email:** `demo@email.com`  
- **Password:** `12345`

### API (Laravel)
- **https://api.journal-index.org**

Health check example:
- `GET /api/health` → `{ "ok": true }` *(if you have it)*

> Demo data may be reset at any time.

---

## Tech Stack

- PHP 8.2+
- Laravel
- Laravel Sanctum (API tokens)
- SQLite (tests) / MySQL (local dev)
- Queue: `sync` in tests, configurable for async workers
- GitHub Actions CI (tests + coverage gate)

---

## Setup (Local)

### 1) Install dependencies
```bash
composer install
