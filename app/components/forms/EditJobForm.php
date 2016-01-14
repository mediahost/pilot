<?php

namespace AppForms;

use Model\Entity\JobEntity;
use Model\Entity\UserAircraft;
use Model\Service\AircraftService;
use Model\Service\CompanyService;
use Model\Service\JobCategoryService;
use Model\Service\JobService;
use Model\Service\LocationService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * Description of EditJobPositionForm
 *
 * @author Radim Křek
 */
class EditJobForm extends AppForms
{

    /** @var int */
    private $id;
    
    /** @var int */
    protected $companyId;

    /** @var JobService */
    private $service;

    /** @var LocationService */
    private $locationService;

    /** @var JobCategoryService */
    private $categoryService;
    
    /** @var CompanyService */
    protected $companyService;

    /** @var array */
    private $skills;

    /** @var DibiDateTime */
    private $datecreated;
    
    /** @var callback */
    protected $onSave;
    
    /** @var callback */
    protected $onSaveAndBack;

	/** @var AircraftService */
	private $aircraftService;

	/** @var array */
	protected $defaults = array();

    public function __construct(Presenter $presenter, JobService $service, LocationService $locationService, JobCategoryService $categoryService, CompanyService $companyService, AircraftService $aircraftService)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $service;
        $this->locationService = $locationService;
        $this->categoryService = $categoryService;
        $this->companyService = $companyService;
        $this->skills = $this->template->skills = $this->service->buildSkills();
		$this->aircraftService = $aircraftService;
    }
    
    public function setOnSaveCallback($onSave)
    {
        $this->onSave = $onSave;
    }

    public function setOnSaveAndBackCallback($onSaveAndBack)
    {
        $this->onSaveAndBack = $onSaveAndBack;
    }

    public function setId($id)
    {
        $this->id = $id;
		$job = $this->service->find($id);
		$job = $this->service->loadAircrafts($job);
		$this->setDefaults($job);
    }
    
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    public function setDefaults(JobEntity $entity)
    {
		$pilotExperiences = [];
		/** @var UserAircraft $item */
		foreach ($entity->pilotExperiences as $item) {
			$pilotExperiences[] = [
				'type' => $item->aircraftType,
				'manufacturer' => $item->manufacturerId,
				'model' => $item->aircraftId,
				'hours' => $item->hours,
				'pic' => $item->pic,
			];
		}
		$copilotExperiences = [];
		/** @var UserAircraft $item */
		foreach ($entity->copilotExperiences as $item) {
			$copilotExperiences[] = [
				'type' => $item->aircraftType,
				'manufacturer' => $item->manufacturerId,
				'model' => $item->aircraftId,
				'hours' => $item->hours,
			];
		}
		
//        $categories = $this->getCategories();
//        $entity->category = array_search($entity->category, $categories);
        $e = $entity->to_array();
        $this->datecreated = $e['datecreated'];
        unset($e['datecreated']);
        $skills = $this->service->loadSkills($e['id']);
        foreach ($skills as $skill) {
            $e[$skill->cv_skill_id] = array('scale' => $skill->scale, 'number' => $skill->years);
        }
		$this->defaults = array_merge((array) $e, array(
			'pilotExperiences' => $pilotExperiences,
			'copilotExperiences' => $copilotExperiences,
		));
    }

    public function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        $form = $this->form;
        $form->getElementPrototype()->class('form-horizontal ajax2');

        $jobTypes = array(
            1 => 'Full-Time',
            2 => 'Part-Time',
            3 => 'Contract',
        );
        $languages = array(
            'cs' => 'CZ',
            'en' => 'EN',
            'sk' => 'SK',
        );

        $form->addHidden('id');
        $form->addText('ref_num', 'Job Ref. No.')
                ->setRequired("Number must be filled");
        $form->addText('name', 'Job Title')
                ->setRequired("Title must be filled");
        $form->addText('company', 'Company')
                ->setRequired("Company name must be filled");
        if (!$this->companyId) {
            $form->addSelect('company_id', 'Company user', $this->companyService->getPairs())
                    ->setRequired()
                    ->setAttribute("class", "select2me");
        }
        $form->addSelect('lang', 'Language', $languages)
                ->setDefaultValue($this->lang)
                ->setAttribute("class", "select2me");
        $form->addSelect('type', 'Type', $jobTypes)
                ->setRequired("Type must be selected")
                ->setAttribute("class", "select2me");
		
        $form->addText('location_text', 'Location')
                ->setRequired("Location must be inserted");

        $form->addText('salary_from', "From");
        $form->addText('salary_to', "To");
        $form->addSelect('currency', "Currency", JobEntity::getCurrencies())
                ->setAttribute("class", "select2me");

        $form->addGroup('Job administrator', TRUE);
        $form->addText('ref', 'Recruiter Name')
                ->setRequired("Recruiter name name must be filled");
        $form->addText('ref_email', 'Recruiter Email')
                ->setEmptyValue("@")
                ->setRequired("Recruiter email must be filled")
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, 'Please use right format');
        $form->addText('ref_tel', 'Recruiter Phone')
                ->setRequired("Recruiter phone must be filled")
                ->setAttribute("class", "mask_phone");

        $form->addGroup("Summary");
        $form->addTextArea('summary', 'Vacancy Summary')
                ->setAttribute("class", "ckeditor");
        $form->addTextArea('description', 'Vacancy Detail')
                ->setAttribute("class", "ckeditor");
		
        $form->addTextArea('offers', 'Offers')
                ->setAttribute("class", "input_tags")
                ->setAttribute("data-default", $this->translator->translate("add a tag"));
        $form->addTextArea('requirments', 'Requirments')
                ->setAttribute("class", "input_tags")
                ->setAttribute("data-default", $this->translator->translate("add a tag"));
		
		$experiences = $form->addDynamic('experiences', [$this, 'experienceContainerFactory'], count($this->defaults['pilotExperiences'])?:1);
		$experiences->addSubmit('add', 'Add plane')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'addExperience'];

		$copilotExperiences = $form->addDynamic('copilot_experiences', [$this, 'experienceCopilotContainerFactory'], count($this->defaults['copilotExperiences'])?:1);
		$copilotExperiences->addSubmit('add', 'Add plane')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'addExperience'];


        $form->addGroup("IT Skills");
        $skills = $this->skills;
        $scale = array(
            NULL => "n/a",
            'Basic' => "Basic",
            'Intermediate' => "Intermediate",
            'Advanced' => "Advanced",
            'Expert' => "Expert",
        );
        foreach ($skills as $skillCategory => $skillGroups) {
            foreach ($skillGroups as $skillGroup) {
                foreach ($skillGroup as $skillId => $skillName) {
                    $container = $this->form->addContainer(intval($skillId));
                    $container->addSelect('scale', 'Scale', $scale)
                            ->setAttribute("class", "slider live");
                    $container->addText('number', "Years")
                            ->setAttribute("type", "number")
                            ->setAttribute("min", "0")
                            ->setAttribute("class", "number")
                            ->setDefaultValue(0);
                }
            }
        }

        $form->addGroup('Questions');
        $form->addText('question1', 'Question 1');
        $form->addText('question2', 'Question 2');
        $form->addText('question3', 'Question 3');
        $form->addText('question4', 'Question 4');
        $form->addText('question5', 'Question 5');

        $form->addGroup();
        $this->form->addSubmit('back', 'Save & Back');
        $this->form->addSubmit('send', 'Save');

		$form->setDefaults($this->defaults);
        $this->form->onSuccess[] = $this->onSuccess;
		$this->invalidateControl('form_content_captain');
		$this->invalidateControl('form_content_copilot');
		
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
		if ($form['experiences']['add']->submittedBy || $form['copilot_experiences']['add']->submittedBy) {
			$this->invalidateControl();
			return;
		}
        $data = $form->getValues();
        if ($this->companyId) {
            $data->company_id = $this->companyId;
        }
        $values = array('datecreated' => $this->datecreated);
        foreach ($data as $key => $value) {
            if (intval($key) == 0) { //informations
				switch ($key) { // skip
					case 'experiences':
					case 'copilot_experiences':
						break;
					default:
						$values[$key] = $value;
						break;
				}
            }
        }
        try {
            if (!$values['id']) {
                $values['id'] = $this->service->getMaxId() + 1;
                $values['datecreated'] = new \DateTime;
            }

            $entity = new JobEntity($values);
            $entity->lang = "en";
			
			$entity->pilotExperiences = array();
			foreach ($data['experiences'] as $key => $pilotExperience) {
				if ($pilotExperience->model) {
					$userAircraft = new \Model\Entity\JobAircraft();
					$userAircraft->aircraftId = $pilotExperience->model;
					$userAircraft->hours = $pilotExperience->hours;
					$userAircraft->pic = $pilotExperience->pic;
					$entity->pilotExperiences[] = $userAircraft;
				}
			}
			$entity->copilotExperiences = array();
			foreach ($data['copilot_experiences'] as $key => $pilotExperience) {
				if ($pilotExperience->model) {
					$userAircraft = new \Model\Entity\JobAircraft();
					$userAircraft->aircraftId = $pilotExperience->model;
					$userAircraft->hours = $pilotExperience->hours;
					$userAircraft->pic = NULL;
					$entity->copilotExperiences[] = $userAircraft;
				}
			}

            $this->service->save($entity);

            $this->presenter->flashMessage("Job was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }

        if ($form['back']->submittedBy) {
            \Nette\Callback::create($this->onSaveAndBack)->invokeArgs(array($entity));
        } else if ($form['send']->submittedBy) {
            $this->onSave->invokeArgs(array($entity));
        }
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
			->addAttributes(['style' => 'width: 80px', 'class' => 'ajaxSend']);
		$container->addSelect('manufacturer', NULL, $manufacturers)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 180px', 'class' => 'ajaxSend']);
		$container->addSelect('model', NULL, $models)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 210px', 'class' => 'ajaxSend']);
		$container->addSelect('hours', NULL, $this->getHoursItems())
			->getControlPrototype()
			->addAttributes(['style' => 'width: 82px', 'class' => 'ajaxSend']);
		$container->addSelect('pic', NULL, $this->getHoursItems())
			->getControlPrototype()
			->addAttributes(['style' => 'width: 82px', 'class' => 'ajaxSend']);

		/* @var $remove \Nette\Forms\Controls\SubmitButton */
		$container->addSubmit('remove', 'x')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'removeExperience'];
		$container['remove']->getControlPrototype()->class[] = 'button btn btn-primary ajaxSend';

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
		$this->invalidateControl();
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
			->addAttributes(['style' => 'width: 80px', 'class' => 'ajaxSend']);
		$container->addSelect('manufacturer', NULL, $manufacturers)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 180px', 'class' => 'ajaxSend']);
		$container->addSelect('model', NULL, $models)
			->setPrompt('---')
			->getControlPrototype()
			->addAttributes(['style' => 'width: 210px', 'class' => 'ajaxSend']);
		$container->addSelect('hours', NULL, $this->getHoursItems())
			->getControlPrototype()
			->addAttributes(['style' => 'width: 82px', 'class' => 'ajaxSend']);

		$container->addSubmit('remove', 'x')
			->setValidationScope(FALSE)
			->onClick[] = [$this, 'removeExperience'];
		$container['remove']->getControlPrototype()->class[] = 'button btn btn-primary ajaxSend';

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
		$this->invalidateControl();
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

    private function getLocations()
    {
        $parents = $this->locationService->find('parent_id IS', NULL);
        $r = array();
        foreach ($parents as $p) {
            $locations = $this->locationService->find('parent_id=%i', $p->id);
            $help = array();
            foreach ($locations as $l) {
                $help[$l->id] = $l->name;
            }
            $r[$p->name] = $help;
        }
        return $r;
    }

    private function getCategories()
    {
        $data = $this->categoryService->findAll($this->lang);
        $categories = array();
        foreach ($data as $category) {
            $categories[$category->id] = $category->name;
        }
        return $categories;
    }

    private function saveLocations($_data, $_id)
    {
        $this->locationService->deleteConn($_id);
//        foreach ($_data as $d) {
//            $parent = $this->locationService->findById(intval($d));
//            $location = array('location_id' => intval($d), 'jobs_id' => $_id, 'location_parent_id' => $parent->parent_id);
//            $this->locationService->saveConnection($location);
//        }
    }
    
    public function render()
    {
        $this->template->showCompanySelect = !$this->companyId;
        parent::render();
    }

}
