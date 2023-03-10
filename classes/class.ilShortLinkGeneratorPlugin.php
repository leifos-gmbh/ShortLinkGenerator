<?php

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
    const PNAME = 'ShortLinkGenerator';
    
    /**
     *
     * @var type ilDBInterface
     */
    private $ilDB;

    public function __construct() {
        parent::__construct();
        global $DIC;
        
        $this->ilDB = $DIC->database();

        $this->includeClass('../interfaces/interface.ilShortLinkCollection.php');
        $this->includeClass('class.ilShortLinkArrayWrapper.php');
        $this->includeClass('class.ilShortLinkTable.php');
        $this->includeClass('class.ilShortLink.php');
        $this->includeClass('class.ilShortLinkDBCollection.php');
    }
    
    private final function classFileOf($a_classname): string
    {
        return $this->getClassesDirectory() . '/class.'.$a_classname . '.php';
    }
    
    private final function interfaceFileOf($a_classname) : string
    {
        return $this->getClassesDirectory() . '../interfaces/interface.' . $a_classname . '.php';
    }
    

    /**
     * Auto load implementation.
     * @param string class name
     */
    private final function autoLoad($a_classname) : void
    {
        $class_file = $this->classFileOf($a_classname);
        if (file_exists($class_file))
        {
            include_once $class_file;
        }
        
        $interface_file = $this->interfaceFileOf($a_classname);
        if(file_exists($interface_file)) 
        {
            include_once $interface_file;
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

    public function getPluginName(): string
    {
        return self::PNAME;
    }    
}
