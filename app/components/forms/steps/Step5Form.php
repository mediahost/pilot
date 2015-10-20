<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Model\Entity\CvWorkEntity;

/**
 * Step5 Form
 *
 * @author Petr Poupě
 */
class Step5Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->addHidden('changed_id')
                ->setAttribute("class", "changeId");
        $this->form->addText('work_company', 'Company name', NULL, 100)
                ->addRule(Form::FILLED, 'Enter company name!');
        $this->form->addDatePicker('work_from', "Date")
                ->setAttribute("class", "birthDate novalidate");
        $this->form->addDatePicker('work_to', "-")
                ->setAttribute("class", "birthDate novalidate");
        $this->form->addCheckbox('till_now', "till now")
                ->setAttribute("class", "birthDate novalidate")
                ->setDefaultValue(TRUE)
                ->addCondition(Form::EQUAL, FALSE)
                ->toggle('frmstep5Form-work_to');
        $this->form->addText('work_position', 'Position held', NULL, 200);
        $this->form->addTextArea('work_activities', "Main activities and responsibilities", 30, 1);
        $this->form->addTextArea('work_achievment', "Achievement", 30, 1);
        $this->form->addCheckbox('ref_public', 'Show Referee in CV')
                ->setDefaultValue(FALSE);
        $this->form->addText('ref_name', 'Referee name', NULL, 255);
        $this->form->addText('ref_position', 'Position', NULL, 255);
        $this->form->addText('ref_phone', 'Phone', NULL, 20);
        $this->form->addText('ref_email', 'Email', NULL, 255)
                ->setEmptyValue("@")
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, 'Entered value is not email!');

        $this->form->addSubmit('send', 'Save')
                ->setAttribute("class", "button");

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        parent::onSuccess($form);
    }
    
    public function afterSuccess(Form $form, $submittedBy = FALSE)
    {
        if ($submittedBy) {
            $form->setValues(array(
                'till_now' => TRUE,
            ), TRUE);
        } else {
            parent::afterSuccess($form, $submittedBy);
        }
        if ($submittedBy) {
            $this->presenter->invalidateControl("timeline");
        }
    }

    private function setDefaults()
    {
        $this->form->setDefaults($this->defaults);
    }

    public function setWork(CvWorkEntity $work)
    {
        $this->defaults = array(
            'changed_id' => $work->id,
            'work_company' => $work->company,
            'work_from' => $work->from,
            'work_to' => $work->to,
            'till_now' => $work->to === NULL,
            'work_position' => $work->position,
            'work_activities' => $work->activities,
            'work_achievment' => $work->achievment,
            'ref_public' => $work->refPublic,
            'ref_name' => $work->refName,
            'ref_position' => $work->refPosition,
            'ref_phone' => $work->refPhone,
            'ref_email' => $work->refEmail,
        );
    }

    /**
     * Fill entity from form
     * @param \Nette\ArrayHash $values
     * @param \Model\Entity\CvEntity $entity
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        if ($submByBtn) {
            $work = new CvWorkEntity;
            if ($this->step === 5)
                $work->type = CvWorkEntity::TYPE_WORK;
            else
                $work->type = CvWorkEntity::TYPE_OTHER;
            $keys = array(
                'id' => "changed_id",
                'company' => "work_company",
                'position' => "work_position",
                'activities' => "work_activities",
                'achievment' => "work_achievment",
                'refPublic' => "ref_public",
                'refName' => "ref_name",
                'refPosition' => "ref_position",
                'refPhone' => "ref_phone",
                'refEmail' => "ref_email",
                'file' => "ref_file",
            );
            foreach ($keys as $itemKey => $valueKey) {
                if (isset($values->$valueKey) && $values->$valueKey !== "") {
                    $work->$itemKey = $values->$valueKey;
                }
            }
            $work->from = $values->work_from === "" ? NULL : $values->work_from;
            $work->to = ($values->work_to === "" || $values->till_now) ? NULL : $values->work_to;
            $entity->addWork($work);
        }
    }

    public function render()
    {
        $this->template->step = $this->step;
        $this->template->works = $this->cv->getWorks($this->step == 5 ? CvWorkEntity::TYPE_WORK : CvWorkEntity::TYPE_OTHER);
        $this->template->translator = $this->translator;
        $this->template->registerHelper("CvWorkDates", "\Model\Entity\CvWorkEntity::helperGetDates");
        parent::render();
    }

    public function handleEditWork($workId)
    {
        /* @var $work \Model\Entity\CvWorkEntity */
        $work = $this->cv->getWork($workId);
        $this->setWork($work);
    }

    public function handleDeleteWork($workId)
    {
        $this->cv = $this->service->deleteWork($this->cv, $workId);
        $this->presenter->flashMessage('Selected experience was deleted', 'success');
    }

}

?>
