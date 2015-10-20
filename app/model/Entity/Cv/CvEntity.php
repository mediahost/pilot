<?php

namespace Model\Entity;

/**
 * CV Entity
 *
 * @author Petr Poupě
 * @author Marek Šneberger
 */
class CvEntity extends Entity
{

    const GENDER_DEFAULT = 3;

    public $id;
    public $userId;
    public $name = "new CV";
    public $createDate;
    public $changeDate;
    public $lastStep = 1; // last opened step
    public $photo;
    public $showPhoto;
    public $video;
    public $title;
    public $firstname;
    public $middlename;
    public $surname;
    public $degreeBefore;
    public $degreeAfter;
    public $gender;

    /** @var \Nette\DateTime */
    protected $birthday;
    public $nationality;
    public $address;
    public $house;
    public $zipcode;
    public $city;
    public $county;
    public $country;
    public $phone;
    public $email;
    public $isDefault = FALSE;
    public $showCareerObjective;
    public $showDesiredEmployment;
    public $careerObjective;
    public $sector;
    public $jobPosition;
    /** @var \Nette\DateTime */
    protected $avaliblityFrom;
    public $salaryPublic;
    public $salaryFrom;
    public $salaryTo;
    public $showSummary;
    public $careerSummary;
    public $motherLanguage;
    public $skillSocial;
    public $skillOrganise;
    public $skillTechnical;
    public $skillComputer;
    public $skillArtistic;
    public $skillOther;
    public $licenses;
    public $info;
    public $templateName = "default";

    /** @var string */
    protected $fulltext;
    public $public;
    public $isGraduated;
    private $inEu;
    public $itSkills = array();
    public $it_skills;
    public $itSkillsForRendering;
    /** @var string */
    protected $otherItSkills;
    private $works = array();
    private $educations = array();
    private $languages = array();

    public function setInEu($bool) {
        $this->inEu = $bool;
    }

    public function isInEu() {
        return self::isEuCountry($this->nationality);
    }

    public function isCompleted() {
        return $this->firstname &&
                $this->surname &&
                $this->email &&
                $this->photo;
    }

    public function isFinished() {
        return $this->firstname &&
                $this->surname &&
                $this->email &&
                $this->birthday &&
                $this->city &&
                $this->country &&
                $this->phone &&
                $this->photo;
    }

    public function getRequirmentsUncompletedCount()
    {
        return $this->getRequirementsList(TRUE);
    }

    public function getRequirementsList($getCountOnly = FALSE)
    {
        $requirments = array(
            1 => array('First name is required', (bool) $this->firstname),
            2 => array('Surname is required', (bool) $this->surname),
            3 => array('Email is required', (bool) $this->email),
            4 => array('Photo is required', (bool) $this->photo),
        );
        if ($getCountOnly) {
            $uncompleted = 0;
            foreach ($requirments as $item) {
                if (!$item) {
                    $uncompleted++;
                }
            }
            return $uncompleted;
        }
        return $requirments;
    }

    public function setCompleted($completed) {

    }

    public function getFulltext()
    {
        $strings = array(
            $this->firstname,
            $this->middlename,
            $this->surname,
        );
        foreach ($this->works as $work) {
            $strings[] = $work->company;
        }
        if (is_array($this->itSkills)) {
            foreach ($this->itSkills as $skill) {
                if (isset($skill->name) && isset($skill->category)) {
                    $strings[] = $skill->name;
                    $strings[] = $skill->category;
                }
            }
        }
        return \CommonHelpers::concatStrings(" ", $strings);
    }

    public function hasPhoto()
    {
        return (bool) !($this->photo === NULL);
    }

    /**
     * @param array $skill
     *
     * @return $this
     */
    public function addSkill($skill)
    {
        $this->itSkills = $skill;
        return $this;
    }

    public function getSkills() {
        return $this->itSkills;
    }

    /**
     * @param array $skills
     */
    public function addSkillsForRendering($skills)
    {
        $this->itSkillsForRendering = $skills;
    }

    public function setTitle($value)
    {
        $this->title = self::titles($value);
    }

    public function setGender($value)
    {
        $this->gender = self::genders($value);
    }

    public function setLicenses($value)
    {
        if (is_array($value)) {
            $this->licenses = array();
            foreach ($value as $item) {
                $this->licenses[$item] = self::licenses($item);
            }
        } else {
            $this->licenses = "...";
        }
    }

