<?php

declare(strict_types=1);

class Mammotion extends IPSModule
{
    private const AUTH_URL = 'https://id.mammotion.com/oauth2/token';
    private const API_URL = 'https://api-open.mammotion.com';
    private const PROFILE_TASKS = 'MAMMO.Tasks';
    private const PROFILE_CONTROL = 'MAMMO.Control';
    private const PROFILE_OPERATION = 'MAMMO.OperationStatus';
    private const PROFILE_SYSTEM = 'MAMMO.SystemState';
    private const TOKEN_SAFETY_SECONDS = 300;

    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyString('ClientID', '');
        $this->RegisterPropertyString('ClientSecret', '');
        $this->RegisterPropertyString('DeviceID', '');
        $this->RegisterPropertyInteger('PollInterval', 60);
        $this->RegisterPropertyBoolean('EnableConnection', true);
        $this->RegisterPropertyBoolean('EnableControl', false);

        $this->RegisterAttributeString('AccessToken', '');
        $this->RegisterAttributeString('RefreshToken', '');
        $this->RegisterAttributeInteger('TokenValidUntil', 0);
        $this->RegisterAttributeString('CredentialHash', '');
        $this->RegisterAttributeString('ResolvedDeviceID', '');
        $this->RegisterAttributeString('TaskMap', '{}');
        $this->RegisterAttributeBoolean('RefreshRunning', false);
        $this->RegisterAttributeInteger('RetryAttempt', 0);
        $this->RegisterAttributeBoolean('RetryStartCheck', false);

        $this->RegisterTimer('UpdateTimer', 0, 'MAMMO_Refresh($_IPS["TARGET"]);');
        $this->RegisterTimer('StartupTimer', 0, 'MAMMO_StartCheck($_IPS["TARGET"]);');
        $this->RegisterTimer('RetryTimer', 0, 'MAMMO_RetryRefresh($_IPS["TARGET"]);');

