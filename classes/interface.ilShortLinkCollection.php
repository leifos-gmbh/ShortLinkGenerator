<?php

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
