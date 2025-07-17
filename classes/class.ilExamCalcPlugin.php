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

    // Sicherstellen, dass wir im Testkontext sind
    $cmd_class = strtolower($_GET["cmdClass"] ?? "");
    $cmd       = strtolower($_GET["cmd"] ?? "");

    // Nur im Testplayer und nur bei showQuestion aktivieren
    if (!str_contains($cmd_class, "iltestplayer") || $cmd !== "showquestion") {
        return;
    }

    // ✅ Nur dann einbinden
    $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
}



}
