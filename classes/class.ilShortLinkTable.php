<?php
declare(strict_types=1);
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

use \ILIAS\UI\Implementation\Factory;
use \ILIAS\UI\Implementation\DefaultRenderer;
use \ILIAS\UI\Component\Input\Container\Filter\Standard;
use \ILIAS\HTTP\GlobalHttpState;

/**
 *
 * @author Christoph Ludolf
 */
class ilShortLinkTable extends ilTable2GUI
{
    private ilCtrl $ilCtrl;
    private ilShortLinkGeneratorPlugin $shliPlugin;
    private Factory $ui;
    private DefaultRenderer $renderer;
    private ilUIService $uiService;
    private Standard $filter;
    private GlobalHttpState $http;
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        $this->setId('shli'); // bevor constructor
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        global $DIC;
        $this->ilCtrl = $DIC->ctrl();
        $this->ui = $DIC->ui()->factory();
        $this->uiService = $DIC->uiService();
        $this->renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        
        $this->shliPlugin = ilShortLinkGeneratorPlugin::getInstance();

        $this->addColumn('', 'checkboxes', '1px');
        $this->addColumn($this->shliPlugin->txt('table_col_title'), 'title', '20%');
        $this->addColumn($this->shliPlugin->txt('table_col_targeturl'), 'url', '70%');
        $this->addColumn('', 'action', '10%');

        $this->addMultiCommand('confirmDeleteSelected', $this->lng->txt('delete'));
        $this->setSelectAllCheckbox('shliids');

        $this->setRowTemplate("tpl.summary_row.html", $this->shliPlugin->getDirectory());
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('desc');

        $this->buildFilter();

        $this->setFormAction($this->ilCtrl->getFormAction($this->getParentObject()));
    }

    private function buildFilter() : void
    {
        // Define input fields
        $shortlink_input = $this->ui->input()->field()->text($this->shliPlugin->txt('filter_shortlink_title'));
        $url_input = $this->ui->input()->field()->text($this->shliPlugin->txt('filter_url_title'));

        // Define filter and attach inputs
        $action = $this->ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()), 'filter', '', true);
        $this->filter = $this->uiService->filter()->standard(
            'shli_filter',
            $action,
            [
                'shortlink_filter' => $shortlink_input,
                'url_filter' => $url_input
            ],
            [true, true],
            true,
            true
        );
    }
    
    public function populateWith(ilShortLinkCollection $shortlinkCollection) : void
    {
        // Filter shortlinks
        $filterData = $this->uiService->filter()->getData($this->filter);
        $filterName = $filterData['shortlink_filter'];
        $filterURL = $filterData['url_filter'];
        $patternName = is_null($filterName) ? '' : $filterName;
        $patternURL = is_null($filterURL) ? '' : $filterURL;
        $shortlinks = $shortlinkCollection->getShortLinksByPattern($patternName, $patternURL);

        // Build table data
        $data = array();
        
        foreach ($shortlinks as $shortLink) {
            $row['id'] = (string) $shortLink->getId();
            $row['title'] = $shortLink->getName();
            $row['url'] = $shortLink->getTargetUrl();
            $data[] = $row;
        }
        $this->setData($data);
    }
    
    public function getMyRender() : string
    {
        $filter_html = $this->renderer->render($this->filter);
        $table_html = $this->getHTML();
        return $filter_html . $table_html;
    }

    protected function fillRow($a_set) : void
    {
        // Set parameter
        $this->ilCtrl->setParameterByClass(get_class($this->getParentObject()), 'shliid', $a_set['id']);
        
        $item = $this->ui->modal()->interruptiveItem(
            $a_set['id'],
            $this->shliPlugin->txt('table_col_title') . ': ' . $a_set['title'],
            null,
            $this->shliPlugin->txt('table_col_targeturl') . ': ' . $a_set['url']
        );

        // Needed, but i dont know why.
        // Cmd is 'delete' instead of 'deleteModalShortlink' when not
        // creating+rendering a second modal.
        $modalEmpty = $this->ui->modal()->interruptive('such empty', 'much empty', '');
        
        $modal = $this->ui->modal()->interruptive(
            $this->shliPlugin->txt('gui_message_confirm_delete_title'),
            $this->shliPlugin->txt('gui_message_confirm_delete'),
            $this->ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()), 'deleteModalShortLink')
        )
                ->withAffectedItems(array($item));

        $editAction = $this->ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()), 'displayShortLinkEditPage');

        $items = array(
            $this->ui->button()->shy($this->shliPlugin->txt("table_dropdown_edit"), $editAction),
            $this->ui->divider()->horizontal(),
            $this->ui->button()->shy($this->shliPlugin->txt("table_dropdown_delete"), $modal->getShowSignal())
        );
        
        $dropDown = $this->ui->dropdown()->standard($items)->withLabel($this->shliPlugin->txt("table_dropdown_title"));
        $dropDownHTML = $this->renderer->render([$modalEmpty, $modal, $dropDown]);
        
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('OBJ_TITLE', $a_set['title']);
        $this->tpl->setVariable('OBJ_URL', $a_set['url']);
        $this->tpl->setVariable('OBJ_ACTION', $dropDownHTML);

        $this->ilCtrl->clearParameterByClass(get_class($this->getParentObject()), 'shliid');
    }
}