    public function setSector($value)
    {
        if (is_array($value)) {
            $this->sector = array();
            foreach ($value as $item) {
                $this->sector[$item] = self::sectors($item);
            }
        } else {
            $this->sector = NULL;
        }
    }

    public function setJobPosition($value)
    {
        if (is_array($value)) {
            $positions = array();
            foreach ($value as $item) {
                if (!empty($item)) {
                    $positions[] = $item;
                }
            }
            $this->jobPosition = $positions;
        } else if (is_string($value)) {
            $this->setJobPosition(preg_split("@\s*[,\n]\s*@", $value));
        } else {
            $this->jobPosition = NULL;
        }
    }

    public function getJobPosition($separator = ", ")
    {
        $position = "";
        if (is_array($this->jobPosition)) {
            foreach ($this->jobPosition as $part) {
                $position .= ($position === "" ? "" : $separator) . $part;
            }
        }
        return $position;
    }

    public function setNationality($value)
    {
        $this->nationality = self::nationalities($value);
    }

    public function addWork(CvWorkEntity $work)
    {
        $this->works[$work->id] = $work;
    }

    public function deleteWork($workId)
    {
        unset($this->works[$workId]);
    }

    public function deleteWorks()
    {
        $this->works = array();
    }

    public function getWorks($type = FALSE)
    {
        uasort($this->works, "\Model\Entity\CvWorkEntity::cmp");
        $works = array();
        switch ($type) {
            case CvWorkEntity::TYPE_WORK:
            case CvWorkEntity::TYPE_OTHER:
                foreach ($this->works as $work) {
                    if ($work->type == $type) {
                        $works[$work->id] = $work;
                    }
                }
                break;

            default:
                $works = $this->works;
                break;
        }
        return $works;
    }

    public function getWork($workId)
    {
        if (array_key_exists($workId, $this->works)) {
            return $this->works[$workId];
        } else {
            return new CvWorkEntity();
        }
    }

    public function addEducation(CvEducEntity $educ)
    {
        $this->educations[$educ->id] = $educ;
    }

    public function deleteEducation($educId)
    {
        unset($this->educations[$educId]);
    }

    public function deleteEducations()
    {
        $this->educations = array();
    }

    public function getEducations()
    {
        uasort($this->educations, "\Model\Entity\CvEducEntity::cmp");
        return $this->educations;
    }

    public function getEducation($educId)
    {
        if (array_key_exists($educId, $this->educations)) {
            return $this->educations[$educId];
        } else {
            return new CvEducEntity();
        }
    }

    public function addLanguage(CvLangEntity $lang)
    {
        $this->languages[$lang->id] = $lang;
    }

    public function deleteLanguage($langId)
    {
        unset($this->languages[$langId]);
    }

    public function deleteLanguages()
    {
        $this->languages = array();
    }

    public function getLanguages()
    {
        uasort($this->languages, "\Model\Entity\CvLangEntity::cmp");
        return $this->languages;
    }

    public function getLanguage($langId)
    {
        if (array_key_exists($langId, $this->languages)) {
            return $this->languages[$langId];
        } else {
            return new CvLangEntity();
        }
    }

    public function getFullName($noTitle = FALSE)
    {
        $fullName = "";
        $separator = " ";
        // Before Name
        if ($this->degreeBefore !== NULL) {
            $fullName .= $this->degreeBefore;
        } elseif (!$noTitle) {
            $fullName .= self::titles($this->title);
        }
        // Name
        if ($this->firstname !== NULL) {
            $fullName .= $separator . $this->firstname;
        }
        if ($this->middlename !== NULL) {
            $fullName .= $separator . $this->middlename;
        }
        if ($this->surname !== NULL) {
            $fullName .= $separator . $this->surname;
        }
        // After Name
        if ($fullName !== "" && $this->degreeAfter !== NULL) {
            $fullName .= ", " . $this->degreeAfter;
        }

        return trim($fullName);
    }

	public function importProfileData(CvEntity $source)
	{
		$this->photo = $source->photo;
		$this->showPhoto = $source->showPhoto;
		$this->video = $source->video;
		$this->title = $source->title;
		$this->firstname = $source->firstname;
		$this->middlename = $source->middlename;
		$this->surname = $source->surname;
		$this->degreeBefore = $source->degreeBefore;
		$this->degreeAfter = $source->degreeAfter;
		$this->gender = $source->gender;
		$this->birthday = $source->birthday;
		$this->nationality = $source->nationality;
		$this->address = $source->address;
		$this->house = $source->house;
		$this->zipcode = $source->zipcode;
		$this->city = $source->city;
		$this->county = $source->county;
		$this->country = $source->country;
		$this->phone = $source->phone;
		$this->email = $source->email;
	}

