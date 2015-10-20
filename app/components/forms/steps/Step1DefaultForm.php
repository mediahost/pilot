<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Model\Entity\CvEntity;

/**
 * Class Step1DefaultForm
 * @package AppForms
 *
 * @author Petr Poupě
 */
class Step1DefaultForm extends StepsForm
{

    /**
     * @param $name
     *
     * @return Form|\Nette\ComponentModel\IComponent
     */
    protected function createComponent($name)
    {
        $this->form->addCheckbox('is_default', 'Set as default CV');

//        $this->form->addSubmit('send', 'Save')
//                        ->getControlPrototype()->class = "button";

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function onSuccess(Form $form)
    {
        parent::onSuccess($form);
    }

    private function setDefaults()
    {
        $this->form->setDefaults(
                array(
                    'is_default' => $this->cv->isDefault,
                )
        );
    }

    /**
     * Fill entity from form
     *
     * @param \Nette\ArrayHash $values
     * @param CvEntity $entity
     * @param bool $submByBtn
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        $keys = array(// itemKey => valueKey
            'isDefault' => 'is_default',
        );
        $this->fillEntity($entity, $values, $keys);
    }

}
