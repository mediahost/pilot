<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    \Model\Entity\CvEntity;

/**
 * Step9 Form
 *
 * @author Petr Poupě
 */
class Step9Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->addTextArea('skill_social', 'Social skills and competences', 30, 1);
        $this->form->addTextArea('skill_organise', 'Organisational skills and competences', 30, 1);
        $this->form->addTextArea('skill_technical', 'Technical skills and competences', 30, 1);;
        $this->form->addTextArea('skill_artistic', 'Artistic skills and competences', 30, 1);
        $this->form->addTextArea('skill_other', 'Other skills and competences', 30, 1);

        $this->form->addText('passport_number', 'Passport number', NULL, 255);
		
//        $licenses = CvEntity::licenses();
//        $this->form->addMultiSelect('licenses', "Driving licenses", $licenses)
//                        ->getControlPrototype()->class = "bigger labelTop";

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
            'skill_social' => $this->cv->skillSocial,
            'skill_organise' => $this->cv->skillOrganise,
            'skill_technical' => $this->cv->skillTechnical,
            'skill_computer' => $this->cv->skillComputer,
            'skill_artistic' => $this->cv->skillArtistic,
            'skill_other' => $this->cv->skillOther,
			//'licenses' => $this->cv->licenses,
			'passport_number' => $this->cv->passportNumber,
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
            'skillSocial' => 'skill_social',
            'skillOrganise' => 'skill_organise',
            'skillTechnical' => 'skill_technical',
            'skillComputer' => 'skill_computer',
            'skillArtistic' => 'skill_artistic',
            'skillOther' => 'skill_other',
//            'licenses' => 'licenses',
			'passportNumber' => 'passport_number'
        );
        $this->fillEntity($entity, $values, $keys);
    }

}

