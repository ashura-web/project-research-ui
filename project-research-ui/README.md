# project-research-ui

UI/UX research mock for a **peer-reviewed, rank-based community wiki**: a knowledge archive where members
write articles and short notes, reviewers approve or reject them, and contributors climb a rank ladder.
On top of the editorial workflow sit light gamification (credits and a cosmetics store) and community
governance (complaints, petitions and confidence polls). The real project catalogues speculative
cosmology and dimensional hierarchies, so the mock content uses neutral placeholder topics of the same shape.

Technically it is a **server-rendered PHP 8 site with a single front controller** (`index.php`), the same
architecture as the real build, but every page is a mock: there are no real accounts, no mail, no uploads,
no OAuth and no external services. All data is invented.

> Research material only. This repository is not the production site and contains none of its secrets,
> database, user data, logo or third-party embeds. See `LICENSE`.

## Quick start (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) (PHP 8.1 or newer).
2. Copy this folder to `C:\xampp\htdocs\project-research-ui` (macOS: `/Applications/XAMPP/htdocs/`).
3. Start **Apache** in the XAMPP Control Panel.
4. Open <http://localhost/project-research-ui/>.

That is all you need. Only Apache is used; MySQL does not need to be started.

### No database, on purpose

This research folder ships **no database, no SQL files and no connection settings**. Every page reads
invented sample data from PHP arrays in `app/mock-data.php` through `rows()` / `find()` in `app/data.php`.
The goal is to study the interface, not the data layer, and it keeps anything resembling production data
out of a public repository.

### Without XAMPP

```
php -S localhost:8000 index.php
```

Then open <http://localhost:8000/>. Pretty URLs work on the built-in server and on Apache with
`mod_rewrite` (XAMPP default). Without `mod_rewrite` the app prints `index.php?page=about` links instead.

## Exploring the mock

- **Preview as** (sidebar): switch between Guest, Member, Contributor, Reviewer, Admin and Owner. Pages
  above the chosen role show the Access denied screen, exactly like the real permission model.
- **All pages** (`/pages`): an index of every screen with its minimum role, the quickest way to review them all.
- **Display settings** (sliders icon in the top bar): text size, animations, wider spacing, plain font,
  reading guide, high contrast, underlined links and a strong focus ring, saved in `localStorage` and
  applied before first paint.
- Forms validate on the client and the server, then show a success message. Nothing is stored except the
  current session.
- Useful states: `governance-case?id=1` (view it as Member or Owner) and `governance-case?id=2` (as Admin);
  `setup?closed=1` for the closed setup window; any unknown address for the 404 page.
- Keyboard: `?` shortcuts, `/` search, `g` then `h`/`g`/`e`/`m`/`d` for Home, Gallery, Explore, Media,
  Dashboard, `r` random article, `t` back to top.

## Pages (48)

| Area | Pages |
|---|---|
| Public | Home, About, Explore, Gallery, Glossary, Help centre, Media, Article, Community note, Member profile, Random article, Contact, All pages |
| Account | Log in, Second step (2FA), Sign up, Choose a username, Forgot password, Reset password, Email preferences |
| Member workspace | Dashboard, Write a note, Upload media, My profile, Cosmetics store, Rank request, Notification centre |
| Contributor | Write an article |
| Governance | Governance, File a complaint, Governance case, Petitions, Petition, Confidence poll |
| Reviewer | Review queue, Rank requests board, Bulletin desk |
| Admin | Admin console, Users, Support inbox, Audit log, Email outbox |
| Owner | Owner console, Admin appointments, Content manager, First-time setup |
| System | Page not found, Access denied |

`docs/pages.md` lists every slug with its minimum role and breadcrumb parent.

## Folder layout

```
index.php              front controller: routing, CSRF check, role gate, mock POST handling
.htaccess              pretty URLs, blocks private folders, security headers
app/
  config.php           app name, tagline, default preview role (nothing secret)
  core.php             helpers, Validator, Csrf, Flash, url(), roles
  data.php             rows()/find()/where(): read the mock arrays
  pages.php            page map: slug => title, minimum role, parent
  layout.php           shell: sidebar, top bar, account menu, dialogs, footer
  mock-data.php        all invented sample data
  governance-rules.php who may file, review or decide each governance action
views/                 one template per page
assets/css/            style.css (adapted from the real site), tailwind.css (pre-built, no CDN), mock.css
assets/js/             validator.js (adapted), mock-ui.js (adapted interactions, no network calls)
docs/pages.md          page inventory
```

`app/` and `views/` each carry a `Require all denied` `.htaccess`, so only
`index.php` and `assets/` are reachable over HTTP.

## What was reused from the real project

Only code that carries no data or credentials, adapted to run against mock data:

- **PHP**: the front-controller pattern and page map, the built-in-server branch and private-folder guard,
  the `Validator` rules, `Csrf` tokens, `Flash` messages, the governance permission rules, the display
  preferences pre-paint script, and the role ladder / permission checks.
- **JavaScript**: `validator.js` (field patterns, counters, password strength, reveal) and the UI parts of
  `app.js` (toasts, modals with focus trap, confirm dialogs, display settings, draft autosave, wizard,
  accordions, gallery filter, lightbox, carousel, sidebar, account menu, discussion dock, shortcuts).
  Every `fetch` call was removed or replaced with a local toast.
- **CSS**: the real `style.css`, with project-specific names neutralised.

## What is deliberately left out

Any database (real or mock), SQL files and connection settings, secrets and API keys, real user data, password hashes, sessions and
tokens, Google OAuth configuration and screenshots, the Cloudflare tunnel tooling, the mail sender, upload
handling, the real logo, and YouTube or other external embeds. Cover art is generated with CSS gradients.

## Licence

Copyright (c) 2026 Ar Kar Phyo. All rights reserved. Viewing and study only; see `LICENSE`.
