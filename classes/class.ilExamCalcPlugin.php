<?php

class ilExamCalcPlugin extends ilUserInterfaceHookPlugin
{
    protected $config = null;

    public function getPluginName()
    {
        return "ExamCalc";
    }

    public function hasConfiguration()
    {
        return true;
    }

    public function getConfig()
    {
        if (!$this->config) {
            require_once __DIR__ . "/class.ilExamCalcConfig.php";
            $this->config = new ilExamCalcConfig("examcalc");
        }
        return $this->config;
    }

public function modifyGUI($a_comp, $a_part, $a_par = array())
{
    error_log("📦 ilExamCalcPlugin::modifyGUI() ausgeführt");

    if (!isset($GLOBALS['tpl']) || !is_object($GLOBALS['tpl'])) {
        error_log("❌ \$GLOBALS['tpl'] nicht gesetzt oder kein Objekt");
        return;
    }

    // ILIAS 7 verwendet ilGlobalPageTemplate → safe addJavaScript nutzen
    try {
        $GLOBALS['tpl']->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
        error_log("✅ examcalc.js eingebunden über GLOBAL tpl");
    } catch (Throwable $e) {
        error_log("❌ Fehler beim Einfügen von JS: " . $e->getMessage());
    }
}


}
