<?php

/**
 * Common helpers.
 *
 * @author     Petr Poupě
 */
class CommonHelpers
{

    const CURRENCY_EUR = "EUR";
    const CURRENCY_CZK = "CZK";

    /**
     * Static class - cannot be instantiated.
     */
    final public function __construct()
    {
        throw new LogicException("Cannot instantiate static class " . get_class($this));
    }

    public static function isFirst($needle, array $haystack)
    {
        return (bool) (reset($haystack) === $needle);
    }

    public static function isLast($needle, array $haystack)
    {
        return (bool) (end($haystack) === $needle);
    }

    public static function currency($value, $type = self::CURRENCY_EUR)
    {
        $currency = $value;
        switch ($type) {
            case "Kč":
            case self::CURRENCY_CZK:
                $currency = str_replace(" ", "\xc2\xa0", number_format($value, 0, "", " ")) . "\xc2\xa0Kč";
                break;

            case "€":
            case self::CURRENCY_EUR:
                $currency = str_replace(" ", "\xc2\xa0", number_format($value, 0, "", " ")) . "\xc2\xa0€";
            default:
                break;
        }
        return $currency;
    }

    public static function concatTwoStrings($first = NULL, $second = NULL, $separator = " ")
    {
        return self::concatStrings($separator, $first, $second);
        return ($first === NULL) ? $second : ($first . ($second === NULL ? : ($separator . $second)));
    }

    public static function concatStrings($separator = " ")
    {
        $args = func_get_args();
        if (count($args) > 1) {
            $separator = is_string($args[0]) ? $args[0] : $separator;
            array_shift($args);
            if (count($args) == 1 && is_array($args[0]))
                $args = $args[0];
            $string = NULL;
            foreach ($args as $item) {
                if ($string === NULL) {
                    $string = $item;
                } else if ($item !== NULL) {
                    $string .= $separator . $item;
                }
            }
            return $string;
        } else {
            return NULL;
        }
    }

    public static function concatArray($array, $separator)
    {
        return self::concatStrings($separator, $array);
    }

