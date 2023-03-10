<?php
/**
 *
 * @author Christoph Ludolf
 */
class ilShortLinkArrayWrapper implements Iterator
{
    /**
     *
     * @var array
     */
    private $shortLinks;
    
    /**
     *
     * @var int
     */
    private $index;
    
    public function __construct() {
        $this->shortLinks = array();
        $this->index = 0;
    }
    
    public function add(ilShortLink $shortLink) : void 
    {
        $this->shortLinks[] = $shortLink;
    }
    
    public function rewind(): void 
    {
        $this->index = 0;
    }
    
    public function current()
    {
        return $this->shortLinks[$this->index];
    }
    
    public function next(): void 
    {
        $this->index++;
    }
    
    public function key(): \scalar 
    {
        return $this->index;
    }
    
    public function valid(): bool 
    {
        return $this->index < count($this->shortLinks);
    }
}
