# Changelog

Alle relevanten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/).

## [Unreleased]

### Geplant

- Erweiterungen auf Basis zusätzlicher stabiler Mammotion-API-Endpunkte

## [0.7c] - 2026-09-25

### Geändert

- Entwicklungsvariablen entfernt
- Objektbaum bereinigt
- Diagnose und Startprüfung vereinfacht

### Beibehalten

- Verbindungsschalter
- Automatische Tokenverwaltung
- Steuerbefehle
- Offline-Erkennung
- Wiederholungslogik

## [0.7b] - 2026-09-25

### Hinzugefügt

- Schalter `Verbindung aktiv`
- Systemzustand `Deaktiviert`
- Token-Ablaufzeit im Objektbaum

### Geändert

- API-Aufrufe und Token-Erneuerungen werden bei deaktivierter Verbindung blockiert
- Hintergrundtimer werden bei deaktivierter Verbindung gestoppt

## [0.7a] - 2026-09-25

### Hinzugefügt

- Erweiterte Startdiagnose
- Fortschritts- und Laufzeitanzeige für die Entwicklung
