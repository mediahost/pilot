<?php

namespace Model\Entity;

/**
 * CV IT Scale Entity
 *
 * @author Petr Poupě
 * @author Marek Šneberger
 */
class CvItScaleEntity extends Entity
{

    /** @var array  */
    private $itSkills = array();

    /**
     * @param array $value
     */
    public function __construct($value = array())
    {
        $this->fromArray((array) $value);
    }

    /**
     * @param array $array
     */
    public function fromArray(array $array = array())
    {
        $this->itSkills = $array;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->itSkills;
    }

    /**
     * @param null $id
     *
     * @return array|null
     */
    public static function scale($id = NULL)
    {
        $scales = array(
            NULL => "n/a",
            'Basic' => "Basic",
            'Intermediate' => "Intermediate",
            'Advanced' => "Advanced",
            'Expert' => "Expert",
        );

        if ($id === NULL) {
            return $scales;
        } else {
            if (array_key_exists($id, $scales)) {
                return $scales[$id];
            } else {
                return NULL;
            }
        }
    }

}