    public static function steps($id = FALSE)
    {
        $steps = array(
//            1 => "Personal Details",
            5 => "Work Experience",
            7 => "Education & Training",
            11 => "IT Skills",
            6 => "Other Experience",
            9 => "Personal Skills and Competence",
            8 => "Language Skills",
            2 => "Career Objective",
            3 => "Desired Employment",
            4 => "Career Summary",
            10 => "Additional Information",
        );

        if ($id === FALSE) {
            return $steps;
        } else {
            if (array_key_exists($id, $steps)) {
                return $steps[$id];
            } else {
                return NULL;
            }
        }
    }

    public static function titles($id = FALSE)
    {
        $titles = array(
            'mr' => "Mr.",
            'mrs' => "Mrs.",
            'ms' => "Ms.",
        );

        if ($id === FALSE) {
            return $titles;
        } else {
            if (array_key_exists($id, $titles)) {
                return $titles[$id];
            } else {
                return NULL;
            }
        }
    }

    public static function genders($id = FALSE)
    {
        $genders = array(
            1 => "Male",
            2 => "Female",
            3 => "Not disclosed",
        );

        if ($id === FALSE) {
            return $genders;
        } else {
            if (array_key_exists($id, $genders)) {
                return $genders[$id];
            } else {
                return NULL;
            }
        }
    }

    public static function licenses($id = FALSE)
    {
        $licenses = array(
            'a' => "A",
            'a1' => "A1",
            'b' => "B",
            'b1' => "B1",
            'be' => "BE",
            'c' => "C",
            'c1' => "C1",
            'ce' => "CE",
            'c1e' => "C1E",
            'd' => "D",
            'd1' => "D1",
            'de' => "DE",
            'd1e' => "D1E",
        );

        if ($id === FALSE) {
            return $licenses;
        } else {
            if (array_key_exists($id, $licenses)) {
                return $licenses[$id];
            } else {
                return NULL;
            }
        }
    }

    public static function sectors($id = FALSE)
    {
        $sectors = array(
            1 => "Accounts",
            2 => "Administration",
            3 => "Advertising",
            4 => "Architecture",
            5 => "Automotive",
            6 => "Chemical Engineering",
            7 => "Construction and Engineering",
            8 => "Education and Education management",
            9 => "Finance",
            10 => "Food processing",
            11 => "General Business Management",
            12 => "Healthcare",
            13 => "Hospitality",
            14 => "Human Resources",
            15 => "IT",
            16 => "Industrial",
            17 => "Manufacturing and Automation",
            18 => "Marketing",
            19 => "Media",
            20 => "Nursing and social care",
            21 => "Pharmaceutical",
            22 => "Property",
            23 => "Public Relations",
            24 => "Real Estate and Property Management",
            25 => "Retail",
            26 => "Sales and business development",
            27 => "Transport and logistics",
        );

        if ($id === FALSE) {
            return $sectors;
        } else {
            if (array_key_exists($id, $sectors)) {
                return $sectors[$id];
            } else {
                return NULL;
            }
        }
    }

    public static function getEuContries() {
        return array(
            "AT",
            "BE",
            "BG",
            "HR",
            "CY",
            "CZ",
            "DK",
            "EE",
            "FI",
            "FR",
            "DE",
            "GR",
            "HU",
            "IE",
            "IT",
            "LV",
            "LT",
            "LU",
            "MT",
            "NL",
            "PL",
            "PT",
            "RO",
            "SK",
            "SI",
            "ES",
            "SE",
            "GB",
        );
    }

    public static function isEuCountry($countryCode) {
        return in_array($countryCode, self::getEuContries());
    }

