<?php

namespace Model\Mapper\Dibi;

use Model\Entity\JobEntity;
use Model\Service\JobService;

/**
 * Description of JobsDibiMapper
 *
 * @author Radim Křek
 */
class JobsDibiMapper extends DibiMapper
{

    const JOB_USER_CATEGORY_NONE = 0;
    const JOB_USER_CATEGORY_SHORTLISTED = 1;
    const JOB_USER_CATEGORY_REJECTED = 2;

    const JOB_USER_ACTION_SHORTLISTED = 1;
    const JOB_USER_ACTION_REJECTED = 2;
    const JOB_USER_ACTION_MESSAGE = 3;
    const JOB_USER_ACTION_STATUS = 4;

    public $jobsTable = 'job';
    public $jobsCategoryTable = 'job_category';
    public $jobsSkillTable = 'job_skills';
    public $jobsLocationsTable = 'job_to_locations';
    public $jobUser = 'job_user';
    public $jobUserAction = 'job_user_action';
    public $jobUserNotes = 'job_user_note';

    public function findBy($column, $value)
    {
        $dibiFluent = $this->conn->select($this->jobsTable . '.*, ' . $this->jobsCategoryTable . '.name as category,
            COUNT(matched_job_user.id) AS matched_count,
            COUNT(applyed_job_user.id) AS applyed_count,
            SUM(cv_main.is_completed) AS applyed_completed_count,
            COUNT(shotlisted_job_user.id) AS shortlisted_count,
            COUNT(invited_job_user.id) AS invited_count,
            COUNT(process_completed_job_user.id) AS process_completed_count,
            COUNT(offer_made_job_user.id) AS offer_made_count'
        )
                ->from($this->jobsTable)
                ->where($this->jobsTable . '.' . $column . '=%i', $value)
                ->join($this->jobsCategoryTable)->on($this->jobsTable . '.category=' . $this->jobsCategoryTable . '.id')
                ->leftJoin('job_user AS matched_job_user')->on("matched_job_user.job_id = {$this->jobsTable}.id")
                ->leftJoin('job_user AS applyed_job_user')->on("applyed_job_user.id = matched_job_user.id AND applyed_job_user.applyed = 1")
                ->leftJoin('job_user AS shotlisted_job_user')->on("shotlisted_job_user.id = matched_job_user.id AND shotlisted_job_user.category = %i", self::JOB_USER_CATEGORY_SHORTLISTED)
                ->leftJoin('job_user AS invited_job_user')
                    ->on('invited_job_user.id = shotlisted_job_user.id AND invited_job_user.status_by_company IS NOT NULL')
                ->leftJoin('job_user AS process_completed_job_user')
                    ->on('process_completed_job_user.id = shotlisted_job_user.id AND process_completed_job_user.status_by_company >= %i', JobService::STATUS_BY_COMPANY_COMPLETED_PHONE)
                ->leftJoin('job_user AS offer_made_job_user')
                    ->on('offer_made_job_user.id = shotlisted_job_user.id AND offer_made_job_user.status_by_company >= %i', JobService::STATUS_BY_COMPANY_OFFER_MADE)
                ->leftJoin('cv_main')->on('cv_main.user_id = applyed_job_user.user_id AND is_default = 1')
                ->groupBy("{$this->jobsTable}.id");
        $data = $dibiFluent->fetchAll();
        $return = array();
        foreach ($data as $d) {
            $return[] = new JobEntity($d);
        }
        return $return;
    }

    public function findByCode($code, $_userId = NULL)
    {
        if ($_userId) {
            $data = $this->conn
                ->select($this->jobsTable . '.* ' . $this->jobUser . '.applyed AS applyed')
                ->from($this->jobsTable)
                ->where('job.code=%s', $code)
                ->join($this->jobUser)->on("{$this->jobUser}.job_id = {$this->jobsTable}.id AND {$this->jobUser}.user_id = %i", $_userId)
                ->fetch();

        } else {
            $data = $this->conn->select($this->jobsTable . '.*')->from($this->jobsTable)
                ->where('job.code=%s', $code)
                ->fetch();
        }

        $return = new JobEntity($data);
        return $return;
    }

