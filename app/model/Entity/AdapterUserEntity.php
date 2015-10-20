<?php

namespace Model\Entity;

use Illagrenan\Facebook\FacebookUser,
    Netrium\Addons\Twitter\TwitterUser,
    \GoogleUser;

/**
 * Adapter for Imported User - user form FB, Twitter, Google, etc.
 *
 * @author Petr Poupě
 */
class AdapterUserEntity extends Entity
{

    const SOURCE_APP = "app";
    const SOURCE_FB = "facebook";
    const SOURCE_TWITTER = "twitter";
    const SOURCE_GOOGLE = "google";

    /** @var string */
    protected $id;

    /** @var string */
    protected $source;

    /** @var bool */
    protected $remember;

    /** @var bool */
    protected $verified;

    /** @var string */
    protected $mail;

    /** @var string */
    protected $username;

    /** @var AddressEntity */
    protected $address;

    /** @var string */
    protected $gender;

    /** @var string */
    protected $location;

    /** @var string */
    protected $lang;

    /** @var \Nette\DateTime */
    protected $birthday;

    /** @var \Nette\Http\Url */
    protected $url;

    /** @var string */
    protected $facebook;

    /** @var string */
    protected $twitter;

    /** @var string */
    protected $google;

    public function __construct($user = NULL)
    {
        $this->address = new AddressEntity;
        if ($user !== NULL) {
            $this->convert($user);
        }
    }

    public function setFirstName($name)
    {
        $this->address->firstname = $name;
    }

    public function getFirstName()
    {
        return $this->address->firstname;
    }

    public function setLastName($name)
    {
        $this->address->surname = $name;
    }

    public function getLastName()
    {
        return $this->address->surname;
    }

    public function setFullName($value)
    {
        $names = preg_split("@\s+@", $value, 1);
        if (array_key_exists(0, $names)) {
            $this->setFirstName($names[0]);
        }
        if (array_key_exists(1, $names)) {
            $this->setLastName($names[1]);
        }
    }
    
    public function getFullName()
    {
        return $this->address->getFullName();
    }

    public function setRemember($value)
    {
        $this->remember = $this->returnBool($value);
    }

    public function setVerified($value)
    {
        $this->verified = $this->returnBool($value);
    }

    public function setBirthday($value)
    {
        $this->birthday = $this->returnDate($value);
    }

    public function setUrl($value)
    {
        $this->url = $this->returnUrl($value);
    }

    /**
     * Convert inserted user to UserEntity
     * @param type $user
     * @throws Exception
     */
    public function convert($user)
    {
        if ($user instanceof FacebookUser) { // for Facebook

            /* @var $user FacebookUser */
            $this->source = self::SOURCE_FB;
            $this->id = $user->getId();
            $this->mail = $user->getEmail();
            $this->username = $user->getFullName();
            $this->setFirstName($user->getFirstName());
            $this->setLastName($user->getLastName());
            $this->gender = $user->getGender();
            $this->setBirthday($user->getBirthday());
            $this->lang = $user->getLocale();
            $this->setUrl($user->getWebsite());
            $this->facebook = $user->getProfileLink();
            $this->setRemember(TRUE);
            $this->setVerified($user->getVerified());
        } else if ($user instanceof TwitterUser) {

            /* @var $user TwitterUser */
            $this->source = self::SOURCE_TWITTER;
            $this->id = $user->id;
            $this->setFullName($user->name);
            $this->username = $user->screen_name;
            $this->twitter = $user->screen_name;
            $this->url = $user->url;
            $this->lang = $user->lang;
            $this->setRemember(TRUE);
            $this->setVerified(FALSE);
        } else if ($user instanceof GoogleUser) {

            /* @var $user GoogleUser */
            $this->source = self::SOURCE_GOOGLE;
            $this->id = $user->id;
            $this->mail = $user->email;
            $this->username = $user->given_name . " " . $user->family_name;
            $this->setFirstName($user->given_name);
            $this->setLastName($user->family_name);
            $this->google = $user->link;
            $this->gender = $user->gender;
            $this->setBirthday($user->birthday);
            $this->lang = $user->locale;
            $this->setRemember(TRUE);
            $this->setVerified($user->verified_email);
        } else if ($user instanceof SignInEntity) {

            $this->id = $user->login;
            $this->source = self::SOURCE_APP;
            $this->setRemember($user->remember);
        } else {
            throw new \Exception("This User Format isn't implemented");
        }
    }

}

?>
