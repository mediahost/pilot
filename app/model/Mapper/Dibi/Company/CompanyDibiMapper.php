<?php

namespace Model\Mapper\Dibi;

use Model\Entity\Company\UserEntity;
use Model\Service\CompanyService;

/**
 * Class CompanyDibiMapper
 * @package Model\Mapper\Dibi
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class CompanyDibiMapper extends DibiMapper
{
    
    const VIEW_ALL = 1;
    const VIEW_7_DAYS = 2;
    const VIEW_14_DAYS = 3;
    const VIEW_30_DAYS = 4;
    
    public static function getViewOptions()
    {
        return array(
            self::VIEW_ALL => 'all',
            self::VIEW_7_DAYS => '7 days in system',
            self::VIEW_14_DAYS => '14 days in system',
            self::VIEW_30_DAYS => '30 days in system',
        );
    }
    
    public static function getRegistrationDate($view)
    {
        $date = new \Nette\DateTime;
        switch ($view) {
            case self::VIEW_ALL: break;
            case self::VIEW_7_DAYS: $date->modify('-7 days'); break;
            case self::VIEW_14_DAYS: $date->modify('-14 days'); break;
            case self::VIEW_30_DAYS: $date->modify('-30 days'); break;
            default:
                throw new \InvalidArgumentException;
        }
        return $date;
    }

    /**
     * @param $data
     *
     * @return UserEntity
     */
    public function load($data)
    {
        $item = new UserEntity();
        if ($data) {
            foreach ($data as $prop => $val) {
                switch ($prop) {
                    default:
                        if (property_exists($item, $prop)) {
                            $item->$prop = $val;
                        }
                        break;
                }
            }
        }
        
        $pictures = $this->conn
            ->select('id')
            ->from('company_picture')
            ->where('company_id = %i', $item->id)
            ->fetchPairs('id', 'id');
        $item->setPictures($pictures);
        
        return $item;
    }

    /**
     * @param UserEntity $entity
     *
     * @return \DibiResult|int
     */
    public function save(UserEntity $entity, $except = array())
    {
        if ($entity->id) {
            $entity->slug = $this->generateSlug($entity->company_name, $entity->id);
        } else {
            $entity->slug = $this->generateSlug($entity->company_name);
        }
        
        $data = $this->toArray($entity);

        foreach ($except as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }
        if ($entity->id) {
            return $this->conn->update('candidate_users', $data)->where('id = %i', $entity->id)->execute();
        } else {
            return $this->conn->query('INSERT INTO [candidate_users]', $data, ' ON DUPLICATE KEY UPDATE %a', $data);
        }
    }
    
    private function generateSlug($name, $except = NULL)
    {
        $tempSlug = $slug = \Nette\Utils\Strings::webalize($name);
        $counter = 1;
        while($this->slugExist($tempSlug, $except)) {
            $tempSlug = $slug . '-' . $counter;
            $counter++;
        }
        return $tempSlug;
    }
    
    public function findUserBySlug($slug)
    {
        $user = $this->conn->select('*')
                ->from('[candidate_users]')
                ->where('[slug] =%s', $slug)
                ->fetch();

        return $user ? $this->load($user) : FALSE;
    }
    
    public function slugExist($slug, $except = NULL)
    {
        $selection = $this->conn->select('*')
                ->from('[candidate_users]');
        if ($except) {
            $selection->where('[slug] = %s AND [id] != %i', $slug, $except);
        } else {
            $selection->where('[slug] =%s', $slug);
        }
        return $selection->fetch();
    }

    /**
     * @param $id
     *
     * @return bool|UserEntity
     */
    public function findUser($id)
    {
        $user = $this->conn->select('*')
                ->from('[candidate_users]')
                ->where('[id] =%i', $id)
                ->fetch();

        return $user ? $this->load($user) : FALSE;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function toArray($entity)
    {
        $data = get_object_vars($entity);
        return $data;
    }

    /**
     * @param $username
     *
     * @return bool|UserEntity
     */
    public function findByUsername($username)
    {
        $user = $this->conn->select('*')
                ->from(['candidate_users'])
                ->where('[username] =%s', $username)
                ->fetch();

        return $user ? $this->load($user) : FALSE;
    }

    /**
     * @return \DibiFluent
     */
    public function getDataGrid()
    {
        $fluent = $this->conn->select('id, email, username, company_name')
                ->from(['candidate_users']);

        return $fluent;
    }
    
    public function getPairs()
    {
        return $this->conn
            ->select('id, username')
            ->from('candidate_users')
            ->fetchPairs('id', 'username');
    }
    
    public function createCompanyPicture($companyId)
    {
        $this->conn->query('INSERT INTO [company_picture]', array('company_id' => $companyId));
        return $this->conn->insertId;
    }
    
    public function updatePassword($companyId, $hash, $salt)
    {
        return $this->conn->query('UPDATE [candidate_users] SET', array('password' => $hash, 'salt' => $salt), 'WHERE id = %i', $companyId);
    }
    
    public function getUserRow($username)
    {
        $user = $this->conn->select('*')
                ->from(['candidate_users'])
                ->where('[username] =%s', $username)
                ->fetch();

        return $user ? $user : FALSE;
    }
    
    public function removeCompanyPicture($id)
    {
        $this->conn
            ->delete('company_picture')
            ->where('id = %i', $id)
            ->execute();
    }

}
