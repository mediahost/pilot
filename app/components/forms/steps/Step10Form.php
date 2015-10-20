<?php

namespace AppForms;

use \Nette\Application\UI\Form;

/**
 * Step10 Form
 *
 * @author Petr Poupě
 */
class Step10Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->addTextArea('info', "Additional information", 30, 1);
        $this->form['info']->getControlPrototype()->maxlength = "600";
        $this->form['info']->getControlPrototype()->class = "bigger";

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
            'info' => $this->cv->info,
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
            'info' => "info",
        );
        $this->fillEntity($entity, $values, $keys);
    }

}

?>
