<?php

namespace Model\Entity;

/**
 * Action Log Entity
 *
 * @author Petr Poupě
 */
class ActionLogEntity extends Entity
{

    const JOB_APPLY = "job apply";
    const NEW_CV = "new cv";
    const SAVE_CV = "save cv";
    const DELETE_CV = "delete cv";
    const FORUM_POST = "forum post";
    const READ_BLOG = "read blog";
    const READ_JOB = "read job";

    /** @var int */
    protected $id;

    /** @var \Nette\DateTime */
    protected $datetime;

    /** @var int */
    protected $userId;

    /** @var string */
    protected $action;

    /** @var string */
    protected $name = NULL;

    /** @var mixed[] */
    protected $attrs;

    /** @var bool */
    protected $isSerie = FALSE;

    /** @var int */
    protected $serieId = NULL;

    /** @var string */
    protected $link;

    public function setAttrs($value)
    {
        if (is_array($value)) {
            $this->attrs = $value;
        } else if ($value !== NULL) {
            if (is_array($this->attrs))
                $this->attrs[] = $value;
            else
                $this->attrs = array($value);
        } else {
            $this->attrs = array();
        }
    }

    public function getName()
    {
        if ($this->name === NULL) {
            switch ($this->action) {
                case self::NEW_CV:
                case self::SAVE_CV:
                    if (is_array($this->attrs) && array_key_exists(0, $this->attrs)) {
                        $cv = $this->attrs[0];
                        if ($cv instanceof CvEntity) {
                            $this->name = $cv->name;
                        }
                    }
                    break;
                case self::DELETE_CV:
                    if (is_array($this->attrs) && array_key_exists(1, $this->attrs)) {
                        $this->name = $this->attrs[1];
                    }
                    break;
                case self::FORUM_POST:
                    if (is_array($this->attrs) && array_key_exists(0, $this->attrs)) {
                        $topic = $this->attrs[0];
                        if ($topic instanceof ForumTopicEntity) {
                            $this->name = $topic->name;
                        }
                    }
                    break;
                case self::READ_BLOG:
                    if (is_array($this->attrs) && array_key_exists(0, $this->attrs)) {
                        $blog = $this->attrs[0];
                        if ($blog instanceof PageEntity) {
                            $this->name = \Nette\Utils\Strings::truncate($blog->nameNoTags, 25);
                        }
                    }
                    break;
                case self::READ_JOB:
                    if (is_array($this->attrs) && array_key_exists(0, $this->attrs)) {
                        $job = $this->attrs[0];
                        if ($job instanceof ProfesiaJobEntity) {
                            $this->name = \Nette\Utils\Strings::truncate($job->position, 25);
                        }
                    }
                    break;
            }
        }
        return $this->name;
    }

}

?>
