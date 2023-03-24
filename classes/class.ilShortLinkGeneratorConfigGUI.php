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
declare(strict_types=1);

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Implementation\Factory as UIFactory;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * @ilCtrl_IsCalledBy ilShortLinkGeneratorConfigGUI : ilObjComponentSettingsGUI
 * @author Christoph Ludolf
 */
class ilShortLinkGeneratorConfigGUI extends \ilPluginConfigGUI
{
    private ilLanguage $lng;
    private ilCtrl $ilCtrl;
    private ilGlobalPageTemplate $tpl;
    private ilTabsGUI $ilTabs;
    private ilToolbarGUI $ilToolbar;
    private ilShortLinkGeneratorPlugin $shliPlugin;
    private ilShortLinkCollection $shortLinkCollection;
    private GlobalHttpState $http;
    private UIFactory $ui;
    private DefaultRenderer $renderer;
    private RefineryFactory $refinery;
    
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ilCtrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ilTabs = $DIC->tabs();
        $this->ilToolbar = $DIC->toolbar();
        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        
        // Manually initialize the plugin class if the plugin is deactivated.
        if (is_null($this->getPluginObject())) {
            include_once 'class.ilShortLinkGeneratorPlugin.php';
            $this->shliPlugin = new ilShortLinkGeneratorPlugin();
        } else {
            $this->shliPlugin = $this->getPluginObject();
        }
        
