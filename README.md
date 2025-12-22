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

### API (Laravel)
- **https://api.journal-index.org**

Quick check:
- `GET /api/health` → `{ "ok": true }`

> The API is the core deliverable for this assessment.

### Optional Frontend (Angular)
- **https://journal-index.org/login**

The frontend is **not part of the original assessment requirements**.  
It is provided only to **visualize the features** and **test the API faster**.

You can register using email + password, or use demo credentials:

- **Email:** `demo@email.com`
- **Password:** `123456789`

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
