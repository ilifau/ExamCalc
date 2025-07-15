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
        global $tpl;

        if (!$tpl instanceof ilTemplate) {
            return;
        }

        $cmd_class = strtolower($_GET["cmdClass"] ?? "");
        $current_ref_id = (int) ($_GET["ref_id"] ?? 0);

        $global = $this->getConfig()->get("global_enable") === "1";
        $ref_ids = array_filter(array_map("trim", explode(",", $this->getConfig()->get("refid_list") ?? "")));

        if (!$global && !in_array($current_ref_id, $ref_ids)) {
            return;
        }

        if (strpos($cmd_class, "iltestplayer") === false) {
            return;
        }

        $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
    }
}
