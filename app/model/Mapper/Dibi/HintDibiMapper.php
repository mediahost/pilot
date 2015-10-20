<?php

namespace Model\Mapper\Dibi;

use Model\Entity\HintEntity;

/**
 * Hint DibiMapper
 *
 * @author Petr Poupě
 */
class HintDibiMapper extends MultilangDibiMapper
{

    protected $list = "hint_list";
    protected $item = "hint_item";
    protected $key = 'id';
    protected $foreignKey = 'hint_list_id';
    protected $order = 'list.form_id ASC';

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param HintEntity $item
     * @return type
     */
    protected function itemToData(HintEntity $item, $type = self::TYPE_LIST)
    {
        $data = array();
        switch ($type) {
            case self::TYPE_LIST:
                $data['id'] = $item->id;
                $data['form_id'] = $item->form;
                $data['comment'] = $item->comment;
                break;
            case self::TYPE_ITEM:
                $data['hint_list_id'] = $item->id;
                $data['lang'] = $item->lang;
                $data['text'] = $item->text;
                break;
        }
        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return HintEntity
     */
    public function load($data)
    {
        $item = new HintEntity;

        if ($data) {
            foreach ($data as $prop => $val) {
                switch ($prop) {
//                    case "form_id":
//                        $item->form = $val;
//                        break;
                    default:
                        $item->$prop = $val;
                        break;
                }
            }
        }

        return $item;
    }

    /**
     * Save only selected columns
     * @param \Model\Entity\HintEntity $item
     * @param type $what
     * @return HintEntity
     */
    protected function saveOnly(HintEntity $item, $what)
    {
        $listData = array();
        $itemData = array();
        foreach ($what as $whatItem) {
            switch ($whatItem) {
                default:
                    break;
            }
        }
        return $this->saveData($item, $listData, $itemData);
    }

    /**
     * Save all columns
     * @param \Model\Entity\HintEntity $item
     * @return type
     * @throws \Exception|HintEntity
     */
    protected function saveAll(HintEntity $item)
    {
        if ($item->form === NULL)
            throw new \Exception("Form number cannot be empty");
        
        $listData = $this->itemToData($item, self::TYPE_LIST);

        return $this->saveData($item, $listData);
    }

    public function delete(HintEntity $item)
    {
        if ($item === NULL)
            return FALSE;
        return $this->conn->delete($this->list)
                        ->where("{$this->key} = %i", $item->id)
                        ->execute();
    }

    /**
     * Returns selector for select all items
     * @param type $lang
     * @return string
     */
    protected function _getSelectAll($lang)
    {
        $select = array(
            "{$this->listAlias}.id" => "id",
            "{$this->listAlias}.form_id" => "form",
            "{$this->listAlias}.comment" => "comment",
        );
        if ($lang !== NULL) {
            $select["{$this->itemAlias}.lang"] = "lang";
            $select["{$this->itemAlias}.text"] = "text";
        }
        return $select;
    }

    /**
     * Returns WHERE array inserted by entity keys
     * @param type $by
     * @return type
     */
    protected function _getWhere($by)
    {
        $where = array();
        foreach ($by as $item => $cond) {
            switch ($item) {
                case "id":
                    $where["{$this->listAlias}.id%i"] = $cond;
                    break;
                case "form":
                    $where["{$this->listAlias}.form_id%i"] = $cond;
                    break;
            }
        }
        return $where;
    }

}

?>
