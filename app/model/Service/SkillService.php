<?php

namespace Model\Service;

/**
 * Skill Service
 *
 * @author Petr Poupě
 */
class SkillService
{

	/** @var \DibiConnection */
	protected $conn;

	public function __construct(\DibiConnection $conn)
	{
		$this->conn = $conn;
	}

	public function getSkills()
	{
		$skills = array();

		// first level
		$parents = $this->conn->select('*')
				->from('skill')
				->where('parent_id IS NULL')
				->orderBy('priority ASC, id ASC')
				->fetchPairs('id', 'name');
		foreach ($parents as $parentId => $parentName) {
			$skills[$parentId] = array(
				'name' => $parentName,
				'children' => array(),
			);
		}

		// second level
		$children = $this->conn->select('*')
				->from('skill')
				->where('parent_id IS NOT NULL')
				->orderBy('priority ASC, id ASC')
				->fetchAll();
		foreach ($children as $child) {
			if (array_key_exists($child->parent_id, $skills)) { // second level
				$skills[$child->parent_id]['children'][$child->id] = array(
					'name' => $child->name,
					'children' => array(),
				);
			} else { // third level
				foreach ($skills as $skillId => $skill) {
					if (array_key_exists($child->parent_id, $skill['children'])) {
						$skills[$skillId]['children'][$child->parent_id]['children'][$child->id] = array(
							'name' => $child->name,
							'children' => array(),
						);
					}
				}
			}
		}
		return $skills;
	}

	public function getFlatSkills()
	{
		return $this->conn->select('*')
						->from('skill')
						->orderBy('priority ASC, id ASC')
						->fetchPairs('id', 'name');
	}

}
