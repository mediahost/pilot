<?php

namespace Model\Entity;

/**
 * Description of JobEntity
 *
 * @author Radim Křek
 */
class JobEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** vvar string */
    protected $lang;

    /** @var string */
    protected $name;

    /** var string */
    protected $company;

    /** @var int */
    protected $company_id;

    /** @var int */
    protected $type;

    /** @var string */
    protected $ref;

    /** @var string */
    protected $ref_num;

    /** @var string */
    protected $ref_email;

    /** @var string */
    protected $ref_tel;

    /** @var int */
    protected $category;

    /** @var LocationEntity[] */
    protected $locations;

    /** @var string */
    protected $location_text;

    /** @var int */
    protected $salary_from;

    /** @var int */
    protected $salary_to;

    /** @var string */
    protected $currency = 1;

    /** @var string */
    protected $summary;

    /** @var string */
    protected $description;

    /** @var string */
    protected $offers;

    /** @var string */
    protected $requirments;

    /** @var \Nette\DateTime */
    protected $datecreated;

    /** @var int */
    protected $matched_count;

    /** @var bool */
    protected $applyed;

    /** @var int */
    protected $applyed_count;

    /** @var int */
    protected $applyed_completed_count;

    /** @var int */
    protected $shortlisted_count;

    /** @var int */
    protected $invited_count;

    /** @var int */
    protected $process_completed_count;

    /** @var int */
    protected $offer_made_count;

    /** @var string */
    protected $question1;

    /** @var string */
    protected $question2;

    /** @var string */
    protected $question3;

    /** @var string */
    protected $question4;

    /** @var string */
    protected $question5;

	/** @var JobAircraft[] */
	protected $pilotExperiences = array();

	/** @var JobAircraft[] */
	protected $copilotExperiences = array();

    public function __construct($_data)
    {
        $this->commonSet($_data);
    }

    public function hasQuestions()
    {
        foreach (range(1,5) as $counter) {
            if (!empty($this->{'question'.$counter})) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getQuestions()
    {
        $questions = [];
        foreach (range(1,5) as $counter) {
            if (!empty($this->{'question'.$counter})) {
                $questions[] = $this->{'question'.$counter};
            }
        }
        return $questions;
    }

    public function to_array(array $_notIncluded = array())
    {
        $_notIncluded[] = 'locations';
		$_notIncluded[] = 'currencySymbol';
		$_notIncluded[] = 'matched_count';
        $_notIncluded[] = 'applyed';
        $_notIncluded[] = 'applyed_count';
        $_notIncluded[] = 'applyed_completed_count';
        $_notIncluded[] = 'shortlisted_count';
        $_notIncluded[] = 'invited_count';
        $_notIncluded[] = 'offer_made_count';
        $_notIncluded[] = 'process_completed_count';
        $_notIncluded[] = 'pilotExperiences';
        $_notIncluded[] = 'copilotExperiences';
        return parent::to_array($_notIncluded);
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function setLocations($locations)
    {
        $this->locations = $locations;
    }

	public function getJobType()
	{
		$jobTypes = array(
            1 => 'Full-Time',
            2 => 'Part-Time',
            3 => 'Contract',
        );
		return $jobTypes[$this->type];
	}
	
	public function getCurrencySymbol()
	{
		$currencies = self::getCurrencies();
		return $currencies[$this->currency];
	}
	
	static public function getCurrencies()
	{
		return array(
            1 => '€',
            2 => 'Ł',
            3 => '$',
        );
	}

}
