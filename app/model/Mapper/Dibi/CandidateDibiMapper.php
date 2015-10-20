<?php

namespace Model\Mapper\Dibi;

use Model\Entity\CandidateEntity;

/**
 * Candidate DibiMapper
 *
 * @author Petr Poupě
 */
class CandidateDibiMapper extends DibiMapper
{

    private $favorites = "candidate_favorite";
    private $cvMain = "cv_main";
    private $cvSkills = "cv_skills";

    /** @var UserDocDibiMapper */
    protected $userDocMapper;

    public function __construct(\DibiConnection $conn)
    {
        $this->userDocMapper = new \Model\Mapper\Dibi\UserDocDibiMapper($conn);
        parent::__construct($conn);
    }

    public function getCandidates(array $where = array(), $offset = NULL, $limit = NULL)
    {
        $query = $this->_getQuery();
        $query = $this->_setWhere($query, $where);
        $query->orderBy("surname ASC, firstname ASC, middlename ASC, id ASC");
        if ($offset !== NULL) {
            $query->offset($offset);
        }
        if ($limit !== NULL) {
            $query->limit($limit);
        }

        $ids = $query->fetchPairs("id", "id");
        return $this->loadArray($ids);
    }

    public function getCandidatesCount(array $where = array())
    {
        $query = $this->_getQuery();
        $query = $this->_setWhere($query, $where);

        return $query->count();
    }

    private function _getQuery()
    {
        $query = $this->conn->select(array("user_id" => "id"))
                ->from($this->cvMain)
                ->leftJoin($this->cvSkills)->on("{$this->cvMain}.id = {$this->cvSkills}.cv_id")
                ->join('users')->on("users.id = {$this->cvMain}.user_id")
                ->groupBy("{$this->cvMain}.user_id")
                ->where(array(
                    "is_default%b" => TRUE,
                    "is_completed = 1",
                ));

        return $query;
    }

    private function _setWhere($query, array $where = array())
    {
        $filterSkills = FALSE;
        foreach ($where as $by => $condition) {
            switch ($by) {
                case "skills":
                    if (!is_array($condition) || !count($condition)) {
                        break;
                    }

                    $skillScale = array(
                        "Basic" => "'Basic'",
                        "Intermediate" => "'Intermediate'",
                        "Advanced" => "'Advanced'",
                        "Expert" => "'Expert'",
                    );

                    $whereString = NULL;
                    foreach ($condition as $skillId => $skillParams) {
                        if ($whereString === NULL) {
                            $whereString = "(";
                        } else {
                            $whereString .= " OR ";
                        }
                        $whereString .= "(";
                        $whereString .= "{$this->cvSkills}.skill_id = '{$skillId}'";

                        $skillScaleIn = array();
                        $add = FALSE;
                        foreach ($skillScale as $skillScaleId => $skillScaleName) {
                            if ($skillScaleId === $skillParams->scale) {
                                $add = TRUE;
                            }
                            if ($add) {
                                $skillScaleIn[] = $skillScaleName;
                            }
                        }
                        if (count($skillScaleIn)) {
                            $in = "(" . \CommonHelpers::concatStrings(",", $skillScaleIn) . ")";
                            $whereString .= " AND {$this->cvSkills}.scale IN {$in}";
                        }
                        if ($skillParams->number > 0) {
                            $whereString .= " AND {$this->cvSkills}.years >= '{$skillParams->number}'";
                        }
                        $whereString .= ")";
                    }
                    if ($whereString !== NULL) {
                        $whereString .= ")";
                        $query->where($whereString);
                    }
                    $filterSkills = TRUE;
                    break;
                case "text":
                    if (!empty($condition)) {
                        $query->where(array("fulltext%~like~" => $condition));
                    }
                    break;
                case "photo":
                    if ($condition == "NOT NULL") {
                        $query->where('photo IS NOT NULL');
                    }
                    break;
                case "id":
                    $query->where('user_id = %i', $condition);
                    break;
                case "registered_until":
                    $query->where('created < %t', $condition);
                    break;
            }
        }

        if (!$filterSkills) {
            $query->having("count(user_id) >= %i", 1);
        }
        return $query;
    }

