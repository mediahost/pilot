<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ForumPostEntity;

/**
 * Forum DibiMapper
 *
 * @author Petr Poupě
 */
class ForumPostDibiMapper extends ForumParentDibiMapper
{

    /** @var ForumDibiMapper */
    private $forumMapper;

    /** @var ForumTopicDibiMapper */
    private $topicMapper;

    /** @var string */
    private $table;

    public function __construct(\DibiConnection $conn)
    {
        parent::__construct($conn);
        $this->table = $this->posts;
    }

    private function getForumMapper()
    {
        if (!$this->forumMapper instanceof ForumDibiMapper) {
            $this->forumMapper = new ForumDibiMapper($this->conn);
        }
        return $this->forumMapper;
    }

    private function getTopicMapper()
    {
        if (!$this->topicMapper instanceof ForumTopicDibiMapper) {
            $this->topicMapper = new ForumTopicDibiMapper($this->conn);
        }
        return $this->topicMapper;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param ForumPostEntity $item
     * @return type
     */
    private function itemToData(ForumPostEntity $item)
    {
        $data = array(
            "id" => $item->id,
            "forum_topic_id" => $item->topicId,
            "user_id" => $item->userId,
            "body" => $item->body,
            "date" => $item->date,
        );

        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return ForumPostEntity
     */
    private function load($data)
    {
        $item = new ForumPostEntity;
        if ($data) {
            $item->id = $data->id;
            $item->topicId = $data->forum_topic_id;
            $item->userId = $data->user_id;
            $item->username = $data->username;
            $item->body = $data->body;
            $item->date = $data->date;
        }
        return $item;
    }

    private function selectList($orderDir = TRUE)
    {
        return $this->conn->select(array(
                            "{$this->table}.*",
                            "{$this->users}.username" => "username",
                        ))->from($this->table)
                        ->leftJoin($this->users)->on("{$this->table}.user_id = {$this->users}.id")
                        ->orderBy(array("{$this->table}.date" => $orderDir));
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
                    $where["{$this->table}.id%i"] = $cond;
                    break;
                case "topic":
                case "forum_topic_id":
                    $where["{$this->table}.forum_topic_id%i"] = $cond;
                    break;
            }
        }
        return $where;
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return ForumPostEntity
     */
    public function find($id)
    {
        return $this->findBy(array(
                    "id" => $id,
        ));
    }

    public function findByTopic($tid, $fromStart = TRUE)
    {
        return $this->findBy(array(
                    "topic" => $tid,
                        ), $fromStart);
    }

    /**
     * Find one entity by conditions
     * @param type $by
     * @return PageEntity
     */
    public function findBy($by = array(), $orderDir = TRUE)
    {
        $data = $this->selectList($orderDir);
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
     * @param ForumPostEntity $item
     * @return ForumPostEntity
     */
    public function save(ForumPostEntity $item, $withChangeLastPost = TRUE)
    {

        $item->date = time();
        $data = $this->itemToData($item);
        if ($item->id === NULL) { // insert
            $item->id = $this->conn->insert($this->table, $data)
                    ->execute(\dibi::IDENTIFIER);
            if ($withChangeLastPost)
                $this->changeLastPost($item, TRUE);
        } else { // update
            unset($data["date"]);
            $this->conn->update($this->table, $data)
                    ->where('id = %i', $item->id)
                    ->execute();
        }
        return $item;
    }

    private function changeLastPost(ForumPostEntity $lastPost, $insertion = FALSE, $deletion = FALSE)
    {
        $topic = $this->getTopicMapper()->setLastPost($lastPost, $insertion, $deletion);
        $this->getForumMapper()->setLastPost($topic->forumId, $lastPost, $insertion, $deletion);
    }

    private function changePostCount($tid, $op = TRUE)
    {
        if ($op) {
            $add = TRUE;
            $sub = FALSE;
        } else {
            $add = FALSE;
            $sub = TRUE;
        }
        $topic = $this->getTopicMapper()->setPostCount($tid, $add, $sub);
        $this->getForumMapper()->setPostCount($topic->forumId, $add, $sub);
    }

    /**
     * Delete entity
     * @param ForumPostEntity $item
     * @return bool
     */
    public function delete(ForumPostEntity $item)
    {
        $data = $this->selectList()->where($this->_getWhere(array("topic" => $item->topicId)));
        $result = $data->fetchPairs("id", "id");

        if (\CommonHelpers::isFirst($item->id, $result)) {
            return FALSE;
        }
        if (\CommonHelpers::isLast($item->id, $result)) {
            end($result);
            $preLastId = prev($result);
            $preLastPost = $this->find($preLastId);
            $this->changeLastPost($preLastPost, FALSE, TRUE);
        } else {
            $this->changePostCount($item->topicId, FALSE);
        }

        return $this->conn->delete($this->table)
                        ->where('id = %i', $item->id)
                        ->execute();
    }

    public function getPosts($tid)
    {
        return $this->findAll(array(
                    "topic" => $tid,
        ));
    }

}

?>
