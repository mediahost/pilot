<?php

namespace Model\Service;

use Model\Mapper\Dibi\ForumDibiMapper,
    Model\Mapper\Dibi\ForumCategoryDibiMapper,
    Model\Mapper\Dibi\ForumTopicDibiMapper,
    Model\Mapper\Dibi\ForumPostDibiMapper,
    Model\Entity\ForumEntity,
    Model\Entity\ForumCategoryEntity,
    Model\Entity\ForumTopicEntity,
    Model\Entity\ForumPostEntity;

/**
 * Forum Service
 *
 * @author Petr Poupě
 */
class ForumService
{

    /** @var ForumDibiMapper */
    private $forumMapper;

    /** @var ForumCategoryDibiMapper */
    private $categoryMapper;

    /** @var ForumTopicDibiMapper */
    private $topicMapper;

    /** @var ForumPostDibiMapper */
    private $postMapper;

    public function __construct(ForumDibiMapper $forumMapper, ForumCategoryDibiMapper $categoryMapper, ForumTopicDibiMapper $topicMapper, ForumPostDibiMapper $postMapper)
    {
        $this->forumMapper = $forumMapper;
        $this->categoryMapper = $categoryMapper;
        $this->topicMapper = $topicMapper;
        $this->postMapper = $postMapper;
    }

// <editor-fold defaultstate="collapsed" desc="category">
    public function getCategories($lang, $parent = NULL, $onlyWithTopics = TRUE)
    {
        $categories = $this->categoryMapper->getCategories($lang, $parent);
        if ($onlyWithTopics) // pouze pokud obsahují témata
            foreach ($categories as $key => $category) {
                if (!$category->forumsCount) {
                    unset($categories[$key]);
                }
            }
        return $categories;
    }

    public function getCategory($cid)
    {
        return $this->categoryMapper->find($cid);
    }

    public function sortCategories(array $sorted)
    {
        return $this->categoryMapper->sort($sorted);
    }

    public function saveCategory(ForumCategoryEntity $category)
    {
        return $this->categoryMapper->save($category);
    }

    public function deleteCategory(ForumCategoryEntity $category)
    {
        return $this->categoryMapper->delete($category);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="forum">
    /**
     * Returns forum by ID
     * @param string $id
     * @return ForumEntity
     */
    public function getForum($fid)
    {
        return $this->forumMapper->find($fid);
    }

    public function saveForum(ForumEntity $forum)
    {
        return $this->forumMapper->save($forum);
    }

    public function deleteForum(ForumEntity $forum)
    {
        return $this->forumMapper->delete($forum);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="topic">
    /**
     * Returns forum by ID
     * @param string $id
     * @param int $offset
     * @param int $limit
     * @return ForumEntity
     */
    public function getTopics($fid, $offset = 0, $limit = 0)
    {
        return $this->topicMapper->getTopics($fid, $offset, $limit);
    }

    public function getTopicsCount($fid)
    {
        return $this->topicMapper->getTopicsCount($fid);
    }

    public function getTopic($tid)
    {
        return $this->topicMapper->find($tid);
    }

    public function addTopicView($tid)
    {
        $topic = $this->topicMapper->find($tid);
        if ($topic !== NULL) {
            $topic->countViews++;
            $this->topicMapper->save($topic);
        }
    }

    public function saveTopic(ForumTopicEntity $topic)
    {
        return $this->topicMapper->save($topic);
    }

    public function deleteTopic(ForumTopicEntity $topic)
    {
        return $this->topicMapper->delete($topic);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="post">
    public function getPosts($tid)
    {
        return $this->postMapper->getPosts($tid);
    }

    public function getPost($pid)
    {
        return $this->postMapper->find($pid);
    }

    public function getFirstPost($tid)
    {
        return $this->postMapper->findByTopic($tid);
    }

    public function savePost(ForumPostEntity $post)
    {
        return $this->postMapper->save($post);
    }

    public function deletePost(ForumPostEntity $post)
    {
        return $this->postMapper->delete($post);
    }

// </editor-fold>
}

?>