    public function findById($_id, $_userId = NULL)
    {
        if ($_userId) {
            $data = $this->conn
                ->select($this->jobsTable . '.* ' . $this->jobUser . '.applyed AS applyed')
                ->from($this->jobsTable)
                ->where('job.id=%i', $_id)
                ->join($this->jobUser)->on("{$this->jobUser}.job_id = {$this->jobsTable}.id AND {$this->jobUser}.user_id = %i", $_userId)
                ->fetch();

        } else {
            $data = $this->conn->select($this->jobsTable . '.*')->from($this->jobsTable)
                ->where('job.id=%i', $_id)
                ->fetch();
        }

        $return = new JobEntity($data);
        return $return;
    }

    public function findOneBy($array)
    {
        $data = $this->conn->select($this->jobsTable . '.*')->from($this->jobsTable)
                ->where($array)
                ->fetch();

        $return = new JobEntity($data);
        return $return;
    }

    public function getAll($_offset = NULL, $_limit = NULL, $_where = NULL, $_order = NULL)
    {
        $data = $this->conn->select($this->jobsTable . '.*, COUNT(job_user.id) AS matched_count, job_user.applyed AS applyed')
                        ->from($this->jobsTable)
                        ->leftJoin('job_user')->on("job_user.job_id = {$this->jobsTable}.id")
                        ->groupBy("{$this->jobsTable}.id");

        if ($_where !== NULL && $_where !== array()) {

            if (isset($_where['job.text'])) {
                $text = $_where['job.text'];
                $data->where("(job.company LIKE %~like~ "
                        . "OR job.summary LIKE %~like~ "
                        . "OR job.description LIKE %~like~ "
                        . "OR job.offers LIKE %~like~ "
                        . "OR job.requirments LIKE %~like~)", $text, $text, $text, $text, $text);
                unset($_where['job.text']);
            }

            $min = isset($_where['jobs.salary_from']) ? $_where['jobs.salary_from'] : NULL;
            $max = isset($_where['jobs.salary_to']) ? $_where['jobs.salary_to'] : NULL;
            if ((int) $min > 1) {
                $data->where("job.salary_from >= %i", $min);
                $data->where("(job.salary_to >= %i OR job.salary_to = %i OR job.salary_to IS NULL)", $min, 0);
            }
            if ((int) $max > 1) {
                $data->where("job.salary_to <= %i", $max);
                $data->where("(job.salary_from <= %i OR job.salary_from = %i OR job.salary_from IS NULL)", $max, 0);
            }
            unset($_where['jobs.salary_to']);
            unset($_where['jobs.salary_from']);

            if (isset($_where['datecreated'])) {
                $data->where('datecreated > %t', $_where['datecreated']);
            }
            unset($_where['datecreated']);
            if (isset($_where['job.salary_from'])) {
                $data->where('job.salary_to >= %i', $_where['job.salary_from']);
            }
            unset($_where['job.salary_from']);
            if (isset($_where['job.salary_to'])) {
                $data->where('job.salary_from <= %i', $_where['job.salary_to']);
            }
            unset($_where['job.salary_to']);
        }
        if ($_where !== NULL && $_where !== array()) {
            $data->where($_where);
        }
        if ($_offset !== NULL) {
            $data->offset($_offset);
        }
        if ($_limit !== NULL) {
            $data->limit($_limit);
        }

        $orderBy = NULL;
        switch ($_order) {
            case 'time':
                $orderBy = 'datecreated DESC';
                break;
            case 'salary':
                $orderBy = 'salary_from DESC';
        }
        if ($orderBy) {
            $data->orderBy($orderBy);
        }

        $data->fetchAll();
        $return = array();
        foreach ($data as $job) {
            $return[] = new JobEntity($job);
        }
        return $return;
    }

    public function save(JobEntity $job)
    {
        if ($this->findById($job->id)->id) {
            //update
            $data = $job->to_array();
            unset($data['code']);
            $this->conn->update($this->jobsTable, $data)->where('id=%i', $data->id)->execute();
        } else {
            $data = $job->to_array();
            $data['code'] = $this->generateUniqCode();
            //insert
            $job->id = $this->conn->insert($this->jobsTable, $data)->execute(\dibi::IDENTIFIER);
        }
		return $this->saveAircrafts($job);
    }
	
	public function saveAircrafts(JobEntity $job)
	{
		$this->conn->delete('job_aircraft')
			->where('job_id = %i', $job->id)
			->execute();
		/** @var JobAircraft $pilotExperience */
		foreach ($job->pilotExperiences as $pilotExperience) {
			$this->conn->insert('job_aircraft', [
				'job_id' => $job->id,
				'aircraft_id' => $pilotExperience->aircraftId,
				'hours' => $pilotExperience->hours,
				'pic' => $pilotExperience->pic,
			])->execute();
		}
		/** @var JobAircraft $copilotExperience */
		foreach ($job->copilotExperiences as $copilotExperience) {
			$this->conn->insert('job_aircraft', [
				'job_id' => $job->id,
				'aircraft_id' => $copilotExperience->aircraftId,
				'hours' => $copilotExperience->hours,
				'pic' => NULL,
			])->execute();
		}
		return $this->loadAircrafts($job);
	}

