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
    
    /**
     * WICHTIG: Keine eigene Tabellenerstellung mehr!
     * ExamExtendedSettings erstellt die examcalc_settings Tabelle
     */
    protected function afterActivation(): void
    {
        // Nichts tun - ExamExtendedSettings verwaltet die Tabellen
        // Alte Tabelle löschen falls sie existiert
        global $DIC;
        $db = $DIC->database();
        
        // Alte standalone examcalc_test_settings löschen falls vorhanden
        if ($db->tableExists('examcalc_test_settings')) {
            $db->dropTable('examcalc_test_settings');
        }
    }
    
    protected function beforeUninstall(): bool
    {
        // Tabelle wird von ExamExtendedSettings verwaltet - nicht hier löschen
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
}