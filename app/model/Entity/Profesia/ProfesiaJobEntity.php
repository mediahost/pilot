<?php

namespace Model\Entity;

/**
 * Profesia Job Entity
 *
 * @author Petr Poupě
 */
class ProfesiaJobEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $importedFrom;

    /** @var int */
    protected $externalid;

    /** @var string */
    protected $position;

    /** @var string */
    protected $refnr;

    /** @var \Nette\DateTime */
    protected $datecreated;

    /** @var string */
    protected $offerlocationDescription;

    /** @var string */
    protected $jobtasks;

    /** @var int */
    protected $minsalary;

    /** @var int */
    protected $maxsalary;

    /** @var int */
    protected $currencyId;

    /** @var string */
    protected $currency;

    /** @var string */
    protected $salaryPeriod;

    /** @var string */
    protected $startdate;

    /** @var string */
    protected $otherbenefits;

    /** @var string */
    protected $noteforcandidate;

    /** @var string */
    protected $languageconjuction;

    /** @var bool */
    protected $validforgraduate;

    /** @var string */
    protected $prerequisites;

    /** @var string */
    protected $shortcompanycharacteristics;

    /** @var int */
    protected $contactperson;

    /** @var string */
    protected $contactname;

    /** @var string */
    protected $contactemail;

    /** @var string */
    protected $contactphone;

    /** @var string */
    protected $contactaddress;

    /** @var string */
    protected $offerlanguage;

    /** @var int */
    protected $customcategory;

    /** @var int[] */
    protected $offerLocations;

    /** @var string[] */
    protected $offerLocationNames;

    /** @var int[] */
    protected $jobTypes;

    /** @var string[] */
    protected $jobTypeNames;

    /** @var int[] */
    protected $educationLevels;

    /** @var string[] */
    protected $educationLevelNames;

    /** @var type */
    protected $offerSkills;

    /** @var string[] */
    protected $offerSkillNames;

    /** @var string[] */
    protected $offerSkillLevels;

    /** @var int[] */
    protected $offerCategories;

    /** @var string[] */
    protected $offerCategoryNames;

    /** @var type */
    protected $offerCategoryPositions;

    /** @var string[] */
    protected $offerCategoryPositionCategories;

    /** @var string[] */
    protected $offerCategoryPositionPositions;

    /** @var string[] */
    protected $tags;

    public function setOfferSkills($value)
    {
        $this->removeEmptyArrItems($this->offerSkills);
        if ($value === NULL)
            $this->offerSkills = array();
        else if (array_key_exists("id", $value) && array_key_exists("level", $value))
            $this->offerSkills[] = $value;
    }

    public function setOfferCategoryPositions($value)
    {
        $this->removeEmptyArrItems($this->offerCategoryPositions);
        if ($value === NULL)
            $this->offerCategoryPositions = array();
        else if (array_key_exists("category", $value) && array_key_exists("position", $value))
            $this->offerCategoryPositions[] = $value;
    }

    private function removeEmptyArrItems(&$source)
    {
        if (is_array($source)) {
            foreach ($source as $key => $item) {
                if (empty($item)) {
                    unset($source[$key]);
                }
            }
        }
        else
            return array();
    }

    public function getOfferLocationFirstName()
    {
        if ($this->offerLocationNames !== NULL) {
            $names = array_values($this->offerLocationNames);
            return array_shift($names);
        }
        return NULL;
    }
    
    public function getTagsCount()
    {
        return count($this->tags);
    }

}

?>
