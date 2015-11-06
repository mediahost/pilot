<?php

namespace Model\Entity;

/**
 * User Entity
 *
 * @author Petr Poupě
 */
class UserEntity extends Entity
{

	const ENGLISH_LEVEL_NATIVE = 1;
	const ENGLISH_LEVEL_L6 = 2;
	const ENGLISH_LEVEL_L5 = 3;
	const ENGLISH_LEVEL_L4 = 4;

	/** @var array */
	static $englishLevelOptions = [
		self::ENGLISH_LEVEL_NATIVE => 'native',
		self::ENGLISH_LEVEL_L6 => 'L6',
		self::ENGLISH_LEVEL_L5 => 'L5',
		self::ENGLISH_LEVEL_L4 => 'L4',
	];

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

	/** @var int */
	protected $englishLevel;

	/** @var int */
	protected $medical;

	/** @var string */
	protected $medicalText;

	/** @var array */
	protected $pilotExperiences = array();

	/** @var array */
	protected $copilotExperiences = array();

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
		return count($this->work_countries) &&
			(count($this->pilotExperiences) + count($this->copilotExperiences)) &&
			$this->medical !== NULL &&
			$this->englishLevel !== NULL;
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
			'Europe' => [
				1 => 'Albania',
				2 => 'Andorra',
				3 => 'Armenia',
				4 => 'Austria',
				5 => 'Azerbaijan',
				6 => 'Belarus',
				7 => 'Belgium',
				8 => 'Bosnia and Herzegovina',
				9 => 'Bulgaria',
				10 => 'Croatia',
				11 => 'Cyprus',
				12 => 'Czech Republic',
				13 => 'Denmark',
				14 => 'Estonia',
				15 => 'Finland',
				16 => 'France',
				17 => 'Georgia',
				18 => 'Germany',
				19 => 'Greece',
				20 => 'Hungary',
				21 => 'Iceland',
				22 => 'Ireland',
				23 => 'Italy',
				24 => 'Latvia',
				25 => 'Liechtenstein',
				26 => 'Lithuania',
				27 => 'Luxembourg',
				28 => 'Macedonia',
				29 => 'Malta',
				30 => 'Moldova',
				31 => 'Monaco',
				32 => 'Montenegro',
				33 => 'Netherlands',
				34 => 'Norway',
				35 => 'Poland',
				36 => 'Portugal',
				37 => 'Romania',
				38 => 'San Marino',
				39 => 'Serbia',
				40 => 'Slovakia',
				41 => 'Slovenia',
				42 => 'Spain',
				43 => 'Sweden',
				44 => 'Switzerland',
				45 => 'Ukraine',
				46 => 'United Kingdom',
				47 => 'Vatican City',
			],
			'Asia' => [
				48 => 'Afghanistan',
				49 => 'Bahrain',
				50 => 'Bangladesh',
				51 => 'Bhutan',
				52 => 'Brunei',
				53 => 'Burma (Myanmar)',
				54 => 'Cambodia',
				55 => 'China',
				56 => 'East Timor',
				57 => 'India',
				58 => 'Indonesia',
				59 => 'Iran',
				60 => 'Iraq',
				61 => 'Israel',
				62 => 'Japan',
				63 => 'Jordan',
				64 => 'Kazakhstan',
				65 => 'Korea, North',
				66 => 'Korea, South',
				67 => 'Kuwait',
				68 => 'Kyrgyzstan',
				69 => 'Laos',
				70 => 'Lebanon',
				71 => 'Malaysia',
				72 => 'Maldives',
				73 => 'Mongolia',
				74 => 'Nepal',
				75 => 'Oman',
				76 => 'Pakistan',
				77 => 'Philippines',
				78 => 'Qatar',
				79 => 'Russian Federation',
				80 => 'Saudi Arabia',
				81 => 'Singapore',
				82 => 'Sri Lanka',
				83 => 'Syria',
				84 => 'Tajikistan',
				85 => 'Thailand',
				86 => 'Turkey',
				87 => 'Turkmenistan',
				88 => 'United Arab Emirates',
				89 => 'Uzbekistan',
				90 => 'Vietnam',
				91 => 'Yemen',
			],
			'N. America' => [
				92 => 'Antigua and Barbuda',
				93 => 'Bahamas',
				94 => 'Barbados',
				95 => 'Belize',
				96 => 'Canada',
				97 => 'Costa Rica',
				98 => 'Cuba',
				99 => 'Dominica',
				100 => 'Dominican Republic',
				101 => 'El Salvador',
				102 => 'Grenada',
				103 => 'Guatemala',
				104 => 'Haiti',
				105 => 'Honduras',
				106 => 'Jamaica',
				107 => 'Mexico',
				108 => 'Nicaragua',
				109 => 'Panama',
				110 => 'Saint Kitts and Nevis',
				111 => 'Saint Lucia',
				112 => 'Saint Vincent and the Grenadines',
				113 => 'Trinidad and Tobago',
				114 => 'United States',
			],
			'S. America' => [
				115 => 'Argentina',
				116 => 'Bolivia',
				117 => 'Brazil',
				118 => 'Chile',
				119 => 'Colombia',
				120 => 'Ecuador',
				121 => 'Guyana',
				122 => 'Paraguay',
				123 => 'Peru',
				124 => 'Suriname',
				125 => 'Uruguay',
				126 => 'Venezuela',
			],
//			'America' => [
//				'Antigua and Barbuda',
//				'Argentina',
//				'Bahamas',
//				'Barbados',
//				'Belize',
//				'Bolivia',
//				'Brazil',
//				'Canada',
//				'Chile',
//				'Colombia',
//				'Costa Rica',
//				'Cuba',
//				'Dominica',
//				'Dominican Republic',
//				'Ecuador',
//				'El Salvador',
//				'Grenada',
//				'Guatemala',
//				'Guyana',
//				'Haiti',
//				'Honduras',
//				'Jamaica',
//				'Mexico',
//				'Nicaragua',
//				'Panama',
//				'Paraguay',
//				'Peru',
//				'Saint Kitts and Nevis',
//				'Saint Lucia',
//				'Saint Vincent and the Grenadines',
//				'Suriname',
//				'Trinidad and Tobago',
//				'United States',
//				'Uruguay',
//				'Venezuela',
//			],
			'Africa' => [
				127 => 'Algeria',
				128 => 'Angola',
				129 => 'Benin',
				130 => 'Botswana',
				131 => 'Burkina',
				132 => 'Burundi',
				133 => 'Cameroon',
				134 => 'Cape Verde',
				135 => 'Central African Republic',
				136 => 'Chad',
				137 => 'Comoros',
				138 => 'Congo',
				139 => 'Congo, Democratic Republic of',
				140 => 'Djibouti',
				141 => 'Egypt',
				142 => 'Equatorial Guinea',
				143 => 'Eritrea',
				144 => 'Ethiopia',
				145 => 'Gabon',
				146 => 'Gambia',
				147 => 'Ghana',
				148 => 'Guinea',
				149 => 'Guinea-Bissau',
				150 => 'Ivory Coast',
				151 => 'Kenya',
				152 => 'Lesotho',
				153 => 'Liberia',
				154 => 'Libya',
				155 => 'Madagascar',
				156 => 'Malawi',
				157 => 'Mali',
				158 => 'Mauritania',
				159 => 'Mauritius',
				160 => 'Morocco',
				161 => 'Mozambique',
				162 => 'Namibia',
				163 => 'Niger',
				164 => 'Nigeria',
				165 => 'Rwanda',
				166 => 'Sao Tome and Principe',
				167 => 'Senegal',
				168 => 'Seychelles',
				169 => 'Sierra Leone',
				170 => 'Somalia',
				171 => 'South Africa',
				172 => 'South Sudan',
				173 => 'Sudan',
				174 => 'Swaziland',
				175 => 'Tanzania',
				176 => 'Togo',
				177 => 'Tunisia',
				178 => 'Uganda',
				179 => 'Zambia',
				180 => 'Zimbabwe',
			],
			'Oceania' => [
				181 => 'Australia',
				182 => 'Fiji',
				183 => 'Kiribati',
				184 => 'Marshall Islands',
				185 => 'Micronesia',
				186 => 'Nauru',
				187 => 'New Zealand',
				188 => 'Palau',
				189 => 'Papua New Guinea',
				190 => 'Samoa',
				191 => 'Solomon Islands',
				192 => 'Tonga',
				193 => 'Tuvalu',
				194 => 'Vanuatu',
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
