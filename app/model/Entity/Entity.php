<?php

namespace Model\Entity;

/**
 * Parent of Entity
 *
 * @author Petr Poupě
 */
abstract class Entity extends \Nette\Object
{

    /**
     * Standard setter
     * @param type $name
     * @param type $value
     * @throws Exception
     */
    public function __set($name, $value)
    {
        try {
            return parent::__set($name, $value); // touto funkcí neprojdou private a protected bez setteru
        } catch (\Nette\MemberAccessException $exc) {
            $refex = new \Nette\Reflection\Property($this, $name);
            if ($refex->isProtected()) {
                switch ($refex->getAnnotation("var")) {
                    case "bool":
                        $value = $this->returnBool($value);
                        break;
                    case "stringEmpty":
                        $value = $this->returnString($value);
                        break;
                    case "string":
                        $value = $this->returnNotEmpty($this->returnString($value));
                        break;
                    case "string[]":
                        $value = $this->returnStringArray($this->$name, $value);
                        break;
                    case "int":
                        $value = $this->returnInt($value);
                        break;
                    case "int[]":
                        $value = $this->returnIntArray($this->$name, $value);
                        break;
                    case "mixed[]":
                        $value = $this->returnMixedArray($this->$name, $value);
                        break;
                    case "\Nette\Http\Url":
                        $value = $this->returnInt($value);
                        break;
                    case "\Nette\DateTime":
                        $value = $this->returnDate($value);
                        break;
                }
                $this->$name = $value;
            } else {
                throw new \Nette\MemberAccessException("Cannot write to undeclared property " . get_class($this) . "::\$$name.");
            }
        }
    }

    /**
     * Standard getter
     * @param type $name
     * @return type
     */
    public function &__get($name)
    {
        try {
            return parent::__get($name); // touto funkcí neprojdou private a protected bez getteru
        } catch (\Nette\MemberAccessException $exc) {
            $refex = new \ReflectionProperty($this, $name);
            if ($refex->isProtected()) {
                return $this->$name;
            } else {
                throw new \Nette\MemberAccessException("Cannot read undeclared property " . get_class($this) . "::\$$name.");
            }
        }
    }

    protected function returnDate($value)
    {
        if ($value !== NULL) {
            return \Nette\DateTime::from($value);
        } else {
            return NULL;
        }
    }

    protected function returnUrl($value)
    {
        return new \Nette\Http\Url($value);
    }

    protected function returnInt($value)
    {
        if ($value === NULL) {
            return NULL;
        } else {
            return (int) $value;
        }
    }

    protected function returnIntArray($array, $value)
    {
        $return = array();
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $return[$key] = (int) $item;
            }
        } else if ($value !== NULL) {
            $val = (int) $value;
            if (is_array($array)) {
                $array[] = $val;
            } else {
                $array = array($val);
            }
            return $array;
        }
        return $return;
    }

    protected function returnMixedArray($array, $value)
    {
        $return = array();
        if ($value !== NULL) {
            if (is_array($array)) {
                $array[] = $value;
            } else {
                $array = array($value);
            }
            return $array;
        }
        return $return;
    }

    protected function returnString($value)
    {
        return (string) $value;
    }

    protected function returnStringArray($array, $value)
    {
        $return = array();
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $return[$key] = (string) $item;
            }
        } else if ($value !== NULL) {
            $val = (string) $value;
            if (is_array($array)) {
                $array[] = $val;
            } else {
                $array = array($val);
            }
            return $array;
        }
        return $return;
    }

    protected function returnBool($value)
    {
        return (bool) $value;
    }

    protected function returnLang($value)
    {
        $parts = preg_split("@[_\-]@", $value, 2);
        return strtolower($parts[0]);
    }

    protected function returnNotEmpty($value)
    {
        return (empty($value) && $value !== "0" && $value !== 0 && $value !== 0.0) ? NULL : $value;
    }

    /**
     * Nastaví vlastnosti objektu.
     * @param \DibiRow|\Nette\ArrayHash|array|\Nette\Database\Table\ActiveRow|null $_data Data předaná objektu
     */
    public function commonSet($_data = null)
    {
        if (!is_null($_data)) {
            if ($_data instanceof \DibiRow or $_data instanceof \Nette\ArrayHash) {
                $vars = get_object_vars($_data);
                foreach ($vars as $key => $value) {
                    $this->$key = $value;
                }
            } elseif (is_array($_data)) {
                foreach ($_data as $key => $value) {
                    $this->$key = $value;
                }
            } elseif ($_data instanceof \Nette\Database\Table\ActiveRow) {
                $data = $_data->toArray();
                foreach ($data as $key => $value) {
                    $this->$key = $value;
                }
            } elseif ($_data instanceof \DibiRow) {
                $data = $_data->toArray();
                foreach ($data as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Vrací objekt jako pole
     * @param array $_notIncluded pole properties, které se nemají vracet
     * @return \Nette\ArrayHash pole vlastností objektu 
     */
    public function to_array(array $_notIncluded = array())
    {
        $_notIncluded[] = 'activeRow';
        $vars = get_object_vars($this);
        $ret = array();
        foreach ($vars as $key => $value) {
            if (!in_array($key, $_notIncluded)) {
                if ($value instanceof \Nette\Object) {
                    $ret[$key] = $value->toArray($_notIncluded);
                } else {
                    $ret[$key] = $value;
                }
            }
        }
        return \Nette\ArrayHash::from($ret);
    }

}
