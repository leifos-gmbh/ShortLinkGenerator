<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 *
 * @author Christoph Ludolf
 */
class ilShortLinkGeneratorConfigGUI extends ilPluginConfigGUI
{
    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var ilCtrl
     */
    private $ilCtrl;

    /**
     * @var ilTemplate
     */
    private $tpl;

    /**
     *
     * @var ilTabs
     */
    private $ilTabs;

    /**
     *
     * @var ilToolbar
     */
    private $ilToolbar;

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     *
     * @var ilShortlinkGeneratorPlugin
     */
    private $shliPlugin;

    /**
     *
     * @var ilShortLinkCollection
     */
    private $shortLinkCollection;


    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ilCtrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ilTabs = $DIC->tabs();
        $this->ilToolbar = $DIC->toolbar();
        $this->logger = ilLoggerFactory::getLogger('shli');
        $this->shliPlugin = $this->getPluginObject();

        // Manually initialize the plugin class if the plugin is deactivated.
        if (is_null($this->shliPlugin)) {
            include_once 'class.ilShortLinkGeneratorPlugin.php';
            $this->shliPlugin = new ilShortLinkGeneratorPlugin();
        }
        $this->shortLinkCollection = new ilShortLinkDBCollection();
    }

    private function buildTabs() : void
    {
        $this->ilTabs->addTab(
            'configure',
            $this->shliPlugin->txt('gui_tab_shortlinks'),
            $this->ilCtrl->getLinkTarget($this, 'configure')
        );
        $this->ilTabs->activateTab('configure');
    }

    private function buildShortLinkTableForm() : void
    {
        // Toolbar
        $button = ilSubmitButton::getInstance();
        $button->setCaption($this->shliPlugin->txt('gui_button_new_shortlink'), false);
        $button->setCommand('displayShortLinkBuildPage');

        $this->ilToolbar->setFormAction($this->ilCtrl->getFormAction($this));
        $this->ilToolbar->addButtonInstance($button);
    }

    private function buildShortLinkInputForm(bool $isEditMode) : ilPropertyFormGUI
    {
        // Pass on shortlink id
        if ($isEditMode) {
            $id = (int) $_GET['shliid'];
            $this->ilCtrl->setParameterByClass(get_class($this), 'shliid', $id);
        }

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ilCtrl->getFormAction($this));
        $form->setTitle($this->shliPlugin->txt('gui_title_build_shortlink_page'));

        $txtInputShortLink = new ilTextInputGUI($this->shliPlugin->txt('gui_txtinputfield_shortlink'), 'shortlink');
        $txtInputShortLink->setRequired(true);
        $txtInputShortLink->setMaxLength(40);
        $form->addItem($txtInputShortLink);

        $txtInputTargetUrl = new ilTextInputGUI($this->shliPlugin->txt('gui_txtinputfield_url'), 'targeturl');
        $txtInputTargetUrl->setRequired(true);
        $txtInputTargetUrl->setMaxLength(300);
        $form->addItem($txtInputTargetUrl);

        // Edit button
        if ($isEditMode) {
            $form->addCommandButton('saveEditedShortLink', $this->shliPlugin->txt('gui_button_save_changes'));
        }
        // Save Button
        if (!$isEditMode) {
            $form->addCommandButton('saveShortLink', $this->shliPlugin->txt('gui_button_save'));
        }
        $form->addCommandButton('displayShortLinkTablePage', $this->lng->txt('cancel'));

        // Clear shortlink id parameter
        if ($isEditMode) {
            $this->ilCtrl->clearParameterByClass(get_class($this), 'shliid');
        }

        return $form;
    }

    private function populateTable(ilShortLinkTable $table) : void
    {
        $patternName = '/' . $table->getFilterShortLinkValue() . '/';
        $patternUrl = '/' . $table->getFilterUrlValue() . '/';
        $patternName = strcmp($patternName, '//') == 0 ? '/.*/' : $patternName;
        $patternUrl = strcmp($patternUrl, '//') == 0 ? '/.*/' : $patternUrl;
        $shortlinks = $this->shortLinkCollection->getShortLinksByPattern($patternName, $patternUrl);
        $table->populateWith($shortlinks);
    }

    private function displayShortLinkBuildPage() : void
    {
        $form = $this->buildShortLinkInputForm(false);
        $this->tpl->setContent($form->getHTML());
    }

    private function displayShortLinkTablePage() : void
    {
        $this->buildShortLinkTableForm();
        $table = new ilShortLinkTable($this);
        $this->populateTable($table);
        $table->addHtmlTo($this->tpl);
    }

    private function displayShortLinkEditPage() : void
    {
        $id = (int) $_GET['shliid'];
        $form = $this->buildShortLinkInputForm(true);

        // Fill form
        $shortLink = $this->shortLinkCollection->getShortLinkById($id);
        $txtInputShortLink = $form->getItemByPostVar('shortlink');
        $txtInputShortLink->setValue($shortLink->getName());
        $txtInputTargetUrl = $form->getItemByPostVar('targeturl');
        $txtInputTargetUrl->setValue($shortLink->getTargetUrl());

        $this->tpl->setContent($form->getHTML());
    }

    private function saveShortLink() : void
    {
        $form = $this->buildShortLinkInputForm(false);

        if ($this->checkUserInput($form)) { // Input validation succeeded
            // Add new shortlink
            $slName = $form->getInput('shortlink');
            $slUrl = $form->getInput('targeturl');
            $this->shortLinkCollection->createShortLink($slName, $slUrl);

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ilCtrl->redirect($this, 'displayShortLinkTablePage');
        } else { // Input validation failed
            // Restore Input
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());

            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
        }
    }

    private function saveEditedShortLink() : void
    {
        $form = $this->buildShortLinkInputForm(true);

        if ($this->checkUserInputEditShortLink($form)) { // Input validation succeeded
            // Update shortlink
            $id = (int) $_GET['shliid'];
            $slName = $form->getInput('shortlink');
            $slUrl = $form->getInput('targeturl');
            $replacement = new ilShortLink($id, $slName, $slUrl);
            $this->shortLinkCollection->updateShortLink($replacement);

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ilCtrl->redirect($this, 'displayShortLinkTablePage');
        } else { // Input validation failed
            // Restore Input
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());

            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
        }
    }

    private function checkUserInput(ilPropertyFormGUI $form) : bool
    {
        $inputValid = true;

        if (!$form->checkInput()) {
            $inputValid = false;
        }

        $slName = $form->getInput('shortlink');
        $slUrl = $form->getInput('targeturl');
        $shortLink = new ilShortLink(-1, $slName, $slUrl);
        $txtInputShortLink = $form->getItemByPostVar('shortlink');
        $txtInputTargetUrl = $form->getItemByPostVar('targeturl');

        if (!$shortLink->isShortLinkNameValid()) {
            $txtInputShortLink->setAlert($txtInputShortLink->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_shortlink_name_invalid'));
            $inputValid = false;
        }

        if (!$shortLink->isURLValid()) {
            $txtInputTargetUrl->setAlert($txtInputTargetUrl->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_shortlink_url_invalid'));
            $inputValid = false;
        }

        if ($this->shortLinkCollection->containsShortLinkWithName($slName)) {
            $txtInputShortLink->setAlert($txtInputShortLink->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_another_shortlink_with_name_exists'));
            $inputValid = false;
        }

        if ($this->shortLinkCollection->containsShortLinkWithUrl($slUrl)) {
            $txtInputTargetUrl->setAlert($txtInputTargetUrl->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_another_shortlink_with_url_exists'));
            $inputValid = false;
        }

        return $inputValid;
    }

    private function checkUserInputEditShortLink(ilPropertyFormGUI $form) : bool
    {
        $inputValid = true;

        if (!$form->checkInput()) {
            $inputValid = false;
        }

        $id = (int) $_GET['shliid'];
        $slName = $form->getInput('shortlink');
        $slUrl = $form->getInput('targeturl');
        $shortLinkUpdated = new ilShortLink($id, $slName, $slUrl);
        $shortLinkWithName = $this->shortLinkCollection->getShortLinkByName($slName);
        $shortLinkWithUrl = $this->shortLinkCollection->getShortLinkByUrl($slUrl);
        $txtInputShortLink = $form->getItemByPostVar('shortlink');
        $txtInputTargetUrl = $form->getItemByPostVar('targeturl');

        if (!$shortLinkUpdated->isShortLinkNameValid()) {
            $txtInputShortLink->setAlert($txtInputShortLink->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_shortlink_name_invalid'));
            $inputValid = false;
        }

        if (!$shortLinkUpdated->isURLValid()) {
            $txtInputTargetUrl->setAlert($txtInputTargetUrl->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_shortlink_url_invalid'));
            $inputValid = false;
        }

        if (!is_null($shortLinkWithName) && !$shortLinkWithName->sharesIdWith($shortLinkUpdated)) {
            $txtInputShortLink->setAlert($txtInputShortLink->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_another_shortlink_with_name_exists'));
            $inputValid = false;
        }

        if (!is_null($shortLinkWithUrl) && !$shortLinkWithUrl->sharesIdWith($shortLinkUpdated)) {
            $txtInputTargetUrl->setAlert($txtInputTargetUrl->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_another_shortlink_with_url_exists'));
            $inputValid = false;
        }

        if (!is_null($shortLinkWithName) && !is_null($shortLinkWithUrl)
                && $shortLinkWithUrl->sharesIdWith($shortLinkUpdated)
                && $shortLinkWithName->sharesIdWith($shortLinkUpdated)) {
            $txtInputShortLink->setAlert($txtInputShortLink->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_no_changes_made_in_editor'));
            $txtInputTargetUrl->setAlert($txtInputTargetUrl->getAlert()
                    . '<br>'
                    . $this->shliPlugin->txt('gui_error_no_changes_made_in_editor'));
            $inputValid = false;
        }

        return $inputValid;
    }

    private function confirmDeleteShortLink() : void
    {
        $id = (int) $_GET['shliid'];
        $shortLink = $this->shortLinkCollection->getShortLinkById($id);

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ilCtrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteSelected');
        $confirm->setCancel($this->lng->txt('cancel'), 'displayShortLinkTablePage');
        $confirm->addItem('shliids[]', $shortLink->getId(), $shortLink->getName());

        ilUtil::sendQuestion($this->shliPlugin->txt('gui_message_confirm_delete'));
        $this->tpl->setContent($confirm->getHTML());
    }

    private function confirmDeleteSelected() : void
    {
        if (!$_REQUEST['shliids']) {
            ilUtil::sendFailure($this->shliPlugin->txt('gui_error_select_one'), true);
            $this->ilCtrl->redirect($this, 'displayShortLinkTablePage');
            return;
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ilCtrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteSelected');
        $confirm->setCancel($this->lng->txt('cancel'), 'displayShortLinkTablePage');

        foreach ((array) $_REQUEST['shliids'] as $id) {
            $shortLink = $this->shortLinkCollection->getShortLinkById((int) $id);
            $confirm->addItem('shliids[]', $id, $shortLink->getName());
        }

        ilUtil::sendQuestion($this->shliPlugin->txt('gui_message_confirm_delete_multiple'));
        $this->tpl->setContent($confirm->getHTML());
    }

    private function deleteSelected() : void
    {
        if (!$_POST['shliids']) {
            ilUtil::sendFailure($this->shliPlugin->txt('gui_error_delete_not_possible'), true);
            $this->ilCtrl->redirect($this, 'displayShortLinkTablePage');
            return;
        }

        foreach ((array) $_POST['shliids'] as $shliid) {
            $this->shortLinkCollection->removeShortLinkById($shliid);
        }

        ilUtil::sendSuccess($this->shliPlugin->txt('gui_message_shortlink_deleted'), true);
        $this->ilCtrl->redirect($this, 'displayShortLinkTablePage');
    }

    private function applyFilter() : void
    {
        $this->buildShortLinkTableForm();
        $table = new ilShortLinkTable($this);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->populateTable($table);
        $table->addHtmlTo($this->tpl);
    }

    private function resetFilter() : void
    {
        $this->buildShortLinkTableForm();
        $table = new ilShortLinkTable($this);
        $table->resetFilter();
        $table->resetOffset();
        $this->populateTable($table);
        $table->addHtmlTo($this->tpl);
    }

    private function configure() : void
    {
        $this->displayShortLinkTablePage();
    }

    public function performCommand($cmd) : void
    {
        $this->buildTabs();
        switch ($cmd) {
            case 'displayShortLinkEditPage':
            case 'displayShortLinkBuildPage':
            case 'saveEditedShortLink':
            case 'saveShortLink':
            case 'configure':
            case 'displayShortLinkTablePage':
            case 'confirmDeleteShortLink':
            case 'confirmDeleteSelected':
            case 'deleteSelected':
            case 'applyFilter':
            case 'resetFilter':
                $this->$cmd();
                break;
            default:
                throw new Exception('Undefined command: \'' . $cmd . '\'');
        }
    }
}
