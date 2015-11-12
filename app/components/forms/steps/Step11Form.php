<?php

namespace AppForms;

use Model\Entity\UserAircraft;
use Model\Entity\UserEntity;
use Model\Service\AircraftService;
use Model\Service\UserService;
use \Nette\Application\UI\Form,
    Model\Entity\CvItScaleEntity,
    Nette\Application\UI\Presenter,
    Model\Service\CvService,
    Model\Entity\CvEntity;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * Step11 Form
 *
 * @author Petr Poupě
 * @author Marek Šneberger
 */
class Step11Form extends StepsForm
{

    /** @var mixed  */
    protected $skills;

    /** @var array */
    protected $defaults = [];

    /** @var AircraftService */
    protected $aircraftService;

    /** @var UserEntity */
    protected $userEntity;

    /** @var UserService */
    protected $userService;

    public function __construct(Presenter $presenter, CvService $service, CvEntity $cv, $step)
    {
        parent::__construct($presenter, $service, $cv, $step);
        $this->skills = $this->service->buildSkills();
		$this->form->getElementPrototype()->addClass('custom');
    }

    public function loadAircrafts()
    {
        /** @var UserService $userService */
        $this->userService = $userService  = $this->presenter->context->users;
        $this->aircraftService  = $this->presenter->context->getByType('Model\Service\AircraftService');

        $this->userEntity = $userService->find($this->cv->userId);

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

    /**
     * @param $name
     *
     * @return Form|\Nette\ComponentModel\IComponent
     */
    protected function createComponent($name)
    {
        $this->loadAircrafts();
        $experiences = $this->form->addDynamic('experiences', [$this, 'experienceContainerFactory'], count($this->defaults['pilotExperiences'])?:1);
        $experiences->addSubmit('add', 'Add plane')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addExperience'];

        $copilotExperiences = $this->form->addDynamic('copilot_experiences', [$this, 'experienceCopilotContainerFactory'], count($this->defaults['copilotExperiences'])?:1);
        $copilotExperiences->addSubmit('add', 'Add plane')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addExperience'];

        $this->form->addSubmit('send', 'Save')
            ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function onSuccess(Form $form)
    {
        if ($form->submitted === TRUE || in_array($form->submitted->name, ['add', 'remove'])) {
            $this->invalidateControl('form_content_captain');
            $this->invalidateControl('form_content_copilot');
            return;
        }
        parent::onSuccess($form);
    }


    /**
     * Fill entity from form
     *
     * @param \Nette\ArrayHash $values
     * @param \Model\Entity\CvEntity $entity
     * @param bool $submByBtn
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        $form = $this->form;
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
        $this->redirect('this');
    }

    public function experienceContainerFactory(Container $container)
    {
        $manufacturers = $this->aircraftService->getManufacturers();
        $models = $this->aircraftService->getModels();
		
        $container->addSelect('type', 'Type', [
            AircraftService::TYPE_JET => 'Jet',
            AircraftService::TYPE_TURBO => 'Turbo',
        ])
            ->setPrompt('---')
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 80px']);
        $container->addSelect('manufacturer', 'Manufacturer', $manufacturers)
            ->setPrompt('---')
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 180px']);
        $container->addSelect('model', 'Model', $models)
            ->setPrompt('---')
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 210px']);
        $container->addSelect('hours', 'Total hours', $this->getHoursItems())
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 82px']);
        $container->addSelect('pic', 'PIC', $this->getHoursItems())
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 82px']);
        $container->addCheckbox('current', 'Current');

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

        $container->addSelect('type', 'Type', [
            AircraftService::TYPE_JET => 'Jet',
            AircraftService::TYPE_TURBO => 'Turbo',
        ])
            ->setPrompt('---')
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 80px']);
        $container->addSelect('manufacturer', 'Manufacturer', $manufacturers)
            ->setPrompt('---')
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 180px']);
        $container->addSelect('model', 'Model', $models)
            ->setPrompt('---')
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 210px']);
        $container->addSelect('hours', 'Total hours', $this->getHoursItems())
            ->getControlPrototype()
            ->addAttributes(['style' => 'width: 82px']);
        $container->addCheckbox('current', 'Current');

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

}
