<?php

namespace AppForms;

use \Nette\Application\UI\Form;

/**
 * Step6 Form
 *
 * @author Petr Poupě
 */
class Step6Form extends StepsForm
{

	protected function createComponent($name)
	{
		// NON EXISTING
		// using Step5 with step=6

		$this->form->onSuccess[] = $this->onSuccess;
		return $this->form;
	}

	public function onSuccess(Form $form)
	{
		parent::onSuccess($form);
	}

	/**
	 * Fill entity from form
	 * @param \Nette\ArrayHash $values
	 * @param \Model\Entity\CvEntity $entity
	 */
	protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
	{
		
	}

}
