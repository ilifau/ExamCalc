<?php

class ilExamCalcPlugin extends ilUserInterfaceHookPlugin
{
    protected ?ilExamCalcConfig $config = null;
    
    public function getPluginName(): string
    {
        return "ExamCalc";
    }

    public function getUIHookClass(): string
{
    return ilExamCalcUIHookGUI::class;
}

    
    public function hasConfiguration(): bool
    {
        return true;
    }
    
    protected function afterActivation(): void
    {
        // Datenbanktabelle erstellen
        global $DIC;
        $db = $DIC->database();
        
        if (!$db->tableExists('examcalc_test_settings')) {
            $fields = [
                'test_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'enabled' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            ];
            
            $db->createTable('examcalc_test_settings', $fields);
            $db->addPrimaryKey('examcalc_test_settings', ['test_id']);
        }
    }
    
    protected function beforeUninstall(): bool
    {
        global $DIC;
        $db = $DIC->database();
        
        if ($db->tableExists('examcalc_test_settings')) {
            $db->dropTable('examcalc_test_settings');
        }
        
        return true;
    }
    
    public function getConfig(): ilExamCalcConfig
    {
        if (!$this->config) {
            require_once __DIR__ . "/class.ilExamCalcConfig.php";
            $this->config = new ilExamCalcConfig("examcalc");
        }
        return $this->config;
    }
    
    public function modifyGUI(string $a_comp, string $a_part, array $a_par = []): void
{
    global $DIC;

    if (!$DIC->offsetExists('tpl') || !$DIC['tpl'] instanceof ilGlobalTemplateInterface) {
        return;
    }

    $tpl = $DIC['tpl'];
    $cmd_class = strtolower($_GET["cmdClass"] ?? "-");
    $current_ref_id = (int) ($_GET["ref_id"] ?? 0);

    $tpl->addOnLoadCode("console.log('[ExamCalc] modifyGUI ausgeführt');");

    // Debug-Overlay auf der Seite
    $tpl->addOnLoadCode("
        const ec_debug = document.createElement('div');
        ec_debug.id = 'examcalc-debug';
        ec_debug.style = 'position:fixed;bottom:10px;left:10px;background:#111;color:#0f0;padding:10px;font-size:12px;z-index:99999;font-family:monospace;border:1px solid lime;';
        ec_debug.innerText = '[ExamCalc Debug]\\nLade...';
        document.body.appendChild(ec_debug);

        function ecLog(msg) {
            ec_debug.innerText += '\\n' + msg;
            console.log('[ExamCalc]', msg);
        }

        ecLog('Component: " . addslashes($a_comp) . "');
        ecLog('Part: " . addslashes($a_part) . "');
        ecLog('cmdClass: " . addslashes($cmd_class) . "');
        ecLog('ref_id: " . $current_ref_id . "');
    ");

    // Testkontext prüfen
    if (!str_contains($cmd_class, "iltestplayer")) {
        $tpl->addOnLoadCode("ecLog('❌ Nicht im Testplayer-Kontext – abbrechen.');");
        return;
    }

    // Aktiviere Einstellung prüfen
    $setting = new ilSetting("examcalc");
    $enabled = $setting->get("enabled_" . $current_ref_id);

    $tpl->addOnLoadCode("ecLog('Setting enabled_$current_ref_id = " . addslashes($enabled) . "');");

    if ($enabled !== "1") {
        $tpl->addOnLoadCode("ecLog('❌ Einstellung nicht aktiviert für diesen Test');");
        return;
    }

    // Alles erfüllt → JS laden
    $tpl->addOnLoadCode("ecLog('✅ Aktiviert! Lade examcalc.js');");

    $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
}

    
    /**
     * Ermittelt die Test-ID aus dem Kontext
     */
    protected function getTestIdFromContext(): ?int
    {
        global $DIC;
        
        // Versuche über ref_id
        $ref_id = (int) ($_GET["ref_id"] ?? 0);
        if ($ref_id > 0) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            if (ilObject::_lookupType($obj_id) === "tst") {
                return $obj_id;
            }
        }
        
        // Versuche über test_id Parameter (im Testplayer)
        $test_id = (int) ($_GET["test_id"] ?? $_POST["test_id"] ?? 0);
        if ($test_id > 0) {
            return $test_id;
        }
        
        // Versuche über test_ref_id (andere ILIAS Versionen)
        $test_ref_id = (int) ($_GET["test_ref_id"] ?? $_POST["test_ref_id"] ?? 0);
        if ($test_ref_id > 0) {
            return ilObject::_lookupObjectId($test_ref_id);
        }
        
        return null;
    }
    
    /**
     * Prüft ob der Rechner für einen Test aktiviert ist
     */
    public function isCalculatorEnabledForTest(int $test_id): bool
    {
        global $DIC;
        $db = $DIC->database();
        
        $query = "SELECT enabled FROM examcalc_test_settings WHERE test_id = %s";
        $result = $db->queryF($query, ['integer'], [$test_id]);
        
        if ($row = $db->fetchAssoc($result)) {
            return (bool) $row['enabled'];
        }
        
        return false;
    }
}