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

/**
 *
 * @author Christoph Ludolf
 */
class ilShortLinkGeneratorPlugin extends \ilUserInterfaceHookPlugin
{
    private const PLUGIN_ID = 'shli';
    private const PLUGIN_NAME = 'ShortLinkGenerator';
    
    private ilDBInterface $ilDB;
    private \ilComponentRepositoryWrite $ilComponentRepository;

    public function __construct(\ilDBInterface $db, \ilComponentRepositoryWrite $component_repository, string $id)
    {
        parent::__construct($db, $component_repository, $id);

        $this->ilDB = $db;
        $this->ilComponentRepository = $component_repository;
        $this->initAutoLoad();
    }
    
    public static function getInstance() : ilShortLinkGeneratorPlugin
    {
        global $DIC;

        return new ilShortLinkGeneratorPlugin(
            $DIC->database(),
            $DIC["component.repository"],
            self::PLUGIN_ID
        );
    }
    
    private function classFileOf($a_classname) : string
    {
        return __DIR__ . '/class.' . $a_classname . '.php';
    }

    private function interfaceFileOf($a_classname) : string
    {
        return __DIR__ . '/../interfaces/interface.' . $a_classname . '.php';
    }

    private function exceptionsFileOf($a_classname) : string
    {
        return __DIR__ . '/../exceptions/interface.' . $a_classname . '.php';
    }

    protected function init() : void
    {
        $this->initAutoLoad();
    }
    
    /**
     * Auto load implementation.
     * @param string class name
     */
    private function autoLoad($a_classname) : void
    {
        $class_file = $this->classFileOf($a_classname);
        $interface_file = $this->interfaceFileOf($a_classname);
        $exception_file = $this->exceptionsFileOf($a_classname);
        
        foreach ([$class_file, $interface_file, $exception_file] as $filename) {
            if (file_exists($filename)) {
                include_once $filename;
                break;
            }
        }
    }

    protected function afterUninstall() : void
    {
        parent::afterUninstall();

        // Remove data base tables
        $cmdRemoveItemsTable = 'DROP TABLE IF EXISTS uico_uihk_shli_items';
        $cmdRemoveSeqTable = 'DROP TABLE IF EXISTS uico_uihk_shli_items_seq';

        $this->ilDB->manipulate($cmdRemoveItemsTable);
        $this->ilDB->manipulate($cmdRemoveSeqTable);
    }

    /**
     * Init auto loader.
     * @return void
     */
    protected function initAutoLoad()
    {
        spl_autoload_register(
            array($this, 'autoLoad')
        );
    }

    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }
}
