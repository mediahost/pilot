<?php

namespace Model\Mapper\Dibi;

use Model\Entity\JobCategoryEntity;

/**
 * Description of JobCategoryDibiMapper
 *
 * @author Radim Křek
 */
class JobCategoryDibiMapper extends DibiMapper
{

	private $table = "job_category";

	public function findBy($lang = NULL, $by = array())
	{
		$data = $this->selectList($lang);
		if ($lang !== NULL)
		{
			$data = $this->where('lang=%s', $lang);
		}
		$data->where($this->_getWhere($by));

		return new JobCategoryEntity($data->fetch());
	}

	public function find($id, $lang = NULL)
	{
		$data = $this->conn->select('*')->from($this->table)->where('id=%i', $id);
		if ($lang !== NULL)
		{
			$data->where('lang=%s', $lang);
		}

		return new JobCategoryEntity($data->fetch());
	}

	public function findAll($by = array(), $lang=NULL, $limit = NULL, $offset = NULL)
	{
		$data = $this->conn->select('*')->from($this->table);

		if ($lang !== NULL)
		{
			$data->where('lang=%s', $lang);
		}
		if (count($by) > 0)
		{
			$data->where($this->_getWhere($by));
		}
		if ($limit !== NULL)
		{
			$data->limit($limit);
		}
		if ($offset !== NULL)
		{
			$data->offset($offset);
		}

		$items = array();
		foreach ($data->fetchAll() as $item)
		{
			$items[] = new JobCategoryEntity($item);
		}
		return $items;
	}

	public function delete($id)
	{
		return $this->conn->delete($this->table)
						->where('id = %i', $id)
						->execute();
	}
	
	public function save($data)
	{
		if (!intval($data->id))
		{
			//insert
			return $this->conn->insert($this->table, $data->to_array())->execute(\dibi::IDENTIFIER);
		}
		else
		{
			//update
			return $this->conn->update($this->table, $data->to_array())->where('id=%i', $data->id)->execute();
		}
	}

	private function _getWhere($by)
	{
		$where = array();
		foreach ($by as $item => $cond)
		{
			switch ($item)
			{
				case "id":
					$where["{$this->table}.id%i"] = $cond;
					break;
				case "lang":
					$where["{$this->table}.lang%s"] = $cond;
					break;
				case "name":
					$where["{$this->table}.name%s"] = $cond;
					break;
			}
		}
		return $where;
	}
	
	public function getDataGrid($lang)
    {
        return $this->conn->select('*')
                        ->from($this->table)
                        ->where('lang=%s', $lang);
    }

}
