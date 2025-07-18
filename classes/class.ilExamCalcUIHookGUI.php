<?php

class ilExamCalcUIHookGUI extends ilUIHookPluginGUI
{
public function modifyGUI(string $a_comp, string $a_part, array $a_par = []): void
{
    global $DIC;
    if (!$DIC->offsetExists('tpl')) {
        return;
    }
    $tpl = $DIC['tpl'];

    // Nur für Module Test
    if ($a_comp !== "Modules/Test") {
        return;
    }

    $cmd_class = strtolower($_GET["cmdClass"] ?? "-");
    $ref_id = (int) ($_GET["ref_id"] ?? 0);

    // Debug sichtbar anzeigen
    $tpl->addOnLoadCode("
        if (!document.getElementById('examcalc-debug-box')) {
            const box = document.createElement('div');
            box.id = 'examcalc-debug-box';
            box.style = 'position:fixed;bottom:20px;left:20px;padding:10px;background:#111;color:#0f0;font-family:monospace;font-size:12px;z-index:99999;border:1px solid lime;';
            box.innerText = '[ExamCalc Debug]\\nComponent: " . addslashes($a_comp) . "\\nPart: " . addslashes($a_part) . "\\ncmdClass: " . addslashes($cmd_class) . "\\nref_id: " . $ref_id . "';
            document.body.appendChild(box);
        }
    ");

    $setting = new ilSetting("examcalc");
    if ($setting->get("enabled_" . $ref_id) !== "1") {
        $tpl->addOnLoadCode("document.getElementById('examcalc-debug-box').innerText += '\\n❌ Rechner nicht aktiviert';");
        return;
    }

    // Nur einmal JS einbinden!
    static $already_loaded = false;
    if (!$already_loaded) {
        $already_loaded = true;
        $tpl->addOnLoadCode("document.getElementById('examcalc-debug-box').innerText += '\\n✅ Rechner wird geladen';");
        $tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/examcalc.js");
    }
}

}
