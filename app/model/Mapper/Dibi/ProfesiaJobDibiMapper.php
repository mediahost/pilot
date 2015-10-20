<?php

namespace Model\Mapper\Dibi;

use Model\Entity\ProfesiaJobEntity,
    Model\Entity\ProfesiaBookEntity,
    Model\Mapper\ILazyMapper,
    Model\DataSource\JobLazyCollection;

/**
 * Profesia Job DibiMapper
 *
 * @author Petr Poupě
 */
class ProfesiaJobDibiMapper extends DibiMapper implements ILazyMapper
{

    private $job = "profesia_job";
    private $businessareas = "profesia_book_businessareas";
    private $categories = "profesia_book_categories";
    private $currencies = "profesia_book_currencies";
    private $educationlevels = "profesia_book_educationlevels";
    private $job_educationlevel = "profesia_job_educationlevel";
    private $jobtypes = "profesia_book_jobtypes";
    private $job_jobtype = "profesia_job_jobtype";
    private $offercategorypositions = "profesia_book_offercategorypositions";
    private $job_offercategorypositions = "profesia_job_offercategorypositions";
    private $job_offercategories = "profesia_job_offercategories";
    private $positions = "profesia_book_positions";
    private $regions = "profesia_book_regions";
    private $job_offerlocation = "profesia_job_offerlocation";
    private $skillcategories = "profesia_book_skillcategories";
    private $skilllevels = "profesia_book_skilllevels";
    private $skills = "profesia_book_skills";
    private $job_skills = "profesia_job_offerskills";
    private $specializations = "profesia_book_specializations";
    private $summerjobs = "profesia_book_summerjobs";
    private $job_tag = "job_tag";
    private $job_to_tag = "job_to_tag";

    const DATA_PRIMARY = "primary";
    const DATA_JOB_EDUCATIONLEVEL = "educationlevel";
    const DATA_JOB_JOBTYPE = "jobtype";
    const DATA_JOB_OFFERCATEGORIES = "offercategories";
    const DATA_JOB_OFFERCATEGORYPOSITIONS = "offercategorypositions";
    const DATA_JOB_OFFERLOCATION = "offerlocation";
    const DATA_JOB_OFFERSKILLS = "offerskills";
    const DATA_JOB_TAGS = "tags";
    const BOOK_BUSINESSAREAS = 1;
    const BOOK_CATEGORIES = 2;
    const BOOK_CURRENCIES = 3;
    const BOOK_EDUCATIONLEVELS = 4;
    const BOOK_JOBTYPES = 5;
    const BOOK_OFFERCATEGORYPOSITIONS = 6;
    const BOOK_POSITIONS = 7;
    const BOOK_REGIONS = 8;
    const BOOK_SKILLCATEGORIES = 9;
    const BOOK_SKILLLEVELS = 10;
    const BOOK_SKILLS = 11;
    const BOOK_SPECIALIZATIONS = 12;
    const BOOK_SUMMERJOBS = 13;

    /**
     * Vrací Lazy collection všech záznamů
     * @return JobLazyCollection
     */
    public function allDataSource()
    {
        $dataSource = $this->selectList();
        return new JobLazyCollection($this, $dataSource);
    }

