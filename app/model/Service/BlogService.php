<?php

namespace Model\Service;

use Model\Mapper\Dibi\BlogDibiMapper,
    Model\Mapper\Dibi\BlogCategoryDibiMapper,
    Model\Entity\BlogEntity;

/**
 * Blog Service
 *
 * @author Petr Poupě
 */
class BlogService
{

    /** @var BlogDibiMapper */
    private $blogMapper;

    /** @var BlogCategoryDibiMapper */
    private $categoryMapper;

    public function __construct(BlogDibiMapper $blog, BlogCategoryDibiMapper $category)
    {
        $this->blogMapper = $blog;
        $this->categoryMapper = $category;
    }

    public function getDataGrid($lang)
    {
        return $this->blogMapper->allDataSource($lang);
    }

    /**
     * Get array of selected pages
     * @param type $lang
     * @return array
     */
    public function getBlogs($lang, $order = NULL, $categoryId = NULL, $limit = NULL, $offset = NULL)
    {
        $where = $this->getPrintBlogWhere(TRUE, TRUE, $categoryId);
        return $this->blogMapper->findAll($lang, $where, $order, $limit, $offset);
    }

    public function getBlogsCount($lang, $categoryId = NULL, $onlyActive = TRUE, $onlyPublished = TRUE)
    {
        $where = $this->getPrintBlogWhere($onlyActive, $onlyPublished, $categoryId);
        return $this->blogMapper->allDataSource($lang, $where)->count();
    }

    private function getPrintBlogWhere($onlyActive = TRUE, $onlyPublished = TRUE, $categoryId = NULL)
    {
        $where = array();
        if ($onlyActive) {
            $where["active"] = TRUE;
        }
        if ($onlyPublished) {
            $where["published"] = TRUE;
        }
        if ($categoryId) {
            $where["category"] = $categoryId;
        }
        return $where;
    }

    public function getCategories($lang, $onlyActive = FALSE, $limit = NULL)
    {
        $where = array();
        if ($onlyActive) {
            $where["active"] = TRUE;
        }
        return $this->categoryMapper->allDataSource($lang, $where, $limit)->fetchPairs("id", "name");
    }

    /**
     * Get one page by ID
     * @param type $id
     * @param type $lang
     * @return type
     */
    public function getBlog($id, $lang)
    {
        return $this->blogMapper->find($id, $lang);
    }

    public function getBlogByUrl($url, $lang, $onlyActive = TRUE, $onlyPublished = TRUE)
    {
        $where = $this->getPrintBlogWhere($onlyActive, $onlyPublished);
        $where["url"] = $url;
        return $this->blogMapper->findBy($lang, $where);
    }

    /**
     * Save entity
     * @param BlogEntity $entity
     * @param type $what
     * @return BlogEntity
     */
    public function save(BlogEntity $entity, $what = NULL)
    {
        if ($entity->url === NULL) {
            $entity->url = $entity->name;
        }
        if ($entity->createDate === NULL) {
            $entity->createDate = time();
        }
        return $this->blogMapper->save($entity, $what);
    }

    public function addRead(BlogEntity $entity)
    {
        $entity->read++;
        return $this->save($entity, "read");
    }

    /**
     * Returns entity by ID
     * @param string $id
     * @param string $lang
     * @return BlogEntity
     */
    public function find($id, $lang = NULL)
    {
        return $this->blogMapper->find($id, $lang);
    }

    /**
     * Delete inserted entity, or item by inserted ID
     * @param BlogEntity|string $id
     * @return bool
     */
    public function delete($id)
    {
        if ($id instanceof BlogEntity) {
            $entity = $id;
        } else {
            $entity = $this->find($id);
        }
        return $this->blogMapper->delete($entity);
    }

    /**
     * Switch activity
     * @param type $id
     * @param type $set
     * @return boolean|NULL
     */
    public function toggleActive($id, $set = NULL)
    {
        if ($set === NULL) {
            return $this->blogMapper->toggleActive($id, $set);
        } else if ($set === TRUE) {
            return $this->blogMapper->toggleActive($id, BlogDibiMapper::ACTIVATE);
        } else if ($set === FALSE) {
            return $this->blogMapper->toggleActive($id, BlogDibiMapper::DEACTIVATE);
        } else {
            return FALSE;
        }
    }

}

?>
