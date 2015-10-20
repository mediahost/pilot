<?php

namespace Model\Service;

use Model\Mapper\Dibi\CvDibiMapper,
    Model\Entity\CvEntity;

/**
 * CV Service
 *
 * @author Petr Poupě
 */
class CvService
{

    /** @var CvDibiMapper */
    private $mapper;

    public function __construct(CvDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function setCurrentPosition(CvEntity &$cv, $step)
    {
        // při nezadaném se bere poslední uložený krok
        if (is_null($step))
            $step = $cv->lastStep;
        //  při chybě nastavuj první krok
        if (!array_key_exists($step, CvEntity::steps()))
            $step = 1;

        // saving last step when change location
        $cv->lastStep = $step;
        $cv = $this->mapper->save($cv, "lastStep");

        // save last used CV
        $this->mapper->changeLastOpened($cv);

        return $step;
    }

    public function create($userId, $clone = NULL)
    {
        if ($clone > 0) {
            $entity = $this->findUserItem($clone, $userId);
            $entity->id = NULL;
            $entity->name .= "(copy)";
            $entity->isDefault = FALSE;
        } else {
            $entity = new CvEntity;
            $entity->name = "CV";
        }
        $entity->isGraduated = 0;
        $entity->userId = $userId;
        $entity->createDate = time();

        $entity = $this->save($entity);
        $skills = array();
        foreach ($entity->itSkills as $skill) {
            $skills[$skill->skill_id] = array(
                "scale" => $skill->scale,
                "number" => $skill->years,
            );
        }
        if ($skills !== array()) {
            $this->saveSkills($entity, $skills);
        }

        // save last used CV
        $this->mapper->changeLastOpened($entity);
        return $entity;
    }

    public function save(CvEntity $entity)
    {
        return $this->mapper->save($entity);
    }

    /**
     * Delete inserted entity | Delete entity by ID
     * @param \Model\Entity\CvEntity $id|int
     * @param type $userId
     * @return boolean
     */
    public function delete($id, $userId = NULL)
    {
        if ($id instanceof CvEntity) {
            return $this->mapper->delete($id);
        } else if ($userId !== NULL) {
            $entity = $this->getCv($id, $userId);
            if ($entity)
                return $this->mapper->delete($entity);
            else
                return FALSE;
        } else {
            return FALSE;
        }
    }

    public function deleteWork(CvEntity $entity, $workId)
    {
        $entity->deleteWork($workId);
        return $this->save($entity);
    }

    public function deleteEducation(CvEntity $entity, $educId)
    {
        $entity->deleteEducation($educId);
        return $this->save($entity);
    }

    public function deleteLanguage(CvEntity $entity, $langId)
    {
        $entity->deleteLanguage($langId);
        return $this->save($entity);
    }

    /**
     * Vrací poslední entitu nebo FALSE v případě, že ID neexistuje
     * @param type $id
     * @param type $userId
     * @return CvEntity | FALSE
     */
    public function getCv($id, $userId = NULL)
    {
        if (!is_null($id)) {
            if (!is_null($userId)) {
                $item = $this->findUserItem($id, $userId);
            } else {
                $item = $this->mapper->find($id);
            }
            if (is_null($item->id))
                $item = FALSE;
        } else {
            $item = $this->findLast($userId);
        }
        return $item;
    }

    /**
     * @return CvEntity | NULL
     */
    public function getDefaultCv($userId) {
        $cvs = $this->mapper->findBy(array(
            'user_id' => $userId,
            'is_default' => 1
        ));
        if (count($cvs) > 0) {
            return $cvs[0];
        } elseif ($userId) {
            return $this->create($userId);
        } else {
            return NULL;
        }
    }

    public function changeName($cvId, $name)
    {
        $entity = new CvEntity;
        $entity->id = $cvId;
        $entity->name = $name;
        return $this->mapper->save($entity, "name");
    }

    public function changeStep($cvId, $step)
    {
        $entity = new CvEntity;
        $entity->id = $cvId;
        $entity->lastStep = $step;
        return $this->mapper->save($entity, "lastStep");
    }

    public function changeTemplateName($cvId, $templateName)
    {
        $entity = new CvEntity;
        $entity->id = $cvId;
        $entity->templateName = $templateName;
        return $this->mapper->save($entity, "templateName");
    }

    /**
     * Najde poslední aktivní CV uživatele
     * - pokud není žádný, pak vrací nový
     * @param type $userId
     * @return type
     */
    public function findLast($userId)
    {
        return $this->mapper->findLast($userId);
    }

    public function findUsersCv($userId, $limit = NULL, $ordedByLastChange = FALSE)
    {
        return $this->mapper->getCvList($userId, $limit, $ordedByLastChange);
    }

    public function findUserItem($id, $userId)
    {
        return $this->mapper->findOneBy(array(
                    'id' => $id,
                    'user' => $userId,
        ));
    }

    /**
     * @return array
     */
    public function buildSkills()
    {
        $skills = $this->mapper->buildSkills();
        return $skills;
    }


    /**
     * @param CvEntity $entity
     * @param array $skills
     *
     * @return \DibiResult|int
     */
    public function saveSkills(CvEntity $entity, array $skills)
    {
        return $this->mapper->saveSkills($entity, $skills);
    }
    
    public function getAll()
    {
        return $this->mapper->getAll();
    }
    
    public function getAllCandidateNames($notAsignedToJobId = NULL)
    {
        return $this->mapper->getAllCandidateNames($notAsignedToJobId);
    }
    
}
