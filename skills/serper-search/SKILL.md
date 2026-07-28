---
name: serper-search
description: Use the Serper.dev Google search plugin — one tool, nine operations (web, images, news, videos, scholar, shopping, patents, maps, places) — and embed results in the reply. Use when the user asks to "search", "look up", "find online", "show me", "find a paper/video/image/news/restaurant/product/patent", or any task that needs fresh information from the web via Google.
license: MIT
compatibility: spora>=0.7 spora-plugin-serper>=1.0
metadata:
  author: spora-ai
  version: "1.0"
allowed-tools: Spora\Plugins\Serper\Tools\SerperSearchTool
---

# Serper search

One tool, one required argument. The tool returns plain-text output (already
formatted as a numbered list). You render it in the reply — verbatim for most
operations, with embed transformations for `image_search` and `video_search`.

## Calling

```
serper_search(action: "<operation>", q: "<query>")
```

`q` is the only required parameter. Pick the operation by intent; do **not**
default to `search` when the user asked for images, news, videos, papers,
products, patents, or local places.

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

Make multiple calls **in parallel** when the user asked for more than one
kind of result (e.g. "pictures AND news about X") — they are independent.

## Rendering

The tool already prints a numbered list. **Echo it as-is** for `search`,
`news_search`, `scholar_search`, `shopping_search`, `patents_search`,
`maps_search`, and `places_search`. Each row already includes the title,
URL/source/date/snippet as the tool formats it.

### Images — convert to a markdown image

`image_search` rows look like:

```
[1] Title
Image URL: https://...direct-image.jpg
Source: https://...page (Wikipedia)
```

In the reply, render each one with the **direct image URL** in markdown:

```markdown
![Title](direct-image-url)
[1] Title — [source page](page-url)
```

Use the direct `imageUrl` from the row — never link a page when an
`imageUrl` is present (it renders inline). Only fall back to a linked
thumbnail when no direct URL is returned.

### Videos — embed the top result, list the rest

`video_search` rows look like:

```
[1] Title
Source: YouTube
Date: Aug 29, 2025
URL: https://www.youtube.com/watch?v=XXXXXXXXXXX
```

Serper returns YouTube primarily. Embed **only the top result** as an
iframe; the remaining rows go in the standard numbered list.

YouTube ID extraction:

- `https://www.youtube.com/watch?v=VIDEO_ID` → use the `v=` value
- `https://youtu.be/VIDEO_ID` → use the path segment
- Other hosts (Vimeo, Dailymotion, TikTok) → do not embed; list as-is.

Embed URL: `https://www.youtube.com/embed/VIDEO_ID`

```html
<iframe width="560" height="315"
  src="https://www.youtube.com/embed/VIDEO_ID"
  title="Title from the row"
  frameborder="0"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
  allowfullscreen></iframe>
```

Render the rest of the rows as the tool returns them (skip the embed for
`[2]`, `[3]`, … — keeps the reply scannable, doesn't drown the user in
iframes).

## Output structure

When combining several operations in one reply, group results under
clear headings. The exact icons are decorative — replace with text if
your renderer doesn't support them.

```markdown
### 🔍 Web
[tool output verbatim]

### 🖼️ Images
![alt](direct-url)
[1] Title — [source](page-url)

### 📰 News
[tool output verbatim]

### 🎥 Videos
<iframe ... src="https://www.youtube.com/embed/..." ...></iframe>
[2] Title — [link](url)
[3] Title — [link](url)

### 📚 Scholar / 🛍️ Shopping / 📜 Patents / 📍 Maps / 🏷️ Places
[tool output verbatim, one heading per kind]
```

## Rules

- **Always cite sources.** Every result needs a working URL in the reply.
- **Never fabricate video IDs.** If the URL isn't YouTube-style, don't
  embed — list the row with its link.
- **Top result gets the embed, the rest get a list.** Cap at one iframe
  per video batch; subsequent results in the same reply link only.
- **Reuse the tool's number prefix `[N]`.** Preserving the numbering
  lets the user match your reply back to the raw results when needed.
- **If the tool returns "No … results found."** — relay it verbatim and
  stop. Do not invent follow-up queries without the user's go-ahead.
  For `patents_search` specifically, fall back to a broader or
  rephrased `q` (e.g. drop `inventor:` operators) before giving up;
  the upstream patents index is narrower than web search.
- **Don't call the tool again to "verify" a result.** One call per
  operation per user request is the rule — extra calls cost quota.
