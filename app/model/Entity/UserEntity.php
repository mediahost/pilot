<?php

namespace Model\Entity;

/**
 * User Entity
 *
 * @author Petr Poupě
 */
class UserEntity extends Entity
{

	/** @var int */
	protected $id;

	/** @var array */
	protected $role;

	/** @var bool */
	protected $active = TRUE;

	/** @var \Nette\DateTime */
	protected $created;

	/** @var \Nette\DateTime */
	protected $lastSign;

	/** @var string */
	protected $mail;

	/** @var string */
	protected $username;

	/** @var string */
	protected $firstName;

	/** @var string */
	protected $lastName;

	/** @var string */
	protected $gender;

	/** @var \Nette\DateTime */
	protected $birthday;

	/** @var string */
	protected $lang;

	/** @var int */
	protected $lastCvOpened = NULL;

	/** @var array */
	protected $smartFilterSettings = array();

	/** @var string */
	protected $launchpadVideoUrl;

	/** @var bool */
	protected $chat_notifications = TRUE;

	/** @var string */
	protected $profile_token;

	/** @var string */
	protected $url_github;

	/** @var string */
	protected $url_stackoverflow;

	/** @var string */
	protected $url_linkedin;

	/** @var string */
	protected $url_facebook;

	/** @var string */
	protected $url_twitter;

	/** @var bool */
	protected $is_profile_public;

	/** @var bool */
	protected $freelancer;

	/** @var array */
	protected $work_countries = array();

	/** @var array */
	protected $skills = array();

	/** @var bool */
	protected $visitGuide = FALSE;

	public function __construct($user = NULL)
	{
		if ($user !== NULL) {
			$this->convert($user);
		}
	}

	public function setRole($value = NULL)
	{
		if ($value === NULL) {
			$value = array("user", "contributor");
		}
		if (!is_array($value)) {
			$value = array($value);
		}
		$this->role = $value;
	}

	public function setActive($value = TRUE)
	{
		$this->active = $this->returnBool($value);
	}

	public function setMail($value)
	{
		$this->mail = $value;
	}

	public function setUsername($value)
	{
		$this->username = $value;
	}

	public function setFirstName($value)
	{
		$this->firstName = $value;
	}

	public function setLastName($value)
	{
		$this->lastName = $value;
	}

	public function setGender($value)
	{
		$this->gender = $value;
	}

	public function setBirthday($value)
	{
		$this->birthday = $this->returnDate($value);
	}

	public function setLang($value)
	{
		$this->lang = $value;
	}

	public function setVisitGuide($value = TRUE)
	{
		$this->visitGuide = $value;
		return $this;
	}

	public function getFullName()
	{
		return trim($this->firstName . " " . $this->lastName);
	}
	
	public function isFinished()
	{
		return count($this->skills) &&
				count($this->work_countries);
	}

	/**
	 * Convert another User Entity to this UserEntity
	 * @param type $user
	 * @throws Exception
	 */
	public function convert($user)
	{
		if ($user instanceof AdapterUserEntity) {
			$this->setRole();
			$this->setActive();
			$this->setMail($user->mail);
			$this->setLang($user->lang);
			$this->setBirthday($user->birthday);
			$this->setGender($user->gender);
			$this->setUsername($user->username);
			$this->setFirstName($user->firstName);
			$this->setLastName($user->lastName);
		} else {
			throw new \Exception("This User Format isn't implemented");
		}
	}

	public function toArray()
	{
		$data = array(
			'id' => $this->id,
			'role' => $this->role,
			'active' => $this->active,
			'created' => $this->created,
			'last_sign' => $this->lastSign,
			'mail' => $this->mail,
			'first_name' => $this->firstName,
			'last_name' => $this->lastName,
			'gender' => $this->gender,
			'birthday' => $this->birthday,
			'lang' => $this->lang,
			'smart_filter_settings' => $this->smartFilterSettings,
			'launchpad_video_url' => $this->launchpadVideoUrl,
			'chat_notifications' => $this->chat_notifications,
			'profile_token' => $this->profile_token,
			'freelancer' => $this->freelancer,
			'work_countries' => $this->work_countries,
		);
		return $data;
	}

	public static function helperGetLocalities($flat = FALSE)
	{
		$countries = [
			'European Union' => [
				2 => 'Austria',
				3 => 'Belgium',
				4 => 'Bulgaria',
				5 => 'Croatia',
				6 => 'Cyprus',
				7 => 'Czech Republic',
				8 => 'Denmark',
				9 => 'Estonia',
				10 => 'Finland',
				11 => 'France',
				12 => 'Germany',
				13 => 'Greece',
				14 => 'Hungary',
				15 => 'Ireland',
				16 => 'Italy',
				17 => 'Latvia',
				18 => 'Lithuania',
				19 => 'Luxembourg',
				20 => 'Malta',
				21 => 'Netherlands',
				22 => 'Poland',
				23 => 'Portugal',
				24 => 'Romania',
				25 => 'Slovakia',
				26 => 'Slovenia',
				27 => 'Spain',
				28 => 'Sweden',
				29 => 'United Kingdom',
			],
			'Rest of Europe' => [
				30 => 'Albania',
				31 => 'Armenia',
				32 => 'Azerbaijan',
				33 => 'Belarus',
				34 => 'Bosnia & Herzegovina',
				35 => 'Georgia',
				36 => 'Iceland',
				37 => 'Kazakhstan',
				38 => 'Macedonia',
				39 => 'Moldova',
				40 => 'Montenegro',
				41 => 'Russia',
				42 => 'Serbia',
				43 => 'Switzerland',
				44 => 'Turkey',
				45 => 'Ukraine',
			],
			'Middle East' => [
				46 => 'Israel',
				47 => 'United Arab Emirate',
				48 => 'Saudi Arabia',
				49 => 'Qatar',
			],
			'North America' => [
				50 => 'USA',
				51 => 'Canada',
			],
		];
		if ($flat) {
			$flatArray = [];
			foreach ($countries as $countryId => $country) {
				if (is_array($country)) {
					foreach ($country as $countryItemId => $countryItem) {
						$flatArray[$countryItemId] = $countryItem;
					}
				} else {
					$flatArray[$countryId] = $country;
				}
			}
			return $flatArray;
		} else {
			return $countries;
		}
	}

}
