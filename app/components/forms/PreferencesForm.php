<?php

namespace AppForms;

use Model\Entity\UserEntity;
use Model\Service\AircraftService;
use Model\Service\SkillService;
use Model\Service\UserService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

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

	/** @var AircraftService */
	private $aircraftService;

	public function __construct(Presenter $presenter, UserService $userService, SkillService $skillService, AircraftService $aircraftService)
	{
		parent::__construct(get_class($this), $presenter, FALSE);

		$this->userService = $userService;
		$this->skillService = $skillService;
		$this->userEntity = $userService->find($this->user->id);
		$this->setDefaults();
//		$this->setSkills();
		$this->setCountries();
		$this->aircraftService = $aircraftService;
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
	}

	private function setCountries()
	{
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
		$this->form->getElementPrototype()->class = "styled ajax";

		$this->form->addRadioList('english_level', 'English level', [
			'native',
			'L6',
			'L5',
			'L4',
		])
			->setDefaultValue(0)
			->setRequired();

		$this->form->addRadioList('medical', 'Medical', [1 => 'Yes', 0 => 'No'])
			->setDefaultValue(1)
			->addCondition(Form::EQUAL, 0)
			->toggle('frmpreferencesForm-medical_text');

		$this->form->addTextArea('medical_text');

		$experiences = $this->form->addDynamic('experiences', [$this, 'experienceContainerFactory'], 1);
		$experiences->addSubmit('add', 'Add plane')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'addExperience'];

		$copilotExperiences = $this->form->addDynamic('copilot_experiences', [$this, 'experienceCopilotContainerFactory'], 1);
		$copilotExperiences->addSubmit('add', 'Add plane')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'addExperience'];

//		$skillsContainer = $this->form->addContainer('skills');
//		foreach ($this->flatSkills as $skillId => $skillName) {
//			$skillsContainer->addCheckbox($skillId, $skillName)
//							->getControlPrototype()->class = 'inSkillTree';
//		}

		$countryContainer = $this->form->addContainer('countries');
		foreach ($this->flatCountries as $countryId => $countryName) {
			$countryContainer->addCheckbox($countryId, $countryName)
							->getControlPrototype()->class = 'inCountryTree';
		}

		$this->form->addCheckbox('freelancer', "I am also interested in freelance or remote work");

		$this->form->addSubmit('send', 'Save')
						->getControlPrototype()->class = "button";

		$this->form['send']->onClick[] = $this->processForm;
		$this->invalidateControl('form_content_captain');
		$this->invalidateControl('form_content_copilot');

		return $this->form;
	}

	public function experienceContainerFactory(Container $container)
	{
		$manufacturers = $this->aircraftService->getManufacturers();
		$models = $this->aircraftService->getModels();

		$container->addSelect('type', NULL, [
			AircraftService::TYPE_JET => 'Jet',
			AircraftService::TYPE_TURBO => 'Turbo',
		])
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 85px']);
		$container->addSelect('manufacturer', NULL, $manufacturers)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 200px']);
		$container->addSelect('model', NULL, $models)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 240px']);
		$container->addText('hours')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 50px']);
		$container->addText('pic')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 50px']);

		$container->addSubmit('remove', 'Remove')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'removeExperience'];

		if ($this->form->isSubmitted()) {
			$type = $container->values->type;
			$manufacturers = $this->aircraftService->getManufacturers($type);

			$manufacturer = $container->values->manufacturer;
			if (!isset($manufacturers[$manufacturer])) {
			    $manufacturer = NULL;
			}

			$models = $this->aircraftService->getModels($type, $manufacturer);

			$container['manufacturer']->setItems($manufacturers);
			$container['model']->setItems($models);
		}
	}

	public function experienceCopilotContainerFactory(Container $container)
	{
		$manufacturers = $this->aircraftService->getManufacturers();
		$models = $this->aircraftService->getModels();

		$container->addSelect('type', NULL, [
			AircraftService::TYPE_JET => 'Jet',
			AircraftService::TYPE_TURBO => 'Turbo',
		])
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 85px']);
		$container->addSelect('manufacturer', NULL, $manufacturers)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 200px']);
		$container->addSelect('model', NULL, $models)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 240px']);
		$container->addText('hours')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 50px']);

		$container->addSubmit('remove', 'Remove')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'removeExperience'];

		if ($this->form->isSubmitted()) {
			$type = $container->values->type;
			$manufacturers = $this->aircraftService->getManufacturers($type);

			$manufacturer = $container->values->manufacturer;
			if (!isset($manufacturers[$manufacturer])) {
			    $manufacturer = NULL;
			}

			$models = $this->aircraftService->getModels($type, $manufacturer);

			$container['manufacturer']->setItems($manufacturers);
			$container['model']->setItems($models);
		}
	}

	public function removeExperience(SubmitButton $submitButton)
	{
		$container = $submitButton->parent->parent;
		$container->remove($submitButton->parent, TRUE);
		$this->invalidateControl('form_content_captain');
		$this->invalidateControl('form_content_copilot');
	}

	public function addExperience(SubmitButton $submitButton)
	{
		$submitButton->parent->createOne();
		$this->invalidateControl('form_content_captain');
		$this->invalidateControl('form_content_copilot');
	}

	public function processForm(SubmitButton $button)
	{
		return;
		$form = $button->form;
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
