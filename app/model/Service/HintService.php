<?php

namespace Model\Service;

use Model\Mapper\Dibi\HintDibiMapper,
    Model\Entity\HintEntity;

/**
 * Hint Service
 *
 * @author Petr Poupě
 */
class HintService
{

    /** @var \Model\Mapper\Dibi\HintDibiMapper */
    private $mapper;

    public function __construct(HintDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Return dibi fluent of all or selected hints
     * @param type $lang
     * @param type $type
     * @return \DibiFluent
     */
    public function getHints($lang)
    {
        $where = array();
        return $this->mapper->allDataSource($lang, $where);
    }

    /**
     * Get one hint by form ID
     * @param type $form
     * @param type $lang
     * @return type
     */
    public function getHint($form, $lang)
    {
        return $this->mapper->findBy($lang, array(
                    'form' => $form,
        ));
    }

    /**
     * Save entity
     * @param \Model\Entity\HintEntity $entity
     * @param type $what
     * @return HintEntity
     */
    public function save(HintEntity $entity, $what = NULL)
    {
        return $this->mapper->save($entity, $what);
    }

    /**
     * Returns entity by ID
     * @param string $id
     * @param string $lang
     * @return HintEntity
     */
    public function find($id, $lang = NULL)
    {
        return $this->mapper->find($id, $lang);
    }

    /**
     * Delete inserted entity, or item by inserted ID
     * @param \Model\Entity\HintEntity|string $id
     * @return bool
     */
    public function delete($id)
    {
        if ($id instanceof HintEntity) {
            $entity = $id;
        } else {
            $entity = $this->find($id);
        }
        return $this->mapper->delete($entity);
    }

}

?>
