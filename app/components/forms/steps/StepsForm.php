<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\CvService,
    Model\Entity\CvEntity,
    Model\Entity\ActionLogEntity;

/**
 * Steps Form
 *
 * @author Petr Poupě
 */
abstract class StepsForm extends AppForms
{

    protected $step;

    /** @var CvEntity */
    protected $cv;

    /** @var CvService */
    protected $service;

    public function __construct(Presenter $presenter, CvService $service, CvEntity $cv, $step)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $service;
        $this->cv = $cv;
        $this->step = $step;
        $this->setTemplatePath("steps/templates");

        $this->form->getElementPrototype()->class = "styled ajax";
//        $this->form->getElementPrototype()->class = "styled";
    }
    
    public function onSuccess(Form $form)
    {
        $userId = $this->user->getId();
        $entity = $this->service->findUserItem($this->cv->id, $userId);
        
        $entity->userId = $this->presenter->user->getId();
		if ($this->step !== FALSE) {
			$entity->lastStep = $this->step;
		}

		$submittedBy = isset($form['send']) ? $form['send']->submittedBy : FALSE;
        $this->formToEntity($form->values, $entity, $submittedBy);
		$entity->email = $this->user->identity->mail;
        $this->cv = $this->service->save($entity);
        if ($this->step === 11) { //skills
            $skills = $entity->itSkills;
            $this->service->saveSkills($this->cv, $skills);
        }
        $this->presenter->context->actionlogs->logSerie(ActionLogEntity::SAVE_CV, $userId, array($this->cv->id));

        $this->afterSuccess($form, $submittedBy);
    }
    
    public function afterSuccess(Form $form, $submittedBy = FALSE)
    {
        if ($this->presenter->ajax) {
            if (!$submittedBy) {
                $this->presenter->validateControl("forms");
            }
            $this->invalidateControl("formList");
        } else {
            $this->presenter->redirect('this');
        }
    }
    
    protected function formToEntity(\Nette\ArrayHash $values, CvEntity &$entity, $submByBtn = FALSE)
    {
        throw new Exception("Must be overwrite in child class.");
    }
    
    protected function fillEntity(CvEntity &$entity, \Nette\ArrayHash $values, array $keys)
    {
        foreach ($keys as $itemKey => $valueKey) {
            if (isset($values->$valueKey)) {
                $entity->$itemKey = $values->$valueKey == "" ? NULL : $values->$valueKey;
            }
        }
    }

    public function render()
    {
        $this->template->cv = $this->cv;
        parent::render();
    }
}
