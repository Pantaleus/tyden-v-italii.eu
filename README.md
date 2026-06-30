# Týden v Itálii 🇮🇹 - Travel Blog & Diary

[English version below](#english-version)

**Týden v Itálii** je moderní, vysoce optimalizovaný cestovatelský blog a deník postavený na zakázkovém MVC objektovém jádru v **PHP 8.4** bez použití jakéhokoliv frameworku. Projekt slouží k zaznamenávání cest, vizualizaci itinerářů na interaktivní časové ose a publikování zážitků.

## 🌟 Klíčové vlastnosti
- **Custom PHP 8.4 MVC Engine**: Čistá architektura (Router, Models, Views, Controllers) postavená od základu bez závislostí.
- **Multijazyčnost (Mutace CZ, EN, IT)**:
  - Statické lokalizace řešené přes přehledné PHP jazykové slovníky.
  - Dynamický obsah (články, cesty, časová osa) plně lokalizován v databázi (vlastní tabulky pro překlady).
- **Strukturovaná Časová Osa (Timeline)**: Správa přesunů (letadlo, vlak, autobus, chůze, ubytování) se specifickými ikonami, časy odletů/příletů a detailními informacemi.
- **Administrační Panel**:
  - Přehledný Dashboard s podrobnými statistikami.
  - Plný CRUD pro správu cest, článků (integrovaný TinyMCE editor) a uživatelských komentářů.
  - Správa administrátorských účtů a bezpečné uložení hesel pomocí `bcrypt`.
- **Pokročilý Traffic Tracker (Statistiky)**:
  - Logování IP, User-Agenta, operačního systému, prohlížeče a typu zařízení.
  - **GeoIP s mezipamětí**: Zjišťování státu a města z IP přes API s cachováním v DB (šetří API limity a zrychluje načítání).
  - **Asynchronní měření rozlišení**: Klientský JavaScript odesílá rozlišení obrazovky na pozadí po načtení stránky.
- **Přepínatelné Grafické Téma**: Možnost zvolit téma v administraci:
  - **Teplé Středomoří** (krémové, terakotové a modré odstíny).
  - **Italská Trikolóra** (čisté zelené, bílé a červené italské barvy).
- **SEO & Bezpečnost**:
  - Dynamicky generovaný `sitemap.xml` a `robots.txt`.
  - Ochrana proti spamu (Honeypoty u formulářů) a CSRF ochrana u požadavků.

---

## 🛠️ Použité Technologie
- **Backend**: PHP 8.4 (PDO MySQL)
- **Frontend**: HTML5, Vanilla CSS3 (s CSS Custom Properties), Vanilla JavaScript
- **Databáze**: MariaDB
- **Webový Server**: Apache (`.htaccess` pro pokročilé routování a zabezpečení)
- **Knihovny**: Composer (`phpmailer/phpmailer` pro SMTP zprávy)

---

## 📦 Lokální instalace a spuštění

1. Naklonujte repozitář do kořenové složky serveru (např. do Laragonu `/www/`):
   ```bash
   git clone https://github.com/Pantaleus/tyden-v-italii.eu.git
   ```
2. Nainstalujte závislosti pomocí Composeru:
   ```bash
   composer install
   ```
3. Vytvořte soubor `.env` v kořenovém adresáři (použijte `.env.example` jako vzor) a vyplňte své přístupové údaje k databázi.
4. Spusťte migrační a seedovací skript pro vytvoření tabulek a založení testovacích dat:
   ```bash
   php seed.php
   ```
   *Poznámka: Skript seed.php po úspěšném spuštění na produkci smažte z bezpečnostních důvodů.*
5. Výchozí přihlašovací údaje do administrace (`/admin`):
   - **E-mail:** `admin@tyden-v-italii.eu`
   - **Heslo:** `admin123`

---

<a id="english-version"></a>

# English Version

**Týden v Itálii** (A Week in Italy) is a modern, highly optimized travel blog and diary application built on a custom **PHP 8.4** object-oriented MVC engine without external frameworks. 

## 🌟 Key Features
- **Custom PHP 8.4 MVC Engine**: Clean router, model, view, controller separation written from scratch.
- **Multilingual Support (CZ, EN, IT)**: Language files for static interface elements, translation tables in DB for dynamic entities.
- **Structured Timeline**: Interactive visual itineraries displaying transport types (flight, train, bus, walk, hotel, car) with departure/arrival times.
- **Advanced Dashboard & Tracker**: Real-time logging of visitor statistics (IP, GeoIP location caching, User-Agent, device type, OS, browser, and asynchronous screen dimensions).
- **Toggled Color Schemes**: Toggled via admin panel (Warm Mediterranean vs. Italian Tricolore theme).
- **SEO & Security**: Dynamic sitemaps, robots.txt, CSRF validation, and Honeypot anti-spam protection.

## 🛠️ Tech Stack
- **Backend**: PHP 8.4 (PDO MySQL)
- **Frontend**: HTML5, Vanilla CSS3 (Custom variables), Vanilla JS
- **Database**: MariaDB
- **Web Server**: Apache (`.htaccess` security & clean URL rewrites)
- **Mailer**: Composer (`phpmailer/phpmailer` package)

## 📦 Local Setup

1. Clone the repository into your web root:
   ```bash
   git clone https://github.com/Pantaleus/tyden-v-italii.eu.git
   ```
2. Install PHP packages:
   ```bash
   composer install
   ```
3. Copy `.env.example` to `.env` and fill in database credentials.
4. Run the database migration and seeder:
   ```bash
   php seed.php
   ```
5. Default Admin Credentials:
   - **Email:** `admin@tyden-v-italii.eu`
   - **Password:** `admin123`
