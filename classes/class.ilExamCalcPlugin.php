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

public function modifyGUI(string $a_comp, string $a_part, array $a_par = []): void
{
    global $DIC;

    if (!$DIC->offsetExists('tpl') || !$DIC['tpl'] instanceof ilGlobalTemplateInterface) {
        return;
    }

    $tpl = $DIC['tpl'];

    $cmd_class     = strtolower($_GET["cmdClass"] ?? "");
    $cmd           = strtolower($_GET["cmd"] ?? "");
    $fallback_cmd  = strtolower($_GET["fallbackCmd"] ?? "");

    $is_testplayer = str_contains($cmd_class, "iltestplayer");
    $is_question_view = in_array("showquestion", [$cmd, $fallback_cmd]);

    if (!$is_testplayer || !$is_question_view) {
        error_log(" ExamCalc NICHT geladen – cmdClass=$cmd_class | cmd=$cmd | fallbackCmd=$fallback_cmd");
        return;
    }

    error_log(" ExamCalc geladen – cmdClass=$cmd_class | cmd=$cmd | fallbackCmd=$fallback_cmd");
    $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
}

}
