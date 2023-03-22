<?php
declare(strict_types=1);
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
    private SplDoublyLinkedList $shortLinks;
    private ilDBInterface $ilDB;

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

    private function saveShortLinkToDB(ilShortLink $shortLink) : int
    {
        $dateTimeImm = new DateTimeImmutable('now', new DateTimeZone('Utc'));
        $id = (int) $this->ilDB->nextId('uico_uihk_shli_items');
        $values = array(
            'id' => array('integer', $id),
            'title' => array('text', $shortLink->getName()),
            'url' => array('text', $shortLink->getTargetUrl()),
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

    /**
     *
     * @param int $index1
     * @param int $index2
     * @return void
     * @throws OutOfBoundsException if $index1 or $index2 is not a valid index.
     */
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

    private function removeShortLinkFromCollection($shortLink) : ?ilShortLink
    {
        $lastIndex = $this->shortLinks->count() - 1;
        $index = -1;

        if ($lastIndex === -1) { // Zero elements in DB.
            return null;
        }

        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if ($shortLink->sharesAPropertyWith($currentShortLink)) {
                $index = $this->shortLinks->key();
                break;
            }
        }

        if ($index === -1) {
            return null;
        }

        try {
            $this->swapElements($index, $lastIndex);
            return $this->shortLinks->pop();
        } catch (OutOfBoundsException $e) {
            return false;
        }
        
        return null;
    }
    
    public function createShortLink(string $slName, string $slUrl) : ?ilShortLink
    {
        $dummyShortLink = new ilShortLink(-1, $slName, $slUrl);

        if (!$dummyShortLink->validate()) {
            return null;
        }
        if ($this->containsShortLink($dummyShortLink)) {
            return null;
        }

        $id = $this->saveShortLinkToDB($dummyShortLink);
        $newShortLink = new ilShortLink($id, $slName, $slUrl);
        $this->shortLinks->push($newShortLink);
        return $newShortLink;
    }

    public function updateShortLink(ilShortLink $replacement) : bool
    {
        if (!$this->containsShortLinkWithId($replacement->getId())) {
            return false;
        }
        if (!$replacement->validate()) {
            return false;
        }

        $this->removeShortLinkFromCollection($replacement);
        $this->updateShortLinkInDB($replacement);
        $this->shortLinks->push($replacement);

        return true;
    }

    public function getShortLinkById(int $id) : ?ilShortLink
    {
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if ($currentShortLink->getId() == $id) {
                return $currentShortLink;
            }
        }
        return null;
    }

    public function getShortLinkByName(string $name) : ?ilShortLink
    {
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if (strcmp($currentShortLink->getName(), $name) == 0) {
                return $currentShortLink;
            }
        }
        return null;
    }

    public function getShortLinkByUrl(string $url) : ?ilShortLink
    {
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if (strcmp($currentShortLink->getTargetUrl(), $url) == 0) {
                return $currentShortLink;
            }
        }
        return null;
    }

    public function removeShortLink(ilShortLink $shortLink) : bool
    {
        $removedShortLink = $this->removeShortLinkFromCollection($shortLink);
        if (is_null($removedShortLink)) {
            return false;
        }
        $this->remShortLinkFromDB($removedShortLink);
        return true;
    }

    public function removeShortLinkById(int $id) : bool
    {
        return $this->removeShortLink(new ilShortLink($id, '', ''));
    }

    public function removeShortLinkByName(string $name) : bool
    {
        return $this->removeShortLink(new ilShortLink(-1, $name, ''));
    }

    public function removeShortLinkByUrl(string $url) : bool
    {
        return $this->removeShortLink(new ilShortLink(-1, '', $url));
    }

    public function containsShortLink(ilShortLink $shortLink) : bool
    {
        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            if ($shortLink->sharesIdWith($currentShortLink)) {
                return true;
            }
            if ($shortLink->sharesNameWith($currentShortLink)) {
                return true;
            }
            if ($shortLink->sharesUrlWith($currentShortLink)) {
                return true;
            }
        }
        return false;
    }

    public function containsShortLinkWithId(int $id) : bool
    {
        return $this->containsShortLink(new ilShortLink($id, '', ''));
    }

    public function containsShortLinkWithName(string $name) : bool
    {
        return $this->containsShortLink(new ilShortLink(-1, $name, ''));
    }

    public function containsShortLinkWithUrl(string $url) : bool
    {
        return $this->containsShortLink(new ilShortLink(-1, '', $url));
    }

    public function getShortLinksByPattern(string $patternName, string $patternURL) : ilShortLinkArrayWrapper
    {
        $shortlinks = new ilShortLinkArrayWrapper();

        $this->shortLinks->rewind();
        for (;$this->shortLinks->valid(); $this->shortLinks->next()) {
            $currentShortLink = $this->shortLinks->current();
            $patternNameLowerCase = strtolower(trim($patternName));
            $nameLowerCase = strtolower($currentShortLink->getName());
            
            $containsName = strlen($patternName) == 0
                    || str_contains($nameLowerCase, $patternNameLowerCase);
            $containsUrl = strlen($patternURL) == 0
                    || str_contains($currentShortLink->getTargetUrl(), trim($patternURL));
            
            if ($containsName && $containsUrl) {
                $shortlinks->add($currentShortLink);
            }
        }
        return $shortlinks;
    }
}
