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
class ilShortLink
{
    /**
     *
     * @var integer
     */
    private $id;

    /**
     *
     * @var string
     */
    private $shortLink;

    /**
     *
     * @var string
     */
    private $url;

    /**
     *
     * @var DateTimeImmutable
     */
    private $lastEdited;

    public function __construct(int $id, string $shortLink, string $url, ?DateTimeImmutable $lastEdited = null)
    {
        $this->id = $id;
        $this->shortLink = $shortLink;
        $this->url = $url;
        $this->lastEdited = $lastEdited;
    }

    public function sharesIdWith(ilShortLink $other) : bool
    {
        return $other->id == $this->id;
    }

    public function sharesNameWith(ilShortLink $other) : bool
    {
        return strcmp($this->shortLink, $other->shortLink) == 0;
    }

    public function sharesUrlWith(ilShortLink $other) : bool
    {
        return strcmp($this->url, $other->url) == 0;
    }

    public function sharesAPropertyWith(ilShortLink $other) : bool
    {
        return $this->sharesIdWith($other) ||
                $this->sharesNameWith($other) ||
                $this->sharesUrlWith($other);
    }


    public function toString() : string
    {
        return 'id:' . $this->id .
                ' ;sl; ' . $this->shortLink .
                ' ;url; ' . $this->url;
    }

    public function isShortLinkNameValid() : bool
    {
        $shortLinkPattern = '/^([a-z]|[A-Z]|[0-9]|_|-)+$/i';
        return preg_match($shortLinkPattern, $this->shortLink);
    }

    public function isURLValid() : bool
    {
        return filter_var($this->url, FILTER_VALIDATE_URL);
    }

    public function validate() : bool
    {
        $isShortLinkValid = $this->isShortLinkNameValid();
        $isTargetUrlValid = $this->isURLValid();

        return $isShortLinkValid && $isTargetUrlValid;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->shortLink;
    }

    public function getTargetUrl() : string
    {
        return $this->url;
    }

    public function getLastEdited() : DateTimeImmutable
    {
        return $this->lastEdited;
    }
}