    /**
     * Return loaded entity
     * @param type $row
     * @return \Model\Entity\ProfesiaJobEntity
     */
    public function load($row)
    {
        $entity = new ProfesiaJobEntity;
        if ($row) {
            foreach ($row as $prop => $val) {
                switch ($prop) {
                    case "imported_from":
                        $entity->importedFrom = $val;
                        break;
                    case "offerlocation_description":
                        $entity->offerlocationDescription = $val;
                        break;
                    case "salary_period":
                        $entity->salaryPeriod = $val;
                        break;
                    case "currency_id":
                        $entity->currencyId = $val;
                        break;
                    case "currency_name":
                        $entity->currency = $val;
                        break;
                    case "tags":
                        $entity->tags = json_decode($val);
                        break;
                    default:
                        $entity->$prop = $val;
                        break;
                }
            }
        }

        if ($entity->id !== NULL && $entity->offerlanguage !== NULL) {
            $locations = $this->_subQueryLocations($entity->id, $entity->offerlanguage);
            if (!empty($locations)) {
                $entity->offerLocations = array_keys($locations);
                $entity->offerLocationNames = $locations;
            }

            $types = $this->_subQueryJobtypes($entity->id, $entity->offerlanguage, "www.profesia.cz");
            if (!empty($types)) {
                $entity->jobTypes = array_keys($types);
                $entity->jobTypeNames = $types;
            }

            $educations = $this->_subQueryEducationLevels($entity->id, $entity->offerlanguage);
            if (!empty($educations)) {
                $entity->educationLevels = array_keys($educations);
                $entity->educationLevelNames = $educations;
            }

            $skills = $this->_subQuerySkills($entity->id, $entity->offerlanguage);
            if (!empty($skills)) {
                $names = array();
                $levels = array();
                foreach ($skills as $row) {
                    $names[$row["skill_id"]] = $row["name"];
                    $levels[$row["skill_level"]] = $row["level"];
                    $entity->offerSkills = array(
                        "id" => $row["skill_id"],
                        "level" => $row["skill_level"],
                    );
                }
                $entity->offerSkillNames = $names;
                $entity->offerSkillLevels = $levels;
            }

            $categories = $this->_subQueryCategories($entity->id, $entity->offerlanguage);
            if (!empty($categories)) {
                $entity->offerCategories = array_keys($categories);
                $entity->offerCategoryNames = $categories;
            }

            $categoryPositions = $this->_subQueryCategoryPositions($entity->id, $entity->offerlanguage);
            if (!empty($categoryPositions)) {
                $category = array();
                $position = array();
                foreach ($categoryPositions as $row) {
                    $category[$row["category_id"]] = $row["category"];
                    $position[$row["position_id"]] = $row["position"];
                    $entity->offerCategoryPositions = array(
                        "category" => $row["category_id"],
                        "position" => $row["position_id"],
                    );
                }
                $entity->offerCategoryPositionCategories = $category;
                $entity->offerCategoryPositionPositions = $position;
            }
        }

        return $entity;
    }

    public function loadBook($row)
    {
        $entity = new ProfesiaBookEntity;
        if ($row) {
            foreach ($row as $prop => $val) {
                switch ($prop) {
                    case "attr_id":
                        $entity->attrId = $val;
                        break;
                    case "attr_category":
                        $entity->attrCategory = $val;
                        break;
                    case "attr_position":
                        $entity->attrPosition = $val;
                        break;
                    case "attr_parent_id":
                        $entity->attrParentId = $val;
                        break;
                    case "attr_cat_level_id":
                        $entity->attrCatLevelId = $val;
                        break;
                    default:
                        $entity->$prop = $val;
                        break;
                }
            }
        }
        return $entity;
    }

    public function entityToItem(ProfesiaJobEntity $entity)
    {
        $primary = array();

        $primary["id"] = $entity->id;
        $primary["imported_from"] = $entity->importedFrom;
        $primary["externalid"] = $entity->externalid;
        $primary["position"] = $entity->position;
        $primary["refnr"] = $entity->refnr;
        $primary["datecreated"] = $entity->datecreated;
        $primary["offerlocation_description"] = $entity->offerlocationDescription;
        $primary["jobtasks"] = $entity->jobtasks;
        $primary["minsalary"] = $entity->minsalary;
        $primary["maxsalary"] = $entity->maxsalary;
        $primary["currency"] = $entity->currencyId;
        $primary["salary_period"] = $entity->salaryPeriod;
        $primary["startdate"] = $entity->startdate;
        $primary["otherbenefits"] = $entity->otherbenefits;
        $primary["noteforcandidate"] = $entity->noteforcandidate;
        $primary["languageconjuction"] = $entity->languageconjuction;
        $primary["validforgraduate"] = $entity->validforgraduate;
        $primary["prerequisites"] = $entity->prerequisites;
        $primary["contactperson"] = $entity->contactperson;
        $primary["contactname"] = $entity->contactname;
        $primary["contactemail"] = $entity->contactemail;
        $primary["contactphone"] = $entity->contactphone;
        $primary["contactaddress"] = $entity->contactaddress;
        $primary["shortcompanycharacteristics"] = $entity->shortcompanycharacteristics;
        $primary["offerlanguage"] = $entity->offerlanguage;
        $primary["customcategory"] = $entity->customcategory;

        $education = array();
        $jobtype = array();
        $categories = array();
        $location = array();
        $positions = array();
        $skills = array();
        $tags = array();
        if (is_array($entity->educationLevels)) {
            $education = $entity->educationLevels;
        }
        if (is_array($entity->jobTypes)) {
            $jobtype = $entity->jobTypes;
        }
        if (is_array($entity->offerCategories)) {
            $categories = $entity->offerCategories;
        }
        if (is_array($entity->offerLocations)) {
            $location = $entity->offerLocations;
        }
        if (is_array($entity->offerCategoryPositions)) {
            $positions = $entity->offerCategoryPositions;
        }
        if (is_array($entity->offerSkills)) {
            $skills = $entity->offerSkills;
        }
        if (is_array($entity->tags)) {
            $tags = array_unique($entity->tags);
        }

        return $data = array(
            self::DATA_PRIMARY => $primary,
            self::DATA_JOB_EDUCATIONLEVEL => $education,
            self::DATA_JOB_JOBTYPE => $jobtype,
            self::DATA_JOB_OFFERCATEGORIES => $categories,
            self::DATA_JOB_OFFERCATEGORYPOSITIONS => $positions,
            self::DATA_JOB_OFFERLOCATION => $location,
            self::DATA_JOB_OFFERSKILLS => $skills,
            self::DATA_JOB_TAGS => $tags,
        );
    }

