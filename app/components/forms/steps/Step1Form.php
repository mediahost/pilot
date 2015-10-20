<?php

namespace AppForms;

use \Nette\Application\UI\Form,
	Model\Entity\CvEntity;

/**
 * Class Step1Form
 * @package AppForms
 *
 * @author Petr Poupě
 */
class Step1Form extends StepsForm
{
	
	public $onSave = array();

	/**
	 * @param $name
	 *
	 * @return Form|\Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$this->form->getElementPrototype()->class = "styled";

		$this->form->addGroup('Personal Info');
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
		$nationalities = CvEntity::nationalities();
		$this->form->addSelect('nationality', "Nationality", $nationalities)
				->setRequired('Must be filled')
				->setPrompt(" - select - ");

		$this->form->addGroup('Contact');
		$this->form->addText('house', "House No.", NULL, 10);
		$this->form->addText('address', "Street address", NULL, 255);
		$this->form->addText('zipcode', "Postal code", NULL, 50);
		$this->form->addText('city', "City", NULL, 255)
				->setRequired('Must be filled');
		$this->form->addSelect('country', "Country", $nationalities)
				->setRequired('Must be filled')
				->setPrompt(" - select - ");
		$this->form->addText('phone', "Contact number", NULL, 20)
				->setRequired('Must be filled');

		$this->form->addGroup('Photo');
		$photo = $this->form->addUpload("photo", "Photo");
		$photo->addCondition(Form::FILLED)
				->addRule(Form::IMAGE, 'Photo must be JPG, JPEG or PNG');
		if (!$this->cv->photo) {
			$photo->setRequired('Must be filled');
		}
		$this->form["photo"]
				->setAttribute("accept", "image/x-png, image/jpeg, image/jpg")
				->setOption("description", \Nette\Utils\Html::el("p")
						->setText("please upload photos in JPEG, JPG or PNG format")
						->style("padding-bottom: 5px; font-size: 12px;")
		);

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
			$entity = $this->service->findUserItem($cvRow->id, $this->user->id);
			$entity->userId = $this->presenter->user->id;
			$this->formToEntity($form->values, $entity);
			$entity->email = $this->user->identity->mail;
			$this->service->save($entity);
		}
		$this->onSave($entity);
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
					'nationality' => $this->cv->nationality,
					'address' => $this->cv->address,
					'house' => $this->cv->house,
					'zipcode' => $this->cv->zipcode,
					'city' => $this->cv->city,
					'country' => $this->cv->country,
					'phone' => $this->cv->phone,
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
		if (!$this->cv->photo) {
			$entity->showPhoto = TRUE;
			if ($entity->id !== NULL) {
				$filename = $this->saveImage($values->photo, "cvImages", $entity->id);
				$this->saveImage($values->photo, "photo", $entity->userId);
				if ($filename !== FALSE) {
					$entity->photo = $filename;
				}
			}
		}
		$keys = array(// itemKey => valueKey
			'title' => "title",
			'degreeBefore' => "degree_b",
			'firstname' => "firstname",
			'middlename' => "middlename",
			'surname' => "surname",
			'degreeAfter' => "degree_a",
			'gender' => "gender",
			'birthday' => "birthday",
			'nationality' => "nationality",
			'address' => "address",
			'house' => "house",
			'zipcode' => "zipcode",
			'city' => "city",
			'country' => "country",
			'phone' => "phone",
		);
		$this->fillEntity($entity, $values, $keys);
	}

}
