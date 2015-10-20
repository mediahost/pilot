<?php

namespace Model\Service;

use Model\Mapper\Dibi\JobCategoryDibiMapper,
	Model\Entity\JobCategoryEntity,
	Model\Mapper\Dibi\JobsDibiMapper;

/**
 * Description of JobCategoryService
 *
 * @author Radim Křek
 */

class JobCategoryService
{
	/** @var JobCategoryDibiMapper */
	private $mapper;
	
	/** @var JobsDibiMapper */
	private $jobsMapper;
	
	public function __construct(JobCategoryDibiMapper $category, JobsDibiMapper $jobs)
	{
		$this->mapper = $category;
		$this->jobsMapper = $jobs;
	}
	
	public function save($data)
	{
		return $this->mapper->save($data);
	}
	
	public function find($id, $lang = NULL)
	{
		return $this->mapper->find($id, $lang);
	}
	
	public function findBy($by, $lang = NULL)
	{
		return $this->mapper->findBy($lang, $by);
	}
	
	public function findAll($lang = NULL, $by = NULL,  $limit = NULL, $offset = NULL)
	{
		return $this->mapper->findAll($by, $lang, $limit, $offset);
	}
	
	public function delete($data)
	{
		if ($data instanceof JobCategoryEntity)
		{
			$id = $data->id;
		}
		else
		{
			$id = $data;
		}
		if ($this->jobsMapper->findBy('category', $id))
		{
			return FALSE;
		}
		else
		{
			return $this->mapper->delete($data);
		}		
	}
	
	public function getDataGrid($lang)
	{
		return $this->mapper->getDataGrid($lang);
	}
}
