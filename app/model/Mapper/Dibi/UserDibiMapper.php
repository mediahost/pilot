<?php

namespace Model\Mapper\Dibi;

use Model\Entity\UserAircraft;
use Model\Entity\UserEntity;
use Model\Service\AircraftService;

/**
 * User DibiMapper
 *
 * @author Petr Poupě
 */
class UserDibiMapper extends DibiMapper
{

    private $primary = "users";
    private $auth = "auth";
    private $cv = "cv_main";
	private $userAircraft = "user_aircraft";

    /**
     * Vrací celou tabulku
     * @return DibiFluent
     */
    public function allDataSource($by = array(), $limit = NULL, $offset = NULL)
    {
        $dataSource = $this->selectList();
        $dataSource = $this->joinItem($dataSource);
        $dataSource = $this->groupById($dataSource);
        if ($by !== array()) {
            $dataSource->where($this->_getWhere($by));
        }

        if ($limit) {
            $dataSource->limit($limit);
        }
        if ($offset) {
            $dataSource->offset($offset);
        }

        return $dataSource;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole
     * @param \Model\Entity\UserEntity $item
     * @return type
     */
    public function itemToData(UserEntity $item)
    {
        $data = array(
            'id' => $item->id,
            'role' => json_encode($item->role),
            'active' => $item->active,
            'created' => $item->created,
            'last_sign' => $item->lastSign,
            'mail' => $item->mail,
            'username' => $item->username,
            'first_name' => $item->firstName,
            'last_name' => $item->lastName,
            'gender' => $item->gender,
            'birthday' => $item->birthday,
            'lang' => $item->lang,
            'visit_guide' => $item->visitGuide,
            'smart_filter_settings' => json_encode($item->smartFilterSettings),
            'launchpad_video_url' => $item->launchpadVideoUrl,
            'chat_notifications' => $item->chat_notifications,
            'profile_token' => $item->profile_token,
            'is_profile_public' => $item->is_profile_public,
            'url_github' => $item->url_github,
            'url_stackoverflow' => $item->url_stackoverflow,
            'url_linkedin' => $item->url_linkedin,
            'url_facebook' => $item->url_facebook,
            'url_twitter' => $item->url_twitter,
            'freelancer' => (bool) $item->freelancer,
            'work_countries' => json_encode($item->work_countries),
			'english_level' => $item->englishLevel,
			'medical' => $item->medical,
			'medical_text' => $item->medicalText,
        );
        return $data;
    }

    public function load($data)
    {
        $item = new UserEntity;

        if ($data) {
            foreach ($data as $prop => $val) {
                $rename = array(
                    'first_name' => 'firstName',
                    'last_name' => 'lastName',
                    'last_sign' => 'lastSign',
                    'visit_guide' => 'visitGuide',
                    'cv_main_last_opened' => 'lastCvOpened',
                    'launchpad_video_url' => 'launchpadVideoUrl',
                    'english_level' => 'englishLevel',
                    'medical_text' => 'medicalText',
                );
                if (array_key_exists($prop, $rename)) {
					$prop = $rename[$prop];
				}
				switch ($prop) {
                    case "role":
                        $item->$prop = json_decode($val);
                        break;
                    case "smart_filter_settings":
                        $item->smartFilterSettings = json_decode($val);
                        break;
                    case "work_countries":
                        $item->work_countries = $val ? json_decode($val) : array();
                        break;
                    default:
                        $item->$prop = $val;
                        break;
                }
            }
        }
		$item->skills = $this->loadSkills($item->id);
		$this->loadAircrafts($item);
        return $item;
    }

	private function loadSkills($userId)
	{
		$skills = $this->conn->select('skill_id')
				->from('user_skill')
				->where('users_id = %i', $userId)
				->fetchPairs('skill_id', 'skill_id');
		return $skills ? $skills : array();
	}

    public function save(UserEntity $item)
    {
        $data = $this->itemToData($item);
        if ($item->id === NULL) { // insert
            $item->id = $this->conn->insert($this->primary, $data)
                    ->execute(\dibi::IDENTIFIER);
        } else { // update
            $finded = $this->find($item->id);
            if ($finded->id === NULL) {
                $this->conn->insert($this->primary, $data)
                        ->execute();
            } else {
                $this->conn->update($this->primary, $data)
                        ->where('id = %i', $item->id)
                        ->execute();
            }
        }
		$this->saveAircrafts($item);
        return $item;
    }

	public function saveAircrafts(UserEntity $user)
	{
		$this->conn->delete('user_aircraft')
			->where('user_id = %i', $user->id)
			->execute();
		/** @var UserAircraft $pilotExperience */
		foreach ($user->pilotExperiences as $pilotExperience) {
			$this->conn->insert('user_aircraft', [
				'user_id' => $user->id,
				'aircraft_id' => $pilotExperience->aircraftId,
				'hours' => $pilotExperience->hours,
				'pic' => $pilotExperience->pic,
			])->execute();
		}
		/** @var UserAircraft $copilotExperience */
		foreach ($user->copilotExperiences as $copilotExperience) {
			$this->conn->insert('user_aircraft', [
				'user_id' => $user->id,
				'aircraft_id' => $copilotExperience->aircraftId,
				'hours' => $copilotExperience->hours,
				'pic' => NULL,
			])->execute();
		}
		$this->loadAircrafts($user);
	}

	private function loadAircrafts(UserEntity $user)
	{
		$aircrafts = $this->conn->select('user_aircraft.*, aircraft.name AS aname, aircraft_manufacturer.name AS mname, aircraft.aircraft_manufacturer_id AS manufacturerid, aircraft.type')
			->from('user_aircraft')
			->join('aircraft')->on('aircraft.id = user_aircraft.aircraft_id')
			->join('aircraft_manufacturer')->on('aircraft_manufacturer.id = aircraft.aircraft_manufacturer_id')
			->where('user_id = %i', $user->id)
			->fetchAll();
		$user->pilotExperiences = [];
		$user->copilotExperiences = [];
		foreach ($aircrafts as $aircraft) {
			$userAircraft = new UserAircraft();
			$userAircraft->aircraftId = $aircraft->aircraft_id;
			$userAircraft->aircraftName = $aircraft->aname;
			$userAircraft->aircraftTypeName = AircraftService::getTypeName($aircraft->type);
			$userAircraft->aircraftType = $aircraft->type;
			$userAircraft->manufacturerId = $aircraft->manufacturerid;
			$userAircraft->manufacturerName = $aircraft->mname;
			$userAircraft->hours = $aircraft->hours;
			$userAircraft->pic = $aircraft->pic;

			if ($aircraft->pic === NULL) {
				$user->copilotExperiences[] = $userAircraft;
			} else {
				$user->pilotExperiences[] = $userAircraft;
			}
		}
	}

	private function saveSkills($userId, $skills)
	{
		$this->conn->delete('user_skill')
				->where('users_id = %i', $userId)
				->execute();
		foreach ($skills as $skillId) {
			$this->conn->insert('user_skill', [
				'users_id' => $userId,
				'skill_id' => $skillId,
			])->execute();
		}
	}

    public function find($id)
    {
        return $this->findBy(array(
                    "id" => $id,
        ));
    }

    public function findBy(array $values)
    {
        $data = $this->conn->select('*')->from($this->primary)->where($values)->fetch();
        return $this->load($data);
    }

    public function findOneBy(array $values)
    {
        return $this->findBy($values);
    }

    public function findAll()
    {
        return $this->allDataSource();
    }

    private function selectList()
    {
        return $this->conn->select(array(
                            "{$this->primary}.id" => "id",
                            "{$this->primary}.role" => "role",
                            "{$this->primary}.active" => "active",
                            "{$this->primary}.created" => "created",
                            "{$this->primary}.last_sign" => "last_sign",
                            "{$this->primary}.mail" => "mail",
                            "{$this->primary}.username" => "username",
//                            "{$this->primary}.first_name" => "first_name",
//                            "{$this->primary}.last_name" => "last_name",
                            "{$this->primary}.gender" => "gender",
                            "{$this->primary}.birthday" => "birthday",
                            "{$this->primary}.lang" => "lang",
                            "{$this->primary}.visit_guide" => "visitGuide",
                            "{$this->primary}.smart_filter_settings" => "smart_filter_settings",
                        ))
                        ->select(array("{$this->primary}.id" => "tag_id"))
                        ->from($this->primary);
    }

    private function joinItem(&$select)
    {
		return $select->select(array(
							"{$this->auth}.source" => "source",
							"{$this->auth}.key" => "key",
						))->leftJoin($this->auth)
						->on("{$this->primary}.id = {$this->auth}.users_id")
						->select(array(
							"{$this->cv}.is_completed" => 'is_completed',
							"{$this->cv}.country" => 'country',
                            "{$this->cv}.firstname" => 'first_name',
                            "{$this->cv}.surname" => 'last_name',
						))->leftJoin($this->cv)
						->on("{$this->primary}.id = {$this->cv}.user_id AND {$this->cv}.is_default = %b", TRUE)
                        ->leftJoin("user_tag")->on("user_tag.user_id = {$this->primary}.id")
                        ->leftJoin("tag")->on("tag.id = user_tag.tag_id");
//                        ->select("GROUP_CONCAT([tag.name] SEPARATOR ', ') AS tag_id");
	}

    private function groupById(&$select)
    {
        return $select->select("GROUP_CONCAT({$this->auth}.source) AS source_arr")
                        ->select("GROUP_CONCAT(DISTINCT {$this->auth}.key) AS key_arr")
                        ->groupBy("{$this->primary}.id");
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
                    $where["{$this->primary}.id%i"] = $cond;
                    break;
            }
        }
        return $where;
    }

    public function isUniqueMail($mail, $userId = NULL)
    {
        $query = $this->conn->select("id")->from($this->primary)->where("mail = %s", $mail);
        if ($userId !== NULL) {
            $query->where("id != %i", $userId);
        }
        return !((bool) $query->count());
    }

    public function isUniqeProfileToken($token, $userId = NULL)
    {
        $query = $this->conn->select("id")->from($this->primary)->where("profile_token = %s", $token);
        if ($userId !== NULL) {
            $query->where("id != %i", $userId);
        }
        return !((bool) $query->count());
    }

    public function delete(UserEntity $item)
    {
        $this->conn->delete($this->auth)
				->where("[users_id] = %i", $item->id)
				->where("[key] != %s", 'fake@account.test')
				->execute();
		$otherUserId = $this->conn->select("users_id")
				->from($this->auth)
				->where("[users_id] != %i", $item->id)
				->where("[key] != %s", 'fake@account.test')
				->fetchSingle();
		$this->conn->update($this->auth, array('users_id' => $otherUserId))
				->where("[users_id] = %i", $item->id)
				->where("[key] = %s", 'fake@account.test')
				->execute();
        return $this->conn->delete($this->primary)
				->where("[id] = %i", $item->id)
				->execute();
    }

}
