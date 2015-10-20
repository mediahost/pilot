<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ProfesiaLoadEntity;

/**
 * Profesia Load DibiMapper
 *
 * @author Petr Poupě
 */
class ProfesiaLoadDibiMapper extends DibiMapper
{
    
    private $table = "profesia_load";
    
    public function load($row)
    {
        $entity = new ProfesiaLoadEntity;
        if ($row) {
            $entity->id = $row->id;
            $entity->lastModified = $row->last_modified;
            $entity->loadTime = $row->load_time;
        }
        return $entity;
    }

    public function findLast()
    {
        $result = $this->conn->select("*")
                ->from($this->table)
                ->orderBy("load_time DESC");
        return $this->load($result->fetch());
    }

    public function setNewLoad(\Nette\DateTime $time)
    {
        $data = array(
            "load_time%t" => time(),
            "last_modified%t" => $time,
        );
        $this->conn->insert($this->table, $data)->execute();
    }

}

?>
