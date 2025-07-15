<?php
/**
 * @ilCtrl_isCalledBy ilExamCalcConfigGUI: ilObjComponentSettingsGUI
 */

class ilExamCalcConfigGUI extends ilPluginConfigGUI
{
    protected ilExamCalcPlugin $plugin;

    public function performCommand(string $cmd): void
    {
        $this->plugin = $this->getPluginObject();
        global $DIC;
        $ctrl = $DIC->ctrl();
        $tpl = $DIC->ui()->mainTemplate();

        switch ($cmd) {
            case "configure":
            case "save":
                $this->$cmd();
                break;
            default:
                $this->configure();
                break;
        }
    }

    protected function configure(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    protected function save(): void
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $tpl = $DIC->ui()->mainTemplate();

        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->plugin->getConfig()->set("global_enable", $form->getInput("global_enable") ? "1" : "");
            $this->plugin->getConfig()->set("refid_list", trim($form->getInput("refid_list") ?? ""));
            $ctrl->redirect($this, "configure");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    protected function initForm(): ilPropertyFormGUI
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $form = new ilPropertyFormGUI();
        $form->setTitle("ExamCalc - Einstellungen");
        $form->setFormAction($ctrl->getFormAction($this));

        $saved_global = $this->plugin->getConfig()->get("global_enable");
        $saved_refids = $this->plugin->getConfig()->get("refid_list");

        $cb = new ilCheckboxInputGUI("Global aktivieren?", "global_enable");
        $cb->setInfo("Rechner wird in allen Tests angezeigt.");
        $cb->setChecked($saved_global === "1");
        $form->addItem($cb);

        $ti = new ilTextInputGUI("Ref-IDs (Kommagetrennt)", "refid_list");
        $ti->setInfo("Nur in diesen Kursen anzeigen (z. B. 1204,2409). Gilt nur wenn global deaktiviert ist.");
        $ti->setValue($saved_refids);
        $form->addItem($ti);

        $form->addCommandButton("save", "Speichern");
        return $form;
    }
}
