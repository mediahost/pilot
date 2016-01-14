<?php

namespace Model\Service;

use Model\Entity\JobEntity,
    Model\Mapper\Dibi\JobsDibiMapper,
    Model\Mapper\Dibi\LocationDibiMapper,
    Model\Mapper\Dibi\JobCategoryDibiMapper;

/**
 * Description of JobService
 *
 * @author Radim Křek
 */
class JobService
{

    const CANDIDATE_JOB_STATUS_WAITING = 0;
    const CANDIDATE_JOB_STATUS_REVIEWED = 1;
    const CANDIDATE_JOB_STATUS_REJECTED = 2;
    const CANDIDATE_JOB_STATUS_MESSAGE_SENT = 3;
    const CANDIDATE_JOB_STATUS_INVITED = 4;
    const CANDIDATE_JOB_STATUS_OFFER_MADE = 5;

    const STATUS_BY_COMPANY_INV_PHONE = 1;
    const STATUS_BY_COMPANY_INV_VIDEO = 2;
    const STATUS_BY_COMPANY_INV_TECH = 3;
    const STATUS_BY_COMPANY_COMPLETED_PHONE = 4;
    const STATUS_BY_COMPANY_COMPLETED_VIDEO = 5;
    const STATUS_BY_COMPANY_COMPLETED_TECH = 6;
    const STATUS_BY_COMPANY_JOBS_SPECS_SEND = 7;
    const STATUS_BY_COMPANY_OFFER_MADE = 8;
    const STATUS_BY_COMPANY_OFFER_ACCEPTED = 9;
    const STATUS_BY_COMPANY_OFFER_REJECTED = 10;


    /** @var JobsDibiMapper */
    private $jobMapper;

    /** @var LocationDibiMapper */
    private $locMapper;

    public function __construct(JobsDibiMapper $job, LocationDibiMapper $loc)
    {
        $this->jobMapper = $job;
        $this->locMapper = $loc;
    }

    public static function getCandidateJobStatuses()
    {
        return [
            self::CANDIDATE_JOB_STATUS_WAITING => 'Waiting for Review',
            self::CANDIDATE_JOB_STATUS_REVIEWED => 'Reviewed',
            self::CANDIDATE_JOB_STATUS_REJECTED => 'Rejected',
            self::CANDIDATE_JOB_STATUS_MESSAGE_SENT => 'Message Sent',
            self::CANDIDATE_JOB_STATUS_INVITED => 'Invited for interview',
            self::CANDIDATE_JOB_STATUS_OFFER_MADE => 'Offer made',
        ];
    }

    public static function getCandidateJobStatusName($statusId)
    {
        $statuses = self::getCandidateJobStatuses();
        if (isset($statuses[$statusId])) {
            return $statuses[$statusId];
        }
    }

    public static function getStatusByCompanyList()
    {
        return [
            self::STATUS_BY_COMPANY_INV_PHONE => 'Invited for Phone Interview',
            self::STATUS_BY_COMPANY_INV_VIDEO => 'Invited for Video Interview',
            self::STATUS_BY_COMPANY_INV_TECH => 'Invited for Technical Interview',
            self::STATUS_BY_COMPANY_COMPLETED_PHONE => 'Completed Phone Interview',
            self::STATUS_BY_COMPANY_COMPLETED_VIDEO => 'Completed Video Interview',
            self::STATUS_BY_COMPANY_COMPLETED_TECH => 'Completed Technical Interview',
            self::STATUS_BY_COMPANY_JOBS_SPECS_SEND => 'Jobs Specs sent',
            self::STATUS_BY_COMPANY_OFFER_MADE => 'Offer Made',
            self::STATUS_BY_COMPANY_OFFER_ACCEPTED => 'Offer Accepted',
            self::STATUS_BY_COMPANY_OFFER_REJECTED => 'Offer Rejected',
        ];
    }

    public static function getStatusByCompanyName($statusId)
    {
        $statuses = self::getStatusByCompanyList();
        if (isset($statuses[$statusId])) {
            return $statuses[$statusId];
        }
    }

    public function save(JobEntity $data)
    {
        return $this->jobMapper->save($data);
    }

    public function getMaxId()
    {
        return $this->jobMapper->getMaxId();
    }

    public function getAll($offset = NULL, $limit = NULL, $where = NULL, $order = NULL)
    {
        $data = $this->jobMapper->getAll($offset, $limit, $where, $order);
        $jobs = array();
		$lastId = NULL;
        foreach ($data as $d) {
			if ($d->id != $lastId)
			{
				$d->locations = $this->locMapper->getJobLocations($d->id);
				$jobs[$d->id] = $d;
				$lastId = $d->id;
			}
        }
        return $jobs;
    }

    public function apply($jobId, $userId)
    {
        return $this->jobMapper->apply($jobId, $userId);
    }

	public function getAllForCount($offset = NULL, $limit = NULL, $where = NULL)
    {
        return $this->jobMapper->getAll($offset, $limit, $where);
    }


