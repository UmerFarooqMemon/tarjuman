# Next.js implementation brief — Tarjuman CMS

Copy this file into the Next.js repo (e.g. `docs/cms-from-laravel.md`) or paste the **Agent prompt** below into a new Cursor chat in that project.

Laravel backend repo: `D:\Projects\tarjuman`  
Contract source of truth there: `docs/cms-nextjs-contract.md`

---

## Agent prompt (paste into Next.js Cursor chat)

```
Implement Tarjuman CMS integration for the Home page. Laravel admin already exists; do NOT add Laravel here.

Goals:
1) Public site: fetch GET {API_URL}/api/cms/pages/home with X-API-Token / Bearer API_TOKEN, map sections by type into existing Home components, keep current hard-coded copy as fallbacks when fields are empty.
2) Admin live preview only: when URL has cms_preview=1, listen for window postMessage from the admin parent. Payload shape:
   { source: 'tarjuman-cms', page, type, locale, content }
   Merge `content` into the matching section type in React state (unsaved). Do not write to any API from preview.
   Switch locale/RTL the same way as the site language switcher when `locale` changes.
   Reply with: window.parent.postMessage({ source: 'tarjuman-cms-preview', ready: true }, event.origin)
   Allowlist origins via NEXT_PUBLIC_CMS_PREVIEW_ORIGINS.
3) Section roots: data-cms-section="{type}" and support #cms-{type} / ?focus= scroll on load.
4) Env: NEXT_PUBLIC_API_URL, NEXT_PUBLIC_API_TOKEN (or server-only API_TOKEN), NEXT_PUBLIC_CMS_PREVIEW_ORIGINS.

Home section types and content shapes:

### stats_trust
- heading: { en, ar }
- stats[]: { icon (url), value: {en,ar}, label: {en,ar} }

### supported_documents
- eyebrow, title, description: { en, ar }
- categories[]: { icon, title: {en,ar}, items[]: { label: {en,ar} } }
- Keep shared checkmark icon hard-coded in the component

### why_choose_us
- eyebrow: { en, ar }
- title: { faint: {en,ar}, emphasis: {en,ar} }
- description: { en, ar }
- features[]: { icon, title: {en,ar}, subtitle: {en,ar} }
- side_image: url
- guarantee: { icon, title_lines[]: { text: {en,ar} }, body: {en,ar} }

Behavior matrix:
- Iframe preview (admin): update live from postMessage (unsaved)
- Real website visitors: only saved API data

Match existing Tailwind/layout; do not rebuild sections from scratch—bind props into current components.
```

---

## How to transfer knowledge in Cursor

### Option A — Multi-root workspace (best)
1. File → Add Folder to Workspace → add the Next.js project alongside `tarjuman`
2. In chat, `@docs/cms-nextjs-contract.md` and point at Home section files
3. Agent can read Laravel contract + edit Next.js in one session

### Option B — New chat in Next.js only
1. Copy this file (or `docs/cms-nextjs-contract.md` from Laravel) into the Next.js repo
2. Start a chat there, paste the **Agent prompt** above
3. `@` the copied doc + your Home page / section components

### Option C — Reference the Laravel path
If both repos are on disk, tell the agent:
`Read D:\Projects\tarjuman\docs\cms-nextjs-contract.md and implement it in this Next.js app.`

---

## Env checklist (Next.js)

```
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_TOKEN=same-as-laravel-API_TOKEN
NEXT_PUBLIC_CMS_PREVIEW_ORIGINS=http://localhost:8000
```

Laravel side already expects:

```
FRONTEND_URL=http://localhost:3000
CMS_PREVIEW_ORIGINS=http://localhost:8000
API_TOKEN=…
```

---

## Done when

- [ ] Home renders from `/api/cms/pages/home` with fallbacks
- [ ] EN/AR switcher still works using nested `{en,ar}` fields
- [ ] `?cms_preview=1` enables postMessage merge for one section
- [ ] Admin iframe typing updates preview without Save
- [ ] Save in admin → refresh public page → new content (no postMessage needed for public)
