<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ForumService,
    Model\Entity\ForumPostEntity,
    Model\Entity\ActionLogEntity;

/**
 * Forum Post Form
 *
 * @author Petr Poupě
 */
class ForumPostForm extends AppForms
{

    /** @var ForumService */
    private $service;

    public function __construct(Presenter $presenter, ForumService $service)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $service;
    }

    public function setDefaults(ForumPostEntity $entity)
    {
        $this->entityToForm($entity);
    }

    protected function createComponent($name)
    {
        $this->form->addHidden('id');
        $this->form->addHidden('tid');
        $this->form->addTextArea('post', NULL, 138, 18)
//                        ->addRule(Form::FILLED, "Please fill some message")
                ->setAttribute("class", "ckeditor");

        $this->form->addSubmit('send', 'Send');

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);
        if ($entity->body === NULL) {
            $form->addError("Please fill some message");
        } else {
            try {
                $post = $this->service->savePost($entity);

                $this->presenter->context->actionlogs->log(ActionLogEntity::FORUM_POST, $this->user->getId(), array($post->id));
                $this->presenter->flashMessage("Post was succesfully saved", 'success');
            } catch (Exception $exc) {
                $this->presenter->flashMessage($exc->getMessage(), 'error');
            }

            $this->presenter->redirect("this#post-{$post->id}", array("editPost" => NULL));
        }
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return \Model\Entity\HintEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = $this->service->getPost($values->id);
        
        if ($values->tid !== "")
            $entity->topicId = $values->tid;
        if ($this->user->id !== NULL)
            $entity->userId = $this->user->id;
        if ($values->post !== "")
            $entity->body = $values->post;

        return $entity;
    }

    private function entityToForm(ForumPostEntity $entity)
    {
        parent::setDefaultValues(array(
            'id' => $entity->id,
            'tid' => $entity->topicId,
            'post' => $entity->body,
        ));
    }

}

?>
