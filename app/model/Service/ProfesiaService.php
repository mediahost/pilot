<?php

namespace Model\Service;

use Model\Mapper\Dibi\ProfesiaLoadDibiMapper,
    Model\Mapper\Dibi\ProfesiaJobDibiMapper,
    Model\Entity\ProfesiaJobEntity,
    Model\Entity\ProfesiaBookEntity;

/**
 * Profesia Service
 *
 * @author Petr Poupě
 */
class ProfesiaService
{

    /** @var ProfesiaLoadDibiMapper */
    private $mapperLoad;

    /** @var ProfesiaJobDibiMapper */
    private $mapperJob;

    public function __construct(ProfesiaLoadDibiMapper $mapperLoad, ProfesiaJobDibiMapper $mapperJob)
    {
        $this->mapperLoad = $mapperLoad;
        $this->mapperJob = $mapperJob;
    }

    public function getJobs()
    {
        return $this->mapperJob->allDataSource();
    }

    /**
     * Find Job Entity
     * @param type $externalId
     * @return ProfesiaJobEntity
     */
    public function find($externalId)
    {
        return $this->mapperJob->findByExtId($externalId);
    }

    /**
     * Find Job Entity
     * @param type $id
     * @return ProfesiaJobEntity
     */
    public function findById($id)
    {
        return $this->mapperJob->find($id);
    }

    public function save(ProfesiaJobEntity $entity)
    {
        return $this->mapperJob->save($entity);
    }

    public function saveBook(ProfesiaBookEntity $entity)
    {
        return $this->mapperJob->saveBook($entity);
    }

    /**
     * Return T if date is newer than last saved date
     * @param type $lastModified
     * @return boolean
     */
    public function canUpdateData(\Nette\DateTime $lastModified)
    {
        $last = $this->mapperLoad->findLast();
        if ((string) $last->lastModified === (string) $lastModified)
            return FALSE;
        return TRUE;
    }

    public function updated(\Nette\DateTime $lastModified)
    {
        $this->mapperLoad->setNewLoad($lastModified);
    }

    /**
     * Find Book Entity
     * @param type $type
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    private function findBook($type, $id, $lang, $idSecond = NULL)
    {
        $book = $this->mapperJob->findBookEntity($type, $id, $lang, $idSecond);
        $book->type = $type;
        return $book;
    }

    /**
     * Find BussinesArea
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findBussinesArea($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_BUSINESSAREAS, $id, $lang);
    }

    /**
     * Find Category
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findCategory($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_CATEGORIES, $id, $lang);
    }

    /**
     * Find Currency
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findCurrency($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_CURRENCIES, $id, $lang);
    }

    /**
     * Find EducationLevel
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findEducationLevel($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_EDUCATIONLEVELS, $id, $lang);
    }

    /**
     * Find JobType
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findJobType($id, $web, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_JOBTYPES, $id, $lang, $web);
    }

    /**
     * Find OfferCategoryPosition
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findOfferCategoryPosition($category, $position, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_OFFERCATEGORYPOSITIONS, $category, $lang, $position);
    }

    /**
     * Find Position
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findPosition($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_POSITIONS, $id, $lang);
    }

    /**
     * Find Region
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findRegion($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_REGIONS, $id, $lang);
    }

    /**
     * Find SkillCategory
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findSkillCategory($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_SKILLCATEGORIES, $id, $lang);
    }

    /**
     * Find SkillLevel
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findSkillLevel($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_SKILLLEVELS, $id, $lang);
    }

    /**
     * Find Skill
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findSkill($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_SKILLS, $id, $lang);
    }

    /**
     * Find Specialization
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findSpecialization($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_SPECIALIZATIONS, $id, $lang);
    }

    /**
     * Find SummerJob
     * @param type $id
     * @param type $lang
     * @return ProfesiaBookEntity
     */
    public function findSummerJob($id, $lang)
    {
        return $this->findBook(ProfesiaJobDibiMapper::BOOK_SUMMERJOBS, $id, $lang);
    }

    public function getLocations($lang, $parent = NULL)
    {
        if ($parent === NULL) {
            $locations = $this->mapperJob->findAllLocations($lang, $parent);
        } else {
            $locations = array();
            foreach($this->mapperJob->findAllLocations($lang) as $key => $country) {
                if ($key != $parent) {
                    $locations[$key] = $country;
                } else {
                    $mainName = $this->mapperJob->findLocationName($parent, $lang);
                    $locations[$mainName] = $this->mapperJob->findAllLocations($lang, $parent);
                }
            }
        }
        return $locations;
    }

    public function getLocationsChildren($locations, $lang)
    {
        $allLocations = array();
        $children = $this->mapperJob->findChildLocations($locations, $lang);
        while (!empty($children)) {
            $allLocations += $children;
            $children = $this->mapperJob->findChildLocations(array_keys($children), $lang);
        }
        return $allLocations;
    }

    public function getPositions($lang)
    {
        return $this->mapperJob->findAllPositions($lang);
    }

    public function getCategories($lang)
    {
        return $this->mapperJob->findAllCategories($lang);
    }

    public function getEducations($lang)
    {
        return $this->mapperJob->findAllEducations($lang);
    }

    public function getJobtypes($lang)
    {
        return $this->mapperJob->findAllJobtypes($lang);
    }

    public function getTags($lang)
    {
        return $this->mapperJob->findAllTags($lang);
    }

    public function deleteUnused($used, $importName)
    {
        return $this->mapperJob->deleteUnused($used, $importName);
    }

}

?>
