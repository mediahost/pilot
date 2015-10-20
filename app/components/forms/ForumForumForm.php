<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ForumService,
    Model\Entity\ForumEntity;

/**
 * Forum Form
 *
 * @author Petr Poupě
 */
class ForumForumForm extends AppForms
{

    /** @var ForumService */
    private $service;

    public function __construct(Presenter $presenter, ForumService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setDefaults(ForumEntity $entity)
    {
        $this->entityToForm($entity);
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled innerPage";
        
        $this->form->addHidden('id');
        $this->form->addHidden('cid');
        $this->form->addText("name", "Name", 50, 255)
                ->addRule(Form::FILLED, "Please fill name");
        $this->form->addText("description", "Description", 50, 255)
                ->addRule(Form::FILLED, "Please fill description");

        $this->form->addSubmit('save', 'Save');

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);
        try {
            $this->service->saveForum($entity);
            $this->presenter->flashMessage("Forum was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }

        $this->presenter->redirect("Forum:");
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return \Model\Entity\HintEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = $this->service->getForum($values->id);

        if ($entity->categoryId === NULL) {
            $entity->categoryId = $values->cid;
        }
        if ($values->name !== "") {
            $entity->name = $values->name;
        }
        if ($values->description !== "") {
            $entity->description = $values->description;
        }

        return $entity;
    }

    private function entityToForm(ForumEntity $entity)
    {
        parent::setDefaultValues(array(
            'id' => $entity->id,
            'cid' => $entity->categoryId,
            'name' => $entity->name,
            'description' => $entity->description,
        ));
    }

}

?>
