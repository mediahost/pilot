<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ForumService,
    Model\Entity\ForumTopicEntity;

/**
 * Forum Topic Form
 *
 * @author Petr Poupě
 */
class ForumTopicForm extends AppForms
{

    /** @var ForumService */
    private $service;

    public function __construct(Presenter $presenter, ForumService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setDefaults(ForumTopicEntity $entity)
    {
        $this->entityToForm($entity);
    }

    public function setDisabledBody($value = TRUE)
    {
        if ($value) {
            $form = $this->getForm();
            $form["post"]->setDisabled();
        }
    }

    protected function createComponent($name)
    {
        $this->form->addHidden('id');
        $this->form->addHidden('fid');
        $this->form->addText("name", "Subject", 50, 255)
                ->addRule(Form::FILLED, "Please fill name");
        $this->form->addTextArea('post')
//                        ->addRule(Form::FILLED, "Please fill some message")
                ->setAttribute("class", "ckeditor");

        $this->form->addSubmit('send', 'Send');

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);
        if ($entity->firstPost->body === NULL) {
            $form->addError("Please fill some message");
        } else {
            try {
                $topic = $this->service->saveTopic($entity);
                $this->presenter->flashMessage("Topic was succesfully saved", 'success');
            } catch (Exception $exc) {
                $this->presenter->flashMessage($exc->getMessage(), 'error');
            }

            $this->presenter->redirect("Forum:topic#post-{$topic->firstPostId}", $topic->id);
        }
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return \Model\Entity\HintEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = $this->service->getTopic($values->id);

        if ($values->fid !== "") {
            $entity->forumId = $values->fid;
        }
        if ($values->name !== "") {
            $entity->name = $values->name;
        }

        $firstPost = $this->service->getFirstPost($values->id);
        if ($this->user->id !== NULL && $firstPost->userId === NULL) {
            $firstPost->userId = $this->user->id;
        }
        if ($values->post !== "") {
            $firstPost->body = $values->post;
        }
        $entity->firstPost = $firstPost;

        return $entity;
    }

    private function entityToForm(ForumTopicEntity $entity)
    {
        parent::setDefaultValues(array(
            'id' => $entity->id,
            'fid' => $entity->forumId,
            'name' => $entity->name,
            'post' => $entity->firstPost instanceof \Model\Entity\ForumPostEntity ? $entity->firstPost->body : NULL,
        ));
    }

}

?>
