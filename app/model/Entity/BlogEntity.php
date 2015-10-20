<?php

namespace Model\Entity;

/**
 * Blog Entity
 *
 * @author Petr Poupě
 */
class BlogEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $lang;

    /** @var string */
    protected $url;

    /** @var string */
    protected $image;

    /** @var string */
    protected $name;

    /** @var string */
    protected $perex;

    /** @var string */
    protected $text;

    /** @var bool */
    protected $active = 1;

    /** @var \Nette\DateTime */
    protected $createDate;

    /** @var \Nette\DateTime */
    protected $publishDate;

    /** @var int */
    protected $read = 0;

    /** @var int[] */
    protected $categoryIds = array();

    /** @var string[] */
    protected $tags = array();
    
    public function setTags($value)
    {
        if (is_string($value)) {
            $tags = preg_split("~[,\s]~", $value, NULL, PREG_SPLIT_NO_EMPTY);
        } else if (is_array($value)) {
            $tags = $value;
        } else {
            $tags = array($value);
        }
        $this->tags = $tags;
    }
    
    public function getTags()
    {
        return array_unique($this->tags);
    }
    
    public function getTagsString($separator = ", ")
    {
        $tags = $this->getTags();
        return \CommonHelpers::concatStrings($separator, $tags);
    }
    
    public function getDate()
    {
        return $this->publishDate;
    }

    public function getNameNoTags()
    {
        return strip_tags($this->name);
    }

    public function setUrl($value)
    {
        $this->url = \Nette\Utils\Strings::webalize($value);
    }

}

?>
