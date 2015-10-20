<?php

namespace AppForms;

use \Nette\Application\UI\Form;

/**
 * Step4 Form
 *
 * @author Petr Poupě
 */
class Step4Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->addCheckBox('show_summary', "Show in CV")
                ->setDefaultValue(TRUE);
        $this->form->addTextArea('career_summary', "Career summary", 30, 1);
        $this->form['career_summary']->getControlPrototype()->maxlength = "600";
        $this->form['career_summary']->getControlPrototype()->class = "bigger";

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
            'career_summary' => $this->cv->careerSummary,
            'show_summary' => $this->cv->showSummary,
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
            'careerSummary' => "career_summary",
            'showSummary' => "show_summary",
        );
        $this->fillEntity($entity, $values, $keys);
    }

}

?>