    public function findBy($column, $value)
    {
        $data = $this->jobMapper->findBy($column, $value);
        $rows = array();
        foreach ($data as $d) {
            $d->locations = $this->locMapper->getJobLocations($d->id);
            $rows[$d->id] = $d;
        }
        return $rows;
    }

    public function find($id)
    {
        return $this->findById($id);
    }

    public function findById($id, $userId = NULL)
    {
        $data = $this->jobMapper->findById($id, $userId);

//        $data->locations = $this->locMapper->getJobLocations($data->id);
        return $data;
    }

    public function findByCode($code, $userId = NULL)
    {
        $data = $this->jobMapper->findByCode($code, $userId);

//        $data->locations = $this->locMapper->getJobLocations($data->id);
        return $data;
    }

    public function delete($id)
    {
        $this->locMapper->deleteConn($id);
        return $this->jobMapper->delete($id);
    }

    public function buildSkills()
    {
        return $this->jobMapper->buildSkills();
    }

    public function saveSkills($id, $skills)
    {
        return $this->jobMapper->saveSkills($id, $skills);
    }

    public function loadSkills($id)
    {
        return $this->jobMapper->loadSkills($id);
    }

    public function loadCategorizedSkills($id)
    {
        return $this->jobMapper->loadCategorizedSkills($id);
    }

    public function loadAircrafts(JobEntity $data)
    {
        return $this->jobMapper->loadAircrafts($data);
    }

    public function getDataGrid()
    {
        return $this->jobMapper->getDataGrid();
    }

    public function getLocations()
    {
        $locations = array();
        foreach ($this->locMapper->getAll() as $location) {
            $locations[$location->id] = $location->name;
        }
        return $locations;
    }

    public function getMaxSalary()
    {
        return $this->jobMapper->getMaxSalary();
    }

    public function getJobUserGridDataSource($jobId)
    {
        return $this->jobMapper->getJobUserGridDataSource($jobId);
    }

    public function setJobUserStatus($id, $status)
    {
        if (self::getCandidateJobStatusName($status)) {
            $this->jobMapper->setJobUserStatus($id, $status);
        }
    }

    public function setJobUserStatusByJobAndUser($jobId, $userId, $status)
    {
        if (self::getCandidateJobStatusName($status)) {
            $this->jobMapper->setJobUserStatusByJobAndUser($jobId, $userId, $status);
        }
    }

    public function addUserToJob($jobId, $userId)
    {
        $this->jobMapper->addUserToJob($jobId, $userId);
    }

    public function removeUserFromJob($jobId, $userId)
    {
        $this->jobMapper->removeUserFromJob($jobId, $userId);
    }

    public function getStatusesByJob($jobId)
    {
        return $this->jobMapper->getStatusesByJob($jobId);
    }

    public function getNoteCountsByJob($jobId)
    {
        return $this->jobMapper->getNoteCountsByJob($jobId);
    }

    public function getNotesByJob($jobId)
    {
        return $this->jobMapper->getNotesByJob($jobId);
    }

    public function getUserJobInfoByJob($jobId, $actions = FALSE)
    {
        return $this->jobMapper->getUserJobInfoByJob($jobId, $actions);
    }

    public function getJobByJobUserId($jobUserId)
    {
        $jobId = $this->jobMapper->getJobIdByJobUserId($jobUserId);
        if ($jobId) {
            return $this->find($jobId);
        } else {
            return NULL;
        }
    }

    public function getJobUser($id)
    {
        return $this->jobMapper->getJobUser($id);
    }

    public function getJobUserByJobAndUser($jobId, $userId)
    {
        return $this->jobMapper->getJobUserByJobAndUser($jobId, $userId);
    }

    public function setJobUserCategory($jobUserId, $category)
    {
        $this->jobMapper->setJobUserCategory($jobUserId, $category);
    }

    public function getNotesByJobUser($jobUserId)
    {
        return $this->jobMapper->getNotesByJobUser($jobUserId);
    }

    public function addNote($jobUserId, $note, $adminId = NULL)
    {
        $this->jobMapper->addNote($jobUserId, $note, $adminId);
    }

    public function editNote($id, $note)
    {
        $this->jobMapper->editNote($id, $note);
    }

    public function deleteNote($id)
    {
        $this->jobMapper->deleteNote($id);
    }

    public function getNote($id)
    {
        return $this->jobMapper->getNote($id);
    }

    public function fillEmptyCodes()
    {
        return $this->jobMapper->fillEmptyCodes();
    }

    public function addJobUserAction($jobUser, $action, $text = '')
    {
        $this->jobMapper->addJobUserAction($jobUser, $action, $text);
    }

    public function updateJobUserNotes($id, $values)
    {
        $this->jobMapper->updateJobUserNotes($id, $values);
    }

    public function updateJobUserStatusByCompany($jboUserId, $status, $statusName)
    {
        $this->jobMapper->updateJobUserStatusByCompany($jboUserId, $status, $statusName);
    }

}
