<?php

namespace Model\Entity;

/**
 * Address Entity
 *
 * @author Petr Poupě
 */
class AddressEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $company;

    /** @var string */
    protected $firstname;

    /** @var string */
    protected $surname;

    /** @var string */
    protected $street;

    /** @var string */
    protected $city;

    /** @var string */
    protected $county;

    /** @var string */
    protected $country;

    /** @var string */
    protected $zipcode;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $mail;

    /** @var string */
    protected $ico;

    /** @var string */
    protected $dic;

    /** @var string */
    protected $bankAccount;

    /** @var string */
    protected $bankExt;

    /** @var string */
    protected $info;

    public function getCompanyOrFullName()
    {
        if ($this->company === NULL) {
            return $this->fullName;
        } else {
            return $this->company;
        }
    }

    public function getFullName()
    {
        return \Shopbox\Helpers::concatStrings(" ", $this->firstname, $this->surname);
    }
    
    public function getCityFull($separator = " ", $cityFirst = FALSE)
    {
        if ($cityFirst) {
            return \Shopbox\Helpers::concatStrings($separator, $this->city, $this->zipcode);
        } else {
            return \Shopbox\Helpers::concatStrings($separator, $this->zipcode, $this->city);
        }
    }

    public function getFullBankAccount($separator = "/")
    {
        return \Shopbox\Helpers::concatStrings($separator, $this->bankAccount, $this->bankExt);
    }

    public function getBankAccountFull($separator = "/")
    {
        return $this->getFullBankAccount($separator);
    }
    
    public function getPhone()
    {
        $phone = $this->phone;
        if (preg_match("~^((\+|00)(\d{3})\s?)?(\d{3})\s?(\d{3})\s?(\d{3})$~", $phone, $matches)) {
            $phone = $matches[2] . $matches[3] . " " . $matches[4] . " " . $matches[5] . " " . $matches[6];
        }
        return $phone;
    }

    /**
     * @return bool TRUE if entity is empty
     */
    public function isEmpty()
    {
        $empty = (
                $this->id === NULL &&
                $this->company === NULL &&
                $this->firstname === NULL &&
                $this->surname === NULL &&
                $this->street === NULL &&
                $this->city === NULL &&
                $this->county === NULL &&
                $this->country === NULL &&
                $this->zipcode === NULL &&
                $this->phone === NULL &&
                $this->mail === NULL &&
                $this->ico === NULL &&
                $this->dic === NULL &&
                $this->bankAccount === NULL &&
                $this->bankExt === NULL &&
                $this->info === NULL
                );
        return (bool) $empty;
    }

    /**
     * @return bool TRUE if company is empty
     */
    public function isEmptyCompany()
    {
        return $this->company === NULL;
    }

    public function toArray()
    {
        $array = array(
            "id" => $this->id,
            "company" => $this->company,
            "firstname" => $this->firstname,
            "surname" => $this->surname,
            "street" => $this->street,
            "city" => $this->city,
            "county" => $this->county,
            "country" => $this->country,
            "zipcode" => $this->zipcode,
            "phone" => $this->phone,
            "mail" => $this->mail,
            "ico" => $this->ico,
            "dic" => $this->dic,
            "bank_account" => $this->bankAccount,
            "bank_ext" => $this->bankExt,
            "info" => $this->info,
        );
        return $array;
    }

    public function load($row)
    {
        if ($row) {
            $this->id = $row->id;
            $this->company = $row->company;
            $this->firstname = $row->firstname;
            $this->surname = $row->surname;
            $this->street = $row->street;
            $this->city = $row->city;
            $this->county = $row->county;
            $this->country = $row->country;
            $this->zipcode = $row->zipcode;
            $this->phone = $row->phone;
            $this->mail = $row->mail;
            $this->ico = $row->ico;
            $this->dic = $row->dic;
            $this->bankAccount = $row->bank_account;
            $this->bankExt = $row->bank_ext;
            $this->info = $row->info;
        }
    }

}

?>
