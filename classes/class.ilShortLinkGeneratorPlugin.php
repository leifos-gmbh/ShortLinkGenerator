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
class ilShortLinkGeneratorPlugin extends ilUserInterfaceHookPlugin
{
    /**
     *
     * @var string
     */
    public const PNAME = 'ShortLinkGenerator';

    /**
     *
     * @var type ilDBInterface
     */
    private $ilDB;

    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->ilDB = $DIC->database();

        $this->initAutoLoad();
    }

    final private function classFileOf($a_classname) : string
    {
        return $this->getClassesDirectory() . '/class.' . $a_classname . '.php';
    }

    final private function interfaceFileOf($a_classname) : string
    {
        return $this->getClassesDirectory() . '/../interfaces/interface.' . $a_classname . '.php';
    }
    
    private function exceptionsFileOf($a_classname) : string
    {
        return $this->getClassesDirectory() . '/../exceptions/interface.' . $a_classname . '.php';
    }

    protected function init()
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

    protected function afterUninstall()
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
        return self::PNAME;
    }
}
