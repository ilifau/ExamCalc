<?php
/**
 * @ilCtrl_isCalledBy ilExamCalcConfigGUI: ilObjComponentSettingsGUI
 * @ilCtrl_Calls ilExamCalcConfigGUI: ilInfoScreenGUI
 */

class ilExamCalcConfigGUI extends ilPluginConfigGUI
{
    protected ilExamCalcPlugin $plugin;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;

    public function __construct()

    {
        //parent::__construct(); 
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
    }

    public function performCommand(string $cmd): void
    {
        $this->plugin = $this->getPluginObject();

        switch ($cmd) {
            case "configure":
            case "showInfo":
            default:
                $this->showInfo();
                break;
        }
    }

    protected function showInfo(): void
    {
        $info = new ilInfoScreenGUI($this);
        $info->addSection($this->plugin->getPluginName() . " - " . $this->plugin->txt("configuration"));
        $info->addProperty($this->lng->txt("version"), $this->plugin->getVersion());
        $info->addProperty($this->lng->txt("status"), $this->plugin->txt("config_status_info"));
        $info->addSection($this->plugin->txt("usage_instructions"));
        $info->addProperty("", $this->plugin->txt("usage_instructions_text"));

        $stats = $this->getUsageStatistics();
        if ($stats['total'] > 0) {
            $info->addSection($this->plugin->txt("statistics"));
            $info->addProperty($this->plugin->txt("tests_with_calculator"), $stats['enabled']);
            $info->addProperty($this->plugin->txt("total_tests"), $stats['total']);
        }

        $this->tpl->setContent($info->getHTML());
    }

    protected function getUsageStatistics(): array
    {
        global $DIC;
        $db = $DIC->database();

        $result = $db->query("SELECT COUNT(*) as cnt FROM examcalc_test_settings WHERE enabled = 1");
        $row = $db->fetchAssoc($result);
        $enabled = (int) $row['cnt'];


        return [
            'enabled' => $enabled
        ];
    }

    protected function initForm(int $ref_id): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle("Taschenrechner-Konfiguration");

        $checkbox = new ilCheckboxInputGUI("Rechner aktivieren", "calc_enabled");
        $checkbox->setInfo("Aktiviert den wissenschaftlichen Rechner für diesen Test.");
        $checkbox->setValue("1");
        $form->addItem($checkbox);

        $hidden = new ilHiddenInputGUI("ref_id");
        $hidden->setValue($ref_id);
        $form->addItem($hidden);

        // Fester URL-Fallback, da kein PluginController
        $form->setFormAction("ilias.php?baseClass=iluipluginroutergui&cmd=saveExamCalc&cmdClass=ilExamSymbolsAdvancedGUI&ref_id=$ref_id");
        $form->addCommandButton("saveExamCalc", "Speichern");

        return $form;
    }

    protected function loadCalcEnabled(int $ref_id): bool
    {
        $setting = new ilSetting("examcalc");
        return $setting->get("enabled_" . $ref_id) === "1";
    }

public function getEmbeddedInputs(int $ref_id): string
{
    $enabled = $this->loadCalcEnabled($ref_id);
    $checked = $enabled ? 'checked' : '';

    return <<<HTML
<div class="form-group">
    <input type="checkbox" id="calc_enabled" name="calc_enabled" value="1" $checked>
    <label for="calc_enabled">Rechner aktivieren</label>
</div>
HTML;
}



}
