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

namespace Leifos\ShortLink;

use Closure;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ilShortLinkGeneratorConfigGUI;
use ilShortLinkGeneratorPlugin;
use ilTable2GUI;
use ilUIFilterService;

class ilShortLinkTable extends ilTable2GUI
{
    protected ilShortLinkFilter $shliFilter;
    protected ilShortLinkGeneratorConfigGUI $parent;
    protected Closure $shliTxt;
    protected Closure $lngTxt;

    public function __construct(
        protected \ilCtrl $ctrl,
        protected UIFactory $ui,
        protected Renderer $renderer,
        protected ilUIFilterService $filter_service,
        protected ilShortLinkGeneratorPlugin $shli_plugin,
        ilShortLinkGeneratorConfigGUI $a_parent_obj,
        string $a_parent_cmd = "",
        string $a_template_context = "",
    ) {
        $this->setId('shli_table'); // bevor constructor
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->parent = $a_parent_obj;

        $this->setFormAction($this->ctrl->getFormAction($this->parent));
        $this->shliTxt = static function (string $id) use ($shli_plugin): string {
            return $shli_plugin->txt($id);
        };

        $lng = $this->lng;
        $this->lngTxt = static function (string $id) use ($lng): string {
            return $lng->txt($id);
        };

        $this->shliFilter = new ilShortLinkFilter(
            $this->shli_plugin,
            $this->ui,
            $this->ctrl,
            $this->renderer,
            $this->filter_service,
            $a_parent_obj
        );

        $this->buildTable($this->shli_plugin->getDirectory());
    }

    private function getTxt(string $key, bool $ilDict = false): string
    {
        $func = $ilDict ? $this->lngTxt : $this->shliTxt;
        return $func($key);
    }

    private function buildTable(string $pluginDirectory): void
    {
        $this->addColumn('', 'checkboxes', '1px');
        $this->addColumn($this->getTxt('table_col_title'), 'title', '20%');
        $this->addColumn($this->getTxt('table_col_targeturl'), 'url', '70%');
        $this->addColumn('', 'action', '10%');

        $this->addMultiCommand('confirmDeleteSelected', $this->getTxt('delete', true));
        $this->setSelectAllCheckbox('shliids');

        $this->setRowTemplate("tpl.summary_row.html", $pluginDirectory);
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('desc');
    }

    public function populateWith(ilShortLinkRepository $shortlinkCollection): void
    {
        // Filter shortlinks
        $shortlinks = $shortlinkCollection->getShortLinksByPattern(
            $this->shliFilter->getShortLinkFilterValue(),
            $this->shliFilter->getURLFilterValue()
        );

        // Build table data
        $data = [];

        foreach ($shortlinks as $shortLink) {
            $row['id'] = (string) $shortLink->getId();
            $row['title'] = $shortLink->getName();
            $row['url'] = $shortLink->getTargetUrl();
            $data[] = $row;
        }
        $this->setData($data);
    }

    public function getMyRender(): string
    {
        return $this->shliFilter->getHTML() . $this->getHTML();
    }

    protected function fillRow($a_set): void
    {
        // Set parameter
        $this->ctrl->setParameterByClass(get_class($this->parent), 'shliid', $a_set['id']);

        $item = $this->ui->modal()->interruptiveItem()->keyValue(
            $a_set['id'],
            $a_set['title'],
            $a_set['url']
        );

        // Needed, but i dont know why.
        // Cmd is 'delete' instead of 'deleteModalShortlink' when not
        // creating+rendering a second modal.
        $modalEmpty = $this->ui->modal()->interruptive('such empty', 'much empty', '');

        $modal = $this->ui->modal()->interruptive(
            $this->getTxt('gui_message_confirm_delete_title'),
            $this->getTxt('gui_message_confirm_delete'),
            $this->ctrl->getLinkTargetByClass(get_class($this->parent), 'deleteModalShortLink')
        )
            ->withAffectedItems([$item]);

        $editAction = $this->ctrl->getLinkTargetByClass(get_class($this->parent), 'displayShortLinkEditPage');

        $items = [
            $this->ui->button()->shy($this->getTxt("table_dropdown_edit"), $editAction),
            $this->ui->divider()->horizontal(),
            $this->ui->button()->shy($this->getTxt("table_dropdown_delete"), $modal->getShowSignal())
        ];

        $dropDown = $this->ui->dropdown()->standard($items)->withLabel($this->getTxt("table_dropdown_title"));
        $dropDownHTML = $this->renderer->render([$modalEmpty, $modal, $dropDown]);

        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('OBJ_TITLE', $a_set['title']);
        $this->tpl->setVariable('OBJ_URL', $a_set['url']);
        $this->tpl->setVariable('OBJ_ACTION', $dropDownHTML);
        $this->ctrl->clearParameterByClass(get_class($this->parent), 'shliid');
    }
}
