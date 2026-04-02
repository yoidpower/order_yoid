# YOID Power — Landing Page (order_yoid)

## Overview
Pixel-accurate product landing page for YOID Power charging stations. Built from the "Modern Product Launch" Figma design. Served via PHP 8.2 + Apache Docker container behind nginx-proxy with auto-SSL.

## Live Site
- **URL:** https://order.yoidpower.com
- **GitHub:** https://github.com/vikgr/order_yoid (private)
- **GitHub user:** vikgr
- **GitHub token:** stored in macOS keychain (osxkeychain)

## Server & Deployment
- **Server IP:** 193.124.113.252
- **SSH alias:** `rux` (config: `~/.ssh/config` → IdentityFile `~/.ssh/rux_key`, User `vik`)
- **Server path:** `/home/devops/order_yoid`
- **Branch:** `main`

### First-time deploy (on server terminal)
```bash
git clone https://github.com/vikgr/order_yoid.git /home/devops/order_yoid
cd /home/devops/order_yoid
cp .env.example .env
# fill in SMTP credentials in .env
docker compose up -d --build
```

### Update deploy (after git push)
```bash
cd /home/devops/order_yoid
git pull origin main
docker compose up -d --build
```

### Claude's role
- Make changes locally, push to GitHub
- Give user the deploy commands above to run in the **server terminal**
- NEVER SSH into the server and run commands — always give commands to user

## Local Development
```bash
cd /Users/vikniniadis/devops/order_yoid
python3 -m http.server 8090   # quick preview (no PHP)
# OR
docker compose up --build     # full stack with send.php
# Open: http://localhost:8090
```

## CSS Cache Busting
- CSS is linked as `css/style.css?v=N` — increment N on every CSS change
- Current version: `?v=10`
- Hard refresh browser: Cmd+Shift+R

## Tech Stack
- **HTML/CSS/JS:** Single-page, no framework
- **Font:** Inter (Google Fonts, weights 400–900)
- **Carousel:** Swiper.js v11 (CDN)
- **Form backend:** PHP 8.2 + PHPMailer 6 → SMTP → orders@yoidpower.com
- **Container:** Docker `php:8.2-apache` + Composer
- **Proxy/SSL:** jwilder/nginx-proxy + letsencrypt-companion on `yoidpower_proxy-tier` network

## Project Structure
```
order_yoid/
├── index.html              ← Single-page site (all 6 sections)
├── send.php                ← Contact form → PHPMailer → SMTP
├── css/style.css           ← All brand styles + responsive
├── js/main.js              ← Navbar scroll, Swiper init, form handler
├── images/
│   ├── logo.png            ← YOID logo (gradient O)
│   ├── station_top_page.png ← Hero section — compact station transparent render
│   ├── station-compact.jpg  ← COMPACT row — 8-slot station
│   ├── station-plus.jpg     ← PLUS row — 24-slot floor kiosk
│   ├── station-max.jpg      ← MAX row — 48-slot floor kiosk
│   └── collection/
│       └── card-1.png … card-8.png  ← Poster cards for carousel
├── composer.json           ← PHPMailer dependency
├── .env                    ← 🔒 NOT committed — SMTP secrets
├── .env.example            ← Template
├── Dockerfile              ← php:8.2-apache + Composer
└── docker-compose.yml      ← expose:80, yoidpower_proxy-tier network
```

## Image Assignment (STRICT — never mix)
| Section | Image |
|---------|-------|
| Hero | `images/station_top_page.png` |
| COMPACT row | `images/station-compact.jpg` |
| PLUS row | `images/station-plus.jpg` |
| MAX row | `images/station-max.jpg` |
| COMPACT display card | `images/collection/card-1.png` |
| Collection carousel | `images/collection/card-1.png` … `card-8.png` |

## Brand Colors
| Var | Value | Usage |
|-----|-------|-------|
| `--dark` | `#0C0C14` | Hero, collection, footer BG |
| `--pink` | `#E8175D` | CTA buttons, badges, accents |
| `--purple` | `#7B2FBE` | Hero gradient stripe |
| `--teal` | `#4ECDC4` | Hero gradient stripe |
| `--light-bg` | `#F5F5F0` | Stations section BG |
| `--white` | `#FFFFFF` | Text on dark |

## Page Sections
| # | Section | Notes |
|---|---------|-------|
| 1 | Navbar | Glass pill, fixed, scrolled state |
| 2 | Hero | Light bg + diagonal gradient stripes + dark card + station_top_page.png |
| 3 | OUR STATIONS | White bg — COMPACT / PLUS / MAX rows + dashed SVG arrows |
| 4 | OUR COLLECTION | White bg — 8-card Swiper horizontal carousel |
| 5 | Contact | 2-col: info+features left, form right + newsletter pill |
| 6 | Footer | Dark bg, 2-col links |

## SMTP / .env
```env
SMTP_HOST=mail.yoidpower.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=user@yoidpower.com
SMTP_PASS=112233445566
MAIL_TO=orders@yoidpower.com
MAIL_FROM=user@yoidpower.com
MAIL_FROM_NAME=YOID Power Website
```

## Docker Compose Key Points
- `expose: 80` (NOT ports) — nginx-proxy handles routing
- `VIRTUAL_HOST=order.yoidpower.com` — auto-routing
- `LETSENCRYPT_HOST=order.yoidpower.com` — auto SSL (~60s after start)
- `networks: proxy-tier → yoidpower_proxy-tier` (external, must exist on server)
- Images volume-mounted read-only: `./images:/var/www/html/images:ro`

## Owner
- **Developer:** Vik Niniadis
- **Git user:** vikgr
