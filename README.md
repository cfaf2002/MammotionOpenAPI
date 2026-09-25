# Mammotion Open API für IP-Symcon

[![Version](https://img.shields.io/badge/version-0.7e-blue.svg)](https://github.com/cfaf2002/MammotionOpenAPI)
[![Status](https://img.shields.io/badge/status-stable-green.svg)](https://github.com/cfaf2002/MammotionOpenAPI)
[![IP-Symcon](https://img.shields.io/badge/IP--Symcon-9.0%2B-orange.svg)](https://www.symcon.de/)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Last Commit](https://img.shields.io/github/last-commit/cfaf2002/MammotionOpenAPI)](https://github.com/cfaf2002/MammotionOpenAPI/commits/main)
[![Issues](https://img.shields.io/github/issues/cfaf2002/MammotionOpenAPI)](https://github.com/cfaf2002/MammotionOpenAPI/issues)

Stabile Integration von Mammotion-Mährobotern in IP-Symcon über die offizielle Mammotion Open API.

## Funktionsumfang

- OAuth2-Authentifizierung mit automatischer Token-Erneuerung
- automatische Geräteerkennung oder feste Device-ID
- Online-, Betriebs-, Akku-, Lade- und Netzwerkstatus
- Mähhöhe und Geschwindigkeit
- gespeicherte Aufgaben abrufen und starten
- native IP-Symcon-Steuerung für Pause, Fortsetzen, Stop und Heimfahrt
- Verbindung vollständig deaktivierbar
- Diagnose, Wiederholungslogik und Offline-Erkennung
- Premium-Dashboard als `~HTMLBox`
- API-Nickname, Modell und Gerätebild werden automatisch übernommen

## Voraussetzungen

- IP-Symcon 9.0 oder neuer
- aktives Mammotion-Konto mit eingerichtetem Mähroboter
- Client-ID und Client-Secret aus dem Mammotion Developer Portal
- ausgehender HTTPS-Zugriff auf die Mammotion-Dienste

## Installation

1. In IP-Symcon **Kerninstanzen → Modules** öffnen.
2. **Hinzufügen** wählen.
3. Repository-Adresse eintragen:

```text
https://github.com/cfaf2002/MammotionOpenAPI.git
```

4. Eine Instanz **Mammotion** anlegen.
5. Client-ID und Client-Secret eintragen.
6. Optional die Device-ID eintragen. Leer bedeutet automatische Auswahl des ersten Gerätes.
7. **Verbindung aktiv** einschalten, übernehmen und die **Vollständige Startprüfung** ausführen.

## Konfiguration

| Einstellung | Beschreibung | Empfehlung |
|---|---|---|
| Client ID | API-Client-ID | erforderlich |
| Client Secret | API-Client-Secret | erforderlich |
| Device ID | Kennung des Mähers | leer = automatisch |
| Abfrageintervall | Aktualisierungsintervall | 60 Sekunden |
| Verbindung aktiv | Cloud-Kommunikation ein/aus | ein |
| Schreibbefehle freigeben | native Steuerung aktivieren | zunächst aus |

## Dashboard

Die Variable **Dashboard** kann direkt in die IP-Symcon-Kachelvisualisierung aufgenommen werden. Angezeigt werden API-Nickname, Modell, Gerätebild, Betriebsstatus, Akku, Mähhöhe, WLAN-Qualität, Firmware und letzte Aktualisierung.

Die Steuerung erfolgt bewusst über die nativen Variablen **Steuerung** und **Aufgabe starten**. Dadurch bleibt die Bedienung stabil und mit IP-Symcon Connect kompatibel.

## Objektbaum

```text
Mammotion
├── Dashboard
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

## Sicherheit

- Client-Secret und Access-Token niemals veröffentlichen.
- Schreibbefehle sind standardmäßig deaktiviert.
- Steuerbefehle erst nach erfolgreicher Startprüfung freigeben.
- Für Wartung oder Einwinterung kann **Verbindung aktiv** ausgeschaltet werden.
- Die Nutzung von Steuerbefehlen erfolgt in eigener Verantwortung.

## Fehlerbehebung

### Repository wird nicht erkannt

Das Modul über die GitHub-Adresse in der Modulverwaltung installieren. Ein manuell kopierter Ordner besitzt normalerweise kein `.git`-Verzeichnis.

### Systemzustand Fehler

`API-Status`, `Diagnose` und `Tokenstatus` prüfen. Häufige Ursachen sind ungültige Zugangsdaten, eine falsche Device-ID oder eine vorübergehend nicht erreichbare Cloud.

### Keine Steuerung möglich

```text
Verbindung aktiv = Ja
Schreibbefehle freigeben = Ja
Online = Ja
```

## Bekannte Einschränkungen

Die dokumentierten Work-Report-Endpunkte waren während der Entwicklung nicht zuverlässig nutzbar. Deshalb sind Mähhistorie, Flächenstatistik und historische Laufzeiten noch nicht enthalten.

## Mitwirken

Hinweise stehen in [CONTRIBUTING.md](CONTRIBUTING.md). Fehler und Vorschläge bitte als GitHub-Issue melden.

## Lizenz

Dieses Projekt steht unter der [MIT-Lizenz](LICENSE).
