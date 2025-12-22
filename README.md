# Task Management API (Laravel)

Hi, I finished the coding assessment.

This is a REST API for:
- register / login
- projects
- tasks
- comments
- notifications

I used Laravel (PHP 8.2) and Sanctum.

---

## Live demo

API:
https://api.journal-index.org

Test:
GET /api/health
Result: { "ok": true }

Frontend (Angular):
https://journal-index.org/login

Frontend is not required in task, I made it only to test API faster and to show UI.

You can register as here here https://journal-index.org/login or simply use premade demo user login:
email: demo@email.com
password: 123456789

---

## Tech

- PHP 8.2
- Laravel
- Sanctum
- MySQL (local) / SQLite (tests)
- Queue for notifications
- Tests + GitHub Actions

Test coverage is more than required (minimum was 70%).

---

## Run local

1) install
composer install

2) env
cp .env.example .env
php artisan key:generate

3) migrate
php artisan migrate

4) run
php artisan serve

---

## Example (curl)

Register:
curl -X POST http://127.0.0.1:8000/api/register -H "Content-Type: application/json" -d "{\"name\":\"Test\",\"email\":\"test@test.com\",\"password\":\"12345678\"}"

Login:
curl -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d "{\"email\":\"test@test.com\",\"password\":\"12345678\"}"

Use token:
Authorization: Bearer YOUR_TOKEN

---

## Notes

I used controllers + services + repositories to keep code clean.

Notifications are created when task is updated (queue).
Caching is used for task list.

Thanks.
