# Architecture note — CollabDocs

## Goal

Create a similar flow of Google Docs edit documents, import files, share with another user, and see owned vs shared lists after refresh.

## Stack

| Layer | Choice | Why |
|-------|--------|-----|
| Frontend | Vue 3, Pinia, Vue Router, Tailwind v4, TipTap | Fast SPA, good rich-text ergonomics without building an editor |
| API | Laravel 13 + Sanctum token auth | Mature validation, migrations, tests; simple Bearer auth for SPA |
| DB | SQLite | Zero-config for reviewers and Docker; enough for demo scale |
| Runtime | Docker Compose | One command to run API + UI |

## Data model

```
users
documents (user_id owner, title, content HTML)
document_shares (document_id, user_id, permission)
document_attachments (document_id, path, metadata)
```

- **Content** is stored as HTML from TipTap so formatting survives reloads without a custom JSON schema.
- **Sharing** is a join table; access checks live on the `Document` model (`isAccessibleBy`, `canEdit`, `isOwnedBy`).

## API design

REST-style JSON under `/api`:

- Auth: `POST /login`, `GET /user`, `POST /logout`
- Documents: list (split `owned` / `shared`), CRUD, rename
- Share: `POST /documents/{id}/share`, `DELETE .../share/{user}`
- Upload: import new / import into existing / attach file

Authorization is enforced in controllers (owner for delete/share; `canEdit` for writes).

## Frontend structure

- **Pinia stores:** `auth` (token in `localStorage`), `documents` (lists + current doc)
- **Views:** Login → Dashboard (owned/shared) → Editor (toolbar, save, share modal, import/attachments)
- **API client:** Axios with Bearer interceptor; Vite dev proxy to Laravel

## Prioritized

1. **End-to-end sharing story** — seeded Alice/Bob + visible badges
2. **Editor UX** — toolbar + readable typography, not pixel-perfect Google Docs parity
3. **Persistence proof** — SQLite + HTML content round-trip
4. **Reviewer ergonomics** — Docker, README, seeded accounts, automated tests

## Deprioritized

- WebSockets / live cursors
- Full role matrix (admin, commenter, etc.)
- `.docx` conversion
- Production-grade observability

## Deployment sketch

- Build frontend with `VITE_API_URL=https://api.example.com/api`
- Run Laravel with `php artisan migrate --force && db:seed` on SQLite volume or Postgres if you swap `DB_*`

CORS is configured via `CORS_ALLOWED_ORIGINS` in `backend/config/cors.php`.
