<?php

/**
 * MyTexy - Texy! settings
 *
 * @author Petr Poupě
 */
class MyTexy extends Texy
{

    public function __construct()
    {
        parent::__construct();
        $this->setTexy();
    }

    private function setTexy()
    {
        // here will be common settings
    }
    
    public static function helperTexy($text)
    {
        $texy = new MyTexy;
        
        return $texy->process($text);
    }

}