	public function loadAircrafts(JobEntity $job)
	{
		$aircrafts = $this->conn->select('job_aircraft.*, aircraft.name AS aname, aircraft_manufacturer.name AS mname, aircraft.aircraft_manufacturer_id AS manufacturerid, aircraft.type')
			->from('job_aircraft')
			->join('aircraft')->on('aircraft.id = job_aircraft.aircraft_id')
			->join('aircraft_manufacturer')->on('aircraft_manufacturer.id = aircraft.aircraft_manufacturer_id')
			->where('job_id = %i', $job->id)
			->fetchAll();
		$job->pilotExperiences = [];
		$job->copilotExperiences = [];
		foreach ($aircrafts as $aircraft) {
			$userAircraft = new \Model\Entity\JobAircraft();
			$userAircraft->aircraftId = $aircraft->aircraft_id;
			$userAircraft->aircraftName = $aircraft->aname;
			$userAircraft->aircraftTypeName = \Model\Service\AircraftService::getTypeName($aircraft->type);
			$userAircraft->aircraftType = $aircraft->type;
			$userAircraft->manufacturerId = $aircraft->manufacturerid;
			$userAircraft->manufacturerName = $aircraft->mname;
			$userAircraft->hours = $aircraft->hours;
			$userAircraft->pic = $aircraft->pic;

			if ($aircraft->pic === NULL) {
				$job->copilotExperiences[] = $userAircraft;
			} else {
				$job->pilotExperiences[] = $userAircraft;
			}
		}
		return $job;
	}

    public function delete($id)
    {
        return $this->conn->delete($this->jobsTable)->where('id =%i', $id)->execute();
    }

    public function getMaxId()
    {
        $data = $this->conn->query('SELECT MAX([id]) FROM [' . $this->jobsTable . ']')->fetch();
        return $data['MAX(`id`)'];
    }

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
                ->from("cv_skill_items as skill")
                ->join("cv_skill_categories as category")->on("skill.skill_category_id = category.id")
                ->leftJoin("cv_skill_categories as parent_category")->on("category.parent_category_id = parent_category.id")
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

    public function saveSkills($_id, array $_skills)
    {
        $data = [];
        $this->conn->delete($this->jobsSkillTable)->where('[jobs_id]=%i', $_id)->execute();
        foreach ($_skills as $skillId => $skillItem) {
            if (!empty($skillItem['scale'])) {
                $array = [
                    'jobs_id' => $_id,
                    'cv_skill_id' => $skillId,
                    'scale' => $skillItem['scale'],
                    'years' => $skillItem['number'],
                ];
                array_push($data, $array);
            }
        }
        if (count($data)) {
            return $this->conn->query('INSERT INTO [' . $this->jobsSkillTable . '] %ex', $data);
        }
    }

    public function loadSkills($id)
    {
        $skills = $this->conn->select('s.cv_skill_id')
                        ->select('s.scale')
                        ->select('s.years')
                        ->select('si.name')
                        ->select('si.skill_category_id')
                        ->select('si.order AS skill_order')
                        ->select('sc.name AS category')
                        ->select('sc.order AS category_order')
                        ->select('sc.parent_category_id')
                        ->from('job_skills s')
                        ->join('[cv_skill_items] [si] ON [s].[cv_skill_id] = [si].[id]')
                        ->join('[cv_skill_categories] sc ON [si].[skill_category_id] = [sc].[id]')
                        ->where('s.jobs_id=%i', $id)
                        ->orderBy('category_order, skill_order')->fetchAll();
        return $skills;
    }

    public function loadCategorizedSkills($id)
    {
        $skills = $this->loadSkills($id);

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

        return $skillsForRendering;
    }

