<?php

// class.ilExamCalcPlugin.php - ERWEITERT
class ilExamCalcPlugin extends ilUserInterfaceHookPlugin
{
    protected ?ilExamCalcConfig $config = null;

    public function getPluginName(): string
    {
        return "ExamCalc";
    }

    public function hasConfiguration(): bool
    {
        return true;
    }

    public function getUIHookClass(): string
    {
        return ilExamCalcUIHookGUI::class;
    }

    public function getConfig(): ilExamCalcConfig
    {
        if (!$this->config) {
            require_once __DIR__ . "/class.ilExamCalcConfig.php";
            $this->config = new ilExamCalcConfig("examcalc");
        }
        return $this->config;
    }

    /**
     * Wird beim Aktivieren des Plugins ausgeführt
     */
    protected function afterActivation(): void
    {
        $this->createTables();
    }

    /**
     * Wird beim Deinstallieren ausgeführt
     */
    protected function beforeUninstall(): bool
    {
        $this->dropTables();
        return true;
    }

    /**
     * Erstellt die Plugin-Tabellen
     */
    private function createTables(): void
    {
        global $DIC;
        $db = $DIC->database();

        // Tabelle für Test-spezifische Einstellungen
        if (!$db->tableExists('examcalc_settings')) {
            $fields = [
                'ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'enabled' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ],
                'created_at' => [
                    'type' => 'timestamp',
                    'notnull' => true
                ],
                'updated_at' => [
                    'type' => 'timestamp',
                    'notnull' => true
                ]
            ];

            $db->createTable('examcalc_settings', $fields);
            $db->addPrimaryKey('examcalc_settings', ['ref_id']);
        }
    }

    /**
     * Löscht die Plugin-Tabellen
     */
    private function dropTables(): void
    {
        global $DIC;
        $db = $DIC->database();

        if ($db->tableExists('examcalc_settings')) {
            $db->dropTable('examcalc_settings');
        }
    }

    /**
     * Prüft ob der Taschenrechner für einen Test aktiviert ist
     */
    public function isCalculatorEnabled(int $ref_id): bool
    {
        global $DIC;
        $db = $DIC->database();

        $query = "SELECT enabled FROM examcalc_settings WHERE ref_id = %s";
        $result = $db->queryF($query, ['integer'], [$ref_id]);

        if ($row = $db->fetchAssoc($result)) {
            return (bool) $row['enabled'];
        }

        return false;
    }

    /**
     * Aktiviert/Deaktiviert den Taschenrechner für einen Test
     */
    public function setCalculatorEnabled(int $ref_id, bool $enabled): void
    {
        global $DIC;
        $db = $DIC->database();

        $db->replace('examcalc_settings', [
            'ref_id' => ['integer', $ref_id],
            'enabled' => ['integer', $enabled ? 1 : 0],
            'updated_at' => ['timestamp', date('Y-m-d H:i:s')]
        ], [
            'ref_id' => ['integer', $ref_id]
        ]);
    }

    /**
     * Lädt alle Tests mit aktiviertem Taschenrechner
     */
    public function getActiveTests(): array
    {
        global $DIC;
        $db = $DIC->database();

        $query = "SELECT ref_id, enabled, created_at, updated_at FROM examcalc_settings WHERE enabled = 1 ORDER BY updated_at DESC";
        $result = $db->query($query);

        $tests = [];
        while ($row = $db->fetchAssoc($result)) {
            $tests[] = [
                'ref_id' => (int) $row['ref_id'],
                'enabled' => (bool) $row['enabled'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }

        return $tests;
    }

// In ExamCalc Plugin erweitern
public function modifyGUI(string $a_comp, string $a_part, array $a_par = []): void
{
    global $DIC;
    if (!$DIC->offsetExists('tpl') || !$DIC['tpl'] instanceof ilGlobalTemplateInterface) {
        return;
    }

    $tpl = $DIC['tpl'];
    $cmd_class = strtolower($_GET["cmdClass"] ?? "");
    $current_ref_id = (int) ($_GET["ref_id"] ?? 0);

    // 1. NEUE TABELLE prüfen (ExamExtendedSettings)
    if ($this->isEnabledInNewTable($current_ref_id)) {
        $this->loadCalculator($tpl);
        return;
    }

    // 2. FALLBACK: Alte Konfiguration (settings)
    $global = $this->getConfig()->get("global_enable") === "1";
    $ref_ids = array_filter(array_map("trim", explode(",", $this->getConfig()->get("refid_list") ?? "")));

    if (!$global && !in_array($current_ref_id, $ref_ids)) {
        return;
    }

    if (!str_contains($cmd_class, "iltestplayer")) {
        return;
    }

    $this->loadCalculator($tpl);
}

private function isEnabledInNewTable(int $ref_id): bool
{
    global $DIC;
    $db = $DIC->database();

    $obj_id = ilObject::_lookupObjectId($ref_id);
    
    $query = "SELECT enabled FROM examcalc_settings WHERE test_id = %s";
    $result = $db->queryF($query, ['integer'], [$obj_id]);

    if ($row = $db->fetchAssoc($result)) {
        return (bool) $row['enabled'];
    }

    return false;
}

private function loadCalculator(ilGlobalTemplateInterface $tpl): void
{
    $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
}
}