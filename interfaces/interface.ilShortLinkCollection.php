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
    /**
     * Creates a new shortlink with the given values as properties.
     * @param string $slName Name of the new shortlink.
     * @param string $slUrl Url of the new shortlink.
     * @return ilShortLink|null Returns the new shortlink, or null if a
     * shortlink with the given url or name already exists.
     */
    public function createShortLink(string $slName, string $slUrl) : ?ilShortLink;
    
    /**
     * Changes the properties of a shortlink that matches the id of the given
     * shortlink. The new property values equal the property values of the given
     * shortlink.
     * @param ilShortLink $replacement A shortlink that has the id of the
     * shortlink to update.
     * @return bool Returns true if a shortlink was updated, False otherwise.
     */
    public function updateShortLink(ilShortLink $replacement) : bool ;

    /**
     * Returns the first occurrance of a shortlink with an id equal to the given
     * id.
     * @param int $id The id to look for.
     * @return ilShortLink|null Returns the shortlink if found, null otherwise.
     */
    public function getShortLinkById(int $id) : ?ilShortLink;

    /**
     * Returns the first occurrance of a shortlink with a name equal to the
     * given name.
     * @param string $name The name to look for.
     * @return ilShortLink|null Returns the shortlink if found, null otherwise.
     */
    public function getShortLinkByName(string $name) : ?ilShortLink;
    
    /**
     * Returns the first occurrance of a shortlink with an url equal to the
     * given url.
     * @param string $url The url to look for.
     * @return ilShortLink|null Returns the shortlink if found, null otherwise.
     */
    public function getShortLinkByUrl(string $url) : ?ilShortLink;

    /**
     * Removes the first occurrance of a shortlink with at least one property
     * equal to a property of the given shortlink.
     * @param ilShortLink $shortLink
     * @return bool True if a shortlink was removed, False otherwise.
     */
    public function removeShortLink(ilShortLink $shortLink) : bool;
    
    /**
     * Removes the first occurrance of a shortlink with an id equal to the given
     * id.
     * @param int $id The id to look for.
     * @return bool True if a shortlink was removed, False otherwise.
     */
    public function removeShortLinkById(int $id) : bool;
    
    /**
     * Removes the first occurrance of a shortlink with a name equal to the
     * given name.
     * @param string $name The name to look for.
     * @return bool True if a shortlink was removed, False otherwise.
     */
    public function removeShortLinkByName(string $name) : bool;
    
    /**
     * Removes the first occurrance of a shortlink with a target url equal to
     * the given url.
     * @param string $url The url to look for.
     * @return bool True if a shortlink was removed, False otherwise.
     */
    public function removeShortLinkByUrl(string $url) : bool;
    
    /**
     * Checks if a shortlink with at least one property similiar to the given
     * shortlink exists in the collection.
     * @param ilShortLink $shortLink Shortlink with parameters to check for.
     * @return bool True if at least one shortlink with one matching property is
     * found, False otherwise.
     */
    public function containsShortLink(ilShortLink $shortLink) : bool;
        
    /**
     * Checks if a shortlink with the given id exists in the collection.
     * @param int $id The id to look for.
     * @return bool True if a shortlink with the id exists, False otherwise.
     */
    public function containsShortLinkWithId(int $id) : bool;
    
    /**
     * Checks if a shortlink with the given name exists in the collection.
     * @param string $name The name to look for.
     * @return bool True if a shortlink with the name exists, False otherwise.
     */
    public function containsShortLinkWithName(string $name) : bool;
    
    /**
     * Checks if a shortlink with the given url exists in the collection.
     * @param string $url The url to look for.
     * @return bool True if a shortlink with the url exists, False otherwise.
     */
    public function containsShortLinkWithUrl(string $url) : bool;
    
    /**
     * Returns an ilShortLinkArrayWrapper containing all shortlinks with a
     * name that contains $patternName and an url that contains $patternURL.
     *
     * Important:
     * The check for $patternName should not be case sensitive.
     *
     * @param string $patternName A string that the shortlink name should
     * contain.
     * @param string $patternURL A string that the shortlink target url should
     * contain.
     * @return ilShortLinkArrayWrapper An array containing all shortlinks with
     * a matching name and target url.
     */
    public function getShortLinksByPattern(string $patternName, string $patternURL) : ilShortLinkArrayWrapper;
}
