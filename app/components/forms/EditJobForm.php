<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Entity\JobEntity,
    Model\Service\JobService,
    Model\Service\LocationService,
    Model\Service\JobCategoryService,
    Model\Service\CompanyService;

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

    public function __construct(Presenter $presenter, JobService $service, LocationService $locationService, JobCategoryService $categoryService, CompanyService $companyService)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $service;
        $this->locationService = $locationService;
        $this->categoryService = $categoryService;
        $this->companyService = $companyService;
        $this->skills = $this->template->skills = $this->service->buildSkills();
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
    }
    
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    public function setDefaults(JobEntity $entity)
    {
        $form = $this->getComponent($this->name);
        $categories = $this->getCategories();
        $entity->category = array_search($entity->category, $categories);
        $e = $entity->to_array();
        $this->datecreated = $e['datecreated'];
        unset($e['datecreated']);
        $skills = $this->service->loadSkills($e['id']);
        foreach ($skills as $skill) {
            $e[$skill->cv_skill_id] = array('scale' => $skill->scale, 'number' => $skill->years);
        }
        $form->setDefaults($e);
    }

    public function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        $form = $this->form;
//        $form->getElementPrototype()->class("form-horizontal");

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
		
		$form->addSelect('category', 'Category', $this->getCategories())
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

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $data = $form->getValues();
        if ($this->companyId) {
            $data->company_id = $this->companyId;
        }
        $values = array('datecreated' => $this->datecreated);
        $skills = array();
        foreach ($data as $key => $value) {
            if (intval($key) == 0) { //informations
                $values[$key] = $value;
            } else { //skills
                $skills[$key] = $value;
            }
        }
        try {
            if (!$values['id']) {
                $values['id'] = $this->service->getMaxId() + 1;
                $values['datecreated'] = new \DateTime;
            }

            $entity = new JobEntity($values);
            $entity->lang = "en";

            $this->service->save($entity);

//            $this->saveLocations($values['locations'], $values['id']);
            $this->service->saveSkills($values['id'], $skills);

            $this->presenter->flashMessage("Job was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }

        if ($form['back']->submittedBy) {
            \Nette\Callback::create($this->onSaveAndBack)->invokeArgs(array($entity));
        } else {
            $this->onSave->invokeArgs(array($entity));
        }
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
