<?php

namespace Illagrenan\Facebook;

final class FacebookUser extends \Nette\Object
{

    /**
     * The user's Facebook ID
     * @var int
     */
    private $id;

    /**
     * The user's first name
     * @var string 
     */
    private $firstName;

    /**
     * The user's last name
     * @var string
     */
    private $lastName;

    /**
     * The URL of the profile for the user on Facebook
     * @var string URL 
     */
    private $profileLink;

    /**
     * The user's Facebook username
     * @var type 
     */
    private $username;

    /**
     * @var \Illagrenan\Facebook\UserGender
     */
    private $gender;

    /**
     * The user's locale
     * @var string
     * @link http://developers.facebook.com/docs/internationalization/
     */
    private $locale;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \Nette\DateTime
     */
    private $birthday;

    /**
     * @var string
     */
    private $hometown;

    /**
     * @var string
     */
    private $location;

    /**
     * @var mixed
     */
    private $work;

    /**
     * @var mixed
     */
    private $education;

    /**
     * @var \Nette\Http\Url
     */
    private $website;

    /**
     * @var mixed
     */
    private $languages;

    /**
     * @var bool
     */
    private $verified;

    /**
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $profileLink
     * @param string $username
     * @param string $gender
     * @param string $locale
     */
    public function __construct($id, $firstName, $lastName, $profileLink, $username, $gender, $locale)
    {
        $this->id          = (int) $id;
        $this->firstName   = (string) $firstName;
        $this->lastName    = (string) $lastName;
        $this->profileLink = (string) $profileLink;
        $this->username    = (string) $username;
        $this->gender      = (string) $gender;
        $this->locale      = (string) $locale;
    }
    
    public function setEmail($value)
    {
        $this->email = $value;
    }
    
    public function setBirthday($value)
    {
        $this->birthday = \Nette\DateTime::from($value);
    }
    
    public function setHometown($value)
    {
        $this->hometown = $value;
    }
    
    public function setLocation($value)
    {
        $this->location = $value;
    }
    
    public function setWork($value)
    {
        $this->work = $value;
    }
    
    public function setEducation($value)
    {
        $this->education = $value;
    }
    
    public function setWebsite($value)
    {
        $this->website = new \Nette\Http\Url($value);
    }
    
    public function setLanguages($value)
    {
        $this->languages = $value;
    }
    
    public function setVerified($value)
    {
        $this->verified = (bool) $value;
    }

    public function __toString()
    {
        return "FBUser: " . $this->getFullName() . ", " . $this->getId();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string The user's full name
     */
    public function getFullName()
    {
        return ($this->firstName . " " . $this->lastName);
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        return $this->profileLink;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return \Illagrenan\Facebook\UserGender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return \Nette\DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return string
     */
    public function getVerified()
    {
        return $this->verified;
    }

}

final class UserGender extends \Nette\Object
{

    const MALE    = "male";
    const FEMALE  = "female";
    const UNKNOWN = "unknown";

    private function __construct()
    {
        
    }

}
