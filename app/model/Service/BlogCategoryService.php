<?php

namespace Model\Service;

use Model\Mapper\Dibi\BlogCategoryDibiMapper,
    Model\Entity\BlogCategoryEntity;

/**
 * Blog Service
 *
 * @author Petr Poupě
 */
class BlogCategoryService
{

    /** @var BlogCategoryDibiMapper */
    private $mapper;

    public function __construct(BlogCategoryDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getDataGrid($lang)
    {
        return $this->mapper->allDataSource($lang);
    }

    /**
     * Get array of selected pages
     * @param type $lang
     * @return array
     */
    public function getCategories($lang, $onlyActive)
    {
        $where = array();
        if ($onlyActive) {
            $where["active"] = TRUE;
        }
        return $this->mapper->findAll($lang, $where);
    }

    /**
     * Get one page by ID
     * @param type $id
     * @param type $lang
     * @return type
     */
    public function getCategory($id, $lang)
    {
        return $this->mapper->find($id, $lang);
    }

    /**
     * Save entity
     * @param BlogEntity $entity
     * @return BlogEntity
     */
    public function save(BlogCategoryEntity $entity, $what = NULL)
    {
        return $this->mapper->save($entity, $what);
    }

    /**
     * Returns entity by ID
     * @param string $id
     * @param string $lang
     * @return BlogEntity
     */
    public function find($id, $lang = NULL)
    {
        return $this->mapper->find($id, $lang);
    }

    /**
     * Delete inserted entity, or item by inserted ID
     * @param BlogEntity|string $id
     * @return bool
     */
    public function delete($id)
    {
        if ($id instanceof BlogCategoryEntity) {
            $entity = $id;
        } else {
            $entity = $this->find($id);
        }
        return $this->mapper->delete($entity);
    }

    public function toggleActive($id, $set = NULL)
    {
        $entity = $this->find($id);
        if ($set === TRUE) {
            $entity->active = TRUE;
        } else if ($set === FALSE) {
            $entity->active = FALSE;
        } else {
            $entity->active = !$entity->active;
        }
        return $this->save($entity, "active");
    }

}

?>
