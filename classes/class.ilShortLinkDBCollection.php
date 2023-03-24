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
class ilShortLinkDBCollection implements ilShortLinkCollection
{
    private $shortLinks;
    private $ilDB;

    public function __construct()
    {
        global $DIC;
        $this->ilDB = $DIC->database();
        $this->shortLinks = new SplDoublyLinkedList();
        $this->loadShortLinksFromDB();
    }

    private function loadShortLinksFromDB() : void
    {
        $query = 'SELECT * FROM uico_uihk_shli_items';
        $results = $this->ilDB->query($query);

        while ($row = $this->ilDB->fetchAssoc($results)) {
            $id = (int) $row['id'];
            $name = $row['title'];
            $url = $row['url'];
            $dateTimeImm = new DateTimeImmutable($row['last_update'], new DateTimeZone('Utc'));
            $shortLink = new ilShortLink($id, $name, $url, $dateTimeImm);
            $this->shortLinks->push($shortLink);
        }
    }

    private function saveShortLinkToDB($name, $targetURL) : int
    {
        $dateTimeImm = new DateTimeImmutable('now', new DateTimeZone('Utc'));
        $id = (int) $this->ilDB->nextId('uico_uihk_shli_items');
        $values = array(
            'id' => array('integer', $id),
            'title' => array('text', $name),
            'url' => array('text', $targetURL),
            'last_update' => array('timestamp', $dateTimeImm->format('Y-m-d H:i:s'))
        );

        $this->ilDB->insert('uico_uihk_shli_items', $values);
        return $id;
    }

    private function updateShortLinkInDB(ilShortLink $shortLink) : void
    {
        $dateTimeImm = new DateTimeImmutable('now', new DateTimeZone('Utc'));

        $query = 'UPDATE uico_uihk_shli_items SET' .
                ' id = ' . $this->ilDB->quote($shortLink->getId(), 'integer') .
                ', title = ' . $this->ilDB->quote($shortLink->getName(), 'text') .
                ', url = ' . $this->ilDB->quote($shortLink->getTargetUrl(), 'text') .
                ', last_update = ' . $this->ilDB->quote($dateTimeImm->format('Y-m-d H:i:s'), 'timestamp') .
                ' WHERE id = ' . $this->ilDB->quote($shortLink->getId(), 'integer') . ';';

        $this->ilDB->manipulate($query);
    }

    private function remShortLinkFromDB(ilShortLink $shortLink) : void
    {
        $id = $shortLink->getId();

        $query = 'DELETE FROM uico_uihk_shli_items WHERE id = ' .
                $this->ilDB->quote($id, 'integer');

        $this->ilDB->manipulate($query);
    }

    private function swapElements(int $index1, int $index2) : void
    {
        $index1exists = $this->shortLinks->offsetExists($index1);
        $index2exists = $this->shortLinks->offsetExists($index2);

        if (!$index1exists || !$index2exists) {
            throw new OutOfBoundsException('Index out of bounds.');
        }

        $tmp1 = $this->shortLinks->offsetGet($index1);
        $tmp2 = $this->shortLinks->offsetGet($index2);
        $this->shortLinks->offsetSet($index1, $tmp2);
        $this->shortLinks->offsetSet($index2, $tmp1);
    }

    private function removeShortLinkFromCollectionByID(int $id) : bool
    {
        $lastIndex = $this->shortLinks->count() - 1;
        $index = -1;

        // Zero elements in DB.
        if ($lastIndex === -1) {
            return false;
        }

        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if ($currentShortLink->getId() === $id) {
                $index = $this->shortLinks->key();
                break;
            }
        }

        // No shortlink with id found.
        if ($index === -1) {
            return false;
        }
        
        try {
            $this->swapElements($index, $lastIndex);
            $this->shortLinks->pop();
        } catch (OutOfBoundsException $e) {
            return false;
        }
        
        return true;
    }
    
    public function createShortLink(string $name, string $targetURL) : ilShortLink
    {
        $dummyShortLink = new ilShortLink(-1, $name, $targetURL);
        if (!$dummyShortLink->validate()) {
            throw new ilShortLinkInvalidException($dummyShortLink);
        }
        
        $shortLinksWithName = $this->getAllShortLinksWithName($name);
        if ($shortLinksWithName->count() !== 0) {
            throw new ilShortLinkWithNameAlreadyExistsException($name);
        }
        
        $id = $this->saveShortLinkToDB($name, $targetURL);
        $newShortLink = new ilShortLink($id, $name, $targetURL);
        $this->shortLinks->push($newShortLink);
        
        return $newShortLink;
    }

    public function updateShortLinkByID(int $id, string $name = null, string $targetURL = null) : void
    {
        $oldShortLink = $this->getShortLinkById($id);
        
        $newName = is_null($name) ? $oldShortLink->getName() : $name;
        $newTargetURL = is_null($targetURL) ? $oldShortLink->getTargetUrl() : $targetURL;
        
        $newShortLink = new ilShortLink($id, $newName, $newTargetURL);

        if (!$newShortLink->validate()) {
            throw new ilShortLinkInvalidException($newShortLink);
        }
        
        $this->updateShortLinkInDB($newShortLink);
        $this->removeShortLinkFromCollectionByID($id);
        $this->shortLinks->push($newShortLink);
    }

    public function getShortLinkById(int $id) : ilShortLink
    {
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if ($currentShortLink->getID() === $id) {
                return $currentShortLink;
            }
        }
        throw new ilShortLinkDoesNotExistException(new ilShortLink($id, '', ''));
    }

    public function getAllShortLinksWithName(string $name) : ilShortLinkArrayWrapper
    {
        $shortLinksWrapper = new ilShortLinkArrayWrapper();
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if (strcmp($currentShortLink->getName(), $name) == 0) {
                $shortLinksWrapper->add($currentShortLink);
            }
        }
        return $shortLinksWrapper;
    }

    public function getAllShortLinksWithUrl(string $url) : ilShortLinkArrayWrapper
    {
        $shortLinksWrapper = new ilShortLinkArrayWrapper();
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if (strcmp($currentShortLink->getTargetUrl(), $url) === 0) {
                $shortLinksWrapper->add($currentShortLink);
            }
        }
        return $shortLinksWrapper;
    }

    public function removeShortLinkById(int $id) : void
    {
        $shortLink = $this->getShortLinkById($id);
        $this->removeShortLinkFromCollectionByID($id);
        $this->remShortLinkFromDB($shortLink);
    }
    
    public function containsShortLinkWithId(int $id) : bool
    {
        try {
            $this->getShortLinkById($id);
            return true;
        } catch(ilShortLinkDoesNotExistException $e) {
            return false;
        }
    }

    public function getShortLinksByPattern(string $patternName, string $patternURL) : ilShortLinkArrayWrapper
    {
        $shortlinksWrapper = new ilShortLinkArrayWrapper();

        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            $patternNameLowerCase = strtolower(trim($patternName));
            $nameLowerCase = strtolower($currentShortLink->getName());

            $containsName = empty($patternName)
                    || str_contains($nameLowerCase, $patternNameLowerCase);
            $containsUrl = empty($patternURL)
                    || str_contains($currentShortLink->getTargetUrl(), trim($patternURL));

            if ($containsName && $containsUrl) {
                $shortlinksWrapper->add($currentShortLink);
            }
        }
        return $shortlinksWrapper;
    }
}
