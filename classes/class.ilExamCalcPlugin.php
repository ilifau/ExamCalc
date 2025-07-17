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
    $config = $this->getConfig();
    $enabled_global = $config->get("global_enable") === "1";
    $allowed_refids = array_filter(array_map('trim', explode(",", $config->get("refid_list", ""))));

    // Hole aktuelle ref_id, wenn im Testkontext
    $current_ref_id = (int) ($_GET["ref_id"] ?? 0);
    $is_allowed = in_array($current_ref_id, $allowed_refids);

    if (!$enabled_global && !$is_allowed) {
        error_log(" ExamCalc deaktiviert für ref_id=$current_ref_id");
        return;
    }

    // Dann einfügen:
    try {
        $GLOBALS['tpl']->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
        error_log(" examcalc.js eingebunden");
    } catch (Throwable $e) {
        error_log(" Fehler beim Einfügen von JS: " . $e->getMessage());
    }
}


}
