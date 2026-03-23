# Oda e Fizioterapeutëve të Kosovës – Website

Website zyrtar për Oda e Fizioterapeutëve të Kosovës (Chamber of Physiotherapists of Kosovo). Temë profesionale mjekësore, e pastër dhe e besueshme.

## Përmbajtja

- **Ballina** – Hero, rreth nesh, lajme, CTA, qasje e shpejtë
- **Rreth Nesh** – Misioni, vlerat, shërbimet
- **Lajme** – Lista lajmesh (ngarkohen nga `data/news.json`)
- **Anëtarësimi** – Kërkesat dhe procedura
- **Regjistri i Fizioterapeutëve** – Tabelë e kërkueshme (`data/physiotherapists.json`)
- **Dokumente** – Lista dokumentesh PDF për shkarkim
- **Trajnime & Evente** – Kalendar ngjarjesh
- **Kontakt** – Formular kontakti dhe hartë (Prishtinë)
- **Hyr në Llogari** – Faqe hyrje për anëtarët

## Si ta hapni

Hapni `index.html` në një shfletues. Për JSON dhe harta, përdorni një server lokal (p.sh. Live Server në VS Code).

## Përditësimi i lajmeve

Redaktoni `data/news.json`. Fushat: id, title, date, excerpt, image, url.

## Përditësimi i regjistrit

Redaktoni `data/physiotherapists.json`. Fushat: id, name, license, city, specialty, phone, email.

## Dokumentet PDF

Vendosni PDF-et në dosjen `documents/` me emrat e treguar në faqen Dokumente.

## Struktura e kodit

- **PHP**: `config.php`, `includes/` (db, auth, content), `includes/header.php` dhe `includes/footer.php` për faqet e logimit dhe panelit. Të gjitha skedarët PHP përdorin `declare(strict_types=1)`.
- **Admin**: Paneli admin (`admin/`) përdor `_admin_layout.php` dhe `_admin_footer.php` për pamje të njëtrajtshme.
- **JS**: `js/main.js` (menuja, kërkim), `js/i18n.js` (tri gjuhë), `js/news.js`, `js/registry.js`, etj. për faqe me të dhëna nga JSON.
- **Gjuhet**: Shqip (sq), English (en) – `lang/*.json`.
