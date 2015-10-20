<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ForumService,
    Model\Entity\ForumCategoryEntity;

/**
 * Forum Category Form
 *
 * @author Petr Poupě
 */
class ForumCategoryForm extends AppForms
{

    /** @var ForumService */
    private $service;

    /** @var bool */
    private $inner;

    public function __construct(Presenter $presenter, ForumService $service, $inner = FALSE)
    {
        parent::__construct(get_class($this), $presenter, $inner);

        $this->service = $service;
        $this->inner = $inner;
    }

    public function setDefaults(ForumCategoryEntity $entity)
    {
        $this->entityToForm($entity);
    }

    protected function createComponent($name)
    {
        if ($this->inner) {
            $this->form->getElementPrototype()->class = "styled innerPage";
        }

        $this->form->addHidden('id');
        $this->form->addText("name", "Name", 50, 255)
                ->addRule(Form::FILLED, "Please fill something");

        $sendName = $this->inner ? "Save" : "New category";
        $this->form->addSubmit('save', $sendName);

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);
        try {
            $this->service->saveCategory($entity);
            $this->presenter->flashMessage("Category was succesfully saved", 'success');
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
        $entity = $this->service->getCategory($values->id);
        
        if ($values->name !== "") {
            $entity->name = $values->name;
        }
        if ($entity->lang === NULL) {
            $entity->lang = $this->lang;
        }

        return $entity;
    }

    private function entityToForm(ForumCategoryEntity $entity)
    {
        parent::setDefaultValues(array(
            'id' => $entity->id,
            'name' => $entity->name,
        ));
    }

}

?>
