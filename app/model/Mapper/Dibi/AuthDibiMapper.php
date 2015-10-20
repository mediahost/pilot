<?php

namespace Model\Mapper\Dibi;

use Model\Entity\AuthEntity;

/**
 * User DibiMapper
 *
 * @author Petr Poupě
 */
class AuthDibiMapper extends DibiMapper
{

    const TYPE_PRIMARY = "primary";

    private $table = "auth";

    /**
     * Stará se o načtení dat do entity
     * @param type $data
     * @return \Model\Entity\AuthEntity
     */
    public function load($data)
    {
        $item = new AuthEntity;

        if ($data) {
            foreach ($data as $prop => $val) {
                switch ($prop) {
                    case "id":
                    case "source":
                    case "key":
                    case "salt":
                    case "verified":
                        $item->$prop = $val;
                        break;
                    case "pass":
                        $item->password = $val;
                        break;
                    case "verify_code":
                        $item->verifyCode = $val;
                        break;
                    case "users_id":
                        $item->userId = $val;
                        break;
                }
            }
        }

        return $item;
    }

    /**
     * Vytáhne data z entity a vrátí jako pole pro uložení
     * @param \Model\Entity\AuthEntity $entity
     * @return type
     */
    private function entityToData(AuthEntity $entity, $type = self::TYPE_PRIMARY)
    {
        $data = array();
        switch ($type) {
            case self::TYPE_PRIMARY:
                $data['users_id'] = $entity->userId;
                $data['key'] = $entity->key;
                $data['source'] = $entity->source;
                $data['pass'] = $entity->password;
                $data['salt'] = $entity->salt;
                $data['verified'] = $entity->verified;
                $data['verify_code'] = $entity->verifyCode;
                break;
        }
        return $data;
    }

    /**
     * Returns selector for select all items
     * @param type $lang
     * @return string
     */
    protected function _getSelectAll()
    {
        $select = array(
            "*",
//            "{$this->user}.id" => "id",
        );
        return $select;
    }

    /**
     * Returns WHERE array inserted by entity keys
     * @param type $by
     * @return type
     */
    protected function _getWhere($by)
    {
        $where = array();
        foreach ($by as $item => $cond) {
            switch ($item) {
                case "id":
                    $where["{$this->table}.id%i"] = $cond;
                    break;
                case "!id":
                    $where[] = array("{$this->table}.id != %i", $cond);
                    break;
                case "userId":
                case "usersId":
                    $where["{$this->table}.users_id%i"] = $cond;
                    break;
                case "key":
                    $where["{$this->table}.key%s"] = $cond;
                    break;
                case "source":
                    $where["{$this->table}.source%s"] = $cond;
                    break;
                case "verifyCode":
                    $where["{$this->table}.verify_code%s"] = $cond;
                    break;
            }
        }
        return $where;
    }

    /**
     * Returns base part of query
     * @param type $by
     * @return type
     */
    protected function _getQuery($by = array())
    {
        $query = $this->conn->select($this->_getSelectAll())->from($this->table);
        if ($by !== array()) {
            $query->where($this->_getWhere($by));
        }
        return $query;
    }

    /**
     * Save entity - decide if insert or update
     * @param AuthEntity $entity
     * @param type $what - can save only selected parts
     * @return AuthEntity
     */
    public function save($entity, $what = NULL)
    {
	if ($what === NULL) {
	    return $this->saveAll($entity);
	} else {
	    if (is_string($what)) {
		$what = preg_split("@\s*,\s*@", $what);
	    } else if (!is_array($what)) {
		$what = array($what);
	    }
	    return $this->saveOnly($entity, $what);
	}
    }

    /**
     * Save only selected columns
     * @param \Model\Entity\AuthEntity $entity
     * @param type $what
     * @return AuthEntity
     */
    protected function saveOnly(AuthEntity $entity, $what)
    {
        $prim = array();
        $data = array();
        foreach ($what as $whatItem) {
            switch ($whatItem) { // names from entity
                case "password":
                    $prim['pass'] = $entity->$whatItem;
                    break;
                case "salt":
                    $prim['salt'] = $entity->$whatItem;
                    break;
                case "verified":
                    $prim['verified'] = $entity->$whatItem;
                    break;
                case "verifyCode":
                    $prim['verify_code'] = $entity->$whatItem;
                    break;
            }
        }
        return $this->saveData($entity, $prim, $data);
    }

    /**
     * Save all columns
     * @param \Model\Entity\AuthEntity $entity
     * @return type
     * @throws \Exception|AuthEntity
     */
    protected function saveAll(AuthEntity $entity)
    {
        $prim = $this->entityToData($entity, self::TYPE_PRIMARY);

        return $this->saveData($entity, $prim, TRUE);
    }

    private function saveData(AuthEntity $entity, $primary = array(), $other = array())
    {
        if ($primary !== array()) {
            if ($entity->id === NULL) { // insert
                $entity->id = $this->conn->insert($this->table, $primary)
                        ->execute(\dibi::IDENTIFIER);
            } else { // update
                $this->conn->update($this->table, $primary)
                        ->where('id = %i', $entity->id)
                        ->execute();
            }
        }

        return $entity;
    }

    /**
     * Find one entity by ID
     * @param type $id
     * @return AuthEntity
     */
    public function find($key, $type)
    {
        return $this->findBy(array(
                    "key" => $key,
                    "source" => $type,
        ));
    }

    /**
     * Find one entity by conditions
     * @param type $by
     * @return AuthEntity
     */
    public function findBy($by = array())
    {
        $query = $this->_getQuery($by);

        return $this->load($query->fetch());
    }

    /**
     * Find one entity by app login
     * @param type $login
     * @return type
     */
    public function findByLogin($login, $source = AuthEntity::SOURCE_APP)
    {
        return $this->find($login, $source);
    }

    /**
     * Find one entity by user ID
     * @param type $login
     * @return type
     */
    public function findByUser($userId)
    {
        return $this->findBy(array(
                    "userId" => $userId,
                    "source" => AuthEntity::SOURCE_APP,
                    '!id' => 1,
        ));
    }

    /**
     * Find one entity by user ID
     * @param type $login
     * @return type
     */
    public function findByCode($userId, $code)
    {
        return $this->findBy(array(
                    "userId" => $userId,
                    "verifyCode" => $code,
        ));
    }

    public function delete(AuthEntity $item)
    {
        return $this->conn->delete($this->table)
                        ->where("id = %i", $item->id)
                        ->execute();
    }

    public function isUniqueKey($key, $userId = NULL, $source = AuthEntity::SOURCE_APP)
    {
        $query = $this->conn->select("id")->from($this->table)
                ->where(array(
            "key%s" => $key,
            "source%s" => $source
        ));
        if ($userId !== NULL) {
            $query->where("users_id != %i", $userId);
        }
        return !((bool) $query->count());
    }

    /**
     * Find one facebook entity by user ID
     * @param type $login
     * @return type
     */
    public function findByFacebookUser($userId)
    {
        return $this->findBy(array(
                    "userId" => $userId,
                    "source" => AuthEntity::SOURCE_FB,
        ));
    }

   /**
     * Find one google entity by user ID
     * @param type $login
     * @return type
     */
    public function findByGoogleUser($userId)
    {
        return $this->findBy(array(
                    "userId" => $userId,
                    "source" => AuthEntity::SOURCE_GOOGLE,
        ));
    }    
    
   /**
     * Find one twitter entity by user ID
     * @param type $login
     * @return type
     */
    public function findByTwitterUser($userId)
    {
        return $this->findBy(array(
                    "userId" => $userId,
                    "source" => AuthEntity::SOURCE_TWITTER,
        ));
    }    
}

?>