    public function save(ProfesiaJobEntity $entity)
    {
        $data = $this->entityToItem($entity);

        $primary = $data[self::DATA_PRIMARY];
        if ($entity->id === NULL) {
            $entity->id = $this->conn->insert($this->job, $primary)->execute(\dibi::IDENTIFIER);
        } else {
            $this->conn->update($this->job, $primary)
                    ->where('id = %i', $entity->id)
                    ->execute();
        }

        // Education Level
        $partData = $data[self::DATA_JOB_EDUCATIONLEVEL];
        $table = $this->job_educationlevel;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        if (!empty($partData)) {
            $insert = array(
                "profesia_job_id" => array_fill(0, count($partData), $entity->id),
                "education_id" => $partData,
            );
            $this->conn->query("INSERT INTO %n %m", $table, $insert);
        }

        // Job Type
        $partData = $data[self::DATA_JOB_JOBTYPE];
        $table = $this->job_jobtype;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        if (!empty($partData)) {
            $insert = array(
                "profesia_job_id" => array_fill(0, count($partData), $entity->id),
                "type_id" => $partData,
            );
            $this->conn->query("INSERT INTO %n %m", $table, $insert);
        }

        // Offer Categories
        $partData = $data[self::DATA_JOB_OFFERCATEGORIES];
        $table = $this->job_offercategories;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        if (!empty($partData)) {
            $insert = array(
                "profesia_job_id" => array_fill(0, count($partData), $entity->id),
                "category_id" => $partData,
            );
            $this->conn->query("INSERT INTO %n %m", $table, $insert);
        }

        // Offer Location
        $partData = $data[self::DATA_JOB_OFFERLOCATION];
        $table = $this->job_offerlocation;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        if (!empty($partData)) {
            $insert = array(
                "profesia_job_id" => array_fill(0, count($partData), $entity->id),
                "location_id" => $partData,
            );
            $this->conn->query("INSERT INTO %n %m", $table, $insert);
        }

        // Offer Category Positions
        $partData = $data[self::DATA_JOB_OFFERCATEGORYPOSITIONS];
        $table = $this->job_offercategorypositions;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        if (!empty($partData)) {
            $category = array();
            $position = array();
            foreach ($partData as $key => $part) {
                $category[$key] = $part["category"];
                $position[$key] = $part["position"];
            }
            $insert = array(
                "profesia_job_id" => array_fill(0, count($partData), $entity->id),
                "category" => $category,
                "position" => $position,
            );
            $this->conn->query("INSERT INTO %n %m", $table, $insert);
        }

        // Offer Skills
        $partData = $data[self::DATA_JOB_OFFERSKILLS];
        $table = $this->job_skills;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        if (!empty($partData)) {
            $ids = array();
            $level = array();
            foreach ($partData as $key => $part) {
                $ids[$key] = $part["id"];
                $level[$key] = $part["level"];
            }
            $insert = array(
                "profesia_job_id" => array_fill(0, count($partData), $entity->id),
                "skill_id" => $ids,
                "skill_level" => $level,
            );
            $this->conn->query("INSERT INTO %n %m", $table, $insert);
        }

        // tags
        $partData = $data[self::DATA_JOB_TAGS];
        $table = $this->job_to_tag;
        $this->conn->delete($table)->where("profesia_job_id = %i", $entity->id)->execute();
        foreach ($partData as $part) {
            $findedId = $this->conn->select("id")->from($this->job_tag)
                            ->where("tag = %s", $part)
                            ->where("lang = %s", $entity->offerlanguage)->fetchSingle();
            if (!$findedId) {
                $findedId = $this->conn->insert($this->job_tag, array(
                            "tag%s" => $part,
                            "lang%s" => $entity->offerlanguage,
                        ))->execute(\dibi::IDENTIFIER);
            }
            if ($findedId) {
                try {
                    $this->conn->insert($this->job_to_tag, array(
                        "profesia_job_id%i" => $entity->id,
                        "job_tag_id%i" => $findedId,
                    ))->execute();
                } catch (\DibiDriverException $e) {
                    
                }
            }
        }
        $this->conn->delete($this->job_tag)->where("id NOT IN (SELECT job_tag_id FROM {$this->job_to_tag})")->execute();

        return $entity;
    }

