<?php
// Change working directory to ILIAS main directory.
$inPluginDirectory = false;
chdir('../../../../../../../');

include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SHIBBOLETH);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$uri = $_SERVER['REQUEST_URI'];
$shortLinkName = end(explode('/', $uri));

try {
    // includes short link classes if plugin is not active.
    if(!class_exists('ilShortLinkDBCollection')) 
    {
        // Change working directory to the plugin directory
        $inPluginDirectory = true;
        chdir('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/');
        include_once 'interfaces/interface.ilShortLinkCollection.php';
        include_once 'classes/class.ilShortLinkArrayWrapper.php';
        include_once 'classes/class.ilShortLink.php';
        include_once 'classes/class.ilShortLinkDBCollection.php';
    }
    
    $ilShortLinkCollection = new ilShortLinkDBCollection();
    $shortLink = $ilShortLinkCollection->getShortLinkByName($shortLinkName);
    
    if(is_null($shortLink)) 
    {
        throw new Exception('ShortLink not valid.');
    }
    
    $target_url = $shortLink->getTargetUrl();
    header('Location: ' . $target_url);        
}
catch(Exception $e)
{
    http_response_code(404);
    if(!$inPluginDirectory) 
    {
        chdir('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/');
    }
    readfile('./templates/errorpage/tpl.custom404ErrorPage.html');
}