<?php

class ilExamCalcUIHookGUI extends ilUIHookPluginGUI
{
    /**
     * Modify GUI - wird für jeden Seitenaufruf aufgerufen
     */
    public function modifyGUI(string $component, string $part, array $params = []): void
    {
        global $DIC;

        // Basis-Checks
        if (!$DIC->offsetExists('tpl') || !$DIC['tpl'] instanceof ilGlobalTemplateInterface) {
            return;
        }

        $tpl = $DIC['tpl'];
        $current_ref_id = (int) ($_GET["ref_id"] ?? 0);
        
        // Früh raus wenn keine ref_id
        if ($current_ref_id <= 0) {
            return;
        }

        // Debug-Logging hinzufügen
        $tpl->addOnLoadCode("
            if (typeof window.ecLog === 'undefined') {
                window.ecLog = function(msg) { 
                    console.log('[ExamCalc] ' + msg); 
                };
            }
        ");
        
        $tpl->addOnLoadCode("ecLog('modifyGUI ausgeführt für ref_id: $current_ref_id');");

        // Prüfe Testplayer-Kontext (mehrere Möglichkeiten)
        $cmd_class = strtolower($_GET["cmdClass"] ?? "");
        $is_test_context = $this->isTestPlayerContext($cmd_class);
        
        // Konvertiere PHP boolean zu JavaScript boolean
        $js_test_context = $is_test_context ? 'true' : 'false';
        
        $tpl->addOnLoadCode("ecLog('cmdClass: $cmd_class, isTestContext: ' + $js_test_context);");

        if (!$is_test_context) {
            $tpl->addOnLoadCode("ecLog('❌ Nicht im Testplayer-Kontext – abbrechen');");
            return;
        }

        // Prüfe ob ExamCalc aktiviert ist
        if ($this->isExamCalcEnabled($current_ref_id)) {
            $tpl->addOnLoadCode("ecLog('✅ ExamCalc aktiviert! Lade JavaScript...');");
            
            // JavaScript laden
            $js_path = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js";
            $tpl->addJavaScript($js_path);
            
            $tpl->addOnLoadCode("ecLog('✅ examcalc.js geladen von: $js_path');");
        } else {
            $tpl->addOnLoadCode("ecLog('❌ ExamCalc nicht aktiviert für diesen Test');");
        }
    }

    /**
     * Prüft ob wir uns im Testplayer-Kontext befinden
     */
    private function isTestPlayerContext(string $cmd_class): bool
    {
        // Verschiedene mögliche Testplayer-Klassen in ILIAS
        $test_player_classes = [
            'iltestplayer',
            'iltestplayergui', 
            'iltestplayercommandsgui',
            'iltestoutputgui'
        ];

        foreach ($test_player_classes as $test_class) {
            if (str_contains($cmd_class, $test_class)) {
                return true;
            }
        }

        // Zusätzlich: Prüfe URL-Pattern
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($request_uri, 'cmd=startPlayer') || 
            str_contains($request_uri, 'cmd=resumePlayer') ||
            str_contains($request_uri, 'cmdClass=ilTestPlayer')) {
            return true;
        }

        return false;
    }

    /**
     * Prüft ob ExamCalc für den Test aktiviert ist
     */
    private function isExamCalcEnabled(int $ref_id): bool
    {
        global $DIC;
        $db = $DIC->database();

        try {
            // ref_id → test_id (obj_id) konvertieren
            $test_id = ilObject::_lookupObjectId($ref_id);
            
            if ($test_id <= 0) {
                return false;
            }

            // Prüfe neue Tabelle (ExamExtendedSettings)
            $query = "SELECT enabled FROM examcalc_settings WHERE test_id = %s";
            $result = $db->queryF($query, ['integer'], [$test_id]);

            if ($row = $db->fetchAssoc($result)) {
                $enabled = (bool) $row['enabled'];
                error_log("[ExamCalc] Neue Tabelle - test_id: $test_id, enabled: " . ($enabled ? 'true' : 'false'));
                return $enabled;
            }

            /* Fallback: Alte Settings (für Backwards-Compatibility)
            $setting = new ilSetting("examcalc");
            $old_enabled = $setting->get("enabled_" . $ref_id);
            $enabled = $old_enabled === "1";
            
            error_log("[ExamCalc] Alte Settings - ref_id: $ref_id, enabled: " . ($enabled ? 'true' : 'false'));
            return $enabled;
            */

        } catch (Exception $e) {
            error_log("[ExamCalc] Fehler beim Prüfen der Aktivierung: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Legacy-Methoden falls nötig
     */
    public function getTestIdFromContext(): ?int
    {
        $ref_id = (int) ($_GET["ref_id"] ?? 0);
        if ($ref_id > 0) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            if (ilObject::_lookupType($obj_id) === "tst") {
                return $obj_id;
            }
        }
        
        $test_id = (int) ($_GET["test_id"] ?? $_POST["test_id"] ?? 0);
        if ($test_id > 0) {
            return $test_id;
        }
        
        return null;
    }
}