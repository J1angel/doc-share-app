# Submission checklist — CollabDocs

## Google Drive folder contents

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

## Partial / incomplete

- Live public deployment URL (you must deploy and update links)
- Walkthrough video (record 3–5 min Loom/YouTube → `VIDEO_URL.txt`)
- `.docx` import not implemented

## Next 2–4 hours

1. Deploy backend + frontend to Render/Vercel
2. Record walkthrough video
3. Add 2–3 screenshots to `docs/screenshots/`
