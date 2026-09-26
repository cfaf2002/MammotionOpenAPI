# Changelog

Alle relevanten Änderungen werden in dieser Datei dokumentiert.

## [0.7e1] - 2026-09-23

### Korrigiert

- `SystemState` wird bei jedem Refresh auf `Prüfung läuft` gesetzt.
- `CompleteSuccess()` aktualisiert den Systemzustand auch bei normalen Timer-Abrufen.
- Teilweise erfolgreiche Abrufe setzen den Zustand immer auf `Teilweise verfügbar`.
- Offline-Erkennung setzt den Zustand auch außerhalb der Startprüfung zuverlässig auf `Offline`.

### Dokumentation

- vollständige README erweitert
- Statusvariablen und Zeitstempel erklärt
- API-Endpunkte, Tokenverwaltung, FAQ und Fehlerbehebung dokumentiert

## [0.7e]

### Hinzugefügt

- Premium-Dashboard
- automatische Übernahme von Nickname, Modell und Gerätebild
- Akku-Ring und WLAN-Qualitätsanzeige

## [0.7c]

- Objektbaum bereinigt
- Entwickler-Variablen entfernt

## [0.7b]

- Verbindungsschalter
- deaktivierter Systemzustand
- Token-Ablaufanzeige