        $this->EnsureProfiles();
        $this->RegisterVariableBoolean('Online', 'Online', '~Switch', 10);
        $this->RegisterVariableInteger('OperationStatus', 'Betriebsstatus', self::PROFILE_OPERATION, 20);
        $this->RegisterVariableString('Status', 'Status (Rohwert)', '', 21);
        $this->RegisterVariableInteger('Battery', 'Akku', '~Battery.100', 30);
        $this->RegisterVariableString('Firmware', 'Firmware', '', 40);
        $this->RegisterVariableInteger('ChargeStatus', 'Ladestatus (Code)', '', 50);
        $this->RegisterVariableInteger('WifiRSSI', 'WLAN RSSI', '', 60);
        $this->RegisterVariableString('WifiIP', 'WLAN IP', '', 70);
        $this->RegisterVariableInteger('CellularRSSI', 'Mobilfunk RSSI', '', 80);
        $this->RegisterVariableInteger('KnifeHeight', 'Mähhöhe', '', 90);
        $this->RegisterVariableInteger('Speed', 'Geschwindigkeit (Code)', '', 100);
        $this->RegisterVariableInteger('SystemState', 'Systemzustand', self::PROFILE_SYSTEM, 105);
        $this->RegisterVariableInteger('LastCheck', 'Letzte Startprüfung', '~UnixTimestamp', 107);
        $this->RegisterVariableString('APIStatus', 'API-Status', '', 108);
        $this->RegisterVariableString('LastResult', 'Letztes API-Ergebnis', '', 110);
        $this->RegisterVariableString('TokenStatus', 'Tokenstatus', '', 112);
        $this->RegisterVariableInteger('TokenValidTo', 'Token gültig bis', '~UnixTimestamp', 1121);
        $this->RegisterVariableString('Diagnostic', 'Diagnose', '', 113);
        $this->RegisterVariableInteger('LastSuccess', 'Letzte erfolgreiche Aktualisierung', '~UnixTimestamp', 115);
        $this->RegisterVariableInteger('LastAttempt', 'Letzter API-Versuch', '~UnixTimestamp', 116);
        $this->RegisterVariableInteger('Control', 'Steuerung', self::PROFILE_CONTROL, 120);
        $this->EnableAction('Control');
        $this->RegisterVariableInteger('Task', 'Aufgabe starten', self::PROFILE_TASKS, 130);
        $this->EnableAction('Task');
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->EnsureProfiles();
        $this->SetTimerInterval('StartupTimer', 0);
        $this->SetTimerInterval('RetryTimer', 0);
        $this->SetValue('SystemState', 0);
        if (!$this->ReadPropertyBoolean('EnableConnection')) {
            $this->SetTimerInterval('UpdateTimer', 0);
            $this->SetTimerInterval('StartupTimer', 0);
            $this->SetTimerInterval('RetryTimer', 0);
            $this->WriteAttributeBoolean('RefreshRunning', false);
            $this->SetValue('SystemState', 6);
            $this->SetValue('APIStatus', 'Verbindung manuell deaktiviert');
            $this->SetStatus(104);
            return;
        }
        if (!$this->ValidateConfiguration()) {
            $this->SetTimerInterval('UpdateTimer', 0);
            return;
        }
        $hash = $this->BuildCredentialHash();
        if ($hash !== $this->ReadAttributeString('CredentialHash')) {
            $this->ClearTokenCache();
            $this->WriteAttributeString('ResolvedDeviceID', '');
            $this->WriteAttributeString('CredentialHash', $hash);
            $this->SetValue('APIStatus', 'Credentials geändert');
        }
        $this->SetTimerInterval('UpdateTimer', max(30, $this->ReadPropertyInteger('PollInterval')) * 1000);
        $this->SetStatus(102);
        $this->SetTimerInterval('StartupTimer', 2000);
    }

    public function RequestAction($Ident, $Value): void
    {
        $this->RequireConnectionEnabled();
        $this->RequireControlEnabled();
        if ($Ident === 'Task') {
            $map = json_decode($this->ReadAttributeString('TaskMap'), true) ?: [];
            $key = (string) (int) $Value;
            if (!isset($map[$key])) throw new RuntimeException('Aufgabe unbekannt. Bitte Startprüfung ausführen.');
            $this->StartTask((string) $map[$key]['name']);
            $this->SetValue('Task', (int) $Value);
            return;
        }
        if ($Ident === 'Control') {
            $actions = [1 => 'PAUSE', 2 => 'RESUME', 3 => 'STOP', 4 => 'RETURN', 5 => 'CANCEL_RETURN'];
            $v = (int) $Value;
            if (!isset($actions[$v])) throw new RuntimeException('Unbekannter Steuerbefehl.');
            $this->ExecuteAction($actions[$v]);
            $this->SetValue('Control', $v);
            return;
        }
        throw new InvalidArgumentException('Unbekannte Aktion: ' . $Ident);
    }

    public function StartCheck(): string
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) return 'DEAKTIVIERT: Verbindung ist ausgeschaltet.';
        $this->SetTimerInterval('StartupTimer', 0);
        $this->SetValue('SystemState', 1);
        $this->SetValue('LastCheck', time());
        $this->WriteAttributeInteger('RetryAttempt', 0);
        $this->WriteAttributeBoolean('RetryStartCheck', true);
        $ok = $this->RunRefresh(true);
        return $this->BuildVisibleResult($ok, true);
    }

    public function Refresh(): bool
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) return false;
        $this->WriteAttributeInteger('RetryAttempt', 0);
        $this->WriteAttributeBoolean('RetryStartCheck', false);
        return $this->RunRefresh(false);
    }

    public function RefreshWithResult(): string
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) return 'DEAKTIVIERT: Verbindung ist ausgeschaltet.';
        return $this->BuildVisibleResult($this->Refresh(), false);
    }

    public function RetryRefresh(): bool
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) { $this->SetTimerInterval('RetryTimer', 0); return false; }
        $this->SetTimerInterval('RetryTimer', 0);
        return $this->RunRefresh($this->ReadAttributeBoolean('RetryStartCheck'));
    }

    public function RenewToken(): bool
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) return false;
        try {
            $this->ClearTokenCache();
            $this->GetAccessToken(true);
            $this->SetValue('APIStatus', 'Authentifizierung OK');
            $this->SetValue('LastResult', 'Token erfolgreich erneuert: ' . date('d.m.Y H:i:s'));
            $this->SetStatus(102);
            return true;
        } catch (Throwable $e) {
            $this->RegisterFailure('Authentifizierung fehlgeschlagen', $e->getMessage(), false);
            return false;
        }
    }

    public function RenewTokenWithResult(): string
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) return 'DEAKTIVIERT: Verbindung ist ausgeschaltet.';
        $ok = $this->RenewToken();
        return ($ok ? 'ERFOLG: ' : 'FEHLER: ') . $this->GetValue('LastResult');
    }

    public function StartTask(string $taskName): bool { $this->RequireControlEnabled(); return $this->SendCommand('START', ['taskName' => $taskName]); }
    public function Pause(): bool { return $this->ExecuteAction('PAUSE'); }
    public function Resume(): bool { return $this->ExecuteAction('RESUME'); }
    public function Stop(): bool { return $this->ExecuteAction('STOP'); }
    public function ReturnToDock(): bool { return $this->ExecuteAction('RETURN'); }
    public function CancelReturn(): bool { return $this->ExecuteAction('CANCEL_RETURN'); }

    public function ExecuteAction(string $action): bool
    {
        $this->RequireControlEnabled();
        if (!in_array($action, ['PAUSE', 'RESUME', 'STOP', 'RETURN', 'CANCEL_RETURN'], true)) throw new InvalidArgumentException('Nicht erlaubter Befehl.');
        return $this->SendCommand($action);
    }

    private function RunRefresh(bool $startCheck): bool
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) return false;
        if ($this->ReadAttributeBoolean('RefreshRunning')) {
            $this->SetValue('Diagnostic', 'Übersprungen: Ein Abruf läuft bereits');
            return false;
        }
        if (!$this->ValidateConfiguration()) {
            return false;
        }
        $this->WriteAttributeBoolean('RefreshRunning', true);
        $this->SetValue('LastAttempt', time());
        $steps = [];
        try {
            $this->SetValue('APIStatus', $startCheck ? 'Startprüfung läuft' : 'Aktualisierung läuft');
            $this->GetAccessToken();
            $steps[] = 'Token OK';
            $list = $this->DiagnosticApiRequest('DeviceList', 'GET', '/v1/mowers');
            $steps[] = 'Geräteliste OK';
            $device = $this->SelectDevice(is_array($list['data'] ?? null) ? $list['data'] : []);
            if ($device === null) throw new RuntimeException('Konfigurierte Device-ID wurde nicht gefunden.');
            $id = (string) ($device['id'] ?? '');
            $this->WriteAttributeString('ResolvedDeviceID', $id);
            $steps[] = 'Device-ID OK';
            if (((int) ($device['online'] ?? 0)) !== 1) {
                $this->SetOfflineState('Geräteliste meldet Mäher offline');
                $steps[] = 'Mäher offline';
                $this->CompleteSuccess($steps, 'OK, Gerät offline', $startCheck, 4);
                return true;
            }
            $steps[] = 'Mäher online';
            $detail = $this->DiagnosticApiRequest('DeviceInfo', 'GET', '/v1/mower/' . rawurlencode($id));
            $this->ApplyDeviceDetails($detail['data'] ?? []);
            $steps[] = 'DeviceInfo OK';
            $partial = [];
            try {
                $params = $this->DiagnosticApiRequest('WorkParams', 'GET', '/v1/mower/' . rawurlencode($id) . '/work-params');
                $p = $params['data'] ?? [];
                $this->SetValue('KnifeHeight', (int) ($p['knifeHeight'] ?? 0));
                $this->SetValue('Speed', (int) ($p['speed'] ?? 0));
                $steps[] = 'WorkParams OK';
            } catch (Throwable $e) { $partial[] = 'WorkParams: ' . $e->getMessage(); $steps[] = 'WorkParams FEHLER'; }
            try {
                $plans = $this->DiagnosticApiRequest('Plan', 'GET', '/v1/mower/' . rawurlencode($id) . '/plan');
                $this->UpdateTasks(is_array($plans['data'] ?? null) ? $plans['data'] : []);
                $steps[] = 'Plan OK';
            } catch (Throwable $e) { $partial[] = 'Plan: ' . $e->getMessage(); $steps[] = 'Plan FEHLER'; }
            if (count($partial) > 0) {
                $text = implode(' | ', $steps) . ' | ' . implode(' | ', $partial);
                $this->SetValue('APIStatus', 'Teilweise verfügbar');
                $this->SetValue('Diagnostic', $text);
                $this->SetValue('LastResult', 'Basisdaten aktualisiert; Zusatzabruf fehlgeschlagen');
                $this->SetValue('LastSuccess', time());
                if ($startCheck) {
                    $this->SetValue('SystemState', 3);
                    $this->SetValue('LastCheck', time());
                }
                $this->SetStatus(102);
                $this->ResetRetryState();
                return true;
            }
            $this->CompleteSuccess($steps, 'OK', $startCheck, 2);
            return true;
        } catch (Throwable $e) {
            $message = $e->getMessage();
            if ($this->IsDeviceOfflineMessage($message)) {
                $this->SetOfflineState($message);
                $this->CompleteSuccess(array_merge($steps, ['Mäher offline']), 'OK, Gerät offline', $startCheck, 4);
                return true;
            }
            if ($this->ShouldRetry($message) && $this->ScheduleRetry($startCheck, $message)) return false;
            $this->RegisterFailure($this->ClassifyFailure($message), $message, true);
            if ($startCheck) {
                $this->SetValue('SystemState', 5);
                $this->SetValue('LastCheck', time());
            }
            return false;
        } finally {
            $this->WriteAttributeBoolean('RefreshRunning', false);
        }
    }

    private function CompleteSuccess(array $steps, string $apiStatus, bool $startCheck, int $systemState): void
    {
        $text = implode(' | ', $steps);
        $this->SetValue('APIStatus', $apiStatus);
        $this->SetValue('Diagnostic', $text);
        $this->SetValue('LastResult', 'Aktualisierung erfolgreich: ' . date('d.m.Y H:i:s'));
        $this->SetValue('LastSuccess', time());
        if ($startCheck) {
            $this->SetValue('SystemState', $systemState);
            $this->SetValue('LastCheck', time());
        }
        $this->SetStatus(102);
        $this->ResetRetryState();
    }

    private function BuildVisibleResult(bool $ok, bool $startCheck): string
    {
        if ($startCheck) return ($ok ? 'ERFOLG: ' : 'FEHLER: ') . (string) $this->GetValue('Diagnostic');
        return ($ok ? 'ERFOLG: ' : 'FEHLER: ') . (string) $this->GetValue('LastResult') . ' | ' . (string) $this->GetValue('Diagnostic');
    }

    private function ValidateConfiguration(): bool
    {
        if (trim($this->ReadPropertyString('ClientID')) === '' || trim($this->ReadPropertyString('ClientSecret')) === '') {
            $this->SetValue('SystemState', 5); $this->SetValue('APIStatus', 'Konfiguration unvollständig'); $this->SetValue('Diagnostic', 'Client-ID oder Client-Secret fehlt'); $this->SetStatus(200); return false;
        }
        if ($this->ReadPropertyInteger('PollInterval') < 30) {
            $this->SetValue('SystemState', 5); $this->SetValue('APIStatus', 'Konfiguration unvollständig'); $this->SetValue('Diagnostic', 'Abfrageintervall kleiner 30 Sekunden'); $this->SetStatus(200); return false;
        }
        return true;
    }

    private function BuildCredentialHash(): string { return hash('sha256', $this->ReadPropertyString('ClientID') . '|' . $this->ReadPropertyString('ClientSecret')); }

    private function SelectDevice(array $devices): ?array
    {
        $configured = trim($this->ReadPropertyString('DeviceID'));
        if ($configured === '' && count($devices) > 0) return $devices[0];
        foreach ($devices as $device) if ((string) ($device['id'] ?? '') === $configured) return $device;
        return null;
    }

    private function ApplyDeviceDetails(array $data): void
    {
        $network = is_array($data['network'] ?? null) ? $data['network'] : [];
        $online = ((int) ($data['online'] ?? 0)) === 1;
        $raw = (string) ($data['status'] ?? 'Unbekannt');
        $this->SetValue('Online', $online); $this->SetValue('Status', $raw); $this->SetValue('OperationStatus', $this->MapOperationStatus($raw, $online));
        $this->SetValue('Battery', (int) ($data['batteryLevel'] ?? 0)); $this->SetValue('Firmware', (string) ($data['version'] ?? ''));
        $this->SetValue('ChargeStatus', (int) ($data['chargeStatus'] ?? 0)); $this->SetValue('WifiRSSI', (int) ($network['wifiRssi'] ?? 0));
        $this->SetValue('WifiIP', (string) ($network['wifiIp'] ?? '')); $this->SetValue('CellularRSSI', (int) ($network['cellularRssi'] ?? 0));
    }

    private function SetOfflineState(string $reason): void
    {
        $this->SetValue('Online', false); $this->SetValue('Status', 'Offline'); $this->SetValue('OperationStatus', 0);
        $this->SetValue('Diagnostic', $reason); $this->SetValue('LastResult', 'Mäher ist ausgeschaltet oder nicht erreichbar');
    }

    private function RegisterFailure(string $apiStatus, string $message, bool $cloudError): void
    {
        $this->SetValue('APIStatus', $apiStatus); $this->SetValue('Diagnostic', $message); $this->SetValue('LastResult', 'Fehler: ' . $message);
        if ($cloudError) $this->SetValue('OperationStatus', 7);
        $this->SetValue('SystemState', 5); $this->SetStatus(201); $this->ResetRetryState();
    }

    private function ScheduleRetry(bool $startCheck, string $message): bool
    {
        $attempt = $this->ReadAttributeInteger('RetryAttempt'); if ($attempt >= 2) return false;
        $attempt++; $delay = $attempt === 1 ? 5 : 15;
        $this->WriteAttributeInteger('RetryAttempt', $attempt); $this->WriteAttributeBoolean('RetryStartCheck', $startCheck);
        $this->SetValue('APIStatus', 'Wiederholung ' . $attempt . '/2'); $this->SetValue('Diagnostic', 'Wiederholung in ' . $delay . ' s: ' . $message);
        if ($startCheck) {
            $this->SetValue('SystemState', 1);
        }
        $this->SetTimerInterval('RetryTimer', $delay * 1000); return true;
    }

    private function ResetRetryState(): void { $this->SetTimerInterval('RetryTimer', 0); $this->WriteAttributeInteger('RetryAttempt', 0); $this->WriteAttributeBoolean('RetryStartCheck', false); }

    private function ShouldRetry(string $message): bool
    {
        $m = mb_strtolower($message); foreach (['401', 'client_id', 'client secret', 'device-id wurde', 'nicht gefunden', 'konfiguration'] as $x) if (strpos($m, $x) !== false) return false; return true;
    }

    private function ClassifyFailure(string $message): string
    {
        $m = mb_strtolower($message); if (strpos($m, 'token') !== false || strpos($m, '401') !== false || strpos($m, 'auth') !== false) return 'Authentifizierung fehlgeschlagen';
        if (strpos($m, 'device-id') !== false || strpos($m, 'nicht gefunden') !== false) return 'Gerät nicht gefunden'; return 'Cloud vorübergehend nicht erreichbar';
    }

    private function SendCommand(string $action, ?array $params = null): bool
    {
        $id = trim($this->ReadPropertyString('DeviceID')); if ($id === '') $id = $this->ReadAttributeString('ResolvedDeviceID');
        if ($id === '') throw new RuntimeException('Keine Device-ID verfügbar.');
        $payload = ['deviceId' => $id, 'action' => $action]; if ($params !== null) $payload['params'] = $params;
        $r = $this->ApiRequest('POST', '/v1/mower/action', $payload); $ok = (bool) ($r['data']['commandResult'] ?? (($r['code'] ?? -1) === 0));
        $this->SetValue('LastResult', ($ok ? $action . ' erfolgreich' : $action . ' fehlgeschlagen')); return $ok;
    }

    private function RequireControlEnabled(): void { if (!$this->ReadPropertyBoolean('EnableControl')) throw new RuntimeException('Schreibbefehle sind nicht freigegeben.'); }

    private function RequireConnectionEnabled(): void
    {
        if (!$this->ReadPropertyBoolean('EnableConnection')) throw new RuntimeException('Verbindung ist manuell deaktiviert.');
    }

    private function UpdateTokenDisplay(int $validUntil): void
    {
        $this->SetValue('TokenStatus', $validUntil > time() ? 'Gültig' : 'Abgelaufen');
        $this->SetValue('TokenValidTo', $validUntil);
    }

    private function GetAccessToken(bool $force = false): string
    {
        $token = $this->ReadAttributeString('AccessToken'); $until = $this->ReadAttributeInteger('TokenValidUntil');
        if (!$force && $token !== '' && time() < ($until - self::TOKEN_SAFETY_SECONDS)) { $this->UpdateTokenDisplay($until); return $token; }
        $refresh = $this->ReadAttributeString('RefreshToken');
        if (!$force && $refresh !== '') { try { return $this->RequestToken('refresh_token', $refresh); } catch (Throwable $e) { $this->SendDebug('TokenRefresh', 'Fallback auf Client-Credentials', 0); } }
        return $this->RequestToken('client_credentials');
    }

    private function RequestToken(string $grantType, string $refresh = ''): string
    {
        $fields = ['client_id'=>$this->ReadPropertyString('ClientID'),'client_secret'=>$this->ReadPropertyString('ClientSecret'),'grant_type'=>$grantType]; if ($grantType === 'refresh_token') $fields['refresh_token']=$refresh;
        $http=$this->HttpRequest(self::AUTH_URL,'POST',['Content-Type: application/x-www-form-urlencoded','Accept: application/json'],http_build_query($fields));
        $json=json_decode($http['body'],true); $payload=is_array($json['data']??null)?$json['data']:$json;
        if ($http['status']<200||$http['status']>=300||!is_array($payload)||empty($payload['access_token'])) throw new RuntimeException('Token konnte nicht abgerufen werden: '.$this->SafeApiMessage($json));
        $token=(string)$payload['access_token']; $expires=max(300,(int)($payload['expires_in']??3600));
        $validUntil = time() + $expires;
        $this->WriteAttributeString('AccessToken',$token); $this->WriteAttributeString('RefreshToken',(string)($payload['refresh_token']??$refresh)); $this->WriteAttributeInteger('TokenValidUntil',$validUntil);
        $this->UpdateTokenDisplay($validUntil); return $token;
    }

    private function ClearTokenCache(): void { $this->WriteAttributeString('AccessToken',''); $this->WriteAttributeString('RefreshToken',''); $this->WriteAttributeInteger('TokenValidUntil',0); $this->SetValue('TokenStatus','Token wird erneuert'); $this->SetValue('TokenValidTo',0); }

    private function DiagnosticApiRequest(string $step,string $method,string $path,?array $payload=null): array
    {
        $this->SetValue('Diagnostic',$step.' START'); $started=microtime(true);
        try { $r=$this->ApiRequest($method,$path,$payload); $m=$step.' OK ('.(int)round((microtime(true)-$started)*1000).' ms)'; $this->SetValue('Diagnostic',$m); $this->SendDebug('Diagnostic',$m,0); return $r; }
        catch(Throwable $e){$m=$step.' FEHLER: '.$e->getMessage();$this->SetValue('Diagnostic',$m);$this->SendDebug('Diagnostic',$m,0);throw new RuntimeException($m,0,$e);}
    }

    private function ApiRequest(string $method,string $path,?array $payload=null,bool $retry=true): array
    {
        $this->RequireConnectionEnabled();
        $headers=['Authorization: Bearer '.$this->GetAccessToken(),'Accept: application/json','Accept-Language: de-DE'];$body=null;
        if($payload!==null){$headers[]='Content-Type: application/json';$body=json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);}
        $http=$this->HttpRequest(self::API_URL.$path,$method,$headers,$body);$json=json_decode($http['body'],true);$code=is_array($json)?($json['code']??null):null;
        if($retry&&($http['status']===401||$code===401)){$this->ClearTokenCache();$this->GetAccessToken(true);return $this->ApiRequest($method,$path,$payload,false);}
        if($http['status']<200||$http['status']>=300)throw new RuntimeException('HTTP '.$http['status'].': '.substr($http['body'],0,300));
        if(!is_array($json))throw new RuntimeException('Ungültige JSON-Antwort.');if(($json['code']??-1)!==0)throw new RuntimeException('Mammotion-API: '.$this->SafeApiMessage($json));return $json;
    }

    private function HttpRequest(string $url,string $method,array $headers,?string $body): array
    {
        $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30]);if($body!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
        $response=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);if($response===false)throw new RuntimeException('HTTP-Verbindungsfehler: '.$error);return['status'=>$status,'body'=>(string)$response];
    }

    private function EnsureProfiles(): void
    {
        if(!IPS_VariableProfileExists(self::PROFILE_OPERATION))IPS_CreateVariableProfile(self::PROFILE_OPERATION,VARIABLETYPE_INTEGER);
        foreach([[0,'Offline',0x808080],[1,'Bereit',0x00AA00],[2,'Mäht',0x00CC66],[3,'Pausiert',0xFFCC00],[4,'Lädt',0x3399FF],[5,'Heimfahrt',0x6699FF],[6,'Gerätefehler',0xFF0000],[7,'API/Cloud-Fehler',0xFF8800],[8,'Unbekannt',0xAAAAAA]]as$a)IPS_SetVariableProfileAssociation(self::PROFILE_OPERATION,$a[0],$a[1],'',$a[2]);
        if(!IPS_VariableProfileExists(self::PROFILE_SYSTEM))IPS_CreateVariableProfile(self::PROFILE_SYSTEM,VARIABLETYPE_INTEGER);
        foreach([[0,'Initialisierung',0xAAAAAA],[1,'Prüfung läuft',0x3399FF],[2,'Betriebsbereit',0x00AA00],[3,'Teilweise verfügbar',0xFFCC00],[4,'Offline',0x808080],[5,'Fehler',0xFF0000],[6,'Deaktiviert',0x777777]]as$a)IPS_SetVariableProfileAssociation(self::PROFILE_SYSTEM,$a[0],$a[1],'',$a[2]);
        if(!IPS_VariableProfileExists(self::PROFILE_CONTROL))IPS_CreateVariableProfile(self::PROFILE_CONTROL,VARIABLETYPE_INTEGER);
        foreach([[1,'Pause'],[2,'Fortsetzen'],[3,'Stop'],[4,'Zur Ladestation'],[5,'Heimfahrt abbrechen']]as$a)IPS_SetVariableProfileAssociation(self::PROFILE_CONTROL,$a[0],$a[1],'',-1);
        if(!IPS_VariableProfileExists(self::PROFILE_TASKS))IPS_CreateVariableProfile(self::PROFILE_TASKS,VARIABLETYPE_INTEGER);
    }

    private function MapOperationStatus(string $raw,bool $online): int
    {
        if(!$online)return 0;$s=mb_strtolower(trim($raw));if($s==='')return 8;if(strpos($s,'standby')!==false||strpos($s,'idle')!==false)return 1;
        if(strpos($s,'mow')!==false||strpos($s,'work')!==false)return 2;if(strpos($s,'pause')!==false)return 3;if(strpos($s,'charg')!==false)return 4;
        if(strpos($s,'return')!==false||strpos($s,'dock')!==false)return 5;if(strpos($s,'error')!==false||strpos($s,'fault')!==false)return 6;return 8;
    }

    private function UpdateTasks(array $tasks): void
    {
        foreach(IPS_GetVariableProfile(self::PROFILE_TASKS)['Associations']as$a)IPS_SetVariableProfileAssociation(self::PROFILE_TASKS,(int)$a['Value'],'','',-1);
        $map=[];$i=1;foreach($tasks as$t){$name=trim((string)($t['taskName']??''));if($name==='')continue;$map[(string)$i]=['id'=>(string)($t['taskId']??''),'name'=>$name];IPS_SetVariableProfileAssociation(self::PROFILE_TASKS,$i,$name,'',-1);$i++;}
        $this->WriteAttributeString('TaskMap',json_encode($map,JSON_UNESCAPED_UNICODE));
    }

    private function IsDeviceOfflineMessage(string $message): bool
    {
        $m=mb_strtolower($message);foreach(['gerät antwortet nicht','geraet antwortet nicht','device does not respond','device not responding','device is offline']as$p)if(strpos($m,$p)!==false)return true;return false;
    }

    private function SafeApiMessage($json): string
    {
        if(!is_array($json))return'unbekannte Antwort';return(string)($json['msg']??$json['error_description']??$json['error']??'unbekannter API-Fehler');
    }
}