    public function getDataGrid()
    {
        return $this->conn->select($this->jobsTable . '.*, ' . $this->jobsCategoryTable . '.name as category, candidate_users.username AS companyUser, COUNT(matched_job_user.id) AS candidates, COUNT(applyed_job_user.id) AS applyed_candidates, SUM(cv_main.is_completed) AS applyed_completed_candidates')
                        ->from($this->jobsTable)
                        ->join($this->jobsCategoryTable)->on($this->jobsTable . '.category=' . $this->jobsCategoryTable . '.id')
                        ->join('candidate_users')->on("candidate_users.id = {$this->jobsTable}.company_id")
                        ->leftJoin('job_user AS matched_job_user')->on("matched_job_user.job_id = {$this->jobsTable}.id")
                        ->leftJoin('job_user AS applyed_job_user')->on("applyed_job_user.id = matched_job_user.id AND applyed_job_user.applyed = 1")
                        ->leftJoin('cv_main')->on('cv_main.user_id = applyed_job_user.user_id AND is_default = 1')
                        ->groupBy("{$this->jobsTable}.id");


    }

    public function getMaxSalary()
    {
        return $this->conn
                ->select('MAX(salary_to)')
                ->from('job')
                ->fetchSingle();
    }

    public function getJobUserGridDataSource($jobId)
    {
        return $this->conn
            ->select("{$this->jobUser}.id, firstname, middlename, surname, status, applyed, {$this->jobUser}.user_id, COUNT({$this->jobUserNotes}.id) AS notes_count")
            ->from($this->jobUser)
            ->join('cv_main')->on("cv_main.user_id = {$this->jobUser}.user_id AND cv_main.is_default = 1")
            ->leftJoin($this->jobUserNotes)->on("{$this->jobUserNotes}.job_user_id = {$this->jobUser}.id")
            ->where("{$this->jobUser}.job_id = %i", $jobId)
            ->groupBy("{$this->jobUser}.id");
    }

    public function setJobUserStatus($id, $status)
    {
        $this->conn
            ->update($this->jobUser, ['status' => $status])
            ->where('id = %i', $id)
            ->execute();
    }

    public function setJobUserStatusByJobAndUser($jobId, $userId, $status)
    {
        $this->conn
            ->update($this->jobUser, ['status' => $status])
            ->where('job_id = %i', $jobId)
            ->where('user_id = %i', $userId)
            ->execute();
    }

    public function addUserToJob($jobId, $userId)
    {
        $this->conn
            ->insert($this->jobUser, [
                'job_id' => $jobId,
                'user_id' => $userId,
            ])
            ->execute();
    }

    public function removeUserFromJob($jobId, $userId)
    {
        $this->conn
            ->delete($this->jobUser)
            ->where('job_id = %i', $jobId)
            ->where('user_id = %i', $userId)
            ->execute();
    }

    public function getStatusesByJob($jobId)
    {
        return $this->conn
            ->select('user_id, status')
            ->from($this->jobUser)
            ->where('job_id = %i', $jobId)
            ->fetchPairs('user_id', 'status');
    }

    public function getNotesByJob($jobId)
    {
        return $this->conn
            ->select('user_id, note')
            ->from($this->jobUser)
            ->join($this->jobUserNotes)->on("{$this->jobUserNotes}.job_user_id = {$this->jobUser}.id")
            ->where("job_id = %i", $jobId)
            ->fetchPairs();
    }

