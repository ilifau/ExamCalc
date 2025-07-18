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
            case "overview":
            case "addTest":
            case "saveTest":
            case "removeTest":
            case "toggleTest":
                $this->$cmd();
                break;
            default:
                $this->overview();
                break;
        }
    }

    /**
     * Zeigt Übersicht aller Tests mit aktiviertem Taschenrechner
     */
    protected function overview(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();

        $tpl->setTitle("ExamCalc - Übersicht");
        $tpl->setDescription("Verwaltung der Tests mit aktiviertem Taschenrechner");

        $activeTests = $this->plugin->getActiveTests();
        $html = $this->generateOverviewHTML($activeTests);
        $tpl->setContent($html);
    }

    /**
     * Generiert die Übersichts-HTML
     */
    private function generateOverviewHTML(array $tests): string
    {
        global $DIC;
        $ctrl = $DIC->ctrl();

        $rows = '';
        foreach ($tests as $test) {
            $testInfo = $this->getTestInfo($test['ref_id']);
            if (!$testInfo) continue;

            $toggleLink = $ctrl->getLinkTarget($this, "toggleTest") . "&ref_id=" . $test['ref_id'];
            $removeLink = $ctrl->getLinkTarget($this, "removeTest") . "&ref_id=" . $test['ref_id'];

            $rows .= <<<HTML
            <tr>
                <td>{$test['ref_id']}</td>
                <td>
                    <strong><a href="{$testInfo['link']}" target="_blank">{$testInfo['title']}</a></strong>
                    <br><small style="color: #666;">{$testInfo['description']}</small>
                </td>
                <td><small>{$testInfo['path']}</small></td>
                <td><small>{$test['updated_at']}</small></td>
                <td>
                    <span class="label label-success">✅ Aktiv</span>
                </td>
                <td>
                    <a href="$toggleLink" class="btn btn-sm btn-warning" onclick="return confirm('Taschenrechner für diesen Test deaktivieren?')">
                        Deaktivieren
                    </a>
                    <a href="$removeLink" class="btn btn-sm btn-danger" onclick="return confirm('Einstellung für diesen Test löschen?')" style="margin-left: 5px;">
                        Löschen
                    </a>
                </td>
            </tr>
HTML;
        }

        $addTestLink = $ctrl->getLinkTarget($this, "addTest");
        $testCount = count($tests);

        return <<<HTML
<div class="examcalc-admin" style="max-width: 1200px; margin: 1rem auto;">
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">📊 Übersicht - Tests mit Taschenrechner</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>ℹ️ Information:</strong><br>
                        Hier werden alle Tests angezeigt, für die der Taschenrechner aktiviert ist.
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <strong>📈 Statistik:</strong><br>
                        Aktive Tests: <strong>$testCount</strong>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <a href="$addTestLink" class="btn btn-primary">
                    ➕ Test hinzufügen
                </a>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Aktive Tests</h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ref-ID</th>
                        <th>Test</th>
                        <th>Pfad</th>
                        <th>Letzte Änderung</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    $rows
                </tbody>
            </table>
        </div>
    </div>

</div>
HTML;
    }

    /**
     * Zeigt Formular zum Hinzufügen eines Tests
     */
    protected function addTest(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();

        $form_action = $ctrl->getLinkTarget($this, "saveTest");
        $back_link = $ctrl->getLinkTarget($this, "overview");

        $html = <<<HTML
<div style="max-width: 600px; margin: 2rem auto;">
    <h2>Test hinzufügen</h2>
    
    <form method="post" action="$form_action" style="padding: 1.5rem; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="ref_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
                Test Ref-ID:
            </label>
            <input type="number" name="ref_id" id="ref_id" required 
                   style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <small style="color: #666; margin-top: 0.25rem; display: block;">
                Die Ref-ID des Tests, für den der Taschenrechner aktiviert werden soll.
            </small>
        </div>
        
        <div style="text-align: right; margin-top: 1.5rem;">
            <a href="$back_link" class="btn btn-default" style="margin-right: 0.5rem;">Abbrechen</a>
            <button type="submit" class="btn btn-primary">Test hinzufügen</button>
        </div>
    </form>
</div>
HTML;

        $tpl->setContent($html);
    }

    /**
     * Speichert einen neuen Test
     */
    protected function saveTest(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();

        $ref_id = (int) ($_POST['ref_id'] ?? 0);

        if ($ref_id <= 0) {
            $tpl->setOnScreenMessage('failure', 'Ungültige Ref-ID.');
            $ctrl->redirect($this, "addTest");
            return;
        }

        // Prüfe ob Test existiert
        $testInfo = $this->getTestInfo($ref_id);
        if (!$testInfo) {
            $tpl->setOnScreenMessage('failure', 'Test mit Ref-ID ' . $ref_id . ' nicht gefunden.');
            $ctrl->redirect($this, "addTest");
            return;
        }

        // Aktiviere Taschenrechner für diesen Test
        $this->plugin->setCalculatorEnabled($ref_id, true);

        $tpl->setOnScreenMessage('success', 'Taschenrechner für Test "' . $testInfo['title'] . '" aktiviert.');
        $ctrl->redirect($this, "overview");
    }

    /**
     * Entfernt einen Test
     */
    protected function removeTest(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();

        $ref_id = (int) ($_GET['ref_id'] ?? 0);

        if ($ref_id > 0) {
            $this->plugin->setCalculatorEnabled($ref_id, false);
            $tpl->setOnScreenMessage('success', 'Test entfernt.');
        }

        $ctrl->redirect($this, "overview");
    }

    /**
     * Schaltet einen Test um
     */
    protected function toggleTest(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ctrl = $DIC->ctrl();

        $ref_id = (int) ($_GET['ref_id'] ?? 0);

        if ($ref_id > 0) {
            $current = $this->plugin->isCalculatorEnabled($ref_id);
            $this->plugin->setCalculatorEnabled($ref_id, !$current);
            $tpl->setOnScreenMessage('success', $current ? 'Test deaktiviert.' : 'Test aktiviert.');
        }

        $ctrl->redirect($this, "overview");
    }

    /**
     * Lädt Test-Informationen
     */
    private function getTestInfo(int $ref_id): ?array
    {
        try {
            global $DIC;
            $tree = $DIC->repositoryTree();

            if (!$tree->isInTree($ref_id)) {
                return null;
            }

            $obj_id = ilObject::_lookupObjectId($ref_id);
            $type = ilObject::_lookupType($obj_id);

            if ($type !== 'tst') {
                return null;
            }

            $title = ilObject::_lookupTitle($obj_id);
            $description = ilObject::_lookupDescription($obj_id);
            $path = $this->getTestPath($ref_id);

            return [
                'ref_id' => $ref_id,
                'obj_id' => $obj_id,
                'title' => $title,
                'description' => $description,
                'path' => $path,
                'link' => "ilias.php?ref_id=$ref_id&baseClass=ilRepositoryGUI"
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Erstellt den Pfad zum Test
     */
    private function getTestPath(int $ref_id): string
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        try {
            $path_items = $tree->getPathFull($ref_id);
            $path_names = [];

            foreach ($path_items as $item) {
                if ($item['ref_id'] != $ref_id) {
                    $path_names[] = $item['title'];
                }
            }

            return implode(' > ', $path_names);
        } catch (Exception $e) {
            return 'Pfad nicht verfügbar';
        }
    }

    // Behalte die alte Konfiguration als Fallback
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
        $form->setTitle("ExamCalc - Alte Einstellungen (Fallback)");
        $form->setFormAction($ctrl->getFormAction($this));

        $saved_global = $this->plugin->getConfig()->get("global_enable");
        $saved_refids = $this->plugin->getConfig()->get("refid_list");

        $cb = new ilCheckboxInputGUI("Global aktivieren?", "global_enable");
        $cb->setInfo("Rechner wird in allen Tests angezeigt.");
        $cb->setChecked($saved_global === "1");
        $form->addItem($cb);

        $ti = new ilTextInputGUI("Ref-IDs (Kommagetrennt)", "refid_list");
        $ti->setInfo("Nur in diesen Kursen anzeigen (z. B. 1204,2409). Gilt nur wenn global deaktiviert ist.");
        $ti->setValue($saved_refids);
        $form->addItem($ti);

        $form->addCommandButton("save", "Speichern");
        return $form;
    }
}