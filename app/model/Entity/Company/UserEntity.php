<?php

namespace Model\Entity\Company;

use Model\Entity\Entity;
use Model\Service\UserService;

/**
 * Class UserEntity
 * @package Model\Entity\Company
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class UserEntity extends Entity
{

    /** @var  int */
    public $id;

    /** @var  string */
    public $username;

    /** @var  string */
    public $email;

    /** @var  string */
    public $role;
    
    /** @var bool */
    public $chat_notifications;
    
    /** @var string */
    public $company_name;
    
    /** @var int */
    public $view;
    
    /** @var string */
    public $description;
    
    /** @var string */
    public $slug;
    
    /** @var array */
    protected $pictures;
    
    /**
     * @param null $data
     */
    public function __construct($data = NULL)
    {
        if (!is_null($data)) {
            $this->setValues($data);
        }
    }
    
    public function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }
    
    public function getPictures()
    {
        return $this->pictures;
    }
    
    public function setValues($data)
    {
        $this->username = $data->username;
        $this->email = $data->email;
        $this->role = 'company';
        $this->id = isset($data->id) ? $data->id : NULL;
        $this->company_name = $data->company_name;
        $this->view = $data->view;
        if (isset($data->description)) {
            $this->description = $data->description;
        }
    }
    
    public function to_array(array $_notIncluded = array()) {
        $data = parent::to_array($_notIncluded);
        return $data;
    }

}