    public function getUserJobInfoByJob($jobId, $actions = FALSE)
    {
        $selection = $this->conn
            ->select("user_id, job_user.id, status, status_by_company, status_by_company_text,
            category, category_change_date,
            note_general, note_interview, note_communication, note_technical, note_other,
            COUNT({$this->jobUserNotes}.id) AS count")
            ->from($this->jobUser)
            ->leftJoin($this->jobUserNotes)->on("{$this->jobUserNotes}.job_user_id = {$this->jobUser}.id")
            ->groupBy("user_id")
            ->where("job_id = %i", $jobId)
            ->fetchAssoc('user_id');

        $data = [];
        $jobUserIds = [];
        foreach ($selection as $row) {
            $jobUserIds[] = $row->id;
            $data[$row->user_id] = (array) $row;
            foreach (['note_general', 'note_interview', 'note_communication', 'note_technical', 'note_other'] as $column) {
                if (!empty($row->{$column})) {
                    $data[$row->user_id]['count']++;
                }
            }
            $data[$row->user_id]['notes'] = [];
        }
        foreach ($this->getNotesByJob($jobId) as $userId => $note) {
            $data[$userId]['notes'][] = $note;
        }
        if ($actions) {
            $actions = $this->conn->select('*')
                ->from($this->jobUserAction)
                ->where("job_user_id IN %l", $jobUserIds)
                ->orderBy("id DESC")
                ->fetchAll();
            foreach ($data as $userId => $info) {
                $data[$userId]['history'] = [];
                foreach ($actions as $row) {
                    if ($row->job_user_id == $info['id']) {
                        $data[$userId]['history'][] = $row;
                    }
                }
            }
        }
        return $data;
    }

    public function getJobIdByJobUserId($jobUserId)
    {
        $row = $this->conn->select('job_id')
            ->from($this->jobUser)
            ->where('id = %i', $jobUserId)
            ->fetch();
        if ($row) {
            return $row->job_id;
        } else {
            return NULL;
        }
    }

    public function getJobUser($id)
    {
        return $this->conn->select('*')
            ->from($this->jobUser)
            ->where('id = %i', $id)
            ->fetch();
    }

    public function getJobUserByJobAndUser($jobId, $userId)
    {
        return $this->conn->select('*')
            ->from($this->jobUser)
            ->where('user_id = %i AND job_id = %i', $userId, $jobId)
            ->fetch();
    }

    public function setJobUserCategory($jobUserId, $category)
    {
        $this->conn->update($this->jobUser, array(
            'category' => $category,
            'category_change_date' => new \Nette\DateTime,
        ))
        ->where('id = %i', $jobUserId)
        ->execute();
        switch ($category) {
            case self::JOB_USER_CATEGORY_SHORTLISTED: $action = self::JOB_USER_ACTION_SHORTLISTED; break;
            case self::JOB_USER_CATEGORY_REJECTED: $action = self::JOB_USER_ACTION_REJECTED; break;
            default: $action = NULL;
        }
        if ($action !== NULL) {
            $this->addJobUserAction($jobUserId, $action);
        }
    }

    public function getNotesByJobUser($jobUserId)
    {
        return $this->conn->select('*')
            ->from($this->jobUserNotes)
            ->where('job_user_id = %i', $jobUserId);
    }

    public function addNote($jobUserId, $note, $adminId = NULL)
    {
        $this->conn
            ->insert($this->jobUserNotes, [
                'job_user_id' => $jobUserId,
                'note' => $note,
                'admin_id' => $adminId,
                'created' => new \DateTime,
                'edited' => new \DateTime,
            ])->execute();
    }

    public function editNote($id, $note)
    {
        $this->conn
            ->update($this->jobUserNotes, [
                'note' => $note,
                'edited' => new \DateTime,
            ])
            ->where('id = %i', $id)
            ->execute();
    }

    public function deleteNote($id)
    {
        $this->conn
            ->delete($this->jobUserNotes)
            ->where('id = %i', $id)
            ->execute();
    }

    public function getNote($id)
    {
        return $this->conn
            ->select('*')
            ->from($this->jobUserNotes)
            ->where('id = %i', $id)
            ->fetch();
    }

    public function apply($jobId, $userId)
    {
        return $this->conn
            ->update($this->jobUser, array('applyed' => 1))
            ->where("job_id = %i AND user_id = %i", $jobId, $userId)
            ->execute();
    }

    public function fillEmptyCodes()
    {
        $selection = $this->conn->select('id')
            ->from($this->jobsTable)
            ->where('code = %s','');
        foreach ($selection as $row) {
            $this->conn
                ->update($this->jobsTable, ['code' => $this->generateUniqCode()])
                ->where('id = %i', $row->id)
                ->execute();
        }
        return count($selection);
    }

    public function generateUniqCode()
    {
        do {
            $code = \Nette\Utils\Strings::random(20);
            $row = $this->conn->select('id')->from($this->jobsTable)->where('code = %s', $code)->fetch();
        } while ($row);
        return $code;
    }

    public function addJobUserAction($jobUserId, $action, $text = '')
    {
        $this->conn->insert($this->jobUserAction, array(
            'job_user_id' => $jobUserId,
            'action' => $action,
            'date' => new \Nette\DateTime,
            'text' => $text,
        ))->execute();
    }

    public function updateJobUserNotes($id, $values)
    {
        $this->conn->update($this->jobUser, $values)
            ->where('id = %i', $id)
            ->execute();
    }

    public function updateJobUserStatusByCompany($jobUserid, $status, $statusName)
    {
        if ($status) {
            $statusName = '';
        }
        $this->conn->update($this->jobUser, [
            'status_by_company' => $status,
            'status_by_company_text' => $statusName,
        ])
            ->where('id = %i', $jobUserid)
            ->execute();
    }

}
