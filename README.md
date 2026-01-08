# Password Generator (MVP)

## Kurzbeschreibung
Diese Anwendung stellt einen clientseitigen Passwort‑Generator bereit. Nutzer können die Passwortlänge und Zeichentypen steuern, Mindestanzahlen für Zahlen und Sonderzeichen definieren, das Passwort manuell anpassen und die Stärke live beurteilen. Die Generierung nutzt kryptografisch sichere Zufallszahlen und speichert den Zustand im Browser.

## Start / Installation

### Voraussetzungen
- PHP 8.4+
- Composer
- Node.js und npm

### Schritte
```bash
composer install
npm install
```

Falls keine `.env` vorhanden ist:
```bash
cp .env.example .env
php artisan key:generate
```

Frontend starten:
```bash
npm run dev
```

Laravel starten:
```bash
php artisan serve
```

Aufrufen:
- `/passwords`

## Features
- Passwortlänge 8–128
- Zeichentypen: Kleinbuchstaben, Großbuchstaben, Zahlen, Sonderzeichen
- Mindestanzahl für Zahlen und Sonderzeichen
- Editierbares Passwortfeld
- Passwortstärke‑Anzeige (Schwach/Mittel/Stark)
- Copy‑to‑Clipboard
- Persistenz via LocalStorage
- Crypto‑sichere Zufallszahlen

## KI‑Reflexions‑Protokoll (30% der Note)

### Für welche Teile habe ich KI genutzt?
- Boilerplate und UI‑Grundstruktur
- Alpine‑Logik‑Vorschläge
- Generator‑Algorithmen
- Refactoring‑Ideen für Komponenten‑Struktur

### Konkrete Beispiele (Prompt → Ergebnis → Korrektur)
1) Prompt: „Nutze zufällige Zeichen für den Generator.“ → Ergebnis: Vorschlag mit `Math.random()` → Korrektur: Umstellung auf `window.crypto.getRandomValues()` für kryptografisch sichere Zufallszahlen.
2) Prompt: „Füge Sonderzeichen hinzu.“ → Ergebnis: sehr große Symbolmenge inkl. `|`, `<`, `>` → Korrektur: Reduktion auf eine konservative, akzeptierte Symbolmenge gemäß Anforderungen.
3) Prompt: „Mindestanzahl pro Typ erzwingen.“ → Ergebnis: Summe der Mindestwerte konnte größer als die Länge werden → Korrektur: deterministische Auto‑Korrektur (zuerst Sonderzeichen, dann Zahlen) bis `minSum <= length`.

### Wo hat die KI halluziniert oder schlechten Code geliefert?
- Verwendung veralteter Tailwind‑Utilities (v3‑Syntax), die in Tailwind v4 nicht existieren.
- Vorschläge mit Inline‑Script in Blade statt ausgelagerter JS‑Struktur.
- Unvollständige Prüfung von Eingaben (fehlende Längen‑Clamps bei manueller Eingabe).

## Bewertungskriterien (Kurz‑Selbstcheck)
- Funktionalität: Alle geforderten Features sind implementiert und stabil.
- Code‑Qualität: Struktur in Komponenten, klare Benennung, keine unnötige Logik in Views.
- Dokumentation & KI‑Reflexion: Schritte und KI‑Einsatz sind nachvollziehbar beschrieben.
