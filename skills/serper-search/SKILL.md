---
name: serper-search
description: Use the Serper.dev Google search plugin — one tool, nine operations (web, images, news, videos, scholar, shopping, patents, maps, places) — and turn each row into a markdown link the chat surface actually renders. Use when the user asks to "search", "look up", "find online", "show me", or anything that needs a paper, video, image, news article, restaurant, product, or patent from the web via Google.
license: MIT
compatibility: spora>=0.7 spora-plugin-serper>=1.0
metadata:
  author: spora-ai
  version: "1.1"
allowed-tools: Spora\Plugins\Serper\Tools\SerperSearchTool
---

# Serper search

The plugin returns a numbered list per call. Your job is to render each
row in the reply by adding markdown hyperlinks to the URLs the tool
already printed — do not rewrite, reorder, summarise, or invent
content. Two operations need a small transform: `image_search`
becomes a markdown image; every operation's URLs become clickable
links.

## Calling

```
serper_search(action: "<operation>", q: "<query>")
```

`q` is the only required parameter. Pick the operation by intent; do
**not** default to `search` when the user asked for images, news,
videos, papers, products, patents, or local places.

| User intent                                  | Operation         | Underlying path |
|----------------------------------------------|-------------------|-----------------|
| General lookups, current events from Google  | `search`          | `/search`       |
| Pictures, photos, illustrations              | `image_search`    | `/images`       |
| Recent articles, dated news                  | `news_search`     | `/news`         |
| Videos to watch, tutorials, clips            | `video_search`    | `/videos`       |
| Academic papers, citations                   | `scholar_search`  | `/scholar`      |
| Products with prices and sellers             | `shopping_search` | `/shopping`     |
| Patents, inventors, assignees                | `patents_search`  | `/patents`      |
| Local businesses, "near me", addresses       | `maps_search`     | `/maps`         |
| Specific place with hours and website        | `places_search`   | `/places`       |

Make multiple calls **in parallel** when the user asked for more than
one kind of result — the endpoints are independent.

## Markdown surface — what renders, what doesn't

Spora's chat surface is a plain markdown renderer.

- **Renders:** `[label](https://url)`, `![alt](https://image-url)`,
  bare `https://url`, bullet / numbered lists, headings, fenced code,
  blockquotes.
- **Stripped or silently dropped:** `<iframe>`, `<script>`, `data:`
  URLs, `javascript:` URLs, raw HTML attributes.

Do not produce any of the stripped forms. That includes synthesised
YouTube `embed/` URLs, Vimeo `player/` URLs, or any other host's
embed form — different hosts have different embed schemes,
synthesising a wrong-host URL is a real bug, and even on the right
host the result drops silently in chat.

## Rendering rules per operation

Every row the tool returns is the source of truth. Keep its title,
Source/Date/Address/Phone/Rating/Hours/Website/Snippet/Patent-ID/
Date/Inventor/Assignee/PDF lines verbatim — only the URL line(s)
become links.

- **`search`, `news_search`, `scholar_search`, `shopping_search`,
  `patents_search`, `maps_search`, `places_search`** — turn every
  `URL:` (and `PDF:` for patents) line into
  `[title or label](https://…)`. Link label = the row title for
  `URL:`, or `"PDF"` for `PDF:`. Keep every other field exactly as
  the tool printed it.
- **`places_search`** may include an additional `URL:` line (capital
  `URL`, a Google Maps place link) — link it the same way. Don't
  double-link the address; pick one URL per row.
- **`image_search`** is the only exception:
  - Turn the `Image URL:` line into `![title](image-url)`.
  - Place the markdown image **above** the `[N] Title` caption.
  - If the tool does not emit an `Image URL:` line (rare), link the
    source page instead and keep the `Source:` line if the tool
    printed one. Never synthesise an image URL.
- **`video_search`** — turn the `URL:` line into `[title](url)`. Do
  not try to identify the host or rewrite the URL: different video
  hosts (YouTube, Vimeo, Dailymotion, TikTok, Rumble, etc.) each
  have their own embed scheme, some tracks have regional/CAPTCHA
  gates, and synthesising an embed URL is error-prone in every
  direction. The plain link is the universally correct render.
- **Empty-result text** (`No … results found.`) is the user-facing
  message — relay it word-for-word and stop.

### Worked example — image_search

Tool output:

```
Google Image Results for 'mountain sunset':

[1] Mountain at sunset
Image URL: https://upload.wikimedia.org/.../Sunset.jpg
```

Rendered:

```markdown
![Mountain at sunset](https://upload.wikimedia.org/.../Sunset.jpg)
[1] Mountain at sunset
```

### Worked example — places_search

Tool output:

```
Place Results for 'Italian restaurants New York City':

[1] Lupa Osteria
Address: 170 Thompson St, New York, NY 10012
Phone: (212) 982-5089
Rating: 4.5
Hours: Mon-Sun 12:00 PM-11:00 PM
Website: https://lupaosteria.com
URL: https://maps.google.com/?cid=12345
```

Rendered (any list shape that preserves every fact works — pick what reads best):

```markdown
[Lupa Osteria](https://maps.google.com/?cid=12345) — 170 Thompson St,
(212) 982-5089, ★4.5, [website](https://lupaosteria.com),
Mon-Sun 12:00 PM-11:00 PM
```

The rule is "every URL field becomes a link and nothing else
changes." Don't paraphrase, don't drop facts, don't merge rows.

## Output structure

When the reply combines several operations, group results under
headings. The exact icons are decorative — drop them if your renderer
doesn't support emoji. Within each group, render the rows with the
URL fields turned into markdown links; leave everything else
verbatim.

```markdown
### Web

[1] Page Title — [publisher](https://example.com/page)
Snippet text…

### Images

![title](https://example.com/image.jpg)
[1] Title

### News

[1] Article — [Publisher](https://example.com/article) — Aug 29, 2025
Snippet…

### Videos

- [Video Title](https://www.youtube.com/watch?v=…) — YouTube, Aug 29, 2025
- [Second Video Title](https://vimeo.com/…) — Vimeo, Jun 12, 2024

### Scholar / Shopping / Patents / Maps / Places

(tool rows verbatim, one heading per kind, with URL lines turned into markdown links)
```

## Rules

- **Cite the source the tool returned.** Every URL you link must
  come from the tool's output. Never construct a URL. Never trim
  tracking parameters, downgrade `https://` to `http://`, or swap
  the host.
- **Markdown only.** No `<iframe>`, no `data:`, no `javascript:` —
  Spora's chat surface drops them silently and the user sees
  nothing where the embed should be. Don't synthesise host-specific
  embed URLs (YouTube `embed/`, Vimeo `player/`, etc.) from a
  watch URL.
- **Use the tool's `Image URL:` line for the markdown image.**
  Never link a source page when a direct image URL is present —
  the inline image is the whole point. If the tool did not emit an
  image URL (rare with Serper.dev), link the source page instead.
- **Preserve the tool's `[N]` row numbers.** The user uses them to
  refer back to specific results ("can you tell me more about [3]?").
  Don't renumber or merge rows.
- **If the tool emits `No … results found.`** — relay it verbatim
  and stop. Don't invent follow-up queries. For `patents_search`,
  broaden or rephrase `q`: Serper.dev's patents index is narrower
  than web search, and the upstream API doesn't parse Google-style
  operator prefixes (`inventor:`, `before:`, …) — so don't lean on
  those.
- **One call per operation per user request.** Extra "verification"
  calls cost quota and don't change the answer.
