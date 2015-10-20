<?php

namespace Model\Mapper\Dibi;

use Model\Entity\BlogCategoryEntity;

/**
 * Blog Category DibiMapper
 *
 * @author Petr Poupě
 */
class BlogCategoryDibiMapper extends DibiMapper
{

    const SAVE_TYPE_PRIMARY = "primary";
    const SAVE_TYPE_INFO = "info";

    private $primary = "blog_category";
    private $info = "blog_category_info";
    private $order = 'blog_category_info.name ASC';

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param BlogCategoryEntity $item
     * @return type
     */
    private function itemToData(BlogCategoryEntity $item, $type = self::SAVE_TYPE_PRIMARY)
    {
        $data = array();
        switch ($type) {
            case self::SAVE_TYPE_PRIMARY:
                $data['id'] = $item->id;
                $data['active'] = $item->active;
                break;
            case self::SAVE_TYPE_INFO:
                $data['blog_category_id'] = $item->id;
                $data['lang'] = $item->lang;
                $data['name'] = $item->name;
                break;
        }
        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return BlogCategoryEntity
     */
    public function load($data)
    {
        $item = new BlogCategoryEntity;
        if ($data) {
            foreach ($data as $prop => $val) {
                switch ($prop) {
                    default:
                        $item->$prop = $val;
                        break;
                }
            }
        }

        return $item;
    }

    /**
     * Vrací celou tabulku
     * @return \DibiFluent
     */
    public function allDataSource($lang, $by = array(), $limit = NULL, $offset = NULL)
    {
        $select = $this->selectList($lang);
        $dataSource = $this->joinItem($select, $lang);
        if ($by !== array()) {
            $dataSource->where($this->_getWhere($by));
        }

        $dataSource->orderBy($this->order);

        if ($limit)
            $dataSource->limit($limit);
        if ($offset)
            $dataSource->offset($offset);

        return $dataSource;
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return BlogCategoryEntity
     */
    public function find($id, $lang = NULL)
    {
        return $this->findBy($lang, array(
                    "id" => $id,
        ));
    }

    /**
     * Find one entity by conditions
     * @param type $lang
     * @param type $by
     * @return BlogCategoryEntity
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
    public function findAll($lang, $by = array(), $limit = NULL, $offset = NULL)
    {
        $data = $this->allDataSource($lang, $by, $limit, $offset);

        $items = array();
        foreach ($data as $item) {
            $items[] = $this->load($item);
        }
        return $items;
    }

    /**
     * Save entity
     * @param BlogCategoryEntity $item
     * @param type $what
     * @return BlogCategoryEntity
     */
    public function save(BlogCategoryEntity $item, $what = NULL)
    {
        if ($what === NULL) {
            return $this->saveAll($item);
        } else {
            if (is_string($what)) {
                $what = preg_split("@\s*,\s*@", $what);
            } else if (!is_array($what)) {
                $what = array($what);
            }           
            return $this->saveOnly($item, $what);
        }
    }

    /**
     * Save only selected columns
     * @param BlogCategoryEntity $item
     * @param type $what
     * @return BlogCategoryEntity
     */
    private function saveOnly(BlogCategoryEntity $item, $what)
    {
        $primaryData = array();
        $infoData = array();
        foreach ($what as $whatItem) {
            switch ($whatItem) {
                case "active":
                    $primaryData['active'] = $item->$whatItem;
                    break;
                case "name":
                    $infoData['name'] = $item->$whatItem;
                    break;
            }
        }
        if ($primaryData !== array()) {
            $item = $this->_savePrimary($item, $primaryData);
        }
        if ($infoData !== array()) {
            $item = $this->_saveInfo($item, $infoData);
        }
        return $item;
    }

    /**
     * Save all columns
     * @param BlogCategoryEntity $item
     * @return type
     * @throws \Exception|BlogCategoryEntity
     */
    private function saveAll(BlogCategoryEntity $item)
    {
        if ($item->name === NULL)
            throw new \Exception("Name cannot be empty");

        $primaryData = $this->itemToData($item, self::SAVE_TYPE_PRIMARY);
        $itemPrim = $this->_savePrimary($item, $primaryData);

        $infoData = $this->itemToData($itemPrim, self::SAVE_TYPE_INFO);
        $itemInfo = $this->_saveInfo($itemPrim, $infoData);

        return $itemInfo;
    }

    private function _savePrimary(BlogCategoryEntity $item, array $data)
    {
        if ($data !== array()) {
            if ($item->id === NULL) { // insert
                $item->id = $this->conn->insert($this->primary, $data)
                        ->execute(\dibi::IDENTIFIER);
            } else { // update
                $this->conn->update($this->primary, $data)
                        ->where('id = %i', $item->id)
                        ->execute();
            }
        }
        return $item;
    }

    private function _saveInfo(BlogCategoryEntity $item, array $data)
    {
        if ($item->id !== NULL && $item->lang !== NULL && ($data !== array())) {
            $cond = array(
                'blog_category_id' => $item->id,
                'lang' => $item->lang,
            );

            $data["blog_category_id"] = $item->id;
            $data["lang"] = $item->lang;

            $cnt = $this->conn->select("id")->from($this->info)->where($cond)->count();
            if ($cnt == 1) {
                $this->conn->update($this->info, $data)->where($cond)->execute();
            } else {
                if ($cnt > 1)
                    $this->conn->delete($this->info)->where($cond)->execute();
                $this->conn->insert($this->info, $data)->execute();
            }
        }

        return $item;
    }

    public function delete(BlogCategoryEntity $item)
    {
        if ($item === NULL)
            return FALSE;
        return $this->conn->delete($this->primary)
                        ->where('id = %i', $item->id)
                        ->execute();
    }

    private function selectList($lang)
    {
        return $this->conn->select($this->_getSelectAll($lang))
                        ->from($this->primary);
    }

    private function joinItem(&$select, $lang)
    {
        return $select->leftJoin($this->info)
                        ->on("{$this->primary}.id = {$this->info}.blog_category_id")
                        ->and("{$this->info}.lang = %s", $lang);
    }

    /**
     * Returns selector for select all items
     * @param type $lang
     * @return string
     */
    private function _getSelectAll($lang)
    {
        $select = array(
            "{$this->primary}.id" => "id",
            "{$this->primary}.active" => "active",
        );
        if ($lang !== NULL) {
            $select["{$this->info}.lang"] = "lang";
            $select["{$this->info}.name"] = "name";
        }
        return $select;
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
                    $where["{$this->primary}.id%i"] = $cond;
                    break;
                case "active":
                    $where["{$this->primary}.active%b"] = $cond;
                    break;
                case "lang":
                    $where["{$this->info}.lang%s"] = $cond;
                    break;
                case "name":
                    $where["{$this->info}.name%s"] = $cond;
                    break;
            }
        }
        return $where;
    }

}

?>
