<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ForumEntity,
    Model\Entity\ForumCategoryEntity;

/**
 * Forum DibiMapper
 *
 * @author Petr Poupě
 */
class ForumCategoryDibiMapper extends ForumParentDibiMapper
{

    /** @var ForumDibiMapper */
    private $forumMapper;

    /** @var string */
    private $table;

    public function __construct(\DibiConnection $conn)
    {
        parent::__construct($conn);
        $this->table = $this->category;
    }

    private function getForumMapper()
    {
        if (!$this->forumMapper instanceof ForumDibiMapper) {
            $this->forumMapper = new ForumDibiMapper($this->conn);
        }
        return $this->forumMapper;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param ForumCategoryEntity $item
     * @return type
     */
    private function itemToData(ForumCategoryEntity $item)
    {
        $data = array(
            "id" => $item->id,
            "parent" => $item->parent,
            "name" => $item->name,
            "lang" => $item->lang,
            "priority" => $item->priority,
            "active" => $item->active,
        );

        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return ForumCategoryEntity
     */
    public function load($data)
    {
        $item = new ForumCategoryEntity;

        if ($data) {
            $item->id = $data->id;
            $item->parent = $data->parent;
            $item->name = $data->name;
            $item->lang = $data->lang;
            $item->priority = $data->priority;
            $item->active = $data->active;
            $item->forums = $this->getForums($data->id);
        }

        return $item;
    }

    /**
     * Return PostEntity
     * @param type $id
     * @return ForumEntity
     */
    private function getForums($cid)
    {
        return $this->getForumMapper()->getForums($cid);
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return ForumCategoryEntity
     */
    public function find($id)
    {
        return $this->findBy(array(
                    "id" => $id,
        ));
    }

    /**
     * Find one entity by conditions
     * @param type $by
     * @return ForumCategoryEntity
     */
    public function findBy($by = array())
    {
        $data = $this->selectList();
        $data->where($this->_getWhere($by));

        return $this->load($data->fetch());
    }

    /**
     * Return array of entities
     * @param type $by
     * @return array
     */
    public function findAll($by = array())
    {
        $data = $this->selectList();
        $data->where($this->_getWhere($by));

        $items = array();
        foreach ($data->fetchAssoc("id") as $id => $item) {
            $items[$id] = $this->load($item);
        }
        return $items;
    }

    /**
     * Save entity
     * @param ForumCategoryEntity $item
     * @return ForumCategoryEntity
     */
    public function save(ForumCategoryEntity $item)
    {
        $data = $this->itemToData($item);
        if ($item->id === NULL) { // insert
            $item->id = $this->conn->insert($this->table, $data)
                    ->execute(\dibi::IDENTIFIER);
        } else { // update
            $this->conn->update($this->table, $data)
                    ->where('id = %i', $item->id)
                    ->execute();
        }
        return $item;
    }

    public function delete(ForumCategoryEntity $item)
    {
        return $this->conn->delete($this->table)
                        ->where('id = %i', $item->id)
                        ->execute();
    }

    private function selectList()
    {
        return $this->conn->select("*")
                        ->from($this->table)
                        ->orderBy("priority ASC");
    }

    /**
     * Returns WHERE array inserted by entity keys
     * @param type $by
     * @return type
     */
    private function _getWhere($by)
    {
        $where = array();
        foreach ($by as $item => $cond) {
            switch ($item) {
                case "id":
                    $where["id%i"] = $cond;
                    break;
                case "lang":
                    $where["lang%s"] = $cond;
                    break;
                case "parent":
                    $where["parent%i"] = $cond;
                    break;
                case "active":
                    $where["active%b"] = $cond;
                    break;
            }
        }
        return $where;
    }

    public function getCategories($lang, $parent = NULL, $onlyActive = TRUE)
    {
        $where = array(
            "lang" => $lang,
            "parent" => $parent,
        );
        if ($onlyActive) {
            $where["active"] = $onlyActive;
        }
        return $this->findAll($where);
    }

    public function sort(array $list)
    {
        foreach ($list as $order => $id) {
            $category = $this->find($id);
            if ($category->id !== NULL) {
                $category->priority = $order + 1;
                $this->save($category);
            }
        }
    }

}

?>
