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
interface ilShortLinkCollection
{
    public function createShortLink(string $slName, string $slUrl) : ?ilShortLink;
    
    public function updateShortLink(ilShortLink $replacement) : bool ;

    public function getShortLinkById(int $id) : ?ilShortLink;

    public function getShortLinkByName(string $name) : ?ilShortLink;
    
    public function getShortLinkByUrl(string $url) : ?ilShortLink;

    public function removeShortLink(ilShortLink $shortLink) : bool;
        
    public function removeShortLinkById(int $id) : bool;
    
    public function removeShortLinkByName(string $name) : bool;
    
    public function removeShortLinkByUrl(string $url) : bool;
    
    public function containsShortLink(ilShortLink $shortLink) : bool;
        
    public function containsShortLinkWithId(int $id) : bool;
    
    public function containsShortLinkWithName(string $name) : bool;
    
    public function containsShortLinkWithUrl(string $url) : bool;
    
    public function getShortLinksByPattern(string $patternName, string $patternURL) : ilShortLinkArrayWrapper;
}