    public function entityBookToItem(ProfesiaBookEntity $entity)
    {
        $data = array();
        $data["id"] = $entity->id;
        $data["lang"] = $entity->lang;
        $data["name"] = $entity->name;
        switch ($entity->type) {
            case self::BOOK_BUSINESSAREAS:
            case self::BOOK_CATEGORIES:
            case self::BOOK_CURRENCIES:
            case self::BOOK_EDUCATIONLEVELS:
            case self::BOOK_POSITIONS:
            case self::BOOK_SPECIALIZATIONS:
            case self::BOOK_SUMMERJOBS:
                $data["attr_id"] = $entity->attrId;
                break;
            case self::BOOK_REGIONS:
            case self::BOOK_SKILLS:
                $data["attr_id"] = $entity->attrId;
                $data["attr_parent_id"] = $entity->attrParentId;
                break;
            case self::BOOK_SKILLCATEGORIES:
                $data["attr_id"] = $entity->attrId;
                $data["attr_parent_id"] = $entity->attrParentId;
                $data["attr_cat_level_id"] = $entity->attrCatLevelId;
                break;
            case self::BOOK_SKILLLEVELS:
                $data["attr_id"] = $entity->attrId;
                $data["attr_cat_level_id"] = $entity->attrCatLevelId;
                break;
            case self::BOOK_JOBTYPES:
                $data["attr_id"] = $entity->attrId;
                $data["web"] = $entity->web;
                break;
            case self::BOOK_OFFERCATEGORYPOSITIONS:
                $data["attr_category"] = $entity->attrCategory;
                $data["attr_position"] = $entity->attrPosition;
                break;
            default:
                break;
        }
        return $data;
    }

    public function saveBook(ProfesiaBookEntity $entity)
    {
        $data = $this->entityBookToItem($entity);
        $table = $this->getBookTable($entity->type);
        if ($table === NULL)
            return $entity;

        if ($data !== array()) {
            if ($entity->id === NULL) {
                $entity->id = $this->conn->insert($table, $data)->execute(\dibi::IDENTIFIER);
            } else {
                $this->conn->update($table, $data)
                        ->where('id = %i', $entity->id)
                        ->execute();
            }
        }
        return $entity;
    }

    public function find($id)
    {
        return $this->findBy(array(
                    "id" => $id,
        ));
    }

    public function findByExtId($externalId)
    {
        return $this->findBy(array(
                    "externalid" => $externalId,
        ));
    }

    public function findBy($by = array())
    {
        $data = $this->selectList();
        $data->where($this->_getWhere($by));

        return $this->load($data->fetch());
    }

