# CMS API + live admin preview

Laravel stores section JSON. The marketing site stays in its **separate Next.js repo** (not installed here).

**Handoff for Next.js Cursor chat:** see [cms-nextjs-handoff.md](./cms-nextjs-handoff.md) (copy/paste agent prompt).

## Behavior

| Surface | When content updates |
|---------|----------------------|
| **Admin iframe preview** | Live while editing (unsaved), via `postMessage` |
| **Public website** | Only after **Save** (reads `GET /api/cms/pages/{slug}`) |

## Public API (after Save)

`GET /api/cms/pages/{slug}`  
Auth: `Authorization: Bearer {API_TOKEN}` or `X-API-Token`

Text: `{ "en": "…", "ar": "…" }`. Assets: absolute URLs.

### Home types

| `type` | Fields |
|--------|--------|
| `stats_trust` | `heading`, `stats[]` |
| `supported_documents` | `eyebrow`, `title`, `description`, `categories[]` |
| `why_choose_us` | `eyebrow`, `title`, `description`, `features[]`, `side_image`, `guarantee` |

## Live iframe preview (required Next.js snippet)

Admin iframe loads:

`{FRONTEND_URL}/?cms_preview=1&locale=en&page=home&focus=why_choose_us#cms-why_choose_us`

Laravel posts (debounced on form input):

```js
{
  source: 'tarjuman-cms',
  page: 'home',
  type: 'why_choose_us',
  locale: 'en',
  content: { /* current form JSON, bilingual */ }
}
```

Minimal Next.js listener (client component on the page):

```tsx
'use client';

useEffect(() => {
  const allowed = new Set(
    (process.env.NEXT_PUBLIC_CMS_PREVIEW_ORIGINS || '').split(',').map((s) => s.trim()).filter(Boolean)
  );

  function onMessage(event: MessageEvent) {
    if (allowed.size && !allowed.has(event.origin)) return;
    const data = event.data;
    if (!data || data.source !== 'tarjuman-cms') return;

    // 1) setLocale(data.locale) — same as language switcher / RTL
    // 2) merge data.content into the section where type === data.type
    // 3) do NOT persist; this is preview-only

    window.parent.postMessage({ source: 'tarjuman-cms-preview', ready: true }, event.origin);
  }

  window.addEventListener('message', onMessage);
  window.parent.postMessage({ source: 'tarjuman-cms-preview', ready: true }, '*');
  return () => window.removeEventListener('message', onMessage);
}, []);
```

Also:

1. Fetch saved page from API for initial render (other sections stay saved)
2. Put `id` / `data-cms-section="{type}"` on each section root for `#cms-{type}` scroll
3. Map `sections[].type` → components; fall back to hard-coded defaults if empty

Until this listener exists, the iframe loads the site but **won’t reflect unsaved typing**.

## Laravel env

```
FRONTEND_URL=http://localhost:3000
CMS_PREVIEW_ORIGINS=http://localhost:8000
API_TOKEN=…
```

## Adding a section later

1. Schema in `app/Cms/Schemas` + `SchemaRegistry`
2. Attach to a page
3. Map the type in Next.js
