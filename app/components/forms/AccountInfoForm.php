<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService,
    Model\Entity\UserEntity;

/**
 * Account info Form
 *
 * @author Petr Poupě
 */
class AccountInfoForm extends AppForms
{

    /** @var UserService */
    private $service;

    /** @var bool */
    private $disabledMail = TRUE;
    
    /** @var \Model\Service\TagService */
    protected $tagService;

    public function __construct(Presenter $presenter, UserService $service, \Model\Service\TagService $tagService)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
        $this->tagService = $tagService;
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "front";

        $this->form->addHidden("id");
        $this->form->addText('firstname', 'First Name')
                ->setRequired('Please enter your first name.');
        $this->form->addText('surname', 'Surname')
                ->setRequired('Please enter your surname.');
        $this->form->addRadioList('gender', 'Gender', array("male" => "Male", "female" => "Female"))
                ->getSeparatorPrototype()->setName(NULL);
        $this->form->addDatePicker('birthday', "Bithday")
                ->setAttribute("readonly");
        $this->form['birthday']->getControlPrototype()->class = "birthDate date-picker form-control input-medium";
        $this->form['birthday']->getControlPrototype()->addAttributes(["data-date-format" => "yyyy-mm-dd"]);
        $this->form->addText('email', 'E-mail')
                ->addRule(Form::EMAIL, "Pleas fill valid email.");
        if ($this->disabledMail) {
            $this->form["email"]->setAttribute("class", "disabled")
                    ->setAttribute("readonly");
        }
        if ($this->user->isInRole('admin') || $this->user->isInRole('superadmin')) {
                $this->form->addMultiSelect('tags', 'Tags', $this->tagService->findAll())
                ->setAttribute('class', 'chosen');
            $this->form->addText('new_tags', 'New tags')
                ->setAttribute('placeholder', 'comma separated');
        }

        $this->form->addSubmit('save', 'Save')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $values = $form->values;
        
        if ($this->user->isInRole('admin') || $this->user->isInRole('superadmin')) {
            $newTags = explode(',', $values->new_tags);
            foreach ($newTags as $newTag) {
                $newTag = \Nette\Utils\Strings::trim($newTag);
                if (!empty($newTag)) {
                    $values->tags[] = $this->tagService->saveTag($newTag);
                }
            }
            $this->tagService->saveUserTags($values->id, $values->tags);
        }
        
        $user = $this->service->find($values->id);
        $user->firstName = $values->firstname;
        $user->lastName = $values->surname;
        $user->gender = $values->gender;
        $user->birthday = $values->birthday;
        $user->mail = $values->email;
        if ($this->service->save($user)) {
            $this->presenter->flashMessage("Data was succesfully saved.", "success");
            $this->presenter->redirect("this");
        } else {
            $this->presenter->flashMessage("Data wasn't save. Try it later or contact us.", "error");
        }
    }

    public function setDefaults(UserEntity $entity, $disabledMail = TRUE)
    {
        $this->disabledMail = $disabledMail;
        $values = array(
            "id" => $entity->id,
            "firstname" => $entity->firstName,
            "surname" => $entity->lastName,
            "gender" => $entity->gender,
            "birthday" => $entity->birthday,
            "email" => $entity->mail,
        );
        if ($this->user->isInRole('admin') || $this->user->isInRole('superadmin')) {
            $values['tags'] = $this->tagService->getUserTags($entity->id);
        }
        parent::setDefaultValues($values);
    }

}
