# Mammotion Open API für IP-Symcon

[![Version](https://img.shields.io/badge/version-0.7c-blue.svg)](https://github.com/cfaf2002/MammotionOpenAPI)
[![Status](https://img.shields.io/badge/status-stable-green.svg)](https://github.com/cfaf2002/MammotionOpenAPI)
[![IP-Symcon](https://img.shields.io/badge/IP--Symcon-9.0%2B-orange.svg)](https://www.symcon.de/)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![Mammotion Open API](https://img.shields.io/badge/Mammotion-Open%20API-success.svg)](https://developer.mammotion.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Last Commit](https://img.shields.io/github/last-commit/cfaf2002/MammotionOpenAPI)](https://github.com/cfaf2002/MammotionOpenAPI/commits/main)
[![Issues](https://img.shields.io/github/issues/cfaf2002/MammotionOpenAPI)](https://github.com/cfaf2002/MammotionOpenAPI/issues)

> Integration von Mammotion-Mährobotern in IP-Symcon über die offizielle Mammotion Open API.

**Version:** 0.7c  
**Status:** Stabil

## Inhaltsverzeichnis

- [Features](#features)
- [Installationsvoraussetzungen](#installationsvoraussetzungen)
- [Installation](#installation)
- [Konfiguration](#konfiguration)
- [Objektbaum](#objektbaum)
- [Systemzustände](#systemzustände)
- [Steuerfunktionen](#steuerfunktionen)
- [Sicherheitshinweise](#sicherheitshinweise)
- [Fehlerbehebung](#fehlerbehebung)
- [Bekannte Einschränkungen](#bekannte-einschränkungen)
- [Versionshistorie](#versionshistorie)
- [Roadmap](#roadmap)
- [Mitwirken](#mitwirken)
- [Support](#support)
- [Lizenz](#lizenz)

## Features

### Statusüberwachung

- Online-Status
- Betriebsstatus
- Status-Rohwert
- Akkustand
- Ladestatus
- Firmware-Version

### Netzwerküberwachung

- WLAN-RSSI
- WLAN-IP-Adresse
- Mobilfunk-RSSI

### Steuerung

- Pause
- Fortsetzen
- Stop
- Zur Ladestation
- Rückkehr abbrechen
- Gespeicherte Aufgaben starten

### Diagnose

- Systemzustand
- API-Status
- Diagnoseinformationen
- Tokenstatus
- Token-Ablaufdatum
- Letzte Startprüfung
- Letzte erfolgreiche Aktualisierung
- Letzter API-Versuch

### Verbindungskontrolle

- Cloud-Verbindung aktivieren oder deaktivieren
- Automatische Tokenverwaltung
- Automatische Wiederholung bei vorübergehenden Cloudfehlern
- Offline-Erkennung
- Keine API-Aufrufe bei deaktivierter Verbindung

## Installationsvoraussetzungen

### IP-Symcon

- IP-Symcon 9.0 oder neuer
- PHP 8.x innerhalb der IP-Symcon-Laufzeit

### Mammotion-Konto

Ein aktives Mammotion-Konto mit mindestens einem in der Mammotion-App eingerichteten Gerät wird benötigt.

### Mammotion Developer Portal

Es wird eine registrierte Anwendung im Mammotion Developer Portal benötigt. Folgende Zugangsdaten müssen vorhanden sein:

- Client-ID
- Client-Secret

### Device-ID

Die Device-ID kann mit einem gültigen Bearer-Token über folgenden Endpunkt ermittelt werden:

```http
GET https://api-open.mammotion.com/v1/mowers
```

### Internetverbindung

Der IP-Symcon-Server muss ausgehende HTTPS-Verbindungen zu folgenden Diensten herstellen können:

```text
https://id.mammotion.com
https://api-open.mammotion.com
```

### Zusätzliche PHP-Erweiterungen

Eine Standardinstallation von IP-Symcon enthält die benötigten Funktionen. Zusätzliche Composer-Pakete sind nicht erforderlich.

## Installation

### Installation über die Modulverwaltung

1. In IP-Symcon **Kerninstanzen → Modules** öffnen.
2. **Hinzufügen** wählen.
3. Folgende Repository-Adresse eintragen:

```text
https://github.com/cfaf2002/MammotionOpenAPI.git
```

4. Nach der Installation eine Instanz **Mammotion** anlegen.
5. Client-ID, Client-Secret und Device-ID eintragen.
6. **Verbindung aktiv** einschalten.
7. Konfiguration übernehmen.
8. **Vollständige Startprüfung** ausführen.

### Manuelle Installation

Der Modulordner muss folgende Struktur besitzen:

```text
MammotionOpenAPI/
├── library.json
├── README.md
└── Mammotion/
    ├── form.json
    ├── module.json
    └── module.php
```

Der Ordner wird nach folgendem Pfad kopiert:

```text
/var/lib/symcon/modules/MammotionOpenAPI
```

Hinweis: Ein manuell kopierter Ordner besitzt normalerweise keine `.git`-Metadaten. Die Aktualisierungsprüfung der IP-Symcon-Modulverwaltung funktioniert nur bei einer Installation als Git-Repository.

## Konfiguration

| Einstellung | Beschreibung | Empfehlung |
|---|---|---|
| Client-ID | Client-ID aus dem Mammotion Developer Portal | Erforderlich |
| Client-Secret | Client-Secret aus dem Mammotion Developer Portal | Erforderlich |
| Device-ID | Kennung des Mähroboters | Erforderlich |
| Abfrageintervall | Intervall der Cloud-Abfrage in Sekunden | 60 Sekunden |
| Verbindung aktiv | Aktiviert die Cloud-Kommunikation | Ein |
| Schreibbefehle freigeben | Erlaubt Steuerbefehle an den Mäher | Zunächst Aus |

### Erste Inbetriebnahme

1. Zugangsdaten eintragen.
2. Device-ID eintragen.
3. Verbindung aktivieren.
4. Konfiguration übernehmen.
5. Vollständige Startprüfung ausführen.
6. Systemzustand, API-Status und Diagnose prüfen.
7. Schreibbefehle erst nach erfolgreichem Verbindungstest freigeben.

## Objektbaum

```text
Mammotion
├── Online
├── Betriebsstatus
├── Status (Rohwert)
├── Akku
├── Firmware
├── Ladestatus (Code)
├── WLAN RSSI
├── WLAN IP
├── Mobilfunk RSSI
├── Mähhöhe
├── Geschwindigkeit (Code)
├── Systemzustand
├── API-Status
├── Letztes API-Ergebnis
├── Tokenstatus
├── Token gültig bis
├── Diagnose
├── Letzte Startprüfung
├── Letzte erfolgreiche Aktualisierung
├── Letzter API-Versuch
├── Steuerung
└── Aufgabe starten
```

## Systemzustände

| Wert | Zustand | Bedeutung |
|---:|---|---|
| 0 | Initialisierung | Instanz wird vorbereitet |
| 1 | Prüfung läuft | Startprüfung oder Verbindungsprüfung läuft |
| 2 | Betriebsbereit | Verbindung und Basisabfragen funktionieren |
| 3 | Teilweise verfügbar | Basisdaten verfügbar, optionaler Abruf fehlgeschlagen |
| 4 | Offline | Mäher ist ausgeschaltet oder nicht erreichbar |
| 5 | Fehler | Authentifizierung, Konfiguration oder Cloudzugriff fehlgeschlagen |
| 6 | Deaktiviert | Verbindung wurde in der Konfiguration ausgeschaltet |

## Steuerfunktionen

Unterstützte Befehle:

- Pause
- Fortsetzen
- Stop
- Zur Ladestation
- Rückkehr abbrechen
- Gespeicherte Aufgabe starten

Voraussetzungen:

- Verbindung aktiv
- Mäher online
- Schreibbefehle freigeben aktiviert

## Sicherheitshinweise

### Zugangsdaten schützen

Client-ID und Client-Secret dürfen nicht veröffentlicht werden. Zugangsdaten gehören nicht in Screenshots, Issues, Debugausgaben oder öffentliche Git-Repositories.

### Schreibbefehle bewusst freigeben

Steuerbefehle sind standardmäßig deaktiviert. Vor der Freigabe muss sichergestellt werden, dass der Arbeitsbereich frei ist und keine Personen oder Tiere gefährdet werden.

### Verbindung bei Wartung deaktivieren

Für Wartungsarbeiten oder längere Stillstandszeiten kann **Verbindung aktiv** ausgeschaltet werden. Dadurch werden automatische Abfragen, Token-Erneuerungen und Wiederholungsversuche gestoppt.

### Haftungshinweis

Steuerbefehle werden direkt an den Mähroboter übertragen. Die Nutzung des Moduls und automatisierter Abläufe erfolgt in eigener Verantwortung.

## Fehlerbehebung

### Repository wird nicht gefunden

Fehlermeldung:

```text
could not find repository at '/var/lib/symcon/modules/MammotionOpenAPI'
```

Ursache ist häufig ein manuell kopierter Modulordner ohne `.git`-Verzeichnis. Das Modul sollte über die Git-Repository-Adresse in der IP-Symcon-Modulverwaltung installiert werden.

### Systemzustand „Fehler“

Folgende Variablen prüfen:

- API-Status
- Diagnose
- Tokenstatus

Typische Ursachen:

- Client-ID oder Client-Secret falsch
- Device-ID ungültig
- Internetverbindung fehlt
- Mammotion-Cloud vorübergehend nicht erreichbar

### Systemzustand „Offline“

Mögliche Ursachen:

- Mäher ausgeschaltet
- Mäher im Energiesparzustand
- WLAN- oder Mobilfunkverbindung fehlt

### Tokenstatus „Abgelaufen“

1. Client-ID und Client-Secret prüfen.
2. Konfiguration übernehmen.
3. **Token jetzt erneuern** ausführen.
4. Vollständige Startprüfung ausführen.

### Steuerbefehle funktionieren nicht

Prüfen:

- Verbindung aktiv
- Online = Ja
- Schreibbefehle freigeben = Ja
- Systemzustand = Betriebsbereit oder Teilweise verfügbar

### HTTP 401

Der Access-Token ist ungültig oder abgelaufen. Das Modul versucht automatisch, einen neuen Token zu beziehen und den API-Aufruf einmal zu wiederholen.

### HTTP 404

Der angefragte API-Endpunkt ist nicht verfügbar. Dies wurde insbesondere bei dokumentierten Work-Report-Endpunkten beobachtet.

### API-Code 40200 oder 40300

Die Anfrage wurde von der Mammotion-API abgelehnt. Mögliche Ursachen sind ungültige Parameter, ein nicht freigeschalteter Endpunkt oder eine serverseitige Einschränkung.

## Bekannte Einschränkungen

Die dokumentierten Work-Report-Endpunkte waren während der Entwicklung nicht zuverlässig nutzbar. Deshalb sind folgende Funktionen aktuell nicht enthalten:

- Mähhistorie
- Letzter Mähvorgang
- Gemähte Fläche pro Zeitraum
- Historische Mähdauer

## Versionshistorie

### 0.7c

- Entwicklungsvariablen entfernt
- Objektbaum bereinigt
- Verbindungsschalter beibehalten
- Token-Ablaufanzeige beibehalten
- Diagnose und Startprüfung vereinfacht

### 0.7b

- Schalter **Verbindung aktiv** hinzugefügt
- Systemzustand **Deaktiviert** ergänzt
- API-Aufrufe und Token-Erneuerung bei deaktivierter Verbindung blockiert
- Token-Ablaufzeit sichtbar gemacht

### 0.7a

- Erweiterte Startdiagnose
- Fortschritts- und Laufzeitanzeige für die Entwicklung

## Roadmap

Geplant, sobald die benötigten API-Endpunkte zuverlässig verfügbar sind:

- Mähhistorie
- Flächenstatistik
- Laufzeitstatistik
- Historische Auswertungen

## Mitwirken

Beiträge sind willkommen. Hinweise befinden sich in [CONTRIBUTING.md](CONTRIBUTING.md).

## Support

Fehler und Verbesserungsvorschläge bitte als [GitHub-Issue](https://github.com/cfaf2002/MammotionOpenAPI/issues) melden.

Hilfreiche Angaben:

- Modulversion
- IP-Symcon-Version
- Systemzustand
- API-Status
- Diagnose
- Vollständiger Fehlertext ohne Zugangsdaten

## Lizenz

Dieses Projekt steht unter der [MIT-Lizenz](LICENSE).
