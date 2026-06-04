# Submission checklist — CollabDocs

## Github folder contents

Include this repository (or a zip of `doc-share-app/`) with:

| Item | Path |
|------|------|
| Source code | `backend/`, `frontend/`, `docker-compose.yml` |
| Setup instructions | `README.md` |
| Architecture note | `ARCHITECTURE.md` |
| AI workflow note | `AI_WORKFLOW.md` |
| This checklist | `SUBMISSION.md` |
| Walkthrough video URL | `VIDEO_URL.txt` |
| Live deployment URL | `README.md` + below |
| Demo credentials | `README.md` (alice/bob/carol @ `password`) |
| Screenshots (optional) | `docs/screenshots/` if setup is non-obvious |

## Live product URL

**Replace before submit:**

- Frontend: `https://YOUR-FRONTEND-URL`
- API (if separate): `https://YOUR-API-URL/api`

## Test accounts

- `alice@example.com` / `password` — owner
- `bob@example.com` / `password` — shared Welcome doc
- `carol@example.com` / `password` — use for new share tests

## What is working

- [x] Document CRUD, rename, rich-text save/reopen
- [x] Owned vs shared dashboard
- [x] Share with another user (edit/view permission field)
- [x] Import `.txt` / `.md` (new + into existing)
- [x] Attachments upload/download
- [x] SQLite persistence
- [x] Docker Compose
- [x] Backend + frontend unit tests

