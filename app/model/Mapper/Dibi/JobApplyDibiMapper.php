<?php

namespace Model\Mapper\Dibi;

use Model\Entity\JobApplyEntity;

/**
 * Action Log DibiMapper
 *
 * @author Petr Poupě
 */
class JobApplyDibiMapper extends DibiMapper
{

    private $table = "job_reaction";

    /**
     * Change entity to data to save
     * @param \Model\Entity\JobApplyEntity $entity
     * @return array
     */
    private function entityToData(JobApplyEntity $entity)
    {
        $data = array(
            "id" => $entity->id,
            "user_id%i" => $entity->userId,
            "job_id%i" => $entity->jobId,
            "job_position%s" => $entity->position,
            "datetime%t" => $entity->datetime,
            "to%s" => $entity->reciever,
            "from%s" => $entity->sender,
            "subject%s" => $entity->subject,
            "text%s" => $entity->text,
        );
        return $data;
    }

    public function load($row)
    {
        $entity = new JobApplyEntity;
        if ($row) {
            foreach ($row as $item => $value) {
                switch ($item) {
                    case "user_id":
                        $entity->userId = $value;
                        break;
                    case "job_id":
                        $entity->jobId = $value;
                        break;
                    case "job_position":
                        $entity->position = $value;
                        break;
                    case "to":
                        $entity->reciever = $value;
                        break;
                    case "from":
                        $entity->sender = $value;
                        break;
                    default:
                        $entity->$item = $value;
                        break;
                }
            }
        }
        return $entity;
    }

    public function getLast($userId, $count = NULL)
    {
        $result = $this->conn->select("*")
                ->from($this->table)
                ->where("user_id = %i", $userId)
                ->orderBy("datetime DESC");
        if ($count > 0) {
            $result->limit($count);
        }
        $rows = $result->fetchAll();
        $list = array();
        if ($rows) {
            foreach ($rows as $row) {
                $list[] = $this->load($row);
            }
        }
        return $list;
    }

    /**
     * Save Entity
     * @param JobApplyEntity $entity
     * @return JobApplyEntity
     */
    public function save(JobApplyEntity $entity)
    {
        $data = $this->entityToData($entity);

        if ($entity->id === NULL) { // insert
            $entity->id = $this->conn->insert($this->table, $data)
                    ->execute(\dibi::IDENTIFIER);
        } else { // update
            $this->conn->update($this->table, $data)
                    ->where('id = %i', $entity->id)
                    ->execute();
        }

        return $entity;
    }
    
    public function find($id)
    {
        $result = $this->conn->select("*")->from($this->table)->where("id = %i", $id);
        return $this->load($result->fetch());
    }

}

?>