    /**
     * Check if folder exists
     * @param type $filename
     * @param type $create
     * @param type $mode
     * @return boolean
     */
    public static function dir_exists($filename, $create = TRUE, $mode = 0777)
    {
        if (file_exists($filename)) {
            return TRUE;
        } else if ($create) {
            mkdir($filename, $mode, TRUE);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function file_exists($filename, $create = TRUE, $mode = 0777)
    {
        if ($create) {
            if (preg_match("@(.*)\/([^\/]+)$@", $filename, $matches)) {
                if (!file_exists($matches[1]))
                    mkdir($matches[1], $mode, TRUE);
                return file_exists($filename);
            } else {
                return FALSE;
            }
        } else {
            return file_exists($filename);
        }
    }

    public static function timeAgoInWords($time)
    {
        if (!$time) {
            return FALSE;
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time instanceof DateTime) {
            $time = $time->format('U');
        } else {
            $time = strtotime($time);
        }

        $delta = time() - $time;

        if ($delta < 0) {
            $delta = round(abs($delta) / 60);
            if ($delta == 0)
                return __('after a while');
            if ($delta == 1)
                return __('in a minute');
            if ($delta < 45)
                return _nx("for %s minute", "for %s minutes", $delta, array($delta));
            if ($delta < 90)
                return __('in an hour');
            if ($delta < 1440)
                return _nx("for %s hour", "for %s hours", round($delta / 60), array(round($delta / 60)));
            if ($delta < 2880)
                return __('zítra');
            if ($delta < 43200)
                return _nx("for %s day", "for %s days", round($delta / 1440), array(round($delta / 1440)));
            if ($delta < 86400)
                return __('for a month');
            if ($delta < 525960)
                return _nx("for %s month", "for %s months", round($delta / 43200), array(round($delta / 43200)));
            if ($delta < 1051920)
                return __('for a year');
            return _nx("for %s year", "for %s years", round($delta / 525960), array(round($delta / 525960)));
        }

        $delta = round($delta / 60);
        if ($delta == 0)
            return __('a moment ago');
        if ($delta == 1)
            return __('a minute ago');
        if ($delta < 45)
            return _nx("%s minute ago", "%s minutes ago", $delta, array($delta));
        if ($delta < 90)
            return __('before hour');
        if ($delta < 1440)
            return _nx("%s hour ago", "%s hours ago", round($delta / 60), array(round($delta / 60)));
        if ($delta < 2880)
            return __('yesterday');
        if ($delta < 43200)
            return _nx("%s day ago", "%s days ago", round($delta / 1440), array(round($delta / 1440)));
        if ($delta < 86400)
            return __('a month ago');
        if ($delta < 525960)
            return _nx("%s month ago", "%s months ago", round($delta / 43200), array(round($delta / 43200)));
        if ($delta < 1051920)
            return __('a year ago');
        return _nx("%s year ago", "%s years ago", round($delta / 525960), array(round($delta / 525960)));
    }

    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    public static function plural($n)
    {
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }

    /**
     * Převod českých malých znaků na velké
     * @param <type> $string
     * @return <type>
     */
    public static function myStrToUpper($string)
    {
        return strtr($string, "abcdefghijklmnopqrstuvwxyzáéěíóúůýžščřďťň", "ABCDEFGHIJKLMNOPQRSTUVWXYZÁÉĚÍÓÚŮÝŽŠČŘĎŤŇ");
    }

    /**
     * vrací byty z násobku
     * @param  string
     * @return int
     */
    public static function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'p':
                $val *= 1024;
            case 't':
                $val *= 1024;
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    /**
     * Z zadaným bytům přidá jednotky, level udává počíteční řád bytů (1 = kB)
     * @param int $bytes
     * @param int $level
     * @return <type>
     */
    public static function toUnitsBytes($bytes, $level = 0)
    {
        $kilo = 1024;
        $units = array("B", "kB", "MB", "GB", "TB", "PB");
        for ($level; $bytes > $kilo; $level++) {
            $bytes /= $kilo;
        }
        return round($bytes, 2) . " " . $units[$level];
    }

    /**
     * Ověří zadanou maximální možnou velikost souboru pro nahrání a případně ji sníží
     * @param int $maxFileSize
     * @return int
     */
    public static function getMaxFileSize($maxFileSize = NULL)
    { // 1,5MB
        $maxFileSize = 1572864;
        $phpUploadMaxFileSize = Helpers::returnBytes(ini_get('upload_max_filesize'));
        if ($phpUploadMaxFileSize < $maxFileSize or $maxFileSize == NULL) {
            $maxFileSize = $phpUploadMaxFileSize;
        }
        return $maxFileSize;
    }

    /**
     * number_format — Format a number with grouped thousands
     * with fixed separator
     * @param type $number
     * @param type $decimals
     * @param type $dec_point
     * @param type $thousands_sep
     * @return type 
     */
    public static function myNumber($number, $decimals = 0, $dec_point = ',', $thousands_sep = ' ')
    {
        return str_replace(" ", "\xc2\xa0", number_format($number, $decimals, $dec_point, $thousands_sep));
    }

    /**
     * generátor písmenného hesla s jedním číslem
     * @param int $delkaHesla
     * @return string
     */
    public static function generatePassw($delkaHesla = 8)
    {
        $hlasky[0] = array(1 => "a", "e", "y", "i", "o", "u");
        $hlasky[1] = array(1 => "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "z");
        $poziceCisla = rand(2, $delkaHesla - 1);
        $selectHlaska = rand(0, 1);
        $heslo = "";
        for ($i = 1; $i <= $delkaHesla; $i++) {
            $selectHlaska = ($selectHlaska) ? 0 : 1;
            if ($i == $poziceCisla)
                $heslo .= rand(1, 9);
            else
                $heslo .= $hlasky[$selectHlaska][rand(1, (count($hlasky[$selectHlaska])))];
        }
        return $heslo;
    }

    /**
     * generuje náhodný kód složený z alfanumerických znaků o požadované délce hesla
     * pokud je parametr $md5 nastaven na TRUE, je generován náhodný 32 místný kód
     * @param bool $md5
     * @param int $delkaHesla
     * @return string
     */
    public static function generateCode($md5 = TRUE, $delka_hesla = 5)
    {
        $mozne_znaky = 'abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $vystup = '';
        for ($i = 0; $i < $delka_hesla; $i++) {
            $vystup .= $mozne_znaky[mt_rand(0, strlen($mozne_znaky) - 1)];
        }
        if ($md5)
            $vystup = md5(time() . $vystup);
        return $vystup;
    }

    /**
     * Nahrazeni %s v textu za promennou, uvedenou v parametrech
     * pro vypsani %s je treba zadat %%s
     * @param string $string
     * @return string
     */
    public static function printf($string)
    {
        for ($i = 1; $i < func_num_args(); $i++) {
            $value = func_get_arg($i);
            $string = preg_replace('/%s/', $value, $string, 1);
        }
        $string = preg_replace('/%%s/', '%s', $string);
        return $string;
    }

    /**
     * Zkracuje string na zadaný počet znaků - ukončuje po slovech
     * @param string $string
     * @param int $max
     * @param string $sep
     * @return string
     */
    public static function getShortText($string, $max = 75, $sep = '...')
    {
        $originalString = $string;

        $string = $string . " ";
        $string = substr($string, 0, $max);
        $string = substr($string, 0, strrpos($string, ' '));

        $originalString = trim($originalString);
        $string = trim($string);

        if ($originalString !== $string)
            $string .= $sep;

        return $string;
    }

    public static function helperFirst($value)
    {
        if (is_array($value)) {
            $values = array_values($value);
            return array_pop($values);
        } else {
            return $value;
        }
    }

    public static function helperDateFormat($date, $lang)
    {
        $date = new \Nette\DateTime($date);

        switch ($lang) {
            case "cs":
            case "sk":
                $format = "j.n.Y";
                break;

            case "en":
            default:
                $format = "j/n/Y";
                break;
        }

        return $date->format($format);
    }

    public static function br2nl($text)
    {
        return preg_replace("~<br\s*/?>~i", "\n", $text);
    }

}