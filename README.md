# CollabDocs

A scoped full-stack document app: rich-text editing, file import, sharing, and persistence. Built with **Vue 3 + Tailwind CSS** and **Laravel 11+ (API + Sanctum)**.

## Live demo

> **Replace before submission:** deploy and paste your public URL here and in `SUBMISSION.md`.

Suggested free deployment (no paid services):

- **Backend:** [Render](https://render.com) Web Service (Docker) or Railway
- **Frontend:** Render Static Site or Vercel (set `VITE_API_URL` to your API `/api` base)

Placeholder: `https://YOUR-FRONTEND-URL.example`

## Demo accounts

| Email | Password | Role |
|-------|----------|------|
| `alice@example.com` | `password` | Owner (sample doc) |
| `bob@example.com` | `password` | Collaborator (Welcome doc shared) |
| `carol@example.com` | `password` | Extra user for sharing demos |

## Features

- Create, rename, save, and reopen documents with TipTap rich text (bold, italic, underline, headings, lists)
- Import `.txt` / `.md` as new documents or into an existing draft
- Upload attachments per document (`.txt`, `.md`, `.pdf`, `.png`, `.jpg`)
- Share documents with other users; dashboard separates **Owned** vs **Shared**
- SQLite persistence (formatting stored as HTML)

## Quick start (Docker)

```bash
cd doc-share-app
docker compose up --build
```

- Frontend: http://localhost:5173  
- API: http://localhost:8000/api  

Seeded users are created on backend startup.

## Local development (without Docker)

### Backend

```bash
cd backend
cp .env.example .env   # if needed
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan serve
```

API runs at http://localhost:8000

### Frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

Open http://localhost:5173 — Vite proxies `/api` to the backend.

## Tests

```bash
# Backend
cd backend && php artisan test

# Frontend
cd frontend && npm run test:unit
```

## Project layout

```
doc-share-app/
├── backend/          # Laravel API
├── frontend/         # Vue 3 SPA
├── docker-compose.yml
├── ARCHITECTURE.md
├── AI_WORKFLOW.md
└── SUBMISSION.md
```

## Supported file types (UI + API)

| Action | Types |
|--------|--------|
| Import as document / into draft | `.txt`, `.md` |
| Attachments | `.txt`, `.md`, `.pdf`, `.png`, `.jpg`, `.jpeg` |

## Intentional scope cuts

- No real-time co-editing, comments, or version history
- No `.docx` parsing (plain text / markdown only)
- Simple share permissions (`edit` vs `view`); view-only enforced on save/edit
- Token auth (no OAuth)