        $this->shortLinkCollection = new ilShortLinkDBCollection();
    }

    private function buildTableTabs() : void
    {
        $this->ilTabs->addTab(
            'configure',
            $this->shliPlugin->txt('gui_tab_shortlinks'),
            $this->ilCtrl->getLinkTarget($this, 'configure')
        );
        $this->ilTabs->activateTab('configure');
    }

    private function buildEditorTabs() : void
    {
        $this->ilTabs->clearTargets();
        $this->ilTabs->setBackTarget(
            $this->shliPlugin->txt('gui_tab_back_editor'),
            $this->ilCtrl->getLinkTargetByClass(get_class($this), 'displayShortLinkTablePage')
        );
    }
    
    private function buildShortLinkTableForm() : string
    {
        $buttonAction = $this->ilCtrl->getLinkTargetByClass(get_class($this), 'displayShortLinkBuildPage');
        $button = $this->ui->button()->standard($this->shliPlugin->txt('gui_button_new_shortlink'), $buttonAction);

        $this->ilToolbar->setFormAction($this->ilCtrl->getFormAction($this));
        $this->ilToolbar->addComponent($button);
        
        $table = new ilShortLinkTable($this);
        $table->populateWith($this->shortLinkCollection);
        
        return $table->getMyRender();
    }
    
    private function requestShliid() : int
    {
        return (int) $this->http->request()->getQueryParams()['shliid'];
    }
    
    private function requestShliidArray() : array
    {
        return (array) $this->http->request()->getParsedBody()['shliids'];
    }
    
    private function buildShortLinkInputForm(bool $isEditMode) : StandardForm
    {
        $validShortLink = $this->refinery->custom()->constraint(function ($v) {
            $shortLink = new ilShortLink(-1, $v, '');
            return $shortLink->isShortLinkNameValid();
        }, $this->shliPlugin->txt('gui_error_shortlink_name_invalid'));

        $shortLinkExists = $this->refinery->custom()->constraint(function ($v) {
            $shortLinksWithName = $this->shortLinkCollection->getAllShortLinksWithName($v);
            return $shortLinksWithName->count() === 0;
        }, $this->shliPlugin->txt('gui_error_another_shortlink_with_name_exists'));

        $shortLinkExistsEditMode = $this->refinery->custom()->constraint(function ($v) {
            $shliid = $this->requestShliid();
            $shortLinksWithName = $this->shortLinkCollection->getAllShortLinksWithName($v);
            $shortLink = $this->shortLinkCollection->getShortLinkById($shliid);
            return $shortLinksWithName->count() === 0
                    || $shortLink->sharesIdWith($shortLinksWithName->current());
        }, $this->shliPlugin->txt('gui_error_another_shortlink_with_name_exists'));
        
        $validURL = $this->refinery->custom()->constraint(function ($v) {
            $shortLink = new ilShortLink(-1, '', $v);
            return $shortLink->isURLValid();
        }, $this->shliPlugin->txt('gui_error_shortlink_url_invalid'));

        $outputFormatter = $this->refinery->custom()->transformation(function ($v) {
            list(list($shortLinkName, $targetUrl)) = $v;
            return new ilShortLink(-1, $shortLinkName, $targetUrl);
        });
        
        $outputFormatterEditMode = $this->refinery->custom()->transformation(function ($v) {
            $shliid = $this->requestShliid();
            list(list($shortLinkName, $targetUrl)) = $v;
            return new ilShortLink($shliid, $shortLinkName, $targetUrl);
        });
        
        $shortlinkInput = null;
        $urlInput = null;
        $command = '';
        $sectionTitle = '';
        
        if ($isEditMode) {
            $command = 'updateShortLink';
            $sectionTitle = $this->shliPlugin->txt('gui_title_edit_shortlink_page');
            $shliid = $this->requestShliid();
            $this->ilCtrl->setParameterByClass(get_class($this), 'shliid', $shliid);
            $shortLink = $this->shortLinkCollection->getShortLinkById($shliid);
            
            $shortlinkInput = $this->ui->input()->field()->text(
                'shortlink',
                $this->shliPlugin->txt('gui_txtinputfield_shortlink_info_create')
            )
                    ->withLabel($this->shliPlugin->txt('gui_txtinputfield_shortlink'))
                    ->withAdditionalTransformation($shortLinkExistsEditMode)
                    ->withAdditionalTransformation($validShortLink)
                    ->withValue($shortLink->getName())
                    ->withRequired(true);
            
            $urlInput = $this->ui->input()->field()->text('url')
                    ->withLabel($this->shliPlugin->txt('gui_txtinputfield_url'))
                    ->withAdditionalTransformation($validURL)
                    ->withValue($shortLink->getTargetUrl())
                    ->withRequired(true);
        }
        if (!$isEditMode) {
            $command = 'saveShortLink';
            $sectionTitle = $this->shliPlugin->txt('gui_title_build_shortlink_page');
            $shortlinkInput = $this->ui->input()->field()->text(
                'shortlink',
                $this->shliPlugin->txt('gui_txtinputfield_shortlink_info_create')
            )
                    ->withLabel($this->shliPlugin->txt('gui_txtinputfield_shortlink'))
                    ->withAdditionalTransformation($shortLinkExists)
                    ->withAdditionalTransformation($validShortLink)
                    ->withRequired(true);
            
            $urlInput = $this->ui->input()->field()->text('url')
                    ->withLabel($this->shliPlugin->txt('gui_txtinputfield_url'))
                    ->withAdditionalTransformation($validURL)
                    ->withRequired(true);
        }
        
        $section = $this->ui->input()->field()->section(
            [$shortlinkInput, $urlInput],
            $sectionTitle
        );

        $formAction = $this->ilCtrl->getLinkTargetByClass(get_class($this), $command);
        $form = $this->ui->input()->container()->form()->standard($formAction, [$section]);
        
        if ($isEditMode) {
            $form = $form->withAdditionalTransformation($outputFormatterEditMode);
        } else {
            $form = $form->withAdditionalTransformation($outputFormatter);
        }
        
        return $form;
    }
    
    private function displayShortLinkBuildPage(?MessageBox $msgBox = null) : void
    {
        $this->buildEditorTabs();
        $form = $this->buildShortLinkInputForm(false);
        $formHTML = $this->renderer->render($form);
        $msgBoxHTML = is_null($msgBox) ? '' : $this->renderer->render($msgBox);
        $this->tpl->setContent($msgBoxHTML . $formHTML);
    }
    
    private function displayShortLinkEditPage(?MessageBox $msgBox = null) : void
    {
        $this->buildEditorTabs();
        $form = $this->buildShortLinkInputForm(true);
        $formHTML = $this->renderer->render($form);
        $msgBoxHTML = is_null($msgBox) ? '' : $this->renderer->render($msgBox);
        $this->tpl->setContent($msgBoxHTML . $formHTML);
    }
    
    private function displayShortLinkTablePage(?MessageBox $msgBox = null) : void
    {
        $this->buildTableTabs();
        $tableHTML = $this->buildShortLinkTableForm();
        $msgBoxHTML = is_null($msgBox) ? '' : $this->renderer->render($msgBox);
        $this->tpl->setContent($msgBoxHTML . $tableHTML);
    }

    private function saveShortLink() : void
    {
        $request = $this->http->request();
        $form = $this->buildShortLinkInputForm(false)->withRequest($request);
        $shortLink = $form->getData();
        $msgBoxSuccess = $this->ui->messageBox()->success($this->shliPlugin->txt('gui_message_success_shortlink_saved'));
        $msgBoxFailure = $this->ui->messageBox()->failure($this->shliPlugin->txt('gui_message_failed_shortlink_saved'));

        if (!is_null($shortLink)) {
            $this->shortLinkCollection->createShortLink($shortLink->getName(), $shortLink->getTargetURL());
            $this->displayShortLinkTablePage($msgBoxSuccess);
        } else {
            $this->buildEditorTabs();
            $formHTML = $this->renderer->render([$msgBoxFailure, $form]);
            $this->tpl->setContent($formHTML);
        }
    }

    private function updateShortLink() : void
    {
        $shliid = $this->requestShliid();
        $request = $this->http->request();
        $form = $this->buildShortLinkInputForm(true)->withRequest($request);
        $shortLinkNew = $form->getData();
        $shortLinkOld = $this->shortLinkCollection->getShortLinkById($shliid);
        $msgBoxSuccess = $this->ui->messageBox()->success($this->shliPlugin->txt('gui_message_success_shortlink_saved'));
        $msgBoxFailure = $this->ui->messageBox()->failure($this->shliPlugin->txt('gui_message_failed_shortlink_saved'));
        
        if (is_null($shortLinkNew)) {
            // Input invalid.
            $this->buildEditorTabs();
            $formHTML = $this->renderer->render([$msgBoxFailure, $form]);
            $this->tpl->setContent($formHTML);
            return;
        }
        
        // Prevents datetime update if shortlinks are identical
        if (!($shortLinkOld->sharesNameWith($shortLinkNew) && $shortLinkOld->sharesUrlWith($shortLinkNew))) {
            $this->shortLinkCollection->updateShortLinkByID($shliid, $shortLinkNew->getName(), $shortLinkNew->getTargetUrl());
        }
        
        $this->displayShortLinkTablePage($msgBoxSuccess);
    }

    private function confirmDeleteSelected() : void
    {
        $shortlinkIDs = $this->requestShliidArray();
        $msgBoxFailure = $this->ui->messageBox()->failure($this->shliPlugin->txt('gui_error_select_one'));
        
        if (count($shortlinkIDs) == 0) {
            $this->displayShortLinkTablePage($msgBoxFailure);
            return;
        }
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ilCtrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteSelected');
        $confirm->setCancel($this->lng->txt('cancel'), 'displayShortLinkTablePage');
        $confirm->setHeaderText($this->shliPlugin->txt('gui_message_confirm_delete_multiple'));
        foreach ($shortlinkIDs as $id) {
            $shortLink = $this->shortLinkCollection->getShortLinkById((int) $id);
            $confirm->addItem('shliids[]', '' . $id, $shortLink->getName());
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    private function deleteModalShortLink() : void
    {
        $shliid = $this->requestShliid();
        $msgBoxSuccess = $this->ui->messageBox()->success($this->shliPlugin->txt('gui_message_shortlink_deleted'));
        $msgBoxFailure = $this->ui->messageBox()->failure($this->shliPlugin->txt('gui_error_delete_not_possible'));
        
        if ($this->shortLinkCollection->containsShortLinkWithId($shliid)) {
            $this->shortLinkCollection->removeShortLinkById($shliid);
            $this->displayShortLinkTablePage($msgBoxSuccess);
        } else {
            $this->displayShortLinkTablePage($msgBoxFailure);
        }
    }
    
    private function deleteSelected() : void
    {
        $shortLinkIDs = $this->requestShliidArray();
        $msgBoxSuccess = $this->ui->messageBox()->success($this->shliPlugin->txt('gui_message_shortlink_deleted'));
        $msgBoxFailure = $this->ui->messageBox()->failure($this->shliPlugin->txt('gui_error_delete_not_possible'));
        
        if (count($shortLinkIDs) == 0) {
            $this->displayShortLinkTablePage($msgBoxFailure);
            return;
        }
        foreach ($shortLinkIDs as $shliid) {
            $this->shortLinkCollection->removeShortLinkById((int) $shliid);
        }
        $this->displayShortLinkTablePage($msgBoxSuccess);
    }
    
    private function filter() : void
    {
        $this->displayShortLinkTablePage();
    }

    private function configure() : void
    {
        $this->displayShortLinkTablePage();
    }

    public function performCommand($cmd) : void
    {
        switch ($cmd) {
            case 'displayShortLinkEditPage':
            case 'displayShortLinkBuildPage':
            case 'updateShortLink':
            case 'saveShortLink':
            case 'configure':
            case 'displayShortLinkTablePage':
            case 'confirmDeleteSelected':
            case 'deleteSelected':
            case 'deleteModalShortLink':
            case 'filter':
                $this->$cmd();
                break;
            default:
                throw new Exception('Undefined command: \'' . $cmd . '\'');
        }
    }
}
