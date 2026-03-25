# YOID Power — Landing Page

> **Live site:** [order.yoidpower.com](https://order.yoidpower.com)
> **GitHub repo:** [github.com/vikgr/order_yoid](https://github.com/vikgr/order_yoid) *(private)*

Product landing page for YOID Power charging stations, built from the "Modern Product Launch" Figma mockup. Pixel-accurate design with Inter font, served via PHP 8.2 + Apache Docker container.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Markup | HTML5 (semantic) |
| Styles | CSS3 custom properties + Bootstrap 5.3 grid |
| Font | Google Fonts — Inter (400–900) |
| Carousel | Swiper.js v11 |
| Form backend | PHP 8.2 + PHPMailer 6 (self-hosted SMTP) |
| Container | Docker — `php:8.2-apache` |

---

## Project Structure

```
order_yoid/
├── index.html          ← Single-page site (6 sections)
├── send.php            ← Contact form → PHPMailer → SMTP
├── css/
│   └── style.css       ← All brand styles, responsive
├── js/
│   └── main.js         ← Navbar, carousel, form handler
├── images/             ← Product images (see Images section below)
│   ├── hero-device.png
│   ├── station-compact.png
│   ├── station-plus.png
│   ├── station-max.png
│   └── collection/
│       ├── card-1.png … card-8.png
├── composer.json       ← PHPMailer dependency
├── .env.example        ← SMTP config template
├── .env                ← 🔒 Secrets — NOT committed (copy from .env.example)
├── .htaccess           ← Apache: gzip, cache, security headers
├── Dockerfile          ← php:8.2-apache + Composer
└── docker-compose.yml  ← Port 8082, env_file
```

---

## Page Sections

| # | Section | Background | Key Elements |
|---|---|---|---|
| 1 | Navbar | `#0C0C14` dark | YOID logo, nav links, "REQUEST A STATION" CTA |
| 2 | Hero | `#0C0C14` + gradient | Headline, sub-copy, CTA button, device image, 3 feature pills |
| 3 | Our Stations | `#FFFFFF` white | YOID COMPACT, YOID PLUS, YOID MAX cards |
| 4 | Our Collection | `#0C0C14` dark | 8-card horizontal Swiper carousel |
| 5 | Contact Form | `#F5F5F5` light | Lead form → email to orders@yoidpower.com |
| 6 | Footer | Dark gradient | Logo, links, copyright |

---

## Local Development

### Prerequisites
- Docker Desktop installed

### Run locally

```bash
# 1. Clone the repo
git clone git@github.com:vikgr/order_yoid.git
cd order_yoid

# 2. Copy env file and fill in your SMTP credentials
cp .env.example .env
# Edit .env with your SMTP settings

# 3. Build and start
docker compose up --build

# 4. Open in browser
open http://localhost:8082
```

---

## Images — How to Add Real Photos

The project uses `<img>` tags with `onerror` fallbacks. To add real images, simply place files in the `images/` folder with these exact names:

| File | Description | Recommended size |
|---|---|---|
| `images/hero-device.png` | Hero section device photo | 600×700px, transparent PNG |
| `images/station-compact.png` | YOID COMPACT product photo | 500×600px, transparent PNG |
| `images/station-plus.png` | YOID PLUS product photo | 400×700px, transparent PNG |
| `images/station-max.png` | YOID MAX product photo | 400×800px, transparent PNG |
| `images/collection/card-1.png` | "Power Is Staying Connected" | 520×930px |
| `images/collection/card-2.png` | "Power Is Pure" | 520×930px |
| `images/collection/card-3.png` | "Power Is Moments That Last" | 520×930px |
| `images/collection/card-4.png` | "Power Is Sharing Adventures" | 520×930px |
| `images/collection/card-5.png` | "Power Is Giving Light" | 520×930px |
| `images/collection/card-6.png` | "Power Is Giving Time" | 520×930px |
| `images/collection/card-7.png` | "Power Is Sharing Anywhere" | 520×930px |
| `images/collection/card-8.png` | "Power Is Giving Energy" | 520×930px |

Then restart the container:
```bash
docker compose restart order_yoid
```
> Images are mounted as a read-only volume — no rebuild needed.

---

## SMTP / Email Setup

The contact form submits to `send.php` which uses PHPMailer to send an email to `orders@yoidpower.com`.

### Step 1 — Configure .env on the server

```bash
cp .env.example .env
nano .env
```

Fill in:
```env
SMTP_HOST=mail.yoidpower.com   # Your mail server hostname
SMTP_PORT=587                   # 587 (STARTTLS) or 465 (SSL)
SMTP_SECURE=tls                 # tls or ssl
SMTP_USER=noreply@yoidpower.com
SMTP_PASS=your_password_here
MAIL_TO=orders@yoidpower.com
MAIL_FROM=noreply@yoidpower.com
MAIL_FROM_NAME=YOID Power Website
```

### Step 2 — Restart container
```bash
docker compose restart order_yoid
```

### Step 3 — Test
Submit the form on the site. You should receive an email at `orders@yoidpower.com`.

> **No SMTP server?** Use Gmail SMTP:
> - `SMTP_HOST=smtp.gmail.com`
> - `SMTP_PORT=587`
> - `SMTP_USER=your@gmail.com`
> - `SMTP_PASS=your_app_password` (use Google App Password, not your regular password)

---

## Server Deployment

### Requirements
- Linux server with Docker + Git installed
- Domain `order.yoidpower.com` pointing to server IP
- Existing reverse proxy (Nginx/Caddy) or direct port exposure

### Deploy

```bash
# On the server — first time
git clone git@github.com:vikgr/order_yoid.git /home/devops/order_yoid
cd /home/devops/order_yoid
cp .env.example .env
nano .env   # Fill in SMTP credentials

docker compose up -d --build
```

### Update (after git push)

```bash
cd /home/devops/order_yoid
git pull origin main
docker compose up -d --build
```

### Nginx Reverse Proxy (add to your Nginx config)

```nginx
server {
    listen 80;
    server_name order.yoidpower.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name order.yoidpower.com;

    ssl_certificate     /etc/letsencrypt/live/order.yoidpower.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/order.yoidpower.com/privkey.pem;

    location / {
        proxy_pass         http://127.0.0.1:8082;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
    }
}
```

### SSL with Certbot

```bash
certbot --nginx -d order.yoidpower.com
```

---

## Design System

| Token | Value | Usage |
|---|---|---|
| `--color-dark` | `#0C0C14` | Sections BG (hero, collection, footer) |
| `--color-pink` | `#E8175D` | Primary CTA, accents, highlights |
| `--color-purple` | `#7B2FBE` | Hero gradient |
| `--color-white` | `#FFFFFF` | Light section BG, text on dark |
| `--color-light` | `#F5F5F5` | Contact section BG |
| Font | `Inter` (Google Fonts) | All text — weights 400–900 |

---

## Owner

- **Developer:** Vik Niniadis
- **Git user:** vikgr
- **Contact:** orders@yoidpower.com
