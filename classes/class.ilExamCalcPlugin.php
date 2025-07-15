<?php

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
        $cmd_class = strtolower($_GET["cmdClass"] ?? "");
        $current_ref_id = (int) ($_GET["ref_id"] ?? 0);

        $global = $this->getConfig()->get("global_enable") === "1";
        $ref_ids = array_filter(array_map("trim", explode(",", $this->getConfig()->get("refid_list") ?? "")));

        if (!$global && !in_array($current_ref_id, $ref_ids)) {
            return;
        }

        if (!str_contains($cmd_class, "iltestplayer")) {
            return;
        }

        $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
    }
}
