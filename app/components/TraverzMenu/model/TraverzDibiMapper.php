<?php

namespace Pupek\TraversalMenu\Model;

/**
 * TraverzMenu DibiMapper
 *
 * @author Petr Poupě
 */
class TraversalDibiMapper extends \Nette\Object implements ITraversalMapper
{

    const moveUP = "UP";
    const moveDOWN = "DOWN";

    /** @var \DibiConnection */
    protected $conn;

    /** @var string */
    protected $table;

    public function __construct(\DibiConnection $conn, $table)
    {
        $this->conn = $conn;
        $this->table = $table;
    }

    public function find($id)
    {
        return $this->conn->select('*')->from($this->table)->where("id = %i", $id)->fetch();
    }

    public function getPath($nodeId)
    {
        $main = $this->find($nodeId);
        if ($main) {
            $result = $this->conn->select('*')->from("menu")
                    ->where("lft <= %i", $main->lft)
                    ->where("rgt >= %i", $main->rgt);
            return $result;
        }
        return $main;
    }

    public function getChildren($parentId, $deep)
    {
        if ($parentId) {
            $parent = $this->find($parentId);
            if ($parent) {
                if ($deep)
                    $deep = $deep + $parent->deep;
                $result = $this->conn->select('*')->from($this->table)->orderBy("lft ASC")
                        ->where("lft >= %i", $parent->lft)
                        ->where("rgt <= %i", $parent->rgt);
            } else
                return $parent;
        } else {
            $result = $this->conn->select('*')->from($this->table)->orderBy("lft ASC");
        }

        if (isset($result)) {
            if ($deep) {
                $result->where("deep < %i", $deep);
            }
        }
        return $result;
    }

    public function addChild($parentId, NodeEntity $node)
    {
        $new = FALSE;
        $this->conn->begin();
        $parent = $this->find($parentId);
        if ($parent) {
            $dataLft = array('lft%sql' => 'lft + 2');
            $this->conn->update($this->table, $dataLft)->where("lft > %i", $parent->rgt)->execute();
            $dataRgt = array('rgt%sql' => 'rgt + 2');
            $this->conn->update($this->table, $dataRgt)->where("rgt >= %i", $parent->rgt)->execute();
            $dataIns = array(
                'name' => $node->name,
                'link' => $node->link,
                'lft' => $parent->rgt,
                'rgt' => $parent->rgt + 1,
                'deep' => $parent->deep + 1,
                'parent_id' => $parent->id,
            );
            $new = $this->conn->insert($this->table, $dataIns)->execute(\dibi::IDENTIFIER);
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }
        return $new;
    }

    public function deleteSubtree($nodeId)
    {
        $this->conn->begin();
        $parent = $this->find($nodeId);
        if ($parent) {
            $cond = array(
                array("lft >= %i", $parent->lft),
                array("rgt <= %i", $parent->rgt),
            );
            $this->conn->delete($this->table)->where($cond)->execute();
            $diff = $parent->rgt - $parent->lft + 1;
            $dataLft = array('lft%sql' => "lft - $diff");
            $dataRgt = array('rgt%sql' => "rgt - $diff");
            $condLft = array(array("lft > %i", $parent->rgt));
            $condRgt = array(array("rgt > %i", $parent->rgt));
            $this->conn->update($this->table, $dataLft)->where($condLft)->execute();
            $this->conn->update($this->table, $dataRgt)->where($condRgt)->execute();
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }
    }

    public function insertNew(NodeEntity $node)
    {
        $newId = $this->conn->query("INSERT INTO %n (lft, rgt, name) SELECT IFNULL(MAX(rgt), 0) + 1, IFNULL(MAX(rgt), 0) + 2, %s FROM %n", $this->table, $node->name, $this->table);
        return $newId;
    }

    public function moveEdge($nodeId, $dir = self::moveUP)
    {
        $mainNode = $this->find($nodeId);
        if ($mainNode) {
            $result = $this->conn->select('*')->from($this->table)->where("parent_id = %i", $mainNode->parent_id);
            if ($dir === self::moveUP) {
                $result->where("lft < %i", $mainNode->lft)->orderBy("lft DESC");
            } else {
                $result->where("lft > %i", $mainNode->lft)->orderBy("lft ASC");
            }
            $second = $result->fetch();

            if ($second) {
                $this->conn->begin();

                $this->conn->update($this->table, array('lft' => $second->lft))
                        ->where("id = %i", $mainNode->id)->execute();
                $this->conn->update($this->table, array('lft' => $mainNode->lft))
                        ->where("id = %i", $second->id)->execute();

                $this->rebuildAllTree();

                $this->conn->commit();
            }
        }
    }

    private function rebuildAllTree()
    {
        $this->rebuildTree(0, 0);
    }

    public function rebuildTree($parent, $parentLeft)
    {
        $right = $parentLeft + 1;
        $result = $this->db->select('id, name')->from($this->table)->where("parent_id = %i", $parent)->orderBy("lft ASC");
        foreach ($result->fetchAll() as $row) {
            $right = $this->rebuildTree($row->id, $right);
        }
        $data = array(
            'lft%i' => $parentLeft,
            'rgt%i' => $right,
        );
        $this->db->update($this->table, $data)->where("id = %i", $parent)->execute();
        return $right + 1;
    }

    public function load($data)
    {
        $item = new NodeEntity;
        $rename = array(
            'parent_id' => "parentId",
            'lft' => "left",
            'rgt' => "right",
        );

        foreach ($data as $prop => $val) {
            if (array_key_exists($prop, $rename))
                $item->{$rename[$prop]} = $val;
            else
                $item->$prop = $val;
        }

        return $item;
    }

}

?>
