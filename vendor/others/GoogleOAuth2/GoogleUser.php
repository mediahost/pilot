<?php

/**
 * GoogleUser
 *
 * @author Petr Poupě
 */
class GoogleUser
{

    private $data = array();
    
    public function __construct(\stdClass $user)
    {
        foreach ($user as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data))
            return $this->data[$name];
        else
            return NULL;
    }

}

?>
