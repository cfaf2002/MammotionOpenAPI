# Mitwirken

Vielen Dank für das Interesse am MammotionOpenAPI-Modul.

## Fehler melden

Bitte vor dem Erstellen eines Issues prüfen, ob bereits ein passendes Issue existiert.

Ein Fehlerbericht sollte enthalten:

- Modulversion
- IP-Symcon-Version
- Gerätemodell
- Systemzustand
- API-Status
- Diagnose
- Schritte zum Reproduzieren
- Vollständige Fehlermeldung

Zugangsdaten, Access-Tokens, Client-Secrets und personenbezogene Daten dürfen nicht veröffentlicht werden.

## Verbesserungsvorschläge

Verbesserungsvorschläge bitte als GitHub-Issue mit einer kurzen Beschreibung des Anwendungsfalls einreichen.

## Pull Requests

Pull Requests sind willkommen.

Bitte beachten:

1. Änderungen auf Basis des aktuellen `main`-Branches erstellen.
2. Bestehende Funktionalität nicht unbeabsichtigt verändern.
3. Öffentliche Methoden vollständig typisieren.
4. Keine Zugangsdaten oder Test-Tokens in den Code aufnehmen.
5. README und CHANGELOG bei sichtbaren Änderungen aktualisieren.
6. Änderungen in einer IP-Symcon-Testinstanz prüfen.
7. Aussagekräftige Commit-Nachrichten verwenden.

## Code-Stil

- PHP mit `declare(strict_types=1);`
- Verständliche Bezeichner
- Keine blockierenden `sleep()`-Aufrufe
- Timer für verzögerte Wiederholungen verwenden
- API-Fehler nachvollziehbar diagnostizieren
- Secrets niemals protokollieren

## Lizenz

Mit einem Beitrag erklärst du dich damit einverstanden, dass dein Beitrag unter der MIT-Lizenz dieses Projekts veröffentlicht wird.
