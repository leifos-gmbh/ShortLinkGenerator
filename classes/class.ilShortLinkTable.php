<?php

/**
 *
 * @author Christoph Ludolf
 */
class ilShortLinkTable extends ilTable2GUI
{    
    /**
     *
     * @var ilCtrl
     */
    private $ilCtrl;
    
    /**
     *
     * @var ilLanguage
     */
    protected $lng;

    /**
     *
     * @var ilShortLinkGeneratorPlugin
     */
    private $shliPlugin;

    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        $this->setId('shli'); // bevor constructor
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        global $DIC;
        $this->ilCtrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        
        $this->shliPlugin = new ilShortLinkGeneratorPlugin();
        
        $this->addColumn('', 'checkboxes', '1px');
        $this->addColumn($this->shliPlugin->txt('table_col_title'), 'title', '20%');
        $this->addColumn($this->shliPlugin->txt('table_col_targeturl'), 'url', '70%');
        $this->addColumn('', 'action', '10%');
        
        $this->addMultiCommand('confirmDeleteSelected', $this->lng->txt('delete'));
        $this->setSelectAllCheckbox('shliids');
        
        $this->setRowTemplate("tpl.summary_row.html", substr($this->shliPlugin->getDirectory(), 2));
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('desc');
        
        $this->initFilter();
        
        $this->setFormAction($this->ilCtrl->getFormAction($this->getParentObject()));
    }
        
    public function initFilter()
    {
        $this->setDisableFilterHiding(true);
 
        $txtinput_shortlink = new ilTextInputGUI($this->shliPlugin->txt('filter_shortlink_title'), 'shortlink_filter');
        $txtinput_shortlink->setSize(16);
        $txtinput_shortlink->setMaxLength(64);
        
        $txtinput_url = new ilTextInputGUI($this->shliPlugin->txt('filter_url_title'), 'url_filter');
        $txtinput_url->setSize(16);
        $txtinput_url->setMaxLength(64);
        $this->addFilterItem($txtinput_shortlink);
        $this->addFilterItem($txtinput_url);
        
        $txtinput_shortlink->readFromSession();      
        $txtinput_url->readFromSession();
    }
    
    public function getFilterShortLinkValue() : string 
    {
        $txtinput_shortlink = $this->getFilterItemByPostVar('shortlink_filter');
        if(is_null($txtinput_shortlink)) 
        {
            return '';
        }
        if(is_null($txtinput_shortlink->getValue())) 
        {
            return '';
        }
        return $txtinput_shortlink->getValue();
    }
    
    public function getFilterUrlValue() : string 
    {
        $txtinput_url = $this->getFilterItemByPostVar('url_filter');
        if(is_null($txtinput_url)) 
        {
            return '';
        }
        if(is_null($txtinput_url->getValue())) 
        {
            return '';
        }
        return $txtinput_url->getValue();
    }

    public function setFilterShortLinkValue($value) 
    {
        $txtinput_shortlink = $this->getFilterItemByPostVar('shortlink_filter');
        $txtinput_shortlink->setValue($value);
    }
    
    public function setFilterUrlValue($value) 
    {
        $txtinput_url = $this->getFilterItemByPostVar('url_filter');
        $txtinput_url->setValue($value);
    }

    public function populateWith(ilShortLinkArrayWrapper $shortlinks) : void
    {
        // Build table data
        $data = array();
        
        foreach($shortlinks as $shortLink)
        {
            $row['id'] = $shortLink->getId();
            $row['title'] = $shortLink->getName();
            $row['url']  =  $shortLink->getTargetUrl();
            $data[] = $row;
        }
        $this->setData($data);
    }
    
    public function addHtmlTo($tpl) : void 
    {
        $html = $this->getHTML();
        $tpl->setContent($html);
    }
    
    protected function fillRow($a_set) 
    {   
        $this->ilCtrl->setParameterByClass(
            get_class($this->getParentObject()),
            'shliid',
            $a_set['id']
        );
        
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setId($a_set['id']);
        $actions->setListTitle($this->shliPlugin->txt("table_dropdown_title"));   
      
        // Edit
        $actions->addItem(
            $this->shliPlugin->txt('table_dropdown_edit'),
            '',
            $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()), 'displayShortLinkEditPage')
        );
        
        // Delete
        $actions->addItem(
            $this->shliPlugin->txt('table_dropdown_delete'),
            '',
            $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()), 'confirmDeleteShortLink')
        );
        
        $this->ilCtrl->clearParameterByClass(get_class($this->getParentObject()), 'shliid');

        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('OBJ_TITLE', $a_set['title']);
        $this->tpl->setVariable('OBJ_URL', $a_set['url']);
        $this->tpl->setVariable('OBJ_ACTION', $actions->getHTML());
    }
}
