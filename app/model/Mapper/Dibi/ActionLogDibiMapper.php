<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ActionLogEntity;

/**
 * Action Log DibiMapper
 *
 * @author Petr Poupě
 */
class ActionLogDibiMapper extends DibiMapper
{

    private $table = "action_log";

    /**
     * Change entity to data to save
     * @param \Model\Entity\ActionLogEntity $entity
     * @return array
     */
    private function entityToData(ActionLogEntity $entity)
    {
        $data = array(
            "id" => $entity->id,
            "datetime%t" => $entity->datetime,
            "user_id%i" => $entity->userId,
            "action%s" => $entity->action,
            "attributes%s" => json_encode($entity->attrs),
        );
        if ($entity->serieId !== NULL) {
            $data["serie%i"] = $entity->serieId;
        }
        return $data;
    }

    public function load($row)
    {
        $entity = new ActionLogEntity;
        if ($row) {
            foreach ($row as $item => $value) {
                switch ($item) {
                    case "user_id":
                        $entity->userId = $value;
                        break;
                    case "attributes":
                        $entity->attrs = json_decode($value);
                        break;
                    case "serie":
                        if ($value !== NULL) {
                            $entity->serieId = $value;
                            $entity->isSerie = TRUE;
                        }
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
        $ids = $this->conn->select('MAX(id) AS id')
                ->from('action_log')
                ->where("user_id = %i", $userId)
                ->groupBy('serie')
                ->orderBy('id DESC');
        if ($count > 0) {
            $ids->limit($count);
        }
        $ids = array_keys($ids->fetchPairs('id', 'id'));
        if (count($ids) == 0) {
            return array();
        }
        $result = $this->conn->select('*')
                ->from('action_log')
                ->where('id IN %l', $ids)
                ->orderBy('datetime DESC');
        $rows = $result->fetchAll();
        $list = array();
        if ($rows) {
            foreach ($rows as $row) {
                $list[] = $this->load($row);
            }
        }
        return $list;
    }
    
    public function findSeriesOrigin(ActionLogEntity $log)
    {
        $result = $this->conn->select("*")
                ->from($this->table)
                ->where("action = %s", $log->action)
                ->where("user_id = %i", $log->userId)
                ->where("attributes = %s", json_encode($log->attrs))
                ->orderBy("datetime ASC");
        return $this->load($result->fetch());
    }

    /**
     * Save Entity
     * @param \Model\Entity\ActionLogEntity $entity
     * @return \Model\Entity\ActionLogEntity
     */
    public function save(ActionLogEntity $entity)
    {
        $data = $this->entityToData($entity);

        if ($entity->id === NULL) { // insert
            $entity->id = $this->conn->insert($this->table, $data)
                    ->execute(\dibi::IDENTIFIER);
            // for including serie
            if ($entity->id !== NULL && $entity->serieId === NULL) {
                $entity->serieId = $entity->id;
                $this->save($entity);
            }
        } else { // update
            $this->conn->update($this->table, $data)
                    ->where('id = %i', $entity->id)
                    ->execute();
        }

        return $entity;
    }

}
