# AI workflow note

## Tools used

- **Cursor (Auto / Composer)** — primary coding agent for scaffolding, API controllers, Vue views, Docker, and docs
- **Inline edits** — iterative fixes after running `php artisan test` and `npm run test:unit`

## Where AI sped up work

| Area | Impact |
|------|--------|
| UI | Tailwind layout,TipTap toolbar wiring |
| Docker | `docker-compose.yml` + Dockerfiles in one pass |

## What was changed or rejected after AI output

- **Rejected:** cookie-based Sanctum SPA session as the only auth path — switched to **Bearer tokens** for simpler cross-origin local dev
- **Adjusted:** share permission model kept minimal (`edit` / `view`) with explicit `can_edit` in API responses
- **Trimmed:** `.docx` import (would need extra dependency); documented `.txt` / `.md` only

## How correctness was verified

1. **Automated tests**
   - `php artisan test` — sharing grant, shared list visibility, delete forbidden for non-owner
   - `npm run test:unit` — auth store login persistence
2. **UX check** — owned vs shared badges, disabled editor when `can_edit` is false

