<?php

namespace Model\Mapper\Dibi;

use Model\Entity\BlogEntity;

/**
 * Blog DibiMapper
 *
 * @author Petr Poupě
 */
class BlogDibiMapper extends DibiMapper
{

    const SAVE_TYPE_PRIMARY = "primary";
    const SAVE_TYPE_INFO = "info";
    const SAVE_TYPE_CATEGORY = "category";
    const SAVE_TYPE_TAGS = "tags";
    const ACTIVATE = 1;
    const DEACTIVATE = 2;

    private $primary = "blog";
    private $info = "blog_info";
    private $blog2category = "blog_to_category";
    private $blog2tag = "blog_to_tag";
    private $tag = "blog_tag";
    private $order = 'blog.publish_date DESC, blog.create_date DESC';

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param BlogEntity $item
     * @return type
     */
    private function itemToData(BlogEntity $item, $type = self::SAVE_TYPE_PRIMARY)
    {
        $data = array();
        switch ($type) {
            case self::SAVE_TYPE_PRIMARY:
                $data['id'] = $item->id;
                $data['active'] = $item->active;
                $data['create_date'] = $item->createDate;
                $data['publish_date'] = $item->publishDate;
                $data['read'] = $item->read;
                break;
            case self::SAVE_TYPE_INFO:
                $data['blog_id'] = $item->id;
                $data['lang'] = $item->lang;
                $data['url'] = $item->url;
                $data['name'] = $item->name;
                $data['perex'] = $item->perex;
                $data['text'] = $item->text;
                break;
            case self::SAVE_TYPE_CATEGORY:
                $data['blog_id'] = $item->id;
                $data['categories'] = $item->categoryIds;
                break;
            case self::SAVE_TYPE_TAGS:
                $data['blog_id'] = $item->id;
                $data['lang'] = $item->lang;
                $data['tags'] = $item->tags;
                break;
        }
        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return BlogEntity
     */
    public function load($data)
    {
        $item = new BlogEntity;
        if ($data) {
            foreach ($data as $prop => $val) {
                switch ($prop) {
                    case "create_date":
                        $item->createDate = $val;
                        break;
                    case "publish_date":
                        $item->publishDate = $val;
                        break;
                    case "category_ids":
                        $item->categoryIds = json_decode($val);
                        break;
                    case "tags":
                        $item->tags = json_decode($val);
                        break;
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
        $select = $this->joinItem($select, $lang);
        $select = $this->joinTags($select, $lang);
        $select = $this->joinCategory($select);

        if ($by !== array()) {
            $select->where($this->_getWhere($by));
        }

        switch ($order) {
            case "read":
                $select->orderBy("{$this->primary}.read DESC");
                break;

            default:
                $select->orderBy($this->order);
                break;
        }

        if ($limit)
            $select->limit($limit);
        if ($offset)
            $select->offset($offset);

        return $select;
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return BlogEntity
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
     * @return BlogEntity
     */
    public function findBy($lang, $by = array())
    {
        $select = $this->selectList($lang);
        if ($lang !== NULL) {
            $select = $this->joinItem($select, $lang);
            $select = $this->joinTags($select, $lang);
        }
        $select = $this->joinCategory($select);

        $select->where($this->_getWhere($by));

        return $this->load($select->fetch());
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
     * @param BlogEntity $item
     * @param type $what
     * @return BlogEntity
     */
    public function save(BlogEntity $item, $what = NULL)
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
     * @param BlogEntity $item
     * @param type $what
     * @return BlogEntity
     */
    private function saveOnly(BlogEntity $item, $what)
    {
        $primaryData = array();
        $infoData = array();
        foreach ($what as $whatItem) {
            switch ($whatItem) {
                case "image": // names from entity
                    $primaryData['image'] = $item->$whatItem;
                    break;
                case "active":
                    $primaryData['active'] = $item->$whatItem;
                    break;
                case "read":
                    $primaryData['read'] = $item->$whatItem;
                    break;
                case "name":
                    $infoData['name'] = $item->$whatItem;
                    break;
                case "perex":
                    $infoData['perex'] = $item->$whatItem;
                    break;
                case "text":
                    $infoData['text'] = $item->$whatItem;
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
     * @param BlogEntity $item
     * @return type
     * @throws \Exception|BlogEntity
     */
    private function saveAll(BlogEntity $item)
    {
        if ($item->name === NULL)
            throw new \Exception("Name cannot be empty");
        if ($item->url === NULL)
            throw new \Exception("Url cannot be empty");

        if ($item->id === NULL)
            $item->createDate = time();

        $primaryData = $this->itemToData($item, self::SAVE_TYPE_PRIMARY);
        $itemPrim = $this->_savePrimary($item, $primaryData);

        $infoData = $this->itemToData($itemPrim, self::SAVE_TYPE_INFO);
        $itemInfo = $this->_saveInfo($itemPrim, $infoData);

        $categoryData = $this->itemToData($itemInfo, self::SAVE_TYPE_CATEGORY);
        $itemCategory = $this->_saveCategory($itemInfo, $categoryData);

        $tagsData = $this->itemToData($itemCategory, self::SAVE_TYPE_TAGS);
        $itemTags = $this->_saveTags($itemCategory, $tagsData);

        return $itemTags;
    }

    private function _savePrimary(BlogEntity $item, array $data)
    {
        if ($data !== array()) {
            if ($item->id === NULL) { // insert
                $item->id = $this->conn->insert($this->primary, $data)
                        ->execute(\dibi::IDENTIFIER);
            } else { // update
                unset($data["create_date"]);
                $this->conn->update($this->primary, $data)
                        ->where('id = %i', $item->id)
                        ->execute();
            }
        }
        return $item;
    }

    private function _saveInfo(BlogEntity $item, array $data)
    {
        if ($item->id !== NULL && $item->lang !== NULL && ($data !== array())) {
            $cond = array(
                'blog_id' => $item->id,
                'lang' => $item->lang,
            );

            $data["blog_id"] = $item->id;
            $data["lang"] = $item->lang;
            if (array_key_exists("url", $data)) {
                $data["url"] = $this->_generateUniqueUrl($data["url"], $item->lang, $item->id);
            }

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

    private function _saveCategory(BlogEntity $item, array $data)
    {
        if ($item->id !== NULL && ($data !== array())) {
            $this->conn->delete($this->blog2category)->where("blog_id = %i", $item->id)->execute();
            if (count($data["categories"])) {
                $insert = array(
                    "blog_id" => array_fill(0, count($data["categories"]), $data["blog_id"]),
                    "blog_category_id" => $data["categories"],
                );
                $this->conn->query("INSERT INTO %n %m", $this->blog2category, $insert);
            }
        }
        return $item;
    }

    private function _saveTags(BlogEntity $item, array $data)
    {
        if ($item->id !== NULL && $item->lang !== NULL && ($data !== array())) {
            $deleteCond = array(
                "blog_id%i" => $item->id,
                "lang%s" => $item->lang,
            );
            $this->conn->delete($this->blog2tag)->where($deleteCond)->execute();

            if (array_key_exists("tags", $data)) {
                foreach ($data["tags"] as $tag) {
                    if ($tag !== NULL && $tag !== "") {
                        $tagId = $this->conn->select("id")->from($this->tag)->where("name = %s", $tag)->fetchSingle();
                        if ($tagId === FALSE) {
                            $tagId = $this->conn->insert($this->tag, array("name%s" => $tag))->execute(\dibi::IDENTIFIER);
                        }
                        if ($tagId > 0) {
                            $this->conn->insert($this->blog2tag, array(
                                "blog_id" => $item->id,
                                "lang" => $item->lang,
                                "blog_tag_id" => $tagId,
                            ))->execute();
                        }
                    }
                }
            }
            
            
        }
        return $item;
    }

    private function _isUniqueUrl($url, $lang, $exceptionId = NULL)
    {
        $select = $this->conn->select("id")->from($this->info)
                ->where("url = %s", $url)
                ->where("lang = %s", $lang);
        if ($exceptionId) {
            $select->where("blog_id != %i", $exceptionId);
        }
        return !$select->count();
    }

    private function _generateUniqueUrl($url, $lang, $ownId)
    {
        if ($this->_isUniqueUrl($url, $lang, $ownId)) {
            return $url;
        }

        $url = \Nette\Utils\Strings::webalize($url . " " . $ownId);
        if ($this->_isUniqueUrl($url, $lang, $ownId)) {
            return $url;
        }

        $prefix = "blog";
        $url = \Nette\Utils\Strings::webalize($prefix . " " . $url);
        if ($this->_isUniqueUrl($url, $lang, $ownId)) {
            return $url;
        } else {
            throw new \Exception("URL isn't unique");
        }
    }

    public function delete(BlogEntity $item)
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
                        ->on("{$this->primary}.id = {$this->info}.blog_id")
                        ->and("{$this->info}.lang = %s", $lang);
    }

    private function joinTags(&$select, $lang)
    {
        return $select->select("CONCAT('[\"', GROUP_CONCAT({$this->tag}.name SEPARATOR '\",\"'), '\"]') AS tags")
                        ->leftJoin($this->blog2tag)
                        ->on("{$this->primary}.id = {$this->blog2tag}.blog_id")
                        ->and("{$this->blog2tag}.lang = %s", $lang)
                        ->leftJoin($this->tag)
                        ->on("{$this->blog2tag}.blog_tag_id = {$this->tag}.id")
                        ->groupBy("{$this->blog2tag}.blog_id");
    }

    private function joinCategory(&$select)
    {
        return $select->select("CONCAT('[', GROUP_CONCAT(blog_category_id), ']') AS category_ids")
                        ->leftJoin($this->blog2category)
                        ->on("{$this->primary}.id = {$this->blog2category}.blog_id")
                        ->groupBy("{$this->blog2category}.blog_id");
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
            "{$this->primary}.image" => "image",
            "{$this->primary}.active" => "active",
            "{$this->primary}.create_date" => "create_date",
            "{$this->primary}.publish_date" => "publish_date",
            "{$this->primary}.read" => "read",
        );
        if ($lang !== NULL) {
            $select["{$this->info}.lang"] = "lang";
            $select["{$this->info}.url"] = "url";
            $select["{$this->info}.name"] = "name";
            $select["{$this->info}.perex"] = "perex";
            $select["{$this->info}.text"] = "text";
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
                case "published":
                    if ($cond) {
                        $where[] = array("{$this->primary}.publish_date <= %t", time());
                    }
                    break;
                case "lang":
                    $where["{$this->info}.lang%s"] = $cond;
                    break;
                case "url":
                    $where["{$this->info}.url%s"] = $cond;
                    break;
                case "category":
                    $where["{$this->blog2category}.blog_category_id%i"] = $cond;
                    break;
            }
        }
        return $where;
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
