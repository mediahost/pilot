<?php

namespace AppForms;

use \Nette\Application\UI\Form,
	Model\Entity\CvEntity;

/**
 * Class Step1AddressForm
 * @package AppForms
 *
 * @author Petr Poupě
 */
class Step1AddressForm extends StepsForm
{

	/**
	 * @param $name
	 *
	 * @return Form|\Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$this->form->getElementPrototype()->class = "styled";
		$nationalities = CvEntity::nationalities();
		$this->form->addSelect('nationality', "Nationality", $nationalities)
				->setRequired('Must be filled')
				->setPrompt(" - select - ");
		$this->form->addText('house', "House No.", NULL, 10)
				->setRequired('Must be filled');
		$this->form->addText('address', "Street address", NULL, 255)
				->setRequired('Must be filled');
		$this->form->addText('zipcode', "Postal code", NULL, 50)
				->setRequired('Must be filled');
		$this->form->addText('city', "City", NULL, 255)
				->setRequired('Must be filled');
		$this->form->addSelect('country', "Country", $nationalities)
				->setRequired('Must be filled')
				->setPrompt(" - select - ");
		$this->form->addText('phone', "Contact number", NULL, 20)
				->setRequired('Must be filled');

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
		$keys = array(// itemKey => valueKey
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
