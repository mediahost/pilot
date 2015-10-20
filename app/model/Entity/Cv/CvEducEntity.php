<?php

namespace Model\Entity;

/**
 * CV Education Entity
 *
 * @author Petr Poupě
 */
class CvEducEntity extends Entity
{

    public $id;

    /** @var \Nette\DateTime */
    protected $from;

    /** @var \Nette\DateTime */
    protected $to;
    public $title;
    public $subjects;
    public $institName;
    public $institCity;
    public $institCountry;

    public static function helperGetInstitution(CvEducEntity $educ)
    {
        $instit = $educ->institName;
        $city = $educ->institCity;
        $country = $educ->institCountry;

        return "{$instit}, {$city}, {$country}";
    }

    public static function helperGetBasicView(CvEducEntity $educ)
    {
        $instit = self::helperGetInstitution($educ);
        $title = $educ->title;

        return $instit . ($title === NULL ? "" : ", {$title}");
    }

    public static function helperGetDates(CvEducEntity $educ, \Nette\Localization\ITranslator $translator)
    {
        $current = $translator->translate("Current study");
        $now = $translator->translate("till now");
        $format = "d.m.Y";

        $from = \Nette\DateTime::from($educ->from)->format($format);
        $to = \Nette\DateTime::from($educ->to)->format($format);

        if ($educ->from === NULL)
            return $current;
        else if ($educ->to === NULL)
            return "{$from} - {$now}";
        else
            return "{$from} - {$to}";
    }

    public static function cmp(CvEducEntity $first, CvEducEntity $second)
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
