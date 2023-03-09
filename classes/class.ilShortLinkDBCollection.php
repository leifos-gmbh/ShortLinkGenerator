<?php

/**
 *
 * @author Christoph Ludolf
 */
class ilShortLinkDBCollection implements ilShortLinkCollection
{
    /**
     *
     * @var SplDoublyLinkedList
     */
    private $shortLinks;
    
    /**
     *
     * @var ilDBInterface
     */
    private $ilDB;
    
    public function __construct()
    {
        global $DIC;
        $this->ilDB = $DIC->database();
        $this->shortLinks = new SplDoublyLinkedList();
        $this->loadShortLinksFromDB();
    }
    
    private function loadShortLinksFromDB()
    {
        $query = 'SELECT * FROM uico_uihk_shli_items';
        $results = $this->ilDB->query($query);
        
        while($row = $this->ilDB->fetchAssoc($results))
        {
            $id = (int)$row['id'];
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
        $id = $this->ilDB->nextId('uico_uihk_shli_items');            
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
    
    private function swapElements(int $index1, int $index2): void 
    {
        $index1exists = $this->shortLinks->offsetExists($index1);
        $index2exists = $this->shortLinks->offsetExists($index2);

        if(!$index1exists || !$index2exists) 
        {
            throw new Exception('Index out of bounds.');
        }

        $tmp1 = $this->shortLinks->offsetGet($index1);
        $tmp2 = $this->shortLinks->offsetGet($index2);
        $this->shortLinks->offsetSet($index1, $tmp2);
        $this->shortLinks->offsetSet($index2, $tmp1);
    }

    public function createShortLink(string $slName, string $slUrl) : ?ilShortLink 
    {
        $dummyShortLink = new ilShortLink(-1, $slName, $slUrl);
        
        if (!$dummyShortLink->validate()) 
        {
            return null;
        }
        if ($this->containsShortLink($dummyShortLink)) 
        {
            return null;
        }
        
        $id = $this->saveShortLinkToDB($dummyShortLink);
        
        return new ilShortLink($id, $slName, $slUrl);
    }
    
    public function updateShortLink(ilShortLink $replacement): bool
    {
        if(!$this->containsShortLinkWithId($replacement->getId())) 
        {
            return false;
        }
        if(!$replacement->validate()) 
        {
            return false;
        }
        
        $this->updateShortLinkInDB($replacement);
        
        return true;
    }
    
    public function getShortLinkById(int $id): ?ilShortLink
    {
        foreach($this->shortLinks as $currentShortLink) 
        {
            if($currentShortLink->getId() == $id) 
            {
                return $currentShortLink;
            }
        }
        return null;
    }
    
    public function getShortLinkByName(string $name): ?ilShortLink
    {
        foreach($this->shortLinks as $currentShortLink) 
        {
            if(strcmp($currentShortLink->getName(), $name) == 0) 
            {
                return $currentShortLink;
            }
        }
        return null;
    }
    
    public function getShortLinkByUrl(string $url): ?ilShortLink
    {
        foreach($this->shortLinks as $currentShortLink) 
        {
            if(strcmp($currentShortLink->getTargetUrl(), $url) == 0) 
            {
                return $currentShortLink;
            }
        }
        return null;
    }
    
    public function removeShortLink(ilShortLink $shortLink) : bool
    {
        $lastIndex = $this->shortLinks->count() - 1;
        $index = -1;
        
        if($lastIndex === -1) // Zero elements in DB.
        {
            return false;
        }
        
        foreach($this->shortLinks as $currentShortLink) 
        {
            if($shortLink->sharesAPropertyWith($currentShortLink)) 
            {
                $index = $this->shortLinks->key();
                break;
            }
        }
        
        if($index === -1) 
        {
            return false;
        }
        
        $this->swapElements($index, $lastIndex);
        $this->remShortLinkFromDB($this->shortLinks->pop());
        return true;
    }
    
    public function removeShortLinkById(int $id): bool
    {
        return $this->removeShortLink($this->getShortLinkById($id));
    }
    
    public function removeShortLinkByName(string $name): bool
    {
        return $this->removeShortLink($this->getShortLinkByName($name));
    }
    
    public function removeShortLinkByUrl(string $url): bool
    {
        return $this->removeShortLink($this->getShortLinkByUrl($url));
    }
    
    public function containsShortLink(ilShortLink $shortLink) : bool 
    {
        foreach($this->shortLinks as $currentShortLink) 
        {
            if($shortLink->sharesIdWith($currentShortLink)) 
            {
                return true;
            }
            if($shortLink->sharesNameWith($currentShortLink)) 
            {
                return true;
            }
            if($shortLink->sharesUrlWith($currentShortLink)) 
            {
                return true;
            }
        }
        return false;
    }
    
    public function containsShortLinkWithId(int $id): bool {
        return $this->containsShortLink(new ilShortLink($id, '', ''));
    }
    
    public function containsShortLinkWithName(string $name): bool {
        return $this->containsShortLink(new ilShortLink(-1, $name, ''));
    }
    
    public function containsShortLinkWithUrl(string $url): bool {
        return $this->containsShortLink(new ilShortLink(-1, '', $url));
    }
    
    public function getShortLinksByPattern(string $patternName, string $patternURL) : ilShortLinkArrayWrapper
    {        
        $shortlinks = new ilShortLinkArrayWrapper();
        
        foreach($this->shortLinks as $currentShortLink) 
        {
            if(preg_match($patternName, $currentShortLink->getName()) 
                    && preg_match($patternURL, $currentShortLink->getTargetUrl())) 
            {
                $shortlinks->add($currentShortLink);
            }
        }
        return $shortlinks;
    }
}