    public function getFavorites($userId, $offset = NULL, $limit = NULL)
    {
        $query = $this->conn->select("id, users_id")
                ->from($this->favorites)
                ->where(array(
                    "candidate_users_id%i" => $userId,
                ))
                ->orderBy("id ASC");
        if ($offset !== NULL) {
            $query->offset($offset);
        }
        if ($limit !== NULL) {
            $query->limit($limit);
        }

        $ids = $query->fetchPairs("id", "users_id");
        return $this->loadArray($ids);
    }

    public function getFavoritesCount($userId)
    {
        $query = $this->conn->select("id")
                ->from($this->favorites)
                ->where(array(
            "candidate_users_id%i" => $userId,
        ));

        return $query->count();
    }

    public function loadArray($ids)
    {
        $array = array();
        if ($ids !== array()) {
            foreach ($ids as $id => $candidateId) {
                $candidate = $this->find($candidateId);
                if ($candidate->id !== NULL) {
                    $array[$id] = $candidate;
                }
            }
        }
        return $array;
    }

    public function load($row)
    {
        $candidate = new CandidateEntity;
        if ($row) {
            foreach ($row as $key => $value) {
                switch ($key) {
                    case "id":
                        $candidate->id = $value;
                        break;
                    case "cv_id":
                        $candidate->cvId = $value;
                        break;
                    case "launchpad_video_url":
                        $candidate->launchpadVideoUrl = $value;
                        break;
                    default:
                        $candidate->$key = $value;
                        break;
                }
            }
            $candidate->userDocs = $this->userDocMapper->findByUser($row->id, NULL, TRUE);
        }
        return $candidate;
    }

    public function find($id)
    {
        $select = array(
            "users.id" => "id",
            "{$this->cvMain}.id" => "cv_id",
            "launchpad_video_url" => "launchpad_video_url",
            "users.url_github" => "url_github",
            "users.url_stackoverflow" => "url_stackoverflow",
            "users.url_linkedin" => "url_linkedin",
            "users.url_facebook" => "url_facebook",
            "users.url_twitter" => "url_twitter",
        );
        $row = $this->conn->select($select)
                        ->from($this->cvMain)
                        ->join('users')->on("users.id = {$this->cvMain}.user_id")
                        ->where(array(
                            "user_id%i" => $id,
                            "is_default%b" => TRUE,
                        ))->fetch();

        return $this->load($row);
    }

    public function setFavorite($userId, $candidateId)
    {
        $data = array(
            "candidate_users_id%i" => $userId,
            "users_id%i" => $candidateId,
        );
        $exists = $this->conn->select("id")->from($this->favorites)
                ->where($data)
                ->count();

        if (!$exists) {
            return (bool) $this->conn->insert($this->favorites, $data)->execute(\dibi::IDENTIFIER);
        }
        return TRUE;
    }

    public function unsetFavorite($userId, $candidateId)
    {
        $data = array(
            "candidate_users_id%i" => $userId,
            "users_id%i" => $candidateId,
        );
        return (bool) $this->conn->delete($this->favorites)->where($data)->execute();
    }

    public function getMatched($jobId, $onlyApplyed = FALSE, $category = NULL, $order = NULL)
    {
        $query = $this->conn->select("job_user.id, job_user.user_id, job_id")
                ->from('job_user')
                ->join('cv_main')->on('cv_main.user_id = job_user.user_id AND is_default = 1')
                ->where(array(
                    "job_id%i" => $jobId,
                    "is_completed" => 1,
                ));
        if ($order) {
            $query->orderBy($order);
        } else {
            $query->orderBy("id ASC");
        }
        if ($onlyApplyed) {
            $query->where('applyed = 1');
        }
        if (!is_null($category)) {
            $query->where('category = %i', (int) $category);
        }

        $ids = $query->fetchPairs("id", "user_id");
        return $this->loadArray($ids);
    }

}
