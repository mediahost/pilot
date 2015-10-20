<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Model\Entity\CvEducEntity;

/**
 * Step7 Form
 *
 * @author Petr Poupě
 */
class Step7Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->addText('instit_name', 'Institution', NULL, 255)
                ->addRule(Form::FILLED, 'Enter institution name!');
        $this->form->addText('instit_city', 'City', NULL, 255);
        $this->form->addText('instit_country', 'Country', NULL, 255);

        $this->form->addHidden('changed_id')
                ->setAttribute("class", "changeId");
        $this->form->addDatePicker('edu_from', "Date")
                ->setAttribute("class", "birthDate novalidate");
        $this->form->addDatePicker('edu_to', "-")
                ->setAttribute("class", "birthDate novalidate");
        $this->form->addCheckbox('till_now', "till now")
                ->setAttribute("class", "novalidate")
                ->setDefaultValue(TRUE)
                ->addCondition(Form::EQUAL, FALSE)
                ->toggle('frmstep7Form-edu_to');
        $this->form->addText('edu_title', 'Title of qualification awarded', NULL, 100);
        $this->form->addTextArea('edu_subjects', "Principal subjects / occupational skills covered", 30, 1);


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
    }

    private function setDefaults()
    {
        $this->form->setDefaults($this->defaults);
    }

    public function setEduc(CvEducEntity $educ)
    {
        $this->defaults = array(
            'changed_id' => $educ->id,
            'edu_from' => $educ->from,
            'edu_to' => $educ->to,
            'till_now' => $educ->to === NULL,
            'edu_title' => $educ->title,
            'edu_subjects' => $educ->subjects,
            'instit_name' => $educ->institName,
            'instit_city' => $educ->institCity,
            'instit_country' => $educ->institCountry,
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
            $edu = new CvEducEntity;
            $keys = array(
                'id' => "changed_id",
                'title' => "edu_title",
                'subjects' => "edu_subjects",
                'institName' => "instit_name",
                'institCity' => "instit_city",
                'institCountry' => "instit_country",
            );
            foreach ($keys as $itemKey => $valueKey) {
                if (isset($values->$valueKey) && $values->$valueKey !== "")
                    $edu->$itemKey = $values->$valueKey;
            }
            $edu->from = $values->edu_from === "" ? NULL : $values->edu_from;
            $edu->to = ($values->edu_to === "" || $values->till_now) ? NULL : $values->edu_to;
            $entity->addEducation($edu);
        }
    }

    public function render()
    {
        $this->template->educations = $this->cv->getEducations();
        $this->template->translator = $this->translator;
        $this->template->registerHelper("CvEducDates", "\Model\Entity\CvEducEntity::helperGetDates");
        $this->template->registerHelper("CvEducBasicView", "\Model\Entity\CvEducEntity::helperGetBasicView");
        parent::render();
    }

    public function handleEditEduc($educId)
    {
        /* @var $educ \Model\Entity\CvEducEntity */
        $educ = $this->cv->getEducation($educId);
        $this->setEduc($educ);
    }

    public function handleDeleteEduc($educId)
    {
        $this->service->deleteEducation($this->cv, $educId);
        $this->presenter->flashMessage('Selected education was deleted', 'success');
    }

}

?>
