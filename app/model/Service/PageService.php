<?php

namespace Model\Service;

use Model\Mapper\Dibi\PageDibiMapper,
    Model\Entity\PageEntity;

/**
 * Page Service
 *
 * @author Petr Poupě
 */
class PageService
{

    const DATASOURCE_ALL = "all";
    const DATASOURCE_MODULES = "modules";
    const DATASOURCE_SLIDES = "slides";
    const DATASOURCE_BLOGS = "blogs";
    const DATASOURCE_OTHER = "other";

    /** @var \Model\Mapper\Dibi\PageDibiMapper */
    private $mapper;

    public function __construct(PageDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function mapDatasourceType($type)
    {
        switch ($type) {
            case self::DATASOURCE_MODULES:
                $renamed = PageEntity::TYPE_MODULE;
                break;
            case self::DATASOURCE_SLIDES:
                $renamed = PageEntity::TYPE_SLIDE;
                break;
            case self::DATASOURCE_BLOGS:
                $renamed = PageEntity::TYPE_BLOG;
                break;
            case self::DATASOURCE_OTHER:
            default:
                $renamed = PageEntity::TYPE_OTHER;
                break;
        }
        return $renamed;
    }

    public function _fillWhereType(array &$where, $type = self::DATASOURCE_ALL)
    {
        switch ($type) {
            case self::DATASOURCE_MODULES:
                $where["type"] = PageEntity::TYPE_MODULE;
                break;
            case self::DATASOURCE_SLIDES:
                $where["type"] = PageEntity::TYPE_SLIDE;
                break;
            case self::DATASOURCE_BLOGS:
                $where["type"] = PageEntity::TYPE_BLOG;
                break;
            case self::DATASOURCE_OTHER:
                $where["type"] = PageEntity::TYPE_OTHER;
                break;

            case self::DATASOURCE_ALL:
            default:
                break;
        }
    }

    /**
     * Return dibi fluent of all or selected pages
     * @return \DibiFluent
     */
    public function getPages($lang, $type = self::DATASOURCE_ALL)
    {
        $where = array();
        $this->_fillWhereType($where, $type);
        return $this->mapper->allDataSource($lang, $where);
    }

    /**
     * Get array of selected pages
     * @param type $lang
     * @return type
     */
    public function getPagesArray($lang, $type = self::DATASOURCE_ALL, $order = NULL, $limit = NULL, $offset = NULL, $position = NULL)
    {
        $where = array(
            "active" => TRUE,
        );
        if ($position !== NULL) {
            $where ["position"] = $position;
        }
        $this->_fillWhereType($where, $type);
        return $this->mapper->findAll($lang, $where, $order, $limit, $offset);
    }

    public function getPagesCount($lang, $type = self::DATASOURCE_ALL, $order = NULL)
    {
        $where = array(
            'active' => TRUE,
        );
        $this->_fillWhereType($where, $type);
        return $this->mapper->allDataSource($lang, $where, $order)->count();
    }

    /**
     * Get one page by ID
     * @param type $id
     * @param type $lang
     * @return type
     */
    public function getPage($id, $lang, $type = self::DATASOURCE_ALL)
    {
        $where = array(
            'id' => $id,
            'active' => TRUE,
        );
        $this->_fillWhereType($where, $type);
        return $this->mapper->findBy($lang, $where);
    }
    
    public function getPageByPosition($position, $lang, $type = self::DATASOURCE_ALL)
    {
        $where = array(
            'active' => TRUE,
            'position' => $position,
        );
        $this->_fillWhereType($where, $type);
        return $this->mapper->findBy($lang, $where);
    }

    public function getModules($lang)
    {
        return $this->getPagesArray($lang, self::DATASOURCE_MODULES);
    }

    public function getModule($id, $lang)
    {
        return $this->getPage($id, $lang, self::DATASOURCE_MODULES);
    }

    public function getBlogs($lang, $order = NULL, $limit = NULL, $offset = NULL)
    {
        return $this->getPagesArray($lang, self::DATASOURCE_BLOGS, $order, $limit, $offset);
    }

    public function getBlogsCount($lang, $order = NULL)
    {
        return $this->getPagesCount($lang, self::DATASOURCE_BLOGS, $order);
    }

    public function getBlog($id, $lang)
    {
        return $this->getPage($id, $lang, self::DATASOURCE_BLOGS);
    }

    public function getSlides($lang)
    {
        return $this->getPagesArray($lang, self::DATASOURCE_SLIDES);
    }

    public function getSlide($id, $lang)
    {
        return $this->getPage($id, $lang, self::DATASOURCE_SLIDES);
    }

    public function getPagePositions()
    {
        $positions = array(
            'topmenu' => "Top Menu",
            'footer' => "Footer",
            'terms' => "Terms & Conditions",
        );
        return $positions;
    }

    /**
     * Tree list of pages - id is key
     * @return array
     */
    public function getPageParents($lang, $id = NULL, $type = self::DATASOURCE_OTHER)
    {
        $type = $this->mapDatasourceType($type);
        return $this->mapper->getParentsTree($lang, $type, $id);
    }

    public function getSitemapTree($lang)
    {
        return $this->mapper->getSitemapTree($lang);
    }

    public function getPagesOnTop($lang)
    {
        return $this->getPagesArray($lang, $type = self::DATASOURCE_OTHER, NULL, NULL, NULL, "topmenu");
    }

    public function getTermPage($lang)
    {
        return $this->getPageByPosition("terms", $lang);
    }

    /**
     * Save entity
     * @param \Model\Entity\PageEntity $entity
     * @param type $what
     * @return PageEntity
     */
    public function save(PageEntity $entity, $what = NULL)
    {
        if ($what === NULL) {
            if ($entity->code === NULL) {
                $entity->code = \Nette\Utils\Strings::webalize($entity->name);
            }
        }
        return $this->mapper->save($entity, $what);
    }

    /**
     * Returns entity by ID
     * @param string $id
     * @param string $lang
     * @return PageEntity
     */
    public function find($id, $lang = NULL)
    {
        return $this->mapper->find($id, $lang);
    }

    /**
     * Delete inserted entity, or item by inserted ID
     * @param \Model\Entity\PageEntity|string $id
     * @return bool
     */
    public function delete($id)
    {
        if ($id instanceof PageEntity) {
            $entity = $id;
        } else {
            $entity = $this->find($id);
        }
        return $this->mapper->delete($entity);
    }

    public function moveDown($id)
    {
        return $this->mapper->move($id, PageDibiMapper::MOVE_DOWN);
    }

    public function moveUp($id)
    {
        return $this->mapper->move($id, PageDibiMapper::MOVE_UP);
    }

    public function toggleActive($id, $set = NULL)
    {
        return $this->mapper->toggleActive($id, $set);
    }

}

?>
