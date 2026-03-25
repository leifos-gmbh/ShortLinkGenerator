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

use Leifos\ShortLink\ilShortLinkDBRepository;

try {
    // Change working directory to ILIAS main directory.

    chdir('../../../../../../../../');

    include_once './components/ILIAS/Context/classes/class.ilContext.php';
    ilContext::init(ilContext::CONTEXT_SHIBBOLETH);

    require_once './vendor/composer/vendor/autoload.php';
    ilInitialisation::initILIAS();

    $uri = $_SERVER['REQUEST_URI'];
    $uriParts = explode('/', $uri);
    $shortLinkName = end($uriParts);

    // includes short link classes if plugin is not active.
    if (!class_exists('ilShortLinkDBRepository')) {
        // Change working directory to the plugin directory
        chdir('./public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/');
        include_once './interfaces/interface.ilShortLinkRepository.php';
        include_once './classes/ShortLink/class.ilShortLinkCollection.php';
        include_once './classes/ShortLink/class.ilShortLink.php';
        include_once './classes/ShortLink/class.ilShortLinkDBRepository.php';
        chdir('../../../../../../../../');
    }

    $ilShortLinkCollection = new ilShortLinkDBRepository();
    $shortLinks = $ilShortLinkCollection->getAllShortLinksWithName($shortLinkName);

    if ($shortLinks->count() === 0) {
        throw new Exception('ShortLink not valid.');
    }

    $target_url = $shortLinks->current()->getTargetUrl();
    header('Location: ' . $target_url);
} catch(Exception $e) {
    http_response_code(404);
    chdir('./public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/');
    readfile('./templates/errorpage/tpl.custom404ErrorPage.html');
}