    public static function nationalities($id = FALSE)
    {
        $countries = array(
            "AD" => "Andorra",
            "AE" => "United Arab Emirates",
            "AF" => "Afghanistan",
            "AG" => "Antigua and Barbuda",
            "AI" => "Anguilla",
            "AL" => "Albania",
            "AM" => "Armenia",
            "CW" => "Curcao",
            "AO" => "Angola",
            "AQ" => "Antarctica",
            "AR" => "Argentina",
            "AS" => "American Samoa",
            "AT" => "Austria",
            "AU" => "Australia",
            "AW" => "Aruba",
            "AZ" => "Azerbaijan",
            "BA" => "Bosnia and Herzegovina",
            "BB" => "Barbados",
            "BD" => "Bangladesh",
            "BE" => "Belgium",
            "BF" => "Burkina Faso",
            "BG" => "Bulgaria",
            "BH" => "Bahrain",
            "BI" => "Burundi",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BN" => "Brunei Darussalam",
            "BO" => "Bolivia",
            "BR" => "Brazil",
            "BS" => "Bahamas",
            "BT" => "Bhutan",
            "BV" => "Bouvet Island",
            "BW" => "Botswana",
            "BY" => "Belarus",
            "BZ" => "Belize",
            "CA" => "Canada",
            "CC" => "Cocos (Keeling) Islands",
            "CD" => "Congo, The Democratic Republic of the",
            "CF" => "Central African Republic",
            "CG" => "Congo",
            "CH" => "Switzerland",
            "CI" => "Cote D'Ivoire",
            "CK" => "Cook Islands",
            "CL" => "Chile",
            "CM" => "Cameroon",
            "CN" => "China",
            "CO" => "Colombia",
            "CR" => "Costa Rica",
            "CU" => "Cuba",
            "CV" => "Cape Verde",
            "CX" => "Christmas Island",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DE" => "Germany",
            "DJ" => "Djibouti",
            "DK" => "Denmark",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "DZ" => "Algeria",
            "EC" => "Ecuador",
            "EE" => "Estonia",
            "EG" => "Egypt",
            "EH" => "Western Sahara",
            "ER" => "Eritrea",
            "ES" => "Spain",
            "ET" => "Ethiopia",
            "FI" => "Finland",
            "FJ" => "Fiji",
            "FK" => "Falkland Islands (Malvinas)",
            "FM" => "Micronesia, Federated States of",
            "FO" => "Faroe Islands",
            "FR" => "France",
            "SX" => "Sint Maarten (Dutch part)",
            "GA" => "Gabon",
            "GB" => "United Kingdom",
            "GD" => "Grenada",
            "GE" => "Georgia",
            "GF" => "French Guiana",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GL" => "Greenland",
            "GM" => "Gambia",
            "GN" => "Guinea",
            "GP" => "Guadeloupe",
            "GQ" => "Equatorial Guinea",
            "GR" => "Greece",
            "GS" => "South Georgia and the South Sandwich Islands",
            "GT" => "Guatemala",
            "GU" => "Guam",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HK" => "Hong Kong",
            "HM" => "Heard Island and McDonald Islands",
            "HN" => "Honduras",
            "HR" => "Croatia",
            "HT" => "Haiti",
            "HU" => "Hungary",
            "ID" => "Indonesia",
            "IE" => "Ireland",
            "IL" => "Israel",
            "IN" => "India",
            "IO" => "British Indian Ocean Territory",
            "IQ" => "Iraq",
            "IR" => "Iran, Islamic Republic of",
            "IS" => "Iceland",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JO" => "Jordan",
            "JP" => "Japan",
            "KE" => "Kenya",
            "KG" => "Kyrgyzstan",
            "KH" => "Cambodia",
            "KI" => "Kiribati",
            "KM" => "Comoros",
            "KN" => "Saint Kitts and Nevis",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KY" => "Cayman Islands",
            "KZ" => "Kazakhstan",
            "LA" => "Lao People's Democratic Republic",
            "LB" => "Lebanon",
            "LC" => "Saint Lucia",
            "LI" => "Liechtenstein",
            "LK" => "Sri Lanka",
            "LR" => "Liberia",
            "LS" => "Lesotho",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "LV" => "Latvia",
            "LY" => "Libya",
            "MA" => "Morocco",
            "MC" => "Monaco",
            "MD" => "Moldova, Republic of",
            "MG" => "Madagascar",
            "MH" => "Marshall Islands",
            "MK" => "Macedonia",
            "ML" => "Mali",
            "MM" => "Myanmar",
            "MN" => "Mongolia",
            "MO" => "Macau",
            "MP" => "Northern Mariana Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MS" => "Montserrat",
            "MT" => "Malta",
            "MU" => "Mauritius",
            "MV" => "Maldives",
            "MW" => "Malawi",
            "MX" => "Mexico",
            "MY" => "Malaysia",
            "MZ" => "Mozambique",
            "NA" => "Namibia",
            "NC" => "New Caledonia",
            "NE" => "Niger",
            "NF" => "Norfolk Island",
            "NG" => "Nigeria",
            "NI" => "Nicaragua",
            "NL" => "Netherlands",
            "NO" => "Norway",
            "NP" => "Nepal",
            "NR" => "Nauru",
            "NU" => "Niue",
            "NZ" => "New Zealand",
            "OM" => "Oman",
            "PA" => "Panama",
            "PE" => "Peru",
            "PF" => "French Polynesia",
            "PG" => "Papua New Guinea",
            "PH" => "Philippines",
            "PK" => "Pakistan",
            "PL" => "Poland",
            "PM" => "Saint Pierre and Miquelon",
            "PN" => "Pitcairn Islands",
            "PR" => "Puerto Rico",
            "PS" => "Palestinian Territory",
            "PT" => "Portugal",
            "PW" => "Palau",
            "PY" => "Paraguay",
            "QA" => "Qatar",
            "RE" => "Reunion",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "SA" => "Saudi Arabia",
            "SB" => "Solomon Islands",
            "SC" => "Seychelles",
            "SD" => "Sudan",
            "SE" => "Sweden",
            "SG" => "Singapore",
            "SH" => "Saint Helena",
            "SI" => "Slovenia",
            "SJ" => "Svalbard and Jan Mayen",
            "SK" => "Slovakia",
            "SL" => "Sierra Leone",
            "SM" => "San Marino",
            "SN" => "Senegal",
            "SO" => "Somalia",
            "SR" => "Suriname",
            "ST" => "Sao Tome and Principe",
            "SV" => "El Salvador",
            "SY" => "Syrian Arab Republic",
            "SZ" => "Swaziland",
            "TC" => "Turks and Caicos Islands",
            "TD" => "Chad",
            "TF" => "French Southern Territories",
            "TG" => "Togo",
            "TH" => "Thailand",
            "TJ" => "Tajikistan",
            "TK" => "Tokelau",
            "TM" => "Turkmenistan",
            "TN" => "Tunisia",
            "TO" => "Tonga",
            "TL" => "Timor-Leste",
            "TR" => "Turkey",
            "TT" => "Trinidad and Tobago",
            "TV" => "Tuvalu",
            "TW" => "Taiwan",
            "TZ" => "Tanzania, United Republic of",
            "UA" => "Ukraine",
            "UG" => "Uganda",
            "UM" => "United States Minor Outlying Islands",
            "US" => "United States",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VA" => "Holy See (Vatican City State)",
            "VC" => "Saint Vincent and the Grenadines",
            "VE" => "Venezuela",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.S.",
            "VN" => "Vietnam",
            "VU" => "Vanuatu",
            "WF" => "Wallis and Futuna",
            "WS" => "Samoa",
            "YE" => "Yemen",
            "YT" => "Mayotte",
            "RS" => "Serbia",
            "ZA" => "South Africa",
            "ZM" => "Zambia",
            "ME" => "Montenegro",
            "ZW" => "Zimbabwe",
            "A1" => "Anonymous Proxy",
            "A2" => "Satellite Provider",
            "O1" => "Other",
            "AX" => "Aland Islands",
            "GG" => "Guernsey",
            "IM" => "Isle of Man",
            "JE" => "Jersey",
            "BL" => "Saint Barthelemy",
            "MF" => "Saint Martin",
            "BQ" => "Bonaire, Saint Eustatius and Saba",
        );
        asort($countries);

        if ($id === FALSE) {
            return $countries;
        } else {
            if (array_key_exists($id, $countries)) {
                return $countries[$id];
            } else {
                return NULL;
            }
        }
    }

