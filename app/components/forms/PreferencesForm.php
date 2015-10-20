<?php

namespace AppForms;

use Model\Entity\UserEntity;
use Model\Service\SkillService;
use Model\Service\UserService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

/**
 * Required step 2
 */
class PreferencesForm extends AppForms
{

	/** @var array */
	public $onSuccess = array();

	/** @var array */
	protected $skills = array();

	/** @var array */
	protected $flatSkills = array();

	/** @var array */
	protected $jsonSkills = array();

	/** @var array */
	protected $flatCountries = array();

	/** @var array */
	protected $jsonCountries = array();

	/** @var array */
	protected $defaults = array();

	/** @var UserService */
	protected $userService;

	/** @var SkillService */
	protected $skillService;

	/** @var UserEntity */
	protected $userEntity;

	public function __construct(Presenter $presenter, UserService $userService, SkillService $skillService)
	{
		parent::__construct(get_class($this), $presenter, FALSE);

		$this->userService = $userService;
		$this->skillService = $skillService;
		$this->userEntity = $userService->find($this->user->id);
		$this->setDefaults();
		$this->setSkills();
	}

	private function setDefaults()
	{
		$this->defaults = array(
			'skills' => $this->userEntity->skills,
			'countries' => $this->userEntity->work_countries,
			'freelancer' => $this->userEntity->freelancer
		);
	}

	private function setSkills()
	{
		$this->skills = $this->skillService->getSkills();
		$this->flatSkills = $this->skillService->getFlatSkills();
		$this->jsonSkills = array();
		foreach ($this->skills as $skillId => $skill) {
			$skillChildren = array();

			foreach ($skill['children'] as $skillChildrenID => $skillChildrenItem) {
				$skillChildrenItemChildren = array();

				foreach ($skillChildrenItem['children'] as $skillChildrenItemId => $skillChildrenItemItem) {
					$skillChildrenItemChildren[] = array(
						'id' => $skillChildrenItemId,
						'text' => $skillChildrenItemItem['name'],
						'state' => array(
							'selected' => array_search($skillChildrenItemId, $this->defaults['skills']) !== FALSE,
						),
						'children' => array(),
					);
				}

				$skillChildren[] = array(
					'id' => $skillChildrenID,
					'text' => $skillChildrenItem['name'],
					'state' => array(
						'selected' => array_search($skillChildrenID, $this->defaults['skills']) !== FALSE,
					),
					'children' => $skillChildrenItemChildren,
				);
			}

			$this->jsonSkills[] = array(
				'id' => $skillId,
				'text' => $skill['name'],
				'state' => array(
					'selected' => array_search($skillId, $this->defaults['skills']) !== FALSE,
				),
				'children' => $skillChildren,
			);
		}

		$this->flatCountries = UserEntity::helperGetLocalities(TRUE);
		$this->jsonCountries = array();
		foreach (UserEntity::helperGetLocalities() as $countryId => $country) {
			if (is_array($country)) {
				$countryArray = array();
				foreach ($country as $countryItemId => $countryItem) {
					$countryArray[] = array(
						'id' => $countryItemId,
						'text' => $countryItem,
						'state' => array(
							'selected' => array_search($countryItemId, $this->defaults['countries']) !== FALSE,
						),
					);
				}
				$this->jsonCountries[] = array(
					'text' => $countryId,
					'children' => $countryArray,
				);
			} else {
				$this->jsonCountries[] = array(
					'id' => $countryId,
					'text' => $country,
					'state' => array(
						'selected' => array_search($countryId, $this->defaults['countries']) !== FALSE,
					),
				);
			}
		}
	}

	public function createComponent($name)
	{
		$this->form->getElementPrototype()->class = "styled";

		$skillsContainer = $this->form->addContainer('skills');
		foreach ($this->flatSkills as $skillId => $skillName) {
			$skillsContainer->addCheckbox($skillId, $skillName)
							->getControlPrototype()->class = 'inSkillTree';
		}

		$countryContainer = $this->form->addContainer('countries');
		foreach ($this->flatCountries as $countryId => $countryName) {
			$countryContainer->addCheckbox($countryId, $countryName)
							->getControlPrototype()->class = 'inCountryTree';
		}

		$this->form->addCheckbox('freelancer', "I am also interested in freelance or remote work");

		$this->form->addSubmit('send', 'Save')
						->getControlPrototype()->class = "button";

		$this->form->onSuccess[] = $this->processForm;

		return $this->form;
	}

	public function processForm(Form $form)
	{
		$this->userEntity->skills = array();
		foreach ($form->values->skills as $skillId => $is) {
			if ($is) {
				$this->userEntity->skills[] = $skillId;
			}
		}
		if (!count($this->userEntity->skills)) {
			$form->addError('Enter at least one skill.');
		}
		
		$this->userEntity->work_countries = array();
		foreach ($form->values->countries as $countryId => $is) {
			if ($is) {
				$this->userEntity->work_countries[] = $countryId;
			}
		}
		if (!count($this->userEntity->work_countries)) {
			$form->addError('Enter at least one country.');
		}
		if ($form->hasErrors()) {
			return;
		}
		
		$this->userEntity->freelancer = $form->values->freelancer;
		$this->userService->save($this->userEntity);
		
		$this->onSuccess($this->userEntity);
	}

	public function render()
	{
		$this->template->skills = $this->flatSkills;
		$this->template->countries = $this->flatCountries;
		$this->template->jsonSkills = $this->jsonSkills;
		$this->template->jsonCountries = $this->jsonCountries;
		$this->template->jsonFreelancer = array(
			'id' => 'for-frmpreferencesForm-freelancer',
			'text' => 'Yes',
			'state' => array('selected' => $this->defaults['freelancer']),
		);
		parent::render();
	}

}