    public function findBookEntity($type, $id, $lang, $idSecond = NULL)
    {
        $table = $this->getBookTable($type);
        if ($table === NULL)
            return new \Model\Entity\ProfesiaBookEntity;

        $where = array(
            "lang%s" => $lang,
        );
        switch ($type) {
            case self::BOOK_BUSINESSAREAS:
            case self::BOOK_CATEGORIES:
            case self::BOOK_CURRENCIES:
            case self::BOOK_EDUCATIONLEVELS:
            case self::BOOK_POSITIONS:
            case self::BOOK_SPECIALIZATIONS:
            case self::BOOK_SUMMERJOBS:
            case self::BOOK_REGIONS:
            case self::BOOK_SKILLS:
            case self::BOOK_SKILLCATEGORIES:
            case self::BOOK_SKILLLEVELS:
                $where["attr_id%i"] = $id;
                break;
            case self::BOOK_JOBTYPES:
                $where["attr_id%i"] = $id;
                if ($idSecond !== NULL)
                    $where["web%s"] = $idSecond;
                break;
            case self::BOOK_OFFERCATEGORYPOSITIONS:
                $where["attr_category%i"] = $id;
                if ($idSecond !== NULL)
                    $where["attr_position%i"] = $idSecond;
                break;
        }
        $data = $this->conn->select("*")->from($table)->where($where);

        return $this->loadBook($data->fetch());
    }

    private function selectList()
    {
        return $this->conn->select($this->_getSelectAll())
                        ->select("CONCAT('[\"', GROUP_CONCAT({$this->job_tag}.tag SEPARATOR '\",\"'), '\"]') AS tags")
                        //
                        ->from($this->job)
                        //
                        ->leftJoin($this->currencies)
                        ->on("{$this->job}.currency = {$this->currencies}.attr_id")
                        //
                        ->leftJoin($this->job_to_tag)
                        ->on("{$this->job}.id = {$this->job_to_tag}.profesia_job_id")
                        //
                        ->leftJoin($this->job_tag)
                        ->on("{$this->job_to_tag}.job_tag_id  = {$this->job_tag}.id")
                        //
                        ->and("{$this->currencies}.lang = %s", "en")
                        //
                        ->groupBy("{$this->job}.id");
    }

