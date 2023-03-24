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
     * @param string $name Name of the new shortlink.
     * @param string $targetURL Url of the new shortlink.
     * @return ilShortLink Returns the new shortlink.
     * @throws ilShortLinkWithNameAlreadyExistsException
     * @throws ilShortLinkInvalidException
     */
    public function createShortLink(string $name, string $targetURL) : ilShortLink;
    
    /**
     * Changes the properties of the shortlink that matches the given id.
     * @param int $id The id of the shortlink to be updated.
     * @param string $name The new name of the shortlink.
     * @param string $targetURL The new target url of the shortlink.
     * @throws ilShortLinkDoesNotExistException
     * @throws ilShortLinkInvalidException
     */
    public function updateShortLinkByID(int $id, string $name = null, string $targetURL = null) : void;

    /**
     * Returns the first occurrance of a shortlink with an id equal to the given
     * id.
     * @param int $id The id to look for.
     * @return ilShortLink Returns the shortlink if found.
     * @throws ilShortLinkDoesNotExistException
     */
    public function getShortLinkById(int $id) : ilShortLink;

    /**
     * Returns the first occurrance of a shortlink with a name equal to the
     * given name.
     * @param string $name The name to look for.
     * @return ilShortLinkArrayWrapper Returns a ilShortLinkArrayWrapper
     * containing all shortlinks that have the given name as their name.
     */
    public function getAllShortLinksWithName(string $name) : ilShortLinkArrayWrapper;
    
    /**
     * Returns the first occurrance of a shortlink with an url equal to the
     * given url.
     * @param string $url The url to look for.
     * @return ilShortLinkArrayWrapper Returns a ilShortLinkArrayWrapper
     * containing all shortlinks that have the given url as their url.
     */
    public function getAllShortLinksWithUrl(string $url) : ilShortLinkArrayWrapper;

    /**
     * Removes the first occurrance of a shortlink with an id equal to the given
     * id.
     * @param int $id The id to look for.
     * @throws ilShortLinkDoesNotExistException
     */
    public function removeShortLinkById(int $id) : void;
    
    /**
     * Checks if a shortlink with the given id exists in the collection.
     * @param int $id The id to look for.
     * @return bool True if a shortlink with the id exists, False otherwise.
     */
    public function containsShortLinkWithId(int $id) : bool;
    
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