    /** HELPERS * */
    public static function helperGetFullName(CvEntity $cv)
    {
        $fullName = "";
        $separator = " ";
        // Before Name
        if ($cv->degreeBefore !== NULL) {
            $fullName .= $cv->degreeBefore;
        } else {
            $fullName .= $cv->titles($cv->title);
        }
        // Name
        if ($cv->firstname !== NULL) {
            $fullName .= $separator . $cv->firstname;
        }
        if ($cv->middlename !== NULL) {
            $fullName .= $separator . $cv->middlename;
        }
        if ($cv->surname !== NULL) {
            $fullName .= $separator . $cv->surname;
        }
        // After Name
        if ($fullName !== "" && $cv->degreeAfter !== NULL) {
            $fullName .= ", " . $cv->degreeAfter;
        }

        return trim($fullName);
    }

    public static function helperGetAddress(CvEntity $cv, $lang = NULL, $onlyInline = FALSE)
    {
        $street = $cv->address;
        $house = $cv->house;
        $zipcode = $cv->zipcode;
        $city = $cv->city;
        $county = $cv->county;
        $country = $cv->country;

        $lineSeparator = $onlyInline ? ", " : "\n";

        switch ($lang) {
            case "cs":
            case "sk":
                $street = \CommonHelpers::concatTwoStrings($street, $house, " ");
                $city = \CommonHelpers::concatTwoStrings($zipcode, $city, " ");
                $country = \CommonHelpers::concatTwoStrings($county, $country, ", ");
                $address = \CommonHelpers::concatStrings($lineSeparator, $street, $city, $country);
                break;

            case "en":
            default:
                $street = \CommonHelpers::concatTwoStrings($house, $street, " ");
                $city = \CommonHelpers::concatTwoStrings($city, $zipcode, ", ");
                $country = \CommonHelpers::concatTwoStrings($county, $country, ", ");
                $address = \CommonHelpers::concatStrings($lineSeparator, $street, $city, $country);
                break;
        }
        return trim($address);
    }

