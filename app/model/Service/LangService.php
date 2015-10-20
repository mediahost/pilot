<?php

namespace Model\Service;

use Model\Mapper\Dibi\LangDibiMapper,
    Model\Entity\LangEntity;

/**
 * Lang Service
 *
 * @author Petr Poupě
 */
class LangService
{

    /** @var \Model\Mapper\Dibi\LangDibiMapper */
    private $mapper;
    private $langs;

    public function __construct(LangDibiMapper $mapper)
    {
        $this->mapper = $mapper;
        
        $langs = array();
        
        $lang = new LangEntity;
        $lang->key = 'en';
        $lang->code = "en_GB";
        $lang->name = "English";
        $lang->class = "english";
        $lang->published = FALSE;
        $langs[$lang->key] = $lang;
        
        $lang = new LangEntity;
        $lang->key = 'cs';
        $lang->code = "cs_CZ";
        $lang->name = "Czech";
        $lang->class = "czech";
        $lang->published = FALSE;
        $langs[$lang->key] = $lang;
        
        $lang = new LangEntity;
        $lang->key = 'sk';
        $lang->code = "sk_SK";
        $lang->name = "Slovak";
        $lang->class = "slovakia";
        $lang->published = TRUE;
        $langs[$lang->key] = $lang;
        
        $this->langs = $langs;
    }

    public function getFrontLanguages()
    {
        $langs = array();
        foreach ($this->langs as $key => $lang) {
            if ($lang->published) {
                $langs[$lang->key] = $lang;
            }
        }
        return $langs;
    }

    public function getBackLanguages()
    {
        return $this->langs;
    }

}

?>
