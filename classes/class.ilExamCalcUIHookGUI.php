<?php

class ilExamCalcUIHookGUI extends ilUIHookPluginGUI
{
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
        error_log("✅ ExamCalcUIHookGUI aufgerufen");

        $plugin = $this->getPluginObject();
        if (method_exists($plugin, "modifyGUI")) {
            $plugin->modifyGUI($a_comp, $a_part, $a_par);
        }
    }
}
