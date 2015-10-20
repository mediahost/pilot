<?php

namespace AppForms;

use \Nette\Application\UI\Form;

/**
 * Step2 Form
 *
 * @author Petr Poupě
 */
class Step2Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->addCheckbox('show_career_objective', "Include to CV")
                    ->setDefaultValue(TRUE);
        $this->form->addTextArea('career_objective', "Your career objective", 30, 1);
        $this->form['career_objective']->getControlPrototype()->maxlength = "140";
        $this->form['career_objective']->getControlPrototype()->class = "middle";

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
            'career_objective' => $this->cv->careerObjective,
            'show_career_objective' => $this->cv->showCareerObjective,
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
            'careerObjective' => "career_objective",
            "showCareerObjective" => "show_career_objective"
        );
        $this->fillEntity($entity, $values, $keys);
    }

}

?>