    private function _getSelectAll()
    {
        return array(
            "{$this->job}.id" => "id",
            "{$this->job}.imported_from" => "imported_from",
            "{$this->job}.externalid" => "externalid",
            "{$this->job}.position" => "position",
            "{$this->job}.refnr" => "refnr",
            "{$this->job}.datecreated" => "datecreated",
            "{$this->job}.offerlocation_description" => "offerlocation_description",
            "{$this->job}.jobtasks" => "jobtasks",
            "{$this->job}.minsalary" => "minsalary",
            "{$this->job}.maxsalary" => "maxsalary",
            "{$this->job}.currency" => "currency_id",
            "{$this->currencies}.name" => "currency_name",
            "{$this->job}.salary_period" => "salary_period",
            "{$this->job}.startdate" => "startdate",
            "{$this->job}.otherbenefits" => "otherbenefits",
            "{$this->job}.noteforcandidate" => "noteforcandidate",
            "{$this->job}.languageconjuction" => "languageconjuction",
            "{$this->job}.validforgraduate" => "validforgraduate",
            "{$this->job}.prerequisites" => "prerequisites",
            "{$this->job}.contactperson" => "contactperson",
            "{$this->job}.contactname" => "contactname",
            "{$this->job}.contactemail" => "contactemail",
            "{$this->job}.contactphone" => "contactphone",
            "{$this->job}.contactaddress" => "contactaddress",
            "{$this->job}.shortcompanycharacteristics" => "shortcompanycharacteristics",
            "{$this->job}.offerlanguage" => "offerlanguage",
            "{$this->job}.customcategory" => "customcategory",
        );
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
                    $where["{$this->job}.id%i"] = $cond;
                    break;
                case "externalid":
                    $where["{$this->job}.externalid%i"] = $cond;
                    break;
            }
        }
        return $where;
    }

    private function getBookTable($type)
    {
        $table = NULL;
        switch ($type) {
            case self::BOOK_CATEGORIES:
                $table = $this->categories;
                break;
            case self::BOOK_BUSINESSAREAS:
                $table = $this->businessareas;
                break;
            case self::BOOK_CURRENCIES:
                $table = $this->currencies;
                break;
            case self::BOOK_EDUCATIONLEVELS:
                $table = $this->educationlevels;
                break;
            case self::BOOK_JOBTYPES:
                $table = $this->jobtypes;
                break;
            case self::BOOK_OFFERCATEGORYPOSITIONS:
                $table = $this->offercategorypositions;
                break;
            case self::BOOK_POSITIONS:
                $table = $this->positions;
                break;
            case self::BOOK_REGIONS:
                $table = $this->regions;
                break;
            case self::BOOK_SKILLCATEGORIES:
                $table = $this->skillcategories;
                break;
            case self::BOOK_SKILLLEVELS:
                $table = $this->skilllevels;
                break;
            case self::BOOK_SKILLS:
                $table = $this->skills;
                break;
            case self::BOOK_SPECIALIZATIONS:
                $table = $this->specializations;
                break;
            case self::BOOK_SUMMERJOBS:
                $table = $this->summerjobs;
                break;
        }
        return $table;
    }

    public function findLocationName($id, $lang)
    {
        $result = $this->conn->select(array("name"))
                ->from($this->regions)
                ->where("attr_id = %i", $id)
                ->where("lang = %s", $lang);
        return $result->fetchSingle();
    }

    public function findAllLocations($lang, $parent = NULL)
    {
        $result = $this->conn->select(array("attr_id", "name"))
                ->from($this->regions)
                ->where("lang = %s", $lang)
                ->where("name IS NOT NULL");
        if ($parent !== NULL) {
            $result->where("attr_parent_id = %i", $parent)
                    ->orderBy("attr_id ASC");
        } else {
            $result->where("attr_parent_id = %i", 0)
                    ->orderBy("name ASC");
        }
        return $result->fetchPairs("attr_id", "name");
    }

    public function findChildLocations($parent, $lang)
    {
        if (empty($parent))
            $parent = 0;
        $result = $this->conn->select(array("attr_id", "name"))
                ->from($this->regions)
                ->where("lang = %s", $lang)
                ->where("name IS NOT NULL");
        if (is_array($parent)) {
            $result->where("attr_parent_id IN %l", $parent);
        } else {
            $result->where("attr_parent_id = %i", $parent);
        }
        return $result->fetchPairs("attr_id", "name");
    }

    public function findAllPositions($lang)
    {
        $result = $this->conn->select(array("attr_id", "name"))
                ->from($this->positions)
                ->where("lang = %s", $lang)
                ->orderBy("name ASC");
        return $result->fetchPairs("attr_id", "name");
    }

    public function findAllCategories($lang)
    {
        $result = $this->conn->select(array("attr_id", "name"))
                ->from($this->categories)
                ->where("lang = %s", $lang)
                ->orderBy("name ASC");
        return $result->fetchPairs("attr_id", "name");
    }

    public function findAllEducations($lang)
    {
        $result = $this->conn->select(array("attr_id", "name"))
                ->from($this->educationlevels)
                ->where("lang = %s", $lang);
        return $result->fetchPairs("attr_id", "name");
    }

    public function findAllJobtypes($lang)
    {
        $result = $this->conn->select(array("attr_id", "name"))
                ->from($this->jobtypes)
                ->where("lang = %s", $lang);
        return $result->fetchPairs("attr_id", "name");
    }

    public function findAllTags($lang)
    {
        $result = $this->conn->select(array("id", "tag"))
                ->from($this->job_tag)
                ->where("lang = %s", $lang)
                ->orderBy("tag ASC");
        return $result->fetchPairs("id", "tag");
    }

    private function _subQueryLocations($id, $lang)
    {
        $result = $this->conn->select(array(
                    "{$this->regions}.attr_id" => "id",
                    "{$this->regions}.name" => "name",
                ))
                ->from($this->job_offerlocation)
                ->leftJoin($this->regions)
                ->on("{$this->job_offerlocation}.location_id = {$this->regions}.attr_id")
                ->and("{$this->regions}.lang = %s", $lang)
                ->where("{$this->job_offerlocation}.profesia_job_id = %i", $id);

        $pairs = $result->fetchPairs("id", "name");
        if (count($pairs) === 1 && end($pairs) === NULL)
            return NULL;
        return $pairs;
    }

    private function _subQueryJobtypes($id, $lang, $web)
    {
        $result = $this->conn->select(array(
                    "{$this->jobtypes}.attr_id" => "id",
                    "{$this->jobtypes}.name" => "name",
                ))
                ->from($this->job_jobtype)
                ->leftJoin($this->jobtypes)
                ->on("{$this->job_jobtype}.type_id = {$this->jobtypes}.attr_id")
                ->and("{$this->jobtypes}.lang = %s", $lang)
                ->and("{$this->jobtypes}.web = %s", $web)
                ->where("{$this->job_jobtype}.profesia_job_id = %i", $id);

        $pairs = $result->fetchPairs("id", "name");
        if (count($pairs) === 1 && end($pairs) === NULL)
            return NULL;
        return $pairs;
    }

    private function _subQueryEducationLevels($id, $lang)
    {
        $result = $this->conn->select(array(
                    "{$this->educationlevels}.attr_id" => "id",
                    "{$this->educationlevels}.name" => "name",
                ))
                ->from($this->job_educationlevel)
                ->leftJoin($this->educationlevels)
                ->on("{$this->job_educationlevel}.education_id = {$this->educationlevels}.attr_id")
                ->and("{$this->educationlevels}.lang = %s", $lang)
                ->where("{$this->job_educationlevel}.profesia_job_id = %i", $id);

        $pairs = $result->fetchPairs("id", "name");
        if (count($pairs) === 1 && end($pairs) === NULL)
            return NULL;
        return $pairs;
    }

    private function _subQuerySkills($id, $lang)
    {
        $result = $this->conn->select(array(
                    "{$this->skills}.attr_id" => "id",
                    "{$this->skills}.name" => "name",
                    "{$this->skilllevels}.name" => "level",
                    "{$this->job_skills}.skill_id" => "skill_id",
                    "{$this->job_skills}.skill_level" => "skill_level",
                ))
                ->from($this->job_skills)
                ->leftJoin($this->skills)
                ->on("{$this->job_skills}.skill_id = {$this->skills}.attr_id")
                ->and("{$this->skills}.lang = %s", $lang)
                ->leftJoin($this->skilllevels)
                ->on("{$this->job_skills}.skill_level = {$this->skilllevels}.attr_id")
                ->and("{$this->skilllevels}.lang = %s", $lang)
                ->where("{$this->job_skills}.profesia_job_id = %i", $id);

        return $result->fetchAssoc("id");
    }

    private function _subQueryCategories($id, $lang)
    {
        $result = $this->conn->select(array(
                    "{$this->categories}.attr_id" => "id",
                    "{$this->categories}.name" => "name",
                ))
                ->from($this->job_offercategories)
                ->leftJoin($this->categories)
                ->on("{$this->job_offercategories}.category_id = {$this->categories}.attr_id")
                ->and("{$this->categories}.lang = %s", $lang)
                ->where("{$this->job_offercategories}.profesia_job_id = %i", $id);

        $pairs = $result->fetchPairs("id", "name");
        if (count($pairs) === 1 && end($pairs) === NULL)
            return NULL;
        return $pairs;
    }

    private function _subQueryCategoryPositions($id, $lang)
    {
        $result = $this->conn->select(array(
                    "{$this->categories}.attr_id" => "id",
                    "{$this->categories}.name" => "category",
                    "{$this->positions}.name" => "position",
                    "{$this->job_offercategorypositions}.category" => "category_id",
                    "{$this->job_offercategorypositions}.position" => "position_id",
                ))
                ->from($this->job_offercategorypositions)
                ->leftJoin($this->categories)
                ->on("{$this->job_offercategorypositions}.category = {$this->categories}.attr_id")
                ->and("{$this->categories}.lang = %s", $lang)
                ->leftJoin($this->positions)
                ->on("{$this->job_offercategorypositions}.position = {$this->positions}.attr_id")
                ->and("{$this->positions}.lang = %s", $lang)
                ->where("{$this->job_offercategorypositions}.profesia_job_id = %i", $id);

        return $result->fetchAssoc("id");
    }

    public function deleteUnused($used, $import)
    {
        $ids = $this->conn->select("id")->from($this->job)
                ->where("imported_from = %s", $import)
                ->fetchPairs("id", "id");
        foreach ($used as $id) {
            unset($ids[$id]);
        }
        if (!empty($ids)) {
            return $this->conn->delete($this->job)
                            ->where("imported_from = %s", $import)
                            ->where("id IN %l", $ids)
                            ->execute();
        }
        return 0;
    }

}

?>
