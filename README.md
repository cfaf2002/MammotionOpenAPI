# Mammotion Open API für IP-Symcon

[![Version](https://img.shields.io/badge/version-0.7e1-blue.svg)](https://github.com/cfaf2002/MammotionOpenAPI)
[![Status](https://img.shields.io/badge/status-stable-green.svg)](https://github.com/cfaf2002/MammotionOpenAPI)
[![IP-Symcon](https://img.shields.io/badge/IP--Symcon-9.0%2B-orange.svg)](https://www.symcon.de/)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![Mammotion Open API](https://img.shields.io/badge/Mammotion-Open%20API-success.svg)](https://developer.mammotion.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Last Commit](https://img.shields.io/github/last-commit/cfaf2002/MammotionOpenAPI)](https://github.com/cfaf2002/MammotionOpenAPI/commits/main)
[![Issues](https://img.shields.io/github/issues/cfaf2002/MammotionOpenAPI)](https://github.com/cfaf2002/MammotionOpenAPI/issues)

Integration von Mammotion-Mährobotern in IP-Symcon über die offizielle Mammotion Open API.

Die Version **0.7e1** basiert auf der stabilen Premium-Dashboard-Version 0.7e und korrigiert die Aktualisierung des Systemzustands bei automatischen, manuellen und wiederholten API-Abrufen.

## Inhaltsverzeichnis

- [Funktionsumfang](#funktionsumfang)
- [Unterstützte Geräte](#unterstützte-geräte)
- [Voraussetzungen](#voraussetzungen)
- [Projektstruktur](#projektstruktur)
- [Installation](#installation)
- [Konfiguration](#konfiguration)
- [Erste Inbetriebnahme](#erste-inbetriebnahme)
- [Dashboard](#dashboard)
- [Objektbaum](#objektbaum)
- [Status- und Diagnosevariablen](#status--und-diagnosevariablen)
- [Systemzustände](#systemzustände)
- [Steuerfunktionen](#steuerfunktionen)
- [Verwendete API-Endpunkte](#verwendete-api-endpunkte)
- [Tokenverwaltung](#tokenverwaltung)
- [Zeitsteuerung und Aktualisierung](#zeitsteuerung-und-aktualisierung)
- [Sicherheitshinweise](#sicherheitshinweise)
- [Fehlerbehebung](#fehlerbehebung)
- [Bekannte Einschränkungen](#bekannte-einschränkungen)
- [FAQ](#faq)
- [Versionshistorie](#versionshistorie)
- [Roadmap](#roadmap)
- [Mitwirken](#mitwirken)
- [Lizenz](#lizenz)
- [Haftung und Markenhinweis](#haftung-und-markenhinweis)

## Funktionsumfang

### Verbindung und Authentifizierung

- OAuth2-Anmeldung über Client-ID und Client-Secret
- Zwischenspeicherung des Access-Tokens
- automatische Token-Erneuerung vor Ablauf
- Nutzung eines Refresh-Tokens, sofern von der API bereitgestellt
- einmaliger Wiederholungsversuch nach HTTP 401
- erneute Anmeldung über Client-Credentials als Fallback

### Geräteinformationen

- automatische Geräteauswahl, wenn keine Device-ID eingetragen ist
- Online-Status
- Betriebsstatus und API-Rohstatus
- Akkustand
- Ladestatus
- Firmware-Version
- WLAN-Signalstärke und WLAN-IP
- Mobilfunk-Signalstärke
- Mähhöhe
- Geschwindigkeitscode

### Aufgaben und Steuerung

- Abruf der in Mammotion gespeicherten Aufgaben
- Auswahl und Start einer Aufgabe
- Pause
- Fortsetzen
- Stop
- Rückkehr zur Ladestation
- Abbruch der Heimfahrt
- separater Sicherheitsschalter für Schreibbefehle

### Diagnose und Stabilität

- vollständige Startprüfung
- automatische zyklische Aktualisierung
- Sperre gegen parallele Refresh-Aufrufe
- zwei zeitversetzte Wiederholungsversuche bei vorübergehenden Fehlern
- Offline-Erkennung
- Diagnose- und Ergebnisvariablen
- aktueller Systemzustand bei jedem Refresh
- Zeitstempel des letzten API-Versuchs und der letzten erfolgreichen Aktualisierung

### Premium-Dashboard

- API-Nickname, zum Beispiel `Horst`
- Modellbezeichnung
- Mammotion-Gerätebild
- farbcodierter Betriebsstatus
- Akku-Ring mit Zustandsbewertung
- Mähhöhe
- WLAN-Wert und Qualitätsbewertung
- Firmware
- letzte erfolgreiche Aktualisierung
- responsive Darstellung für Desktop, Tablet und Smartphone

## Unterstützte Geräte

Das Modul verwendet die offizielle Mammotion Open API. Die konkrete Verfügbarkeit einzelner Werte hängt vom eingesetzten Gerät, der Firmware und den für das Entwicklerkonto freigeschalteten API-Funktionen ab.

Das Modul wurde für Gerätefamilien konzipiert, die über `GET /v1/mowers` und die zugehörigen Mäher-Endpunkte bereitgestellt werden. Rückmeldungen zu weiteren Modellen sind willkommen.

## Voraussetzungen

### IP-Symcon

```text
IP-Symcon 9.0 oder neuer
```

### Mammotion-Konto

- aktives Mammotion-Benutzerkonto
- mindestens ein vollständig in der Mammotion-App eingerichteter Mähroboter
- Gerät mit funktionierender Cloudverbindung

### Mammotion Developer Portal

Für das Modul wird eine registrierte Open-API-Anwendung benötigt. Folgende Werte müssen vorliegen:

```text
Client-ID
Client-Secret
```

### Netzwerk

Der IP-Symcon-Server benötigt ausgehenden HTTPS-Zugriff auf:

```text
https://id.mammotion.com
https://api-open.mammotion.com
```

### Zusätzliche PHP-Bibliotheken

Es werden keine Composer-Pakete oder externen PHP-Bibliotheken benötigt. Das Modul nutzt die in IP-Symcon vorhandene PHP- und cURL-Umgebung.

## Projektstruktur

```text
MammotionOpenAPI/
├── .gitignore
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE
├── README.md
├── library.json
└── Mammotion/
    ├── form.json
    ├── module.json
    └── module.php
```

## Installation

### Installation über GitHub

1. In IP-Symcon **Kerninstanzen** öffnen.
2. Die Instanz **Modules** öffnen.
3. **Hinzufügen** auswählen.
4. Folgende Repository-Adresse eintragen:

```text
https://github.com/cfaf2002/MammotionOpenAPI.git
```

5. Installation abschließen.
6. Im Objektbaum eine neue Instanz **Mammotion** anlegen.

### Manuelle Installation

Das Repository kann alternativ in das IP-Symcon-Modulverzeichnis kopiert werden:

```text
/var/lib/symcon/modules/MammotionOpenAPI
```

Bei einer manuellen Kopie fehlt normalerweise der versteckte `.git`-Ordner. Dadurch kann die IP-Symcon-Modulverwaltung keine GitHub-Aktualisierungen prüfen. Für produktive Installationen wird deshalb die Installation über die Repository-Adresse empfohlen.

### Update einer vorhandenen Installation

1. Vorhandenen Modulordner sichern.
2. Geänderte Dateien in das bestehende Repository übernehmen.
3. In IP-Symcon die Module neu laden oder IP-Symcon neu starten.
4. Mammotion-Instanz öffnen.
5. **Übernehmen** drücken.
6. **Vollständige Startprüfung** ausführen.

## Konfiguration

| Einstellung | Bedeutung | Empfehlung |
|---|---|---|
| Client ID | Client-ID aus dem Mammotion Developer Portal | erforderlich |
| Client Secret | Client-Secret aus dem Mammotion Developer Portal | erforderlich |
| Device ID | eindeutige Gerätekennung | leer lassen für automatische Auswahl des ersten Geräts |
| Abfrageintervall | Intervall der zyklischen Aktualisierung | 60 Sekunden |
| Verbindung aktiv | aktiviert Cloudzugriffe, Tokenverwaltung und Timer | aktiv |
| Schreibbefehle freigeben | erlaubt reale Steuerbefehle an den Mäher | erst nach erfolgreichem Test aktivieren |

Das minimale Abfrageintervall beträgt 30 Sekunden. Kürzere Werte werden als ungültige Konfiguration behandelt.

## Erste Inbetriebnahme

1. Client-ID eintragen.
2. Client-Secret eintragen.
3. Device-ID optional eintragen.
4. **Verbindung aktiv** einschalten.
5. Abfrageintervall auf 60 Sekunden setzen.
6. **Schreibbefehle freigeben** zunächst ausgeschaltet lassen.
7. **Übernehmen** drücken.
8. **Vollständige Startprüfung** ausführen.
9. Systemzustand, API-Status, Diagnose und Online-Status prüfen.
10. Schreibbefehle bei Bedarf freigeben und zunächst mit einer unkritischen Aktion testen.

Ein erfolgreicher Zustand sieht typischerweise so aus:

```text
Systemzustand: Betriebsbereit
API-Status: OK
Online: Ja
Tokenstatus: Gültig
```

## Dashboard

Das Dashboard wird als Stringvariable mit dem Profil `~HTMLBox` angelegt. Die Variable kann in die Kachelvisualisierung aufgenommen werden.

### Namensauflösung

Der angezeigte Name wird in folgender Reihenfolge bestimmt:

1. `nickname` aus der Mammotion-API
2. technischer API-Gerätename
3. Name der IP-Symcon-Instanz
4. `MAMMOTION`

Eine Änderung des Nicknames in der Mammotion-App wird nach dem nächsten erfolgreichen Geräteabruf automatisch übernommen.

### Farblogik

| Zustand | Farbe |
|---|---|
| Bereit | Grün |
| Mäht | Grün |
| Lädt | Blau |
| Pausiert | Gelb |
| Heimfahrt | Violett |
| Gerätefehler | Rot |
| API- oder Cloudfehler | Orange |
| Offline oder deaktiviert | Grau |

### Steuerung

Das Dashboard ist bewusst eine reine Anzeige. Die Steuerung erfolgt über die nativen IP-Symcon-Variablen **Steuerung** und **Aufgabe starten**. Das vermeidet zusätzliche WebHooks und bleibt mit lokaler Visualisierung sowie IP-Symcon Connect stabil nutzbar.

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
├── Letzte Startprüfung
├── API-Status
├── Letztes API-Ergebnis
├── Tokenstatus
├── Token gültig bis
├── Diagnose
├── Letzte erfolgreiche Aktualisierung
├── Letzter API-Versuch
├── Steuerung
└── Aufgabe starten
```

## Status- und Diagnosevariablen

### Systemzustand

Der Systemzustand beschreibt den aktuellen Zustand der Modulkommunikation. Seit v0.7e1 wird der Zustand bei jedem normalen Refresh, jedem Wiederholungsversuch und jeder vollständigen Startprüfung neu gesetzt.

### Letzte Startprüfung

Enthält den Zeitpunkt, zu dem zuletzt eine vollständige Startprüfung gestartet beziehungsweise abgeschlossen wurde. Der reguläre Update-Timer verändert diesen Wert bewusst nicht.

### Letzte erfolgreiche Aktualisierung

Wird nach jedem erfolgreichen Basisabruf aktualisiert. Auch ein erkannter Offline-Zustand kann als erfolgreich verarbeiteter API-Abruf gelten.

### Letzter API-Versuch

Wird zu Beginn jedes Refresh-Vorgangs aktualisiert, unabhängig davon, ob der Abruf erfolgreich ist.

### API-Status

Enthält eine kompakte Zusammenfassung, zum Beispiel:

```text
OK
OK, Gerät offline
Teilweise verfügbar
Authentifizierung fehlgeschlagen
Cloud vorübergehend nicht erreichbar
```

### Diagnose

Enthält die ausgeführten Schritte und gegebenenfalls die konkrete Fehlermeldung.

## Systemzustände

| Wert | Bezeichnung | Bedeutung |
|---:|---|---|
| 0 | Initialisierung | Instanz wird vorbereitet oder Konfiguration wurde übernommen |
| 1 | Prüfung läuft | API-Abruf oder vollständige Startprüfung läuft |
| 2 | Betriebsbereit | Basisdaten und optionale Daten wurden erfolgreich verarbeitet |
| 3 | Teilweise verfügbar | Basisdaten verfügbar, optionaler Abruf fehlgeschlagen |
| 4 | Offline | Gerät ist ausgeschaltet oder nicht erreichbar |
| 5 | Fehler | Konfiguration, Authentifizierung oder Cloudkommunikation fehlgeschlagen |
| 6 | Deaktiviert | Verbindung wurde in der Instanzkonfiguration ausgeschaltet |

### Aktualisierungslogik in v0.7e1

Bei jedem Refresh wird der Systemzustand zunächst auf **Prüfung läuft** gesetzt. Danach wird der Zustand abhängig vom Ergebnis auf **Betriebsbereit**, **Teilweise verfügbar**, **Offline** oder **Fehler** gesetzt. Damit bleibt der Zustand nicht mehr auf einem veralteten Wert stehen.

## Steuerfunktionen

Die Profilvariable **Steuerung** unterstützt:

```text
Pause
Fortsetzen
Stop
Zur Ladestation
Heimfahrt abbrechen
```

Die Profilvariable **Aufgabe starten** wird aus den über die API gelieferten gespeicherten Aufgaben aufgebaut.

### PHP-Funktionen

```php
MAMMO_StartCheck($InstanceID);
MAMMO_Refresh($InstanceID);
MAMMO_RenewToken($InstanceID);
MAMMO_Pause($InstanceID);
MAMMO_Resume($InstanceID);
MAMMO_Stop($InstanceID);
MAMMO_ReturnToDock($InstanceID);
MAMMO_CancelReturn($InstanceID);
MAMMO_StartTask($InstanceID, 'Aufgabenname');
```

Schreibende Funktionen benötigen:

```text
Verbindung aktiv = Ja
Schreibbefehle freigeben = Ja
```

## Verwendete API-Endpunkte

### Authentifizierung

```http
POST https://id.mammotion.com/oauth2/token
```

### Geräte und Status

```http
GET /v1/mowers
GET /v1/mower/{deviceId}
GET /v1/mower/{deviceId}/work-params
GET /v1/mower/{deviceId}/plan
```

### Steuerung

```http
POST /v1/mower/action
```

## Tokenverwaltung

Das Modul speichert Access-Token, Refresh-Token und Ablaufzeit intern als Attribute.

Ein Token wird neu angefordert, wenn:

- noch kein Token vorhanden ist
- der Token innerhalb des Sicherheitsfensters von fünf Minuten abläuft
- eine API-Antwort HTTP 401 beziehungsweise API-Code 401 liefert
- die Zugangsdaten geändert wurden
- die Aktion **Token jetzt erneuern** ausgeführt wird

Access-Token und Client-Secret werden nicht als sichtbare Variablen angelegt.

## Zeitsteuerung und Aktualisierung

### Update-Timer

Führt die zyklische Aktualisierung im konfigurierten Intervall aus.

### Startup-Timer

Startet kurz nach dem Übernehmen der Konfiguration die vollständige Startprüfung.

### Retry-Timer

Führt bei vorübergehenden Fehlern maximal zwei Wiederholungen aus:

```text
1. Wiederholung nach 5 Sekunden
2. Wiederholung nach 15 Sekunden
```

Bestimmte Fehler werden nicht wiederholt, zum Beispiel ungültige Zugangsdaten, eine fehlende Device-ID oder eine ungültige Konfiguration.

## Sicherheitshinweise

- Client-Secret und Access-Token niemals veröffentlichen.
- Zugangsdaten nicht in Screenshots, Issues oder Debugausgaben zeigen.
- Schreibbefehle sind standardmäßig gesperrt.
- Schreibbefehle erst nach erfolgreicher Startprüfung freigeben.
- Vor realen Steuerbefehlen sicherstellen, dass der Arbeitsbereich frei ist.
- Nur vertrauenswürdigen Personen Zugriff auf die IP-Symcon-Visualisierung geben.
- Für Wartung, Transport oder Einwinterung **Verbindung aktiv** ausschalten.
- Das Modul ersetzt keine Sicherheitsfunktionen des Mähroboters.

## Fehlerbehebung

### Systemzustand bleibt auf Initialisierung

- Module neu laden.
- Instanz öffnen und **Übernehmen** drücken.
- Verbindung aktivieren.
- vollständige Startprüfung ausführen.
- Diagnose und API-Status prüfen.

### Letzte Startprüfung ändert sich nicht

Der Zeitstempel wird nur durch eine vollständige Startprüfung geändert. Für normale zyklische Abrufe sind **Letzte erfolgreiche Aktualisierung** und **Letzter API-Versuch** maßgeblich.

### Systemzustand ändert sich bei automatischen Abrufen nicht

Dieses Verhalten wurde in v0.7e1 korrigiert. Prüfen, ob die installierte `module.php` den Systemzustand in `CompleteSuccess()` außerhalb der `if ($startCheck)`-Bedingung setzt.

### Mäher wird nicht gefunden

- Device-ID prüfen oder das Feld leeren.
- API-Aufruf `GET /v1/mowers` testen.
- sicherstellen, dass der Mäher dem verwendeten Mammotion-Konto zugeordnet ist.

### Tokenstatus abgelaufen

- Client-ID und Client-Secret prüfen.
- **Token jetzt erneuern** ausführen.
- anschließend vollständige Startprüfung starten.

### Systemzustand Offline

- Mäher einschalten.
- WLAN- oder Mobilfunkverbindung prüfen.
- Gerät kann sich im Energiesparzustand befinden.

### Teilweise verfügbar

Die Basisdaten konnten gelesen werden, aber mindestens ein optionaler Abruf, beispielsweise Arbeitsparameter oder Aufgabenplan, ist fehlgeschlagen. Die Diagnose enthält den betroffenen Schritt.

### Steuerbefehle funktionieren nicht

Prüfen:

```text
Verbindung aktiv = Ja
Schreibbefehle freigeben = Ja
Online = Ja
```

### Repository wird nicht erkannt

Ein manuell kopierter Ordner kann keinen `.git`-Ordner enthalten. Das Repository über die IP-Symcon-Modulverwaltung mit der GitHub-Adresse installieren.

### HTTP 401

Authentifizierung fehlgeschlagen oder Token abgelaufen. Das Modul löscht den Token-Cache und versucht einmalig eine neue Anmeldung.

### HTTP 404

Der angefragte API-Endpunkt ist auf dem Server nicht verfügbar.

### API-Code 40200 oder 40300

Die API hat die Anfrage fachlich abgelehnt. Mögliche Ursachen sind ungültige Parameter, fehlende Freischaltung oder eine serverseitige Einschränkung.

## Bekannte Einschränkungen

Die dokumentierten Work-Report-Endpunkte waren während der Entwicklung nicht zuverlässig nutzbar. Deshalb enthält diese Version noch keine:

- Mähhistorie
- Flächenstatistik
- historische Mähdauer
- Tages-, Wochen- oder Monatsauswertung

## FAQ

### Kann das Modul mehrere Mäher verwalten?

Ja. Für jeden Mäher kann eine eigene Instanz mit der jeweiligen Device-ID angelegt werden. Bleibt die Device-ID leer, wird das erste von der API gelieferte Gerät verwendet.

### Woher kommt der Name im Dashboard?

Primär aus dem API-Feld `nickname`. Wenn kein Nickname vorhanden ist, folgen technischer Gerätename, Instanzname und schließlich `MAMMOTION`.

### Warum zeigt das Dashboard keine Steuerbuttons?

Die Steuerung erfolgt absichtlich über native IP-Symcon-Aktionsvariablen. Damit bleibt die Bedienung stabil, sicherer und mit IP-Symcon Connect kompatibel.

### Wird der Token bei jedem Refresh erneuert?

Nein. Ein gültiger Token wird aus dem Cache verwendet. Eine Erneuerung erfolgt nur bei Bedarf.

### Was bedeutet „Letzte Startprüfung“?

Der Zeitpunkt der letzten vollständigen Diagnoseprüfung. Der Wert ist nicht mit dem normalen Update-Timer gleichzusetzen.

### Welche Variable zeigt die laufende Aktualität?

**Letzte erfolgreiche Aktualisierung** zeigt den letzten erfolgreichen Abruf. **Letzter API-Versuch** zeigt den Beginn des letzten Versuchs.

## Versionshistorie

### 0.7e1

- Systemzustand wird bei jedem Refresh auf `Prüfung läuft` gesetzt
- erfolgreicher Refresh setzt den Systemzustand immer auf den ermittelten Endzustand
- Teilfehler setzen den Systemzustand immer auf `Teilweise verfügbar`
- Offline-Erkennung setzt den Systemzustand zuverlässig auf `Offline`
- Semantik von `Letzte Startprüfung` dokumentiert
- ausführliche GitHub-Dokumentation ergänzt

### 0.7e

- Premium-Dashboard
- automatischer API-Nickname
- Modell und Gerätebild
- Akku-Ring, Statusfarben und WLAN-Qualität

### 0.7c

- Objektbaum bereinigt
- Entwickler-Variablen entfernt

### 0.7b

- Verbindungsschalter
- deaktivierter Zustand
- Token-Ablaufanzeige

## Roadmap

Sobald stabile API-Endpunkte verfügbar sind:

- Mähhistorie
- Flächenstatistiken
- Laufzeitstatistiken
- Tages-, Wochen- und Monatsauswertungen
- weitere modellübergreifende Statusübersetzungen

## Mitwirken

Fehlerberichte und Verbesserungsvorschläge sind willkommen. Bitte keine Zugangsdaten oder Tokens veröffentlichen.

Weitere Hinweise stehen in [CONTRIBUTING.md](CONTRIBUTING.md).

## Lizenz

Dieses Projekt steht unter der [MIT-Lizenz](LICENSE).

## Haftung und Markenhinweis

Dieses Projekt ist ein unabhängiges Open-Source-Projekt und steht nicht in Verbindung mit Mammotion oder IP-Symcon. Produkt- und Markennamen gehören den jeweiligen Rechteinhabern.

Die Nutzung erfolgt in eigener Verantwortung. Es wird keine Gewährleistung für Verfügbarkeit, Kompatibilität oder fehlerfreien Betrieb übernommen.
