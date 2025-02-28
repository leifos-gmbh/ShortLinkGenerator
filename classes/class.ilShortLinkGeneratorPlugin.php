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

use ilComponentFactory;
use ilComponentRepositoryWrite;
use ilUserInterfaceHookPlugin;

class ilShortLinkGeneratorPlugin extends ilUserInterfaceHookPlugin
{
    protected const PLUGIN_ID = 'shli';
    protected const PLUGIN_NAME = 'ShortLinkGenerator';

    public function __construct(
        protected \ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        parent::__construct($db, $component_repository, $id);
    }

    public static function getInstance(): ilShortLinkGeneratorPlugin
    {
        global $DIC;
        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC["component.factory"];
        /** @var ilShortLinkGeneratorPlugin $plugin */
        $plugin = $component_factory->getPlugin(self::PLUGIN_ID);
        return $plugin;
    }

    private function classFileOf($a_classname): string
    {
        return __DIR__ . '/class.' . $a_classname . '.php';
    }

    private function interfaceFileOf($a_classname): string
    {
        return __DIR__ . '/../interfaces/interface.' . $a_classname . '.php';
    }

    private function exceptionsFileOf($a_classname): string
    {
        return __DIR__ . '/../exceptions/interface.' . $a_classname . '.php';
    }

    protected function afterUninstall(): void
    {
        parent::afterUninstall();

        // Remove data base tables
        $cmdRemoveItemsTable = 'DROP TABLE IF EXISTS uico_uihk_shli_items';
        $cmdRemoveSeqTable = 'DROP TABLE IF EXISTS uico_uihk_shli_items_seq';

        $this->db->manipulate($cmdRemoveItemsTable);
        $this->db->manipulate($cmdRemoveSeqTable);
    }

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }
}
