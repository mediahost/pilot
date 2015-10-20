<?php

namespace Model\Entity;

/**
 * @property int $id
 * @property int $userId
 * @property \Nette\DateTime $created
 * @property string $name
 * @property string $originalName
 * @property bool $public
 */
class UserDocEntity extends Entity
{

    const TYPE_PDF = "pdf";
    const TYPE_JPG = "jpg";
    const TYPE_PNG = "png";
    const TYPE_OTHER = NULL;

    /** @var int */
    protected $id = NULL;

    /** @var int */
    protected $userId;

    /** @var \Nette\DateTime */
    protected $created;

    /** @var string */
    protected $name;

    /** @var string */
    protected $originalName;

    /** @var bool */
    protected $public = TRUE;

    public function __construct()
    {
        $this->created = new \Nette\DateTime;
    }

    public function getType()
    {
        $type = self::TYPE_OTHER;
        if (preg_match("~\.([^\.]+)$~i", $this->originalName, $matches)) {
            switch (strtolower($matches[1])) {
                case "jpg":
                case "jpeg":
                    $type = self::TYPE_JPG;
                    break;
                case "png":
                    $type = self::TYPE_PNG;
                    break;
                case "pdf":
                    $type = self::TYPE_PDF;
                    break;
            }
        }
        return $type;
    }

    public function getFilename()
    {
        $filename = $this->originalName;
        if (preg_match("~(.*)(\.[^\.]+)$~i", $this->originalName, $matches)) {
            $filename = $matches[1];
        }
        return $filename;
    }

    public function getExt()
    {
        $ext = NULL;
        if (preg_match("~\.[^\.]+$~i", $this->originalName, $matches)) {
            $ext = $matches[0];
        }
        return $ext;
    }

    public function isImage()
    {
        return in_array($this->getType(), array(self::TYPE_JPG, self::TYPE_PNG));
    }

}
