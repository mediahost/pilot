<?php

namespace AppForms;

use Model\Entity\UserAircraft;
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
		$pilotExperiences = [];
		/** @var UserAircraft $item */
		foreach ($this->userEntity->pilotExperiences as $item) {
			$pilotExperiences[] = [
				'type' => $item->aircraftType,
				'manufacturer' => $item->manufacturerId,
				'model' => $item->aircraftId,
				'hours' => $item->hours,
				'pic' => $item->pic,
				'current' => $item->current,
			];
		}
		$copilotExperiences = [];
		/** @var UserAircraft $item */
		foreach ($this->userEntity->copilotExperiences as $item) {
			$copilotExperiences[] = [
				'type' => $item->aircraftType,
				'manufacturer' => $item->manufacturerId,
				'model' => $item->aircraftId,
				'hours' => $item->hours,
				'current' => $item->current,
			];
		}

		$this->defaults = array(
			'skills' => $this->userEntity->skills,
			'countries' => $this->userEntity->work_countries,
			'freelancer' => $this->userEntity->freelancer,
			'medical' => $this->userEntity->medical,
			'medical_text' => $this->userEntity->medicalText,
			'english_level' => $this->userEntity->englishLevel,
			'pilotExperiences' => $pilotExperiences,
			'copilotExperiences' => $copilotExperiences,
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

		$this->form->addRadioList('english_level', 'English level', UserEntity::$englishLevelOptions)
			->setDefaultValue($this->defaults['english_level']);

		$this->form->addRadioList('medical', 'Medical', [1 => 'Yes', 0 => 'No'])
			->setDefaultValue($this->defaults['medical'])
			->addCondition(Form::EQUAL, 0)
			->toggle('frmpreferencesForm-medical_text');

		$this->form->addTextArea('medical_text')
			->setDefaultValue($this->defaults['medical_text']);

		$experiences = $this->form->addDynamic('experiences', [$this, 'experienceContainerFactory'], count($this->defaults['pilotExperiences'])?:1);
		$experiences->addSubmit('add', 'Add plane')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'addExperience'];

		$copilotExperiences = $this->form->addDynamic('copilot_experiences', [$this, 'experienceCopilotContainerFactory'], count($this->defaults['copilotExperiences'])?:1);
		$copilotExperiences->addSubmit('add', 'Add plane')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'addExperience'];

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
			->addAttributes(['style' => 'width: 80px']);
		$container->addSelect('manufacturer', NULL, $manufacturers)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 180px']);
		$container->addSelect('model', NULL, $models)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 210px']);
		$container->addSelect('hours', NULL, $this->getHoursItems())
			->getControlPrototype()
			->addAttributes(['style' => 'width: 82px']);
		$container->addSelect('pic', NULL, $this->getHoursItems())
			->getControlPrototype()
			->addAttributes(['style' => 'width: 82px']);
		$container->addCheckbox('current');

		$container->addSubmit('remove', '❌')
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
		} else {
			if (isset($this->defaults['pilotExperiences'][$container->name])) {
				$container->setDefaults($this->defaults['pilotExperiences'][$container->name]);
			}
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
			->addAttributes(['style' => 'width: 80px']);
		$container->addSelect('manufacturer', NULL, $manufacturers)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 180px']);
		$container->addSelect('model', NULL, $models)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 210px']);
		$container->addSelect('hours', NULL, $this->getHoursItems())
			->getControlPrototype()
			->addAttributes(['style' => 'width: 82px']);
		$container->addCheckbox('current');

		$container->addSubmit('remove', '❌')
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
		} else {
			if (isset($this->defaults['copilotExperiences'][$container->name])) {
				$container->setDefaults($this->defaults['copilotExperiences'][$container->name]);
			}
		}
	}

	public function getHoursItems()
	{
		$items = [];
		for ($i = 0; $i <= 50000; $i = $i+500) {
			$items[$i] = $i;
		}
		return $items;
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
		$form = $button->form;
		$values = $form->values;

		if ($values->english_level === NULL) {
			$form->addError('Enter your english level.');
		}
		if ($values->medical === NULL) {
			$form->addError('Enter medical.');
		}
		if ($values->medical === "0" && empty($values->medical_text)) {
		    $form->addError('Enter medical description');
		}
		$this->userEntity->englishLevel = $values->english_level;
		$this->userEntity->medical = $values->medical;
		$this->userEntity->medicalText = $values->medical_text;

		$this->userEntity->work_countries = array();
		foreach ($values->countries as $countryId => $is) {
			if ($is) {
				$this->userEntity->work_countries[] = $countryId;
			}
		}
		if (!count($this->userEntity->work_countries)) {
			$form->addError('Enter at least one country.');
		}
		$this->userEntity->pilotExperiences = [];
		$currentSet = FALSE;
		foreach ($values->experiences as $key => $pilotExperience) {
			if ($pilotExperience->model) {
			    $userAircraft = new UserAircraft();
				$userAircraft->aircraftId = $pilotExperience->model;
				$userAircraft->hours = $pilotExperience->hours;
				$userAircraft->pic = $pilotExperience->pic;
				$userAircraft->current = $pilotExperience->current && !$currentSet;
				$this->userEntity->pilotExperiences[] = $userAircraft;
				if ($pilotExperience->current) {
				    $currentSet = TRUE;
				}
			}
		}
		$this->userEntity->copilotExperiences = [];
		$currentSet = FALSE;
		foreach ($values->copilot_experiences as $key => $pilotExperience) {
			if ($pilotExperience->model) {
			    $userAircraft = new UserAircraft();
				$userAircraft->aircraftId = $pilotExperience->model;
				$userAircraft->hours = $pilotExperience->hours;
				$userAircraft->pic = NULL;
				$userAircraft->current = $pilotExperience->current && !$currentSet;
				$this->userEntity->copilotExperiences[] = $userAircraft;
				if ($pilotExperience->current) {
					$currentSet = TRUE;
				}
			}
		}
		if (!(count($this->userEntity->pilotExperiences)+count($this->userEntity->copilotExperiences))) {
			$form->addError('Enter at least one flying experience.');
		}
		if ($form->hasErrors()) {
			$this->invalidateControl('errors');
			return;
		}

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
