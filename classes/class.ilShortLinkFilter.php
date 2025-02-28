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

use ilCtrl;
use ILIAS\UI\Component\Input\Container\Filter\Standard as StandardFilter;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ilUIFilterService;
use InvalidArgumentException;

class ilShortLinkFilter
{
    private const FILTER_ID = 'shli_table';
    private const FILTER_SHORTLINK_ID = 'shortlink_filter';
    private const FILTER_URL_ID = 'url_filter';
    private StandardFilter $filter;

    public function __construct(
        protected ilShortLinkGeneratorPlugin $shliPlugin,
        protected UIFactory $ui,
        protected ilCtrl $ilCtrl,
        protected Renderer $renderer,
        protected ilUIFilterService $filterService,
        protected ilShortLinkGeneratorConfigGUI $parent
    ) {
        $this->renderer = $renderer;
        $this->filterService = $filterService;

        $shortlink_input = $ui->input()->field()->text($shliPlugin->txt('filter_shortlink_title'));
        $url_input = $ui->input()->field()->text($shliPlugin->txt('filter_url_title'));

        $this->filter = $filterService->standard(
            $this::FILTER_ID,
            $ilCtrl->getLinkTarget($parent, 'filter'),
            [
                $this::FILTER_SHORTLINK_ID => $shortlink_input,
                $this::FILTER_URL_ID => $url_input
            ],
            [true, true],
            true,
            true
        );
    }

    /**
     *
     * @return string[]
     */
    private function getData(): array
    {
        $empty = array(
            $this::FILTER_SHORTLINK_ID => '',
            $this::FILTER_URL_ID => ''
        );

        try {
            return $this->filterService->getData($this->filter) ?? $empty;
        } catch (InvalidArgumentException $e) {
            return $empty;
        }
    }

    public function getHTML(): string
    {
        return $this->renderer->render($this->filter);
    }

    public function getShortLinkFilterValue(): string
    {
        return $this->getData()[$this::FILTER_SHORTLINK_ID] ?? '';
    }

    public function getURLFilterValue(): string
    {
        return $this->getData()[$this::FILTER_URL_ID] ?? '';
    }
}