    public static function helperGetYearsOld(CvEntity $cv)
    {
        $now = new \DateTime;
        $birthday = $cv->birthday;
        $diff = date_diff($now, $birthday);
        return $diff->y;
    }

    public static function helperGetNationality($nationality)
    {
        return self::nationalities($nationality);
    }

    public static function helperGetSector($cvSector, $separator)
    {
        $sector = "";
        foreach ($cvSector as $part) {
            $sector .= ($sector === "" ? "" : $separator) . self::sectors($part);
        }
        return $sector;
    }

    public static function helperGetLicenses($cvLincenses, $separator)
    {
        $licenses = "";
        foreach ($cvLincenses as $part) {
            $licenses .= ($licenses === "" ? "" : $separator) . self::licenses($part);
        }
        return $licenses;
    }

    public static function helperGetLanguage($in)
    {
        if ($in instanceof CvEntity) {
            return CvLangEntity::languages($in->motherLanguage);
        } else {
            return CvLangEntity::languages((string) $in);
        }
    }

    public function getSortedProgramingLanguages($limit = NULL)
    {
        $prgLanguages = [];
        foreach ($this->itSkills as $skill) {
            if ($skill->skill_category_id == 1) { // Programming languages
                $skill['quality'] = $this->getSkillQuality($skill);
                $prgLanguages[] = $skill;
            }
        }
        if ($limit && count($prgLanguages) > $limit) {
            $prgLanguages = array_slice($prgLanguages, 0, $limit);
        }
        return $prgLanguages;
    }

    public function getSortedFrameworks($limit = NULL)
    {
        $frameworks = [];
        foreach ($this->itSkills as $skill) {
            if ($skill->parent_category_id == 2) { // Libraries & Frameworks
                $skill['quality'] = $this->getSkillQuality($skill);
                $frameworks[] = $skill;
            }
        }
        if ($limit && count($frameworks) > $limit) {
            $frameworks = array_slice($frameworks, 0, $limit);
        }
        return $frameworks;
    }

    public function getSkillQuality($skill)
    {
        $scale = $this->scaleToNumber($skill->scale);
        if ($scale >= 4 && $skill->years >= 3) {
            // Expert 3+ years
            return 5;
        } elseif ($scale >= 3 && $skill->years >= 3) {
            // Advanced 3+ years
            return 4;
        } elseif ($scale >= 4 && $skill->years >= 1) {
            // Expert 1-2 years
            return 4;
        } elseif ($scale >= 2 && $skill->years >= 3) {
            // Intermediate 3+ years
            return 3;
        } elseif ($scale >= 3 && $skill->years >= 1) {
            // Advanced 1–2 years
            return 3;
        } elseif ($scale >= 2 && $skill->years >= 1) {
            // Intermediate 1–2 years
            return 2;
        } elseif ($scale >= 1) {
            // Basic with any amount of years
            return 1;
        } else {
            return 0;
        }
    }

    public function scaleToNumber($scale)
    {
        switch ($scale) {
            default:
                return 0;
            case 'Basic':
                return 1;
            case 'Intermediate':
                return 2;
            case 'Advanced':
                return 3;
            case 'Expert':
                return 4;
        }
    }

}
