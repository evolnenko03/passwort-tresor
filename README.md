# Password Generator MVP

Kurzbeschreibung:
- Laravel 12 + Blade + Tailwind CSS v4 + Alpine.js
- Clientseitige Passwort-Generierung mit crypto-sicheren Zufallszahlen
- Route: `/passwords`

Start:
1. `npm install`
2. `npm run dev`

## KI-Reflexions-Protokoll (Template)
- Ziel:
- Vorgehen:
- Entscheidungen:
- Aenderungen: Passwortfeld editierbar gemacht, Strength-Meter basiert auf aktuellem Inhalt.
- Logik: Mindestwerte nur fuer digits/symbols, Rest fuellt zuerst Buchstaben.
- Schutz: Mindestens ein Zeichentyp bleibt aktiv (Rollback beim letzten Toggle).
- Eingaben: Passwortfeld trimmt auf Laenge, sanitizing pro Zeichentyp verhindert ungueltige Zeichen.
- Symbols: feste Bitwarden-aehnliche Sonderzeichenliste statt freier Symbolwahl.
- Persistenz: localStorage haelt UI-Status und Passwort nach Reload.
- UX: auto-wachsende Textarea verhindert horizontales Scrollen bei langen Passwoertern.
- Abweichungen/Trade-offs:
- Tests:
- Offene Punkte:
