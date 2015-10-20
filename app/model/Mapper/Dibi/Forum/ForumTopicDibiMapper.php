<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ForumTopicEntity,
    Model\Entity\ForumPostEntity;

/**
 * Topic DibiMapper
 *
 * @author Petr Poupě
 */
class ForumTopicDibiMapper extends ForumParentDibiMapper
{

    /** @var ForumDibiMapper */
    private $forumMapper;

    /** @var ForumPostDibiMapper */
    private $postMapper;

    /** @var string */
    private $table;

    public function __construct(\DibiConnection $conn)
    {
        parent::__construct($conn);
        $this->table = $this->topics;
    }

    private function getForumMapper()
    {
        if (!$this->forumMapper instanceof ForumDibiMapper) {
            $this->forumMapper = new ForumDibiMapper($this->conn);
        }
        return $this->forumMapper;
    }

    private function getPostMapper()
    {
        if (!$this->postMapper instanceof ForumPostDibiMapper) {
            $this->postMapper = new ForumPostDibiMapper($this->conn);
        }
        return $this->postMapper;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole - pro ukládání
     * @param ForumTopicEntity $item
     * @return type
     */
    private function itemToData(ForumTopicEntity $item)
    {
        $data = array(
            "id" => $item->id,
            "forum_id" => $item->forumId,
            "name" => $item->name,
            "date" => $item->date,
            "date_last_post" => $item->dateLastPost,
            "first_post_id" => $item->firstPostId,
            "last_post_id" => $item->lastPostId,
            "count_posts" => $item->countPosts,
            "count_views" => $item->countViews,
        );

        return $data;
    }

    /**
     * Insert data from DB to entity
     * @param type $data
     * @return ForumTopicEntity
     */
    private function load($row)
    {
        $item = new ForumTopicEntity;
        if ($row) {
            $item->id = $row->id;
            $item->forumId = $row->forum_id;
            $item->name = $row->name;
            $item->date = $row->date;
            $item->dateLastPost = $row->date_last_post;
            $item->firstPostId = $row->first_post_id;
            $item->firstPost = $this->getPost($row->first_post_id);
            $item->lastPostId = $row->last_post_id;
            $item->lastPost = $this->getPost($row->last_post_id);
            $item->countPosts = $row->count_posts;
            $item->countViews = $row->count_views;
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
     * @return ForumTopicEntity
     */
    public function find($id)
    {
        return $this->findBy(array(
                    "id" => $id,
        ));
    }
    
    public function findByForum($fid, $fromStart = FALSE)
    {
        return $this->findBy(array(
                    "forum" => $fid,
        ), $fromStart);
    }

    /**
     * Find one entity by conditions
     * @param type $by
     * @return ForumTopicEntity
     */
    public function findBy($by = array(), $orderDir = FALSE)
    {
        $data = $this->selectList($orderDir);
        $data->where($this->_getWhere($by));

        return $this->load($data->fetch());
    }

    private function getFindSource($by = array(), $offset = 0, $limit = 0)
    {
        $data = $this->selectList();
        $data->where($this->_getWhere($by));
        if ($offset)
            $data->offset($offset);
        if ($limit)
            $data->limit($limit);
        return $data;
    }

    /**
     * Return array of entities
     * @param type $by
     * @return array
     */
    public function findAll($by = array(), $offset = 0, $limit = 0)
    {
        $data = $this->getFindSource($by, $offset, $limit);
        $items = array();
        foreach ($data->fetchAssoc("id") as $id => $item) {
            $items[$id] = $this->load($item);
        }
        return $items;
    }

    /**
     * Save entity
     * @param ForumTopicEntity $item
     * @return ForumTopicEntity
     */
    public function save(ForumTopicEntity $item)
    {        
        if ($item->id === NULL) { // insert
            $item->date = time();
            $data = $this->itemToData($item);
            $item->id = $this->conn->insert($this->table, $data)
                    ->execute(\dibi::IDENTIFIER);
            
            $item->firstPost->topicId = $item->id;
            $item->firstPost->date = time();
            $firstPost = $this->getPostMapper()->save($item->firstPost, FALSE);
            $item->firstPostId = $firstPost->id;
            $item->lastPostId = $firstPost->id;
            $item->dateLastPost = $firstPost->date;
            $this->save($item);
            
            $this->getForumMapper()->setTopicCount($item->forumId, TRUE);
            $this->getForumMapper()->updateLastPost($item->forumId);
            
        } else { // update
            $data = $this->itemToData($item);
            $firstPost = $item->firstPost;
            $this->getPostMapper()->save($firstPost); // save first post
            
            $this->conn->update($this->table, $data)
                    ->where('id = %i', $item->id)
                    ->execute();
        }
        
        return $item;
    }

    public function delete(ForumTopicEntity $item)
    {
        $deleted = $this->conn->delete($this->table)
                        ->where('id = %i', $item->id)
                        ->execute();
        $this->getForumMapper()->setTopicCount($item->forumId, FALSE, TRUE);
        $this->getForumMapper()->updateLastPost($item->forumId);
        return $deleted;
    }

    private function selectList($orderDir = FALSE)
    {
        return $this->conn->select("*")
                        ->from($this->table)
                        ->orderBy(array(
                            "date_last_post" => $orderDir,
                            "date" => $orderDir,
                        ));
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
                case "forum":
                case "forum_id":
                    $where["forum_id%i"] = $cond;
                    break;
            }
        }
        return $where;
    }

    public function getTopics($fid, $offset = 0, $limit = 0)
    {
        return $this->findAll(array(
                    "forum" => $fid,
                        ), $offset, $limit);
    }

    public function getTopicsCount($fid)
    {
        return $this->getFindSource(array(
                    "forum" => $fid,
                ))->count();
    }

    public function setLastPost(ForumPostEntity $item, $insertion = FALSE, $deletion = FALSE)
    {
        $topic = $this->find($item->topicId);
        if ($topic->id !== NULL) {
            $topic->lastPostId = $item->id;
            $topic->dateLastPost = $item->date;
            if ($insertion)
                $topic->countPosts++;
            if ($deletion)
                $topic->countPosts--;
            return $this->save($topic);
        }
        return $topic;
    }

    public function setPostCount($tid, $add = FALSE, $sub = FALSE)
    {
        $topic = $this->find($tid);
        if ($topic->id !== NULL) {
            if ($add)
                $topic->countPosts++;
            if ($sub)
                $topic->countPosts--;
            return $this->save($topic);
        }
        return $topic;
    }

}

?>
