<?php

namespace Model\Entity;

/**
 * CV Work Entity
 *
 * @author Petr Poupě
 */
class CvWorkEntity extends Entity
{

    const TYPE_WORK = "work";
    const TYPE_OTHER = "other";

    public $id;
    public $type;
    public $company;

    /** @var \Nette\DateTime */
    protected $from;

    /** @var \Nette\DateTime */
    protected $to;
    public $position;
    public $activities;
    public $achievment;
    public $refPublic;
    public $refName;
    public $refPosition;
    public $refPhone;
    public $refEmail;
    public $file;

    public function setType($value)
    {
        switch ($value) {
            // check allowed values
            case self::TYPE_WORK:
            case self::TYPE_OTHER:
                break;
            default:
                $value = self::TYPE_OTHER;
                break;
        }
        $this->type = $value;
    }

    public static function helperGetDates(CvWorkEntity $work, \Nette\Localization\ITranslator $translator)
    {
        $current = $translator->translate("Current job");
        $now = $translator->translate("till now");
        $format = "d.m.Y";

        $from = \Nette\DateTime::from($work->from)->format($format);
        $to = \Nette\DateTime::from($work->to)->format($format);

        if ($work->from === NULL)
            return $current;
        else if ($work->to === NULL)
            return "{$from} - {$now}";
        else
            return "{$from} - {$to}";
    }

    public static function helperGetReference(CvWorkEntity $work, $lang = NULL)
    {
        switch ($lang) {
            case "cs":
            case "sk":
            case "en":
            default:
                $name = \CommonHelpers::concatStrings(", ", $work->company, $work->refName, $work->refPosition);
                $contact = \CommonHelpers::concatStrings(", ", $work->refPhone, $work->refEmail);
                $reference = \CommonHelpers::concatStrings("\n", $name, $contact, " ");
                break;
        }
        return $reference;
    }

    public static function helperGetReferences(array $references, $lang = NULL)
    {
        $refrees = NULL;
        foreach ($references as $reference) {
            if ($reference->refName !== NULL)
                $refrees = \CommonHelpers::concatStrings("\n", $refrees, self::helperGetReference($reference, $lang));
        }
        return $refrees;
    }

    public static function cmp(CvWorkEntity $first, CvWorkEntity $second)
    {
        $same = 0;
        $low = -1; // UP; to start of array
        $high = 1; // DOWN; to end of array

        $toFrst = $first->to;
        $toScnd = $second->to;
        $fromFrst = $first->from;
        $fromScnd = $second->from;

        if ($toFrst == $toScnd) {
            if ($fromFrst == $fromScnd) {
                return $same;
            } else if ($fromFrst === NULL) {
                return $low;
            } else if ($fromScnd === NULL) {
                return $high;
            } else {
                return ($fromFrst > $fromScnd) ? $low : $high;
            }
        } else if ($toFrst === NULL) {
            return $low;
        } else if ($toScnd === NULL) {
            return $high;
        } else {
            return ($toFrst > $toScnd) ? $low : $high;
        }
    }

}

?>
