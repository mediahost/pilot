<?php

namespace Model\Mapper\Dibi;

/**
 * Parent of Multilanguage DibiMappers
 *
 * @author Petr Poupě
 */
abstract class MultilangDibiMapper extends DibiMapper
{

    const TYPE_LIST = 1;
    const TYPE_ITEM = 2;

    protected $listAlias = "list";
    protected $itemAlias = "item";

    /**
     * Vrací celou tabulku
     * @return \DibiFluent
     */
    public function allDataSource($lang, $by = array())
    {
        $select = $this->selectList($lang);
        $dataSource = $this->joinItem($select, $lang);
        $dataSource->orderBy($this->order);
        if ($by !== array()) {
            $dataSource->where($this->_getWhere($by));
        }
        return $dataSource;
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return MultilangEntity
     */
    public function find($id, $lang = NULL)
    {
        return $this->findBy($lang, array(
                    $this->key => $id,
        ));
    }

    /**
     * Find one entity by conditions
     * @param type $lang
     * @param type $by
     * @return MultilangEntity
     */
    public function findBy($lang, $by = array())
    {
        $data = $this->selectList($lang);
        if ($lang !== NULL) {
            $data = $this->joinItem($data, $lang);
        }
        $data->where($this->_getWhere($by));

        return $this->load($data->fetch());
    }

    /**
     * Return array of entities
     * @param type $lang
     * @param type $by
     * @return array
     */
    public function findAll($lang, $by = array())
    {
        $select = $this->selectList($lang);
        $data = $this->joinItem($select, $lang);
        $data->where($this->_getWhere($by))
                ->orderBy($this->order);

        $items = array();
        foreach ($data as $item) {
            $items[] = $this->load($item);
        }
        return $items;
    }

    /**
     * Save entity
     * @param \Model\Entity\MultilangEntity $item
     * @param type $what
     * @return MultilangEntity
     */
    public function save($item, $what = NULL)
    {
        if ($what === NULL) {
            return $this->saveAll($item);
        } else {
            if (!is_array($what))
                $what = array($what);
            return $this->saveOnly($item, $what);
        }
    }

    protected function saveData($item, $listData, $itemSave = TRUE)
    {
        if ($listData !== array()) {
            if ($item->id === NULL) { // insert
                $item->id = $this->conn->insert($this->list, $listData)
                        ->execute(\dibi::IDENTIFIER);
            } else { // update
                $this->conn->update($this->list, $listData)
                        ->where("{$this->key} = %i", $item->id)
                        ->execute();
            }
        }

        if ($itemSave === TRUE) {
            $itemData = $this->itemToData($item, self::TYPE_ITEM);
        } else {
            $itemData = $itemSave;
        }

        if ($item->lang !== NULL && $item->id !== NULL && (is_array($itemData) && $itemData !== array())) {
            $cond = array(
                $this->foreignKey => $item->id,
                'lang' => $item->lang,
            );
            $cnt = $this->conn->select("*")->from($this->item)->where($cond)->count();
            if ($cnt == 1) {
                $this->conn->update($this->item, $itemData)->where($cond)->execute();
            } else {
                if ($cnt > 1)
                    $this->conn->delete($this->item)->where($cond)->execute();
                $this->conn->insert($this->item, $itemData)->execute();
            }
        }
        return $item;
    }

    protected function selectList($lang)
    {
        return $this->conn->select($this->_getSelectAll($lang))
                        ->from($this->list)
                        ->as($this->listAlias);
    }

    protected function joinItem(&$select, $lang)
    {
        return $select->leftJoin($this->item)
                        ->as($this->itemAlias)
                        ->on("{$this->listAlias}.{$this->key} = {$this->itemAlias}.{$this->foreignKey}")
                        ->and("{$this->itemAlias}.lang = %s", $lang);
    }

}

?>
