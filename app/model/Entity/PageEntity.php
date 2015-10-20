<?php

namespace Model\Entity;

/**
 * Page Entity
 *
 * @author Petr Poupě
 */
class PageEntity extends Entity
{

    const TYPE_MODULE = "module";
    const TYPE_SLIDE = "slide";
    const TYPE_BLOG = "blog";
    const TYPE_OTHER = "other";

    protected $id;
    protected $lang;
    protected $type;
    protected $code;
    protected $order;
    protected $comment;
    protected $image;
    protected $name;
    protected $perex;
    protected $text;
    protected $active = 1;
    protected $link;
    protected $position;
    protected $parentId;

    /** @var \Nette\DateTime */
    protected $date;

    public function getText()
    {
        return $this->text;
    }

    public function getImage()
    {
        return $this->returnNotEmpty($this->image);
    }

    public function getLink()
    {
        return $this->returnNotEmpty($this->link);
    }

    public function getNameNoTags()
    {
        return strip_tags($this->name);
    }

}

?>
