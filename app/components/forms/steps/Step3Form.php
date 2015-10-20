<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    \Model\Entity\CvEntity;

/**
 * Step3 Form
 *
 * @author Petr Poupě
 */
class Step3Form extends StepsForm
{

    protected function createComponent($name)
    {
        $categories = CvEntity::sectors();

        $this->form->addGroup();

        $this->form->addCheckbox('show_desired_employment', 'Include to CV')
                ->setDefaultValue(TRUE);
        $this->form->addDatePicker('avaliblity_from', "Available from")
                ->setAttribute("class", "birthDate");
        $this->form->addTextArea('job_position', "Desired job position")
                ->setOption('description', "- words separated by commas");
        $this->form['job_position']->getControlPrototype()->maxlength = "140";

        $this->form->addGroup("Salary expectations");
        $this->form->addCheckbox('salary_public', 'Mention salary')
                ->addCondition(Form::EQUAL, TRUE)
                ->toggle('sallaryBox');
        $this->form->addGroup()
                ->setOption('container', \Nette\Utils\Html::el('div')->id('sallaryBox'));
        $this->form->addText('salary_from', "From")
                        ->setOption('description', "€ per annum")
                        ->getControlPrototype()->class = "small range from";
        $this->form->addText('salary_to', "To")
                        ->setOption('description', "€ per annum")
                        ->getControlPrototype()->class = "small range to";

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        parent::onSuccess($form);
    }

    private function setDefaults()
    {
        $this->form->setDefaults(array(
            'sector' => $this->cv->sector,
            'job_position' => $this->cv->getJobPosition(),
            'avaliblity_from' => $this->cv->avaliblityFrom instanceof \Nette\DateTime ? $this->cv->avaliblityFrom->format("Y-m-d") : NULL,
            'salary_public' => $this->cv->salaryPublic,
            'salary_from' => $this->cv->salaryFrom === NULL ? "10000" : $this->cv->salaryFrom,
            'salary_to' => $this->cv->salaryTo === NULL ? "50000" : $this->cv->salaryTo,
            'show_desired_employment' => $this->cv->showDesiredEmployment,
        ));
    }

    /**
     * Fill entity from form
     * @param \Nette\ArrayHash $values
     * @param \Model\Entity\CvEntity $entity
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        $keys = array(// itemKey => valueKey
            'sector' => "sector",
            'jobPosition' => "job_position",
            'avaliblityFrom' => "avaliblity_from",
            'salaryPublic' => "salary_public",
            'salaryFrom' => "salary_from",
            'salaryTo' => "salary_to",
            'showDesiredEmployment' => "show_desired_employment"
        );
        $this->fillEntity($entity, $values, $keys);
        $entity->setJobPosition($values->job_position);
    }

}
