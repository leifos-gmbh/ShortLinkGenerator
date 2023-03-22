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

// Change working directory to ILIAS main directory.
$inPluginDirectory = false;
chdir('../../../../../../../');

include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SHIBBOLETH);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', $uri);
$shortLinkName = end($uriParts);


try {
    // includes short link classes if plugin is not active.
    if (!class_exists('ilShortLinkDBCollection')) {
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
    
    if (is_null($shortLink)) {
        throw new Exception('ShortLink not valid.');
    }
    
    $target_url = $shortLink->getTargetUrl();
    header('Location: ' . $target_url);
} catch(Exception $e) {
    http_response_code(404);
    if (!$inPluginDirectory) {
        chdir('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/');
    }
    readfile('./templates/errorpage/tpl.custom404ErrorPage.html');
}
