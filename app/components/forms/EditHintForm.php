<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\HintService,
    Model\Entity\HintEntity;

/**
 * EditHint Form
 *
 * @author Petr Poupě
 */
class EditHintForm extends AppForms
{

    /** @var HintService */
    private $service;

    public function __construct(Presenter $presenter, HintService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setDefaults(HintEntity $entity)
    {
        $this->entityToForm($this->getComponent($this->name), $entity);
    }

    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        
        $this->form->addHidden('id');
        $this->form->addHidden('lang', $this->lang);
        $this->form->addText('form', "Form number", 35, 2)
                ->addRule(Form::RANGE, "Form must be number form %d to %d", array(1, 10));
        $this->form->addText('comment', "Comment", 35, 255);
        $this->form->addTextArea('text', "Text", 100, 20)
                        ->addRule(Form::FILLED, "Text is required")
                        ->getControlPrototype()->class = "ckeditor";

        $this->form->addSubmit('send', 'Save')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);

        try {
            $this->service->save($entity);

            $this->presenter->flashMessage("Hint was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }

        $this->presenter->redirect("this");
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return \Model\Entity\HintEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = new HintEntity;
        if ($values->id !== "")
            $entity->id = $values->id;
        if ($values->form !== "")
            $entity->form = $values->form;
        if ($values->lang !== "")
            $entity->lang = $values->lang;
        if ($values->comment !== "")
            $entity->comment = $values->comment;
        if ($values->text !== "")
            $entity->text = $values->text;
        return $entity;
    }

    private function entityToForm(Form $form, HintEntity $entity)
    {
        $form->setDefaults(array(
            'id' => $entity->id,
            'lang' => $this->lang,
            'form' => $entity->form,
            'comment' => $entity->comment,
            'text' => $entity->text,
        ));
    }

}

?>
