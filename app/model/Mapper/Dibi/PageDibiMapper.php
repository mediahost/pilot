<?php

namespace Model\Mapper\Dibi;

use Model\Entity\PageEntity;

/**
 * Pages DibiMapper
 *
 * @author Petr Poupě
 */
class PageDibiMapper extends DibiMapper
{

    const TYPE_LIST = 1;
    const TYPE_ITEM = 2;
    const ACTIVATE = 1;
    const DEACTIVATE = 2;
    const MOVE_UP = 1;
    const MOVE_DOWN = 2;

    private $list = "page_list";
    private $item = "page_item";
    private $listAlias = "list";
    private $itemAlias = "item";
    private $key = 'id';
    private $order = 'list.order ASC, list.date DESC';

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param PageEntity $item
     * @return type
     */
    private function itemToData(PageEntity $item, $type = self::TYPE_LIST)
    {
        $data = array();
        switch ($type) {
            case self::TYPE_LIST:
                $data['id'] = $item->id;
                $data['type'] = $item->type;
                $data['code'] = $item->code;
                $data['comment'] = $item->comment;
                $data['order'] = $item->order;
                $data['active'] = $item->active;
                $data['date'] = $item->date;
                $data['link'] = $item->link;
                $data['position'] = $item->position;
                $data['parent_id'] = $item->parentId;
                break;
            case self::TYPE_ITEM:
                $data['page_list_id'] = $item->id;
                $data['lang'] = $item->lang;
                $data['name'] = $item->name;
                $data['perex'] = $item->perex;
                $data['text'] = $item->text;
                break;
        }
        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return PageEntity
     */
    public function load($data)
    {
        $item = new PageEntity;
        if ($data) {
            foreach ($data as $prop => $val) {
                switch ($prop) {
                    case "parent_id":
                        $prop = "parentId";
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
    public function allDataSource($lang, $by = array(), $order = NULL, $limit = NULL, $offset = NULL)
    {
        $select = $this->selectList($lang);
        $dataSource = $this->joinItem($select, $lang);
        if ($by !== array()) {
            $dataSource->where($this->_getWhere($by));
        }

        switch ($order) {
            case "date":
                $order = 'list.date DESC';
                break;
            default:
                $order = $this->order;
                break;
        }
        $dataSource->orderBy($order);
        
        if ($limit)
            $dataSource->limit($limit);
        if ($offset)
            $dataSource->offset($offset);

        return $dataSource;
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return PageEntity
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
     * @return PageEntity
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
    public function findAll($lang, $by = array(), $order = NULL, $limit = NULL, $offset = NULL)
    {
        $data = $this->allDataSource($lang, $by, $order, $limit, $offset);

        $items = array();
        foreach ($data as $item) {
            $items[] = $this->load($item);
        }
        return $items;
    }

    /**
     * Save entity
     * @param \Model\Entity\PageEntity $item
     * @param type $what
     * @return PageEntity
     */
    public function save(PageEntity $item, $what = NULL)
    {
        if ($what === NULL) {
            return $this->saveAll($item);
        } else {
            if (!is_array($what))
                $what = array($what);
            return $this->saveOnly($item, $what);
        }
    }

    /**
     * Save only selected columns
     * @param \Model\Entity\PageEntity $item
     * @param type $what
     * @return PageEntity
     */
    private function saveOnly(PageEntity $item, $what)
    {
        $listData = array();
        $itemData = array();
        foreach ($what as $whatItem) {
            switch ($whatItem) {
                case "image": // names from entity
                    $listData['image'] = $item->$whatItem;
                    break;
                case "active":
                    $listData['active'] = $item->$whatItem;
                    break;
                case "order":
                    $listData['order'] = $item->$whatItem;
                    break;
                case "name":
                    $itemData['name'] = $item->$whatItem;
                    break;
                case "perex":
                    $itemData['perex'] = $item->$whatItem;
                    break;
                case "text":
                    $itemData['text'] = $item->$whatItem;
                    break;
            }
        }
        return $this->saveData($item, $listData, $itemData);
    }

    /**
     * Save all columns
     * @param \Model\Entity\PageEntity $item
     * @return type
     * @throws \Exception|PageEntity
     */
    private function saveAll(PageEntity $item)
    {
        if ($item->name === NULL)
            throw new \Exception("Name cannot be empty");
        if ($item->code === NULL)
            throw new \Exception("Code cannot be empty");
        if ($item->active === NULL)
            throw new \Exception("Active must be set");

        if ($item->id === NULL)
            $item->date = time();

        $listData = $this->itemToData($item, self::TYPE_LIST);

        return $this->saveData($item, $listData);
    }

    private function saveData(PageEntity $item, $listData, $itemSave = TRUE)
    {
        if ($listData !== array()) {
            if ($item->id === NULL) { // insert
                $item->id = $this->conn->insert($this->list, $listData)
                        ->execute(\dibi::IDENTIFIER);
            } else { // update
                unset($listData["date"]);
                $this->conn->update($this->list, $listData)
                        ->where('id = %i', $item->id)
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
                'page_list_id' => $item->id,
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

    public function delete(PageEntity $item)
    {
        if ($item === NULL)
            return FALSE;
        return $this->conn->delete($this->list)
                        ->where('id = %i', $item->id)
                        ->execute();
    }

    private function selectList($lang)
    {
        return $this->conn->select($this->_getSelectAll($lang))
                        ->from($this->list)
                        ->as($this->listAlias);
    }

    private function joinItem(&$select, $lang)
    {
        return $select->leftJoin($this->item)
                        ->as($this->itemAlias)
                        ->on("{$this->listAlias}.id = {$this->itemAlias}.page_list_id")
                        ->and("{$this->itemAlias}.lang = %s", $lang);
    }

    /**
     * Returns selector for select all items
     * @param type $lang
     * @return string
     */
    private function _getSelectAll($lang, $listAlias = NULL, $itemAlias = NULL)
    {
        $listAlias = ($listAlias === NULL) ? $this->listAlias : $listAlias;
        $itemAlias = ($itemAlias === NULL) ? $this->itemAlias : $itemAlias;

        $select = array(
            "{$listAlias}.id" => "id",
            "{$listAlias}.type" => "type",
            "{$listAlias}.code" => "code",
            "{$listAlias}.comment" => "comment",
            "{$listAlias}.image" => "image",
            "{$listAlias}.order" => "order",
            "{$listAlias}.active" => "active",
            "{$listAlias}.date" => "date",
            "{$listAlias}.link" => "link",
            "{$listAlias}.position" => "position",
            "{$listAlias}.parent_id" => "parent_id",
        );
        if ($lang !== NULL) {
            $select["{$itemAlias}.lang"] = "lang";
            $select["{$itemAlias}.name"] = "name";
            $select["{$itemAlias}.perex"] = "perex";
            $select["{$itemAlias}.text"] = "text";
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
                    $where["{$this->listAlias}.id%i"] = $cond;
                    break;
                case "type":
                    $where["{$this->listAlias}.type%s"] = $cond;
                    break;
                case "active":
                    $where["{$this->listAlias}.active%b"] = $cond;
                    break;
                case "position":
                    $where["{$this->listAlias}.position%s"] = $cond;
                    break;
            }
        }
        return $where;
    }

    public function getParentsTree($lang, $type, $id = NULL)
    {
        $pages = $this->allDataSource($lang, array(
            'type' => $type,
        ));

        $list = array();
        foreach ($pages as $page)
            $list[$page->id] = $page;

        $parents = array();
        $this->_getTree($list, $parents, $id === NULL ? NULL : (int) $id);

        return $parents;
    }

    public function getSitemapTree($lang)
    {
        $listAAlias = "list_a";
        $listBAlias = "list_b";
        $itemAAlias = "item_a";
        $itemBAlias = "item_b";

        $select = $this->_getSelectAll($lang, $listBAlias, $itemBAlias);
        $select["{$itemAAlias}.name"] = "category";

        $result = $this->conn->select($select)
                ->from($this->list)->as($listBAlias)
                ->leftJoin($this->item)->as($itemBAlias)->on("{$itemBAlias}.page_list_id = {$listBAlias}.id AND {$itemBAlias}.lang = %s", $lang)
                ->join($this->list)->as($listAAlias)->on("{$listBAlias}.parent_id = {$listAAlias}.id")
                ->leftJoin($this->item)->as($itemAAlias)->on("{$itemAAlias}.page_list_id = {$listAAlias}.id AND {$itemAAlias}.lang = %s", $lang)
                ->where(array(
                    "{$listBAlias}.type%s" => "other",
                    "{$listBAlias}.position%s" => "footer",
                    "{$listBAlias}.active%b" => TRUE,
                ))
                ->orderBy("{$listAAlias}.order ASC, {$listBAlias}.order ASC");

        $sitemap = array();
        foreach ($result->fetchAll() as $item) {
            $category = $item->category;
            unset($item->category);
            if (!array_key_exists($category, $sitemap)) {
                $sitemap[$category] = array();
            }
            $sitemap[$category][] = $this->load($item);
        }
        return $sitemap;
    }

    private function _getTree(&$list, &$output, $needle = NULL, $parentId = NULL, $parentName = NULL)
    {
        foreach ($list as $key => $item) {
            if ($item->parent_id === $parentId) {
                if ($key === $needle) {
                    continue;
                }
                $output[$key] = $parentName . (empty($parentName) ? "" : " / ") . $item->name;

                $this->_getTree($list, $output, $needle, $key, $output[$key]);
            }
        }
    }

    public function move($id, $dir = self::MOVE_UP)
    {
        $entity = $this->find($id);
        $orderOld = $entity->order;
        $orderNew = $dir === self::MOVE_UP ? $orderOld - 1 : $orderOld + 1;
        if ($orderNew < 0 && self::MOVE_UP)
            return FALSE;

        $others = $this->findAll(NULL, array(
            'type' => $entity->type,
            'position' => $entity->position,
        ));
        if ($orderNew >= count($others) && self::MOVE_DOWN)
            return FALSE;

        foreach ($others as $key => $item) {
            if ($dir === self::MOVE_UP && $key == ($orderOld - 1)) {
                $item->order = $key + 1;
            } else if ($dir === self::MOVE_UP && $key == $orderOld) {
                $item->order = $key - 1;
            } else if ($dir === self::MOVE_DOWN && $key == $orderOld) {
                $item->order = $key + 1;
            } else if ($dir === self::MOVE_DOWN && $key == $orderOld + 1) {
                $item->order = $key - 1;
            } else {
                $item->order = $key;
            }
            $this->save($item, "order");
        }
        return TRUE;
    }

    public function toggleActive($id, $set = NULL)
    {
        $finded = $this->find($id);
        if ($finded->id !== NULL) {
            if ($set === self::ACTIVATE && !$finded->active) {
                $finded->active = TRUE;
                $this->save($finded, "active");
            }
            if ($set === self::DEACTIVATE && $finded->active) {
                $finded->active = FALSE;
                $this->save($finded, "active");
            }
            if ($set === NULL) {
                $finded->active = !$finded->active;
                $this->save($finded, "active");
            }

            if ($finded->active)
                return self::ACTIVATE;
            else
                return self::DEACTIVATE;
        }
        return FALSE;
    }

}

?>
