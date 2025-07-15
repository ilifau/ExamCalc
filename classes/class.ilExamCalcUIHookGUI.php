<?php

class ilExamCalcUIHookGUI extends ilUIHookPluginGUI
{
    public function modifyGUI(string $a_comp, string $a_part, array $a_par = []): void
    {
        $plugin = $this->getPluginObject();
        if (method_exists($plugin, "modifyGUI")) {
            $plugin->modifyGUI($a_comp, $a_part, $a_par);
        }
    }
}
