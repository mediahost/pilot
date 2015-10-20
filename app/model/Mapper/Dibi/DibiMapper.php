<?php

namespace Model\Mapper\Dibi;

/**
 * Parent of DibiMapper
 *
 * @author Petr Poupě
 */
abstract class DibiMapper extends \Nette\Object
{

    /** @var \DibiConnection */
    protected $conn;

    public function __construct(\DibiConnection $conn)
    {
        $this->conn = $conn;
    }
    
}

?>
