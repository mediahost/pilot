<?php

namespace Model\Entity;

/**
 * SignIn Entity - Entity from SignIn Form
 *
 * @author Petr Poupě
 */
class SignInEntity extends Entity
{

    /** @var string */
    protected $login;

    /** @var bool */
    protected $remember;

    /** @var string */
    protected $password;

    public function setRemember($value)
    {
        $this->remember = $this->returnBool($value);
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

}

?>
