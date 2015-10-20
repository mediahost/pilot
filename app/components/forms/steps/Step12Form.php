<?php

namespace AppForms;

use \Nette\Application\UI\Form;

/**
 * Step12 Form
 *
 * @author Petr Poupě
 */
class Step12Form extends StepsForm
{

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled";

        $this->form->addUpload("photo", "Photo")
                ->addCondition(Form::FILLED)
                ->addRule(Form::IMAGE, 'Photo must be JPG, JPEG or PNG');
        $this->form["photo"]
                ->setAttribute("accept", "image/x-png, image/jpeg, image/jpg")
                ->setOption("description", \Nette\Utils\Html::el("p")
                        ->setText("please upload photos in JPEG, JPG or PNG format")
                        ->style("padding-bottom: 5px; font-size: 12px;")
        );
        $this->form->addCheckbox('show_photo', "Show in CV")
                ->setDefaultValue(TRUE);

        $this->form->addSubmit('send', 'Save')
                        ->getControlPrototype()->class = "button";

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

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
		$this->presenter->flashMessage('Thank you! Photo has been saved!', 'success');
		$this->presenter->redirect('this');
	}

    private function setDefaults()
    {
        $this->form->setDefaults(
                array(
                    'show_photo' => $this->cv->showPhoto,
                )
        );
    }

    /**
     * Fill entity from form
     * @param \Nette\ArrayHash $values
     * @param \Model\Entity\CvEntity $entity
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        $entity->showPhoto = $values->show_photo;
        if ($entity->id !== NULL) {
            $filename = $this->saveImage($values->photo, "cvImages", $entity->id);
            $this->saveImage($values->photo, "photo", $entity->userId);
            if ($filename !== FALSE) {
                $entity->photo = $filename;
            }
        }
    }

}
