<?php

namespace Model\Mapper\Dibi;

use Model\Entity\CvEntity,
    Model\Entity\CvWorkEntity,
    Model\Entity\CvEducEntity,
    Model\Entity\CvLangEntity;

/**
 * Class CvDibiMapper
 * @package Model\Mapper\Dibi
 *
 * @author Petr Poupě
 * @author Marek Šneberger <marek@sneberger.cz>
 */
class CvDibiMapper extends DibiMapper
{

    const SAVE_INSERT = 1;
    const SAVE_UPDATE = 2;

    private $main = "cv_main";
    private $education = "cv_education";
    private $langs = "cv_langs";
    private $works = "cv_works";

    /**
     * Vrací celou tabulku
     * @return DibiFluent
     */
    public function allDataSource()
    {
        return $this->conn->select('*')->from($this->main);
    }

    /**
     * Poskytuje pole pro přejmenování dat to Item
     * - předpokládá se, že ostatní položky jsou pojmenovány shodně
     * @return array
     */
    private function dataToItem()
    {
        $alias = array(
            "id" => "id",
            "user_id" => "userId",
            "create_date" => "createDate",
            "change_date" => "changeDate",
            "last_step" => "lastStep",
            "degree_b" => "degreeBefore",
            "degree_a" => "degreeAfter",
            "show_career_objective" => "showCareerObjective",
            "career_objective" => "careerObjective",
            "show_summary" => "showSummary",
            "career_summary" => "careerSummary",
            "avaliblity_from" => "avaliblityFrom",
            "show_desired_employment" => "showDesiredEmployment",
            "job_position" => "jobPosition",
            "salary_public" => "salaryPublic",
            "salary_from" => "salaryFrom",
            "salary_to" => "salaryTo",
            "mother_language" => "motherLanguage",
            "skill_social" => "skillSocial",
            "skill_organise" => "skillOrganise",
            "skill_technical" => "skillTechnical",
            "skill_computer" => "skillComputer",
            "skill_artistic" => "skillArtistic",
            "skill_other" => "skillOther",
            "other_it_skills" => "otherItSkills",
            "is_default" => "isDefault",
            "template_name" => "templateName",
            "show_photo" => "showPhoto",
            "is_graduated" => "isGraduated",
            "in_eu" => "inEu",
            "is_completed" => "completed",
			"passport_number" => "passportNumber",
        );

        return $alias;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole
     *
     * @param \Model\Entity\CvEntity $item
     *
     * @return type
     */
    private function itemToData(CvEntity $item)
    {
        $data = array(
            'id' => $item->id,
            'name' => $item->name,
            'user_id' => $item->userId,
            'create_date%t' => $item->createDate,
            'change_date%t' => $item->changeDate,
            'last_step' => $item->lastStep,
            'photo' => $item->photo,
            'show_photo' => $item->showPhoto,
            'title' => $item->title,
            'firstname' => $item->firstname,
            'middlename' => $item->middlename,
            'surname' => $item->surname,
            'degree_a' => $item->degreeAfter,
            'degree_b' => $item->degreeBefore,
            'gender' => $item->gender,
            'birthday' => $item->birthday,
            'nationality' => $item->nationality,
            'address' => $item->address,
            'house' => $item->house,
            'zipcode' => $item->zipcode,
            'city' => $item->city,
            'county' => $item->county,
            'country' => $item->country,
            'phone' => $item->phone,
            'email' => $item->email,
            'show_career_objective' => $item->showCareerObjective,
            'career_objective' => $item->careerObjective,
            'sector' => $item->sector,
            'show_desired_employment' => $item->showDesiredEmployment,
            'job_position' => $item->jobPosition,
            'avaliblity_from' => $item->avaliblityFrom,
            'salary_public' => $item->salaryPublic,
            'salary_from' => $item->salaryFrom,
            'salary_to' => $item->salaryTo,
            'show_summary' => $item->showSummary,
            'career_summary' => $item->careerSummary,
            'mother_language' => $item->motherLanguage,
            'skill_social' => $item->skillSocial,
            'skill_organise' => $item->skillOrganise,
            'skill_technical' => $item->skillTechnical,
            'skill_computer' => $item->skillComputer,
            'skill_artistic' => $item->skillArtistic,
            'skill_other' => $item->skillOther,
            'other_it_skills' => $item->otherItSkills,
            'info' => $item->info,
            'is_default' => $item->isDefault,
            'template_name' => $item->templateName,
            'fulltext' => $item->fulltext,
            'public' => $item->public,
            'is_graduated' => $item->isGraduated,
            'in_eu' => CvEntity::isEuCountry($item->nationality),
            'is_completed' => $item->isCompleted(),
			'passport_number' => $item->passportNumber,
        );
        if (is_array($item->sector)) {
            $data['sector'] = json_encode($item->sector);
        }
        if (is_array($item->jobPosition)) {
            $data['job_position'] = json_encode($item->jobPosition);
        }
//        if (is_array($item->licenses)) {
//            $data['licenses'] = json_encode($item->licenses);
//        }

        return $data;
    }

    /**
     * @param $data
     *
     * @return CvEntity
     */
    public function load($data)
    {
        $item = new CvEntity;

        $aliases = $this->dataToItem();
        if ($data) {
            foreach ($data as $prop => $val) {
                if (array_key_exists($prop, $aliases)) {
                    $prop = $aliases[$prop];
                }
                $item->$prop = $val;
            }
            if (array_key_exists('sector', $data)) {
                $item->sector = json_decode($data['sector']);
            }
            if (array_key_exists('job_position', $data)) {
                $item->jobPosition = json_decode($data['job_position']);
            }
//            if (array_key_exists('licenses', $data)) {
//                $item->licenses = json_decode($data['licenses']);
//            }
        }

        $this->loadWorks($item);
        $this->loadEducations($item);
        $this->loadLanguages($item);
        $this->loadSkills($item);

        return $item;
    }

    /**
     * @param CvEntity $item
     */
    private function loadWorks(CvEntity &$item)
    {
        $works = $this->conn->select('*')->from($this->works)->where("cv_main_id = %i", $item->id);
        foreach ($works->fetchAll() as $workData) {
            $work = new CvWorkEntity;
            $keys = array(
                'id' => "id",
                'type' => "type",
                'company' => "company",
                'from' => "from",
                'to' => "to",
                'position' => "position",
                'activities' => "activities",
                'achievment' => "achievment",
                'ref_public' => "refPublic",
                'ref_name' => "refName",
                'ref_position' => "refPosition",
                'ref_phone' => "refPhone",
                'ref_email' => "refEmail",
                'ref_file' => "file",
            );
            foreach ($keys as $dataKey => $itemKey) {
                $work->$itemKey = $workData[$dataKey];
            }
            $item->addWork($work);
        }
    }

    /**
     * @param CvEntity $item
     */
    private function loadEducations(CvEntity &$item)
    {
        $educations = $this->conn->select('*')->from($this->education)->where("cv_main_id = %i", $item->id);
        foreach ($educations->fetchAll() as $educData) {
            $education = new CvEducEntity;
            $keys = array(
                'id' => "id",
                'from' => "from",
                'to' => "to",
                'title' => "title",
                'subjects' => "subjects",
                'instit_name' => "institName",
                'instit_city' => "institCity",
                'instit_country' => "institCountry",
            );
            foreach ($keys as $dataKey => $itemKey) {
                $education->$itemKey = $educData[$dataKey];
            }
            $item->addEducation($education);
        }
    }

    /**
     * @param CvEntity $item
     */
    private function loadLanguages(CvEntity &$item)
    {
        $languages = $this->conn->select('*')->from($this->langs)->where("cv_main_id = %i", $item->id);
        foreach ($languages->fetchAll() as $langData) {
            $language = new CvLangEntity;
            $keys = array(
                'id' => "id",
                'lang' => "lang",
                'listening' => "listening",
                'reading' => "reading",
                'interaction' => "interaction",
                'production' => "production",
                'writing' => "writing",
            );
            foreach ($keys as $dataKey => $itemKey) {
                $language->$itemKey = $langData[$dataKey];
            }
            $item->addLanguage($language);
        }
    }

    /**
     * Sets skills array to CvEntity and formats second array for rendering skills
     *
     * @param CvEntity $entity
     */
    private function loadSkills(CvEntity $entity)
    {
        $skills = $this->conn->select('skill_id')
                ->select('scale')
                ->select('years')
                ->select('skill_category_id')
                ->select('si.name')
                ->select('si.order AS skill_order')
                ->select('sc.name AS category')
                ->select('sc.order AS category_order')
                ->select('sc.parent_category_id')
                ->from('cv_skills s')
                ->join('[cv_skill_items] [si] ON [s].[skill_id] = [si].[id]')
                ->join('[cv_skill_categories] [sc] ON [si].[skill_category_id] = [sc].[id]')
                ->where('[cv_id] =%i', $entity->id)
                ->orderBy('category_order, skill_order')->fetchAll();
        $categories = [];
        foreach ($skills as $skill) {
            if ($skill->parent_category_id) {
                $categories[] = $skill->parent_category_id;
            } else {
                $categories[] = $skill->skill_category_id;
            }
        }
        $categories = $this->conn
                ->select('id')
                ->select('name')
                ->from('cv_skill_categories')
                ->where('[id] IN %in', $categories)->fetchAssoc('id');
        
        $skillsForRendering = [];
        foreach ($skills as $skill) {
            if ($skill->parent_category_id) {
                $category = $categories[$skill->parent_category_id]->name;
            } else {
                $category = $skill->category;
            }
            if (!isset($skillsForRendering[$category])) {
                $skillsForRendering[$category] = [];
            }
            $skillsForRendering[$category][] = $skill;
        }
        foreach ($skillsForRendering as $key => $category) {
            uasort($category, $this->compareSkills);
            $skillsForRendering[$key] = $category;
        }
        
        $orderedSkills = $skills;
        uasort($orderedSkills, $this->compareSkills);
        
        $entity->addSkill($orderedSkills);
        if (isset($skillsForRendering['Others'])) {
            $skillsForRendering['Others'][] = $entity->otherItSkills;
        } else {
            $skillsForRendering['Others'] = [$entity->otherItSkills];
        }
        $entity->addSkillsForRendering($skillsForRendering);
    }
    
    public function compareSkills($skillA, $skillB)
    {
        switch ($skillA->scale) {
            case 'Basic': $scaleA = 1; break;
            case 'Intermediate': $scaleA = 2; break;
            case 'Advanced': $scaleA = 3; break;
            case 'Expert': $scaleA = 4; break;
            default: $scaleA = 0;
        }
        switch ($skillB->scale) {
            case 'Basic': $scaleB = 1; break;
            case 'Intermediate': $scaleB = 2; break;
            case 'Advanced': $scaleB = 3; break;
            case 'Expert': $scaleB = 4; break;
            default: $scaleB = 0;
        }
        if ($scaleA == $scaleB) {
            if ($skillA->years == $skillB->years) {
                return 0;
            } else {
                return $skillA->years < $skillB->years ? 1 : -1;
            }
        } else {
            return $scaleA < $scaleB ? 1 : -1;
        }
    }

    /**
     * Try to save or update CV and fix default CV
     *
     * @param CvEntity $entity
     * @param null $what
     *
     * @return CvEntity
     */
    public function save(CvEntity $entity, $what = NULL)
    {
        if ($what === NULL) {
            $saved = $this->saveAll($entity);
            $this->fixUserDefaultCvs($entity->userId, $entity->id, $entity->isDefault);
        } else {
            if (!is_array($what)) {
                $what = array($what);
            }

            $saved = $this->saveOnly($entity, $what);
            $this->fixUserDefaultCvs($entity->userId, $entity->id, $entity->isDefault);
        }

        return $saved;
    }

    /**
     * @param CvEntity $entity
     * @param $what
     *
     * @return CvEntity
     */
    private function saveOnly(CvEntity $entity, $what)
    {
        $data = array();
        foreach ($what as $whatItem) {
            switch ($whatItem) {
                case "name":
                    $data['name'] = $entity->name;
                    break;
                case "lastStep":
                    $data['last_step'] = $entity->lastStep;
                    break;
                case "templateName":
                    $data['template_name'] = $entity->templateName;
                    break;
            }
        }
        if ($data !== array()) {
            if ($entity->id === NULL) { // insert
                $entity->createDate = time();
                $entity->id = $this->conn->insert($this->main, $data)
                        ->execute(\dibi::IDENTIFIER);
            } else { // update
                $this->conn->update($this->main, $data)
                        ->where('id = %i', $entity->id)
                        ->execute();
            }
        }

        return $entity;
    }

    /**
     * @param CvEntity $entity
     *
     * @return CvEntity
     */
    private function saveAll(CvEntity $entity)
    {
        $entity->changeDate = time();

        if ($entity->id === NULL) { // insert
            $type = self::SAVE_INSERT;
            $entity->createDate = time();
            $data = $this->itemToData($entity);
            $entity->id = $this->conn->insert($this->main, $data)
                    ->execute(\dibi::IDENTIFIER);
        } else { // update
            $type = self::SAVE_UPDATE;
            $data = $this->itemToData($entity);
            $this->conn->update($this->main, $data)
                    ->where('id = %i', $entity->id)
                    ->execute();
        }
        $this->saveWorks($entity, $type);
        $this->saveEducations($entity, $type);
        $this->saveLanguages($entity, $type);

        return $entity;
    }

    private function fixUserDefaultCvs($userId, $id, $isDefault)
    {
        if ($isDefault && $id) {
            $this->conn->update($this->main, ['is_default' => 0])->where('[user_id] = %i', $userId)->execute();
            $this->conn->update($this->main, ['is_default' => 1])->where('[id] = %i', $id)->execute();
            
        } else {
            $defaultCount = $this->conn->select('[id]')
                    ->from('cv_main')
                    ->where('[user_id] = %i', $userId)
                    ->where('[is_default] = %b', TRUE)
                    ->count();
            
            if ($defaultCount !== 1) {
                $this->conn->update($this->main, ['is_default' => 1])->where('[user_id] = %i', $userId)->limit(1)->execute();
            }            
        }
    }

    /**
     * @param CvEntity $item
     * @param int $type
     */
    private function saveWorks(CvEntity &$item, $type = self::SAVE_INSERT)
    {
        $table = $this->works;
        $toDelete = $this->conn->select('id')->from($table)
                        ->where('cv_main_id = %i', $item->id)->fetchPairs('id', 'id');

        /* @var $work CvWorkEntity */
        $oldWorks = $item->getWorks();
        $item->deleteWorks();
        foreach ($oldWorks as $work) {
            if ($type === self::SAVE_INSERT) {
                $work->id = NULL;
            }

            $data = array(
                'cv_main_id' => $item->id,
                'type' => $work->type,
                'company' => $work->company,
                'from' => $work->from,
                'to' => $work->to,
                'position' => $work->position,
                'activities' => $work->activities,
                'achievment' => $work->achievment,
                'ref_public' => $work->refPublic,
                'ref_name' => $work->refName,
                'ref_position' => $work->refPosition,
                'ref_phone' => $work->refPhone,
                'ref_email' => $work->refEmail,
            );
            if ($work->file instanceof \Nette\Http\FileUpload && $work->file->isOk()) { // \Nette\Http\FileUpload
                // TODO: saving file (with deleting)
                $data['ref_file'] = $work->file->name;
            }
            if ($work->id === NULL) {
                $work->id = $this->conn->insert($table, $data)->execute(\dibi::IDENTIFIER);
            } else {
                $this->conn->update($table, $data)->where("id = %i", $work->id)->execute();
            }

            $item->addWork($work);
            unset($toDelete[$work->id]);
        }

        if ($toDelete !== array()) {
            $this->conn->delete($table)
                    ->where('id IN %l', $toDelete)
                    ->execute();
        }
    }

    /**
     * @param CvEntity $item
     * @param int $type
     */
    private function saveEducations(CvEntity &$item, $type = self::SAVE_INSERT)
    {
        $table = $this->education;
        $toDelete = $this->conn->select('id')->from($table)
                        ->where('cv_main_id = %i', $item->id)->fetchPairs('id', 'id');

        /* @var $educ CvEducEntity */
        $oldEducs = $item->getEducations();
        $item->deleteEducations();
        foreach ($oldEducs as $educ) {
            if ($type === self::SAVE_INSERT) {
                $educ->id = NULL;
            }

            $data = array(
                'cv_main_id' => $item->id,
                'from' => $educ->from,
                'to' => $educ->to,
                'title' => $educ->title,
                'subjects' => $educ->subjects,
                'instit_name' => $educ->institName,
                'instit_city' => $educ->institCity,
                'instit_country' => $educ->institCountry,
            );
            if ($educ->id === NULL) {
                $educ->id = $this->conn->insert($table, $data)->execute(\dibi::IDENTIFIER);
            } else {
                $this->conn->update($table, $data)->where("id = %i", $educ->id)->execute();
            }

            $item->addEducation($educ);
            unset($toDelete[$educ->id]);
        }

        if ($toDelete !== array()) {
            $this->conn->delete($table)
                    ->where('id IN %l', $toDelete)
                    ->execute();
        }
    }

    /**
     * @param CvEntity $item
     * @param int $type
     */
    private function saveLanguages(CvEntity &$item, $type = self::SAVE_INSERT)
    {
        $table = $this->langs;
        $toDelete = $this->conn->select('id')->from($table)
                        ->where('cv_main_id = %i', $item->id)->fetchPairs('id', 'id');

        /* @var $lang CvLangEntity */
        $oldLangs = $item->getLanguages();
        $item->deleteLanguages();
        foreach ($oldLangs as $lang) {
            if ($type === self::SAVE_INSERT) {
                $lang->id = NULL;
            }

            $data = array(
                'cv_main_id' => $item->id,
                'lang' => $lang->lang,
                'listening' => $lang->listening,
                'reading' => $lang->reading,
                'interaction' => $lang->interaction,
                'production' => $lang->production,
                'writing' => $lang->writing,
            );
            if ($lang->id === NULL) {
                $lang->id = $this->conn->insert($table, $data)->execute(\dibi::IDENTIFIER);
            } else {
                $this->conn->update($table, $data)->where("id = %i", $lang->id)->execute();
            }

            $item->addLanguage($lang);
            unset($toDelete[$lang->id]);
        }

        if ($toDelete !== array()) {
            $this->conn->delete($table)
                    ->where('id IN %l', $toDelete)
                    ->execute();
        }
    }

    /**
     * @param $id
     *
     * @return CvEntity
     */
    public function find($id)
    {
        $data = $this->conn->select('*')->from($this->main)->where('id = %i', $id)->fetch();

        return $this->load($data);
    }

    /**
     * @param $values
     *
     * @return array
     */
    private function checkColumns($values)
    {
        // poskytuje rozhraní pro vyhledávání pomocí aliasů
        $alias = array(
            'id' => "id%i",
            'user' => "user_id%i",
        );
        $new = array();
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $alias)) {
                $key = $alias[$key];
            }
            $new[$key] = $value;
        }

        return $new;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    public function findBy(array $values)
    {
        $values = $this->checkColumns($values);
        $data = $this->conn->select('*')->from($this->main)->where($values)->fetchAll();
        $return = array();
        foreach ($data as $item) {
            $return[] = $this->load($item);
        }

        return $return;
    }

    /**
     * @param array $values
     *
     * @return CvEntity
     */
    public function findOneBy(array $values)
    {
        $values = $this->checkColumns($values);
        $data = $this->conn->select('*')->from($this->main)->where($values)->fetch();

        return $this->load($data);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->conn->select('*')->from($this->main)->fetchAssoc('id');
    }

    /**
     * @param $userId
     *
     * @return CvEntity
     */
    public function findLast($userId)
    {
        $user = $this->conn->select('cv_main_last_opened')->from('users')
                        ->where('id = %i', $userId)->fetch();
        $lastOpened = $this->conn->select('*')->from($this->main)
                        ->where("id = %i", $user->cv_main_last_opened)->fetch();
        if ($lastOpened) {
            return $this->load($lastOpened);
        } else {
            $data = $this->conn->select('*')->from($this->main)
                    ->where('user_id = %i', $userId)
                    ->orderBy('create_date DESC')
                    ->fetch();

            return $this->load($data);
        }
    }

    /**
     * @param $userId
     * @param null $limit
     * @param bool $ordedByLastChange
     *
     * @return array
     */
    public function getCvList($userId, $limit = NULL, $ordedByLastChange = FALSE)
    {
        $data = $this->conn->select('id, name')->from($this->main)
                ->where("user_id = %i", $userId);
        if ($ordedByLastChange) {
            $data->orderBy('change_date DESC, create_date');
        } else {
            $data->orderBy('create_date');
        }
        if ($limit > 0) {
            $data->limit($limit);
        }

        return $data->fetchAll();
    }

    /**
     * @param CvEntity $entity
     *
     * @return \DibiResult|int
     */
    public function delete(CvEntity $entity)
    {
        $delete = $this->conn->delete($this->main)->where(
                        array(
                            "id%i" => $entity->id,
                            "user_id%i" => $entity->userId,
                        )
                )->execute();
        $this->fixUserDefaultCvs($entity->userId, FALSE, FALSE); // Fix user default cv
        try {
            $this->conn->begin();
            $this->conn->delete('cv_skills')->where('[cv_id] =%i', $entity->id)->execute(); // remove CV skills first
            $delete = $this->conn->delete($this->main)->where(
                            array(
                                "id%i" => $entity->id,
                                "user_id%i" => $entity->userId,
                            )
                    )->execute();
            $this->fixUserDefaultCvs($entity->userId, FALSE, FALSE); // Fix user default cv
            $this->conn->commit();
        } catch (\DibiDriverException $e) {
            $this->conn->rollback();
        }

        return $delete;
    }

    /**
     * @param CvEntity $entity
     *
     * @return CvEntity
     */
    public function changeLastOpened(CvEntity $entity)
    {
        $this->conn->update('users', array("cv_main_last_opened" => $entity->id))
                ->where('id = %i', $entity->userId)
                ->execute();

        return $entity;
    }

    /**
     * Return array for <select> to fill StepForm11 (skillsForm)
     * Returned array looks like ['$skillCategoryName' => [$skillItemId => $skillItemName]]
     *
     * @return array
     */
    public function buildSkills()
    {
        $select = array(
            "skill.id" => "skillId",
            "skill.name" => "skillName",
            "category.id" => "categoryId",
            "category.name" => "categoryName",
            "parent_category.id" => "parentId",
            "parent_category.name" => "parentName",
        );
        $rows = $this->conn->select($select)
                ->from("cv_skill_items skill")
                ->join("cv_skill_categories category")->on("skill.skill_category_id = category.id")
                ->leftJoin("cv_skill_categories parent_category")->on("category.parent_category_id = parent_category.id")
                ->orderBy("category.order ASC, skill.order ASC, category.name ASC, skill.name ASC")
                ->fetchAll();

        $skills = array();
        foreach ($rows as $row) {
            $key1 = $row->parentName;
            $key2 = $row->categoryName;
            $key3 = $row->skillId;
            if ($key1 === NULL) {
                $key1 = $key2;
                $key2 = NULL;
            }
            $skills[$key1][$key2][$key3] = $row->skillName;
        }

        return $skills;
    }

    /**
     * CV skills saving
     * First of all remove all skills from db per CV ## Fastest way instead of searching and updating skills per one skill item
     * And then save all skills that have filled skill scale (EG do NOT save skill item with scale '' and years = 2)
     *
     * @param CvEntity $entity
     * @param array $skills
     *
     * @return \DibiResult|int
     */
    public function saveSkills(CvEntity $entity, array $skills)
    {
        $data = [];
        $this->conn->delete('cv_skills')->where('[cv_id] =%i', $entity->id)->execute();
        foreach ($skills as $skillId => $skillItem) {
            if (!empty($skillItem['scale'])) {
                $array = [
                    'cv_id' => $entity->id,
                    'skill_id' => $skillId,
                    'scale' => $skillItem['scale'],
                    'years' => $skillItem['number'],
                ];
                array_push($data, $array);
            }
        }
        if (count($data)) {
            return $this->conn->query('INSERT INTO [cv_skills] %ex', $data);
        } else {
            return 0;
        }
    }
    
    public function getAll()
    {
        $cvs = array();
        foreach($this->findAll() as $row) {
            $cvs[] = $this->load($row);
        }
        return $cvs;
    }
    
    public function getAllCandidateNames($notAsignedToJobId = NULL)
    {
        $selection = $this->conn
            ->select("{$this->main}.user_id, CONCAT(firstname,' ',IFNULL(middlename,''),' ',surname) AS name")
            ->from($this->main)
            ->where('is_default = %i', 1)
            ->where('is_completed = %i', 1)
            ->orderBy('firstname ASC, middlename ASC, surname ASC');
        if ($notAsignedToJobId) {
            $selection->where("user_id NOT IN (SELECT user_id FROM job_user WHERE job_id = %i)", $notAsignedToJobId);
        }
        return $selection->fetchPairs('user_id', 'name');
    }

}
