<?php

namespace Model\Entity;

/**
 * Auth Entity - user form FB, Twitter, Google, etc.
 *
 * @author Petr Poupě
 */
class AuthEntity extends Entity
{

    const SOURCE_APP = "app";
    const SOURCE_FB = "facebook";
    const SOURCE_TWITTER = "twitter";
    const SOURCE_GOOGLE = "google";

    /** @var int */
    protected $id;

    /** @var string */
    protected $key;

    /** @var int */
    protected $userId;

    /** @var string */
    protected $source;

    /** @var string */
    protected $password;

    /** @var string */
    protected $salt;

    /** @var bool */
    protected $verified;

    /** @var string */
    protected $verifyCode;

    public function __construct($user = NULL)
    {
        if ($user !== NULL) {
            $this->convert($user);
        }
    }

    /**
     * Convert inserted user to UserEntity
     * @param type $user
     * @throws Exception
     */
    public function convert($user)
    {
        if ($user instanceof AdapterUserEntity) {
            switch ($user->source) {
                case self::SOURCE_APP:
                case self::SOURCE_FB:
                case self::SOURCE_TWITTER:
                case self::SOURCE_GOOGLE:
                    $this->source = $user->source;
                    break;
                default:
                    throw new \Exception("This Source isn't implemented");
            }
            $this->key = $user->id;
            $this->verified = $user->verified;
            $this->verifyCode = $user->verified ? NULL : \CommonHelpers::generateCode(FALSE, 20);
        } else {
            throw new \Exception("This User Format isn't implemented");
        }
    }

}

?>
