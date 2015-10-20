<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ForumEntity,
    Model\Entity\ForumPostEntity;

/**
 * Forum DibiMapper
 *
 * @author Petr Poupě
 */
class ForumDibiMapper extends ForumParentDibiMapper
{

    /** @var ForumTopicDibiMapper */
    private $topicMapper;

    /** @var ForumPostDibiMapper */
    private $postMapper;

    /** @var string */
    private $table;

    public function __construct(\DibiConnection $conn)
    {
        parent::__construct($conn);
        $this->table = $this->forum;
    }

    /**
     * @return ForumTopicDibiMapper
     */
    private function getTopicMapper()
    {
        if (!$this->topicMapper instanceof ForumTopicDibiMapper) {
            $this->topicMapper = new ForumTopicDibiMapper($this->conn);
        }
        return $this->topicMapper;
    }

    /**
     * @return ForumPostDibiMapper
     */
    private function getPostMapper()
    {
        if (!$this->postMapper instanceof ForumPostDibiMapper) {
            $this->postMapper = new ForumPostDibiMapper($this->conn);
        }
        return $this->postMapper;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param ForumEntity $item
     * @return type
     */
    private function itemToData(ForumEntity $item)
    {
        $data = array(
            "id" => $item->id,
            "forum_category_id" => $item->categoryId,
            "name" => $item->name,
            "description" => $item->description,
            "date" => $item->date,
            "date_last_topic" => $item->dateLastTopic,
            "date_last_post" => $item->dateLastPost,
            "last_post_id" => $item->lastPostId,
            "count_topics" => $item->countTopics,
            "count_posts" => $item->countPosts,
        );

        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return ForumEntity
     */
    public function load($data)
    {
        $item = new ForumEntity;

        if ($data) {
            $item->id = $data->id;
            $item->name = $data->name;
            $item->description = $data->description;
            $item->date = $data->date;
            $item->categoryId = $data->forum_category_id;
            $item->dateLastTopic = $data->date_last_topic;
            $item->dateLastPost = $data->date_last_post;
            $item->countTopics = $data->count_topics;
            $item->countPosts = $data->count_posts;
            if ($data->last_post_id !== NULL)
                $item->lastPostId = $data->last_post_id;
            $item->lastPost = $this->getPost($data->last_post_id);
        }

        return $item;
    }

    /**
     * Return PostEntity
     * @param type $id
     * @return \Model\Entity\ForumPostEntity
     */
    private function getPost($id)
    {
        return $this->getPostMapper()->find($id);
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return ForumEntity
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
     * @return ForumEntity
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
     * @param \Model\Entity\ForumEntity $item
     * @return ForumEntity
     */
    public function save(ForumEntity $item)
    {
        if ($item->id === NULL) { // insert
            if ($item->date === NULL)
                $item->date = time();
            $data = $this->itemToData($item);
            $item->id = $this->conn->insert($this->table, $data)
                    ->execute(\dibi::IDENTIFIER);
        } else { // update
            $data = $this->itemToData($item);
            $this->conn->update($this->table, $data)
                    ->where('id = %i', $item->id)
                    ->execute();
        }
        return $item;
    }

    public function delete(ForumEntity $item)
    {
        return $this->conn->delete($this->table)
                        ->where('id = %i', $item->id)
                        ->execute();
    }

    private function selectList()
    {
        return $this->conn->select("*")
                        ->from($this->table)
                        ->orderBy("date_last_post DESC, date DESC");
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
                case "category":
                case "forum_category_id":
                    $where["forum_category_id%i"] = $cond;
                    break;
            }
        }
        return $where;
    }

    public function getCategories($lang, $onlyActive = TRUE)
    {
        $select = $this->conn->select("*")->from($this->category)
                ->where("lang = %s", $lang);
        if ($onlyActive) {
            $select->where("active = %b", $onlyActive);
        }
        return $select->fetchAssoc("id");
    }

    public function getForums($cid)
    {
        $results = $this->findAll(array(
            "category" => $cid,
        ));
        return $results;
    }

    public function setLastPost($fid, ForumPostEntity $item, $insertion = FALSE, $deletion = FALSE)
    {
        $forum = $this->find($fid);
        if ($forum->id !== NULL) {
            $forum->lastPostId = $item->id;
            $forum->dateLastPost = $item->date;
            if ($insertion)
                $forum->countPosts++;
            if ($deletion)
                $forum->countPosts--;
            $this->save($forum);
        }
    }

    public function updateLastPost($fid)
    {
        $forum = $this->find($fid);
        $lastTopic = $this->getTopicMapper()->findByForum($fid);
        if ($forum->lastPostId !== $lastTopic->id) {
            $forum->lastPostId = $lastTopic->lastPostId;
            $forum->dateLastTopic = $lastTopic->date;
            $forum->dateLastPost = $lastTopic->dateLastPost;
            $this->save($forum);
        }
    }

    public function setPostCount($fid, $add = FALSE, $sub = FALSE)
    {
        $forum = $this->find($fid);
        if ($forum->id !== NULL) {
            if ($add)
                $forum->countPosts++;
            if ($sub)
                $forum->countPosts--;
            $this->save($forum);
        }
    }

    public function setTopicCount($fid, $add = FALSE, $sub = FALSE)
    {
        $forum = $this->find($fid);
        if ($forum->id !== NULL) {
            if ($add)
                $forum->countTopics++;
            if ($sub)
                $forum->countTopics--;
            $this->save($forum);
        }
    }

}

?>
