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
class ilShortLinkArrayWrapper implements Iterator
{
    private $shortLinks;
    private $index;

    public function __construct()
    {
        $this->shortLinks = array();
        $this->index = 0;
    }

    public function add(ilShortLink $shortLink) : void
    {
        $this->shortLinks[] = $shortLink;
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function current()
    {
        return $this->shortLinks[$this->index];
    }

    public function next() : void
    {
        $this->index++;
    }

    public function key() : \scalar
    {
        return $this->index;
    }

    public function valid() : bool
    {
        return $this->index < count($this->shortLinks);
    }
    
    public function count() : int
    {
        return count($this->shortLinks);
    }
}
