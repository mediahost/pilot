<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Model\Entity\CvEntity;

/**
 * Class Step1PersonalForm
 * @package AppForms
 *
 * @author Petr Poupě
 */
class Step1PersonalForm extends StepsForm
{

    /**
     * @param $name
     *
     * @return Form|\Nette\ComponentModel\IComponent
     */
    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled";
        $title = CvEntity::titles();
        $this->form->addSelect('title', "Title", $title)
                        ->getControlPrototype()->class = "small";
        $this->form->addText('degree_b', 'Degree in front of name', NULL, 50)
                        ->getControlPrototype()->class = "small";
        $this->form->addText('firstname', 'First Name(s)', NULL, 100)
				->setRequired('Must be filled');
        $this->form->addText('middlename', 'Middle Name', NULL, 100);
        $this->form->addText('surname', 'Surname(s)', NULL, 100)
				->setRequired('Must be filled');
        $this->form->addText('degree_a', 'Degree after name', NULL, 50)
                        ->getControlPrototype()->class = "small";
        $gender = CvEntity::genders();
        $this->form->addRadioList('gender', "Gender", $gender)
				->setRequired('Must be filled');
        $this->form->addDatePicker('birthday', "Date of Birth")
				->setRequired('Date of Birth must be filled')
                ->addCondition(Form::FILLED)
                ->addRule(Form::RANGE, 'Entered date is not within allowed range.', array(NULL, new \DateTime('-1 day')));
        $this->form['birthday']->getControlPrototype()->class = "birthDate";

        $this->form->addSubmit('send', 'Save')
                        ->getControlPrototype()->class = "button";

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function onSuccess(Form $form)
    {
		foreach ($this->service->findUsersCv($this->user->id) as $cvRow) {
			if ($cvRow->id !== $this->cv->id) {
				$entity = $this->service->findUserItem($cvRow->id, $this->user->id);
				$entity->userId = $this->presenter->user->id;
				$this->formToEntity($form->values, $entity);
				$entity->email = $this->user->identity->mail;
				$this->service->save($entity);
			}
		}
        parent::onSuccess($form);
    }

	public function afterSuccess(Form $form, $submittedBy = FALSE)
	{
		$this->presenter->flashMessage('Thank you! Information has been saved!', 'success');
		$this->presenter->redirect('this');
	}

    private function setDefaults()
    {
        $this->form->setDefaults(
                array(
                    'title' => $this->cv->title,
                    'degree_a' => $this->cv->degreeAfter,
                    'firstname' => $this->cv->firstname,
                    'middlename' => $this->cv->middlename,
                    'surname' => $this->cv->surname,
                    'degree_b' => $this->cv->degreeBefore,
                    'gender' => is_null($this->cv->gender) ? CvEntity::GENDER_DEFAULT : $this->cv->gender,
                    'birthday' => $this->cv->birthday instanceof \Nette\DateTime ? $this->cv->birthday->format("Y-m-d") : NULL,
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
            'title' => "title",
            'degreeBefore' => "degree_b",
            'firstname' => "firstname",
            'middlename' => "middlename",
            'surname' => "surname",
            'degreeAfter' => "degree_a",
            'gender' => "gender",
            'birthday' => "birthday",
        );
        $this->fillEntity($entity, $values, $keys);
    }

}
