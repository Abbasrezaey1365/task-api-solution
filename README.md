# Collaborative Task Management API (Laravel)

Senior PHP Backend Developer – Coding Assessment solution.

This project provides a REST API for managing:
- Users (Auth)
- Projects (owned by user)
- Tasks (CRUD + filtering + pagination)
- Comments (CRUD per task)
- Notifications (triggered on task changes, delivered async)

Built with **Laravel (PHP 8.2+)**, **Sanctum** for API authentication, layered architecture (Controllers → Services → Repositories), caching for task listings, standardized API responses, and automated tests.

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
