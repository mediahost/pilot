<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * Create Account Form
 *
 * @author Petr Poupě
 */
class CreateAccountForm extends AppForms
{

    /** @var \Model\Service\UserService */
    private $service;
	
	private $withTerms;
	
	private $formData = [
		'send' => 'Register',
		'textSize' => 50,
		'passwSize' => 30,
	];

    public function __construct(Presenter $presenter, UserService $service, $withTerms = TRUE)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $service;
		$this->withTerms = $withTerms;
    }
	
	public function setSizes($textSize, $passwSize)
	{
		$this->formData['textSize'] = $textSize;
		$this->formData['passwSize'] = $passwSize;
	}
	
	public function setSendText($text)
	{
		$this->formData['send'] = $text;
	}

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "form-horizontal";

        $this->form->addText('mail', 'E-mail', $this->formData['textSize'])
                ->setRequired("Please enter your e-mail")
                ->addRule(Form::EMAIL, "Please fill valid e-mail")
                ->setAttribute("placeholder", 'E-mail');

        $this->form->addPassword('password', 'Password', $this->formData['passwSize'])
                ->setRequired('Please enter your password')
                ->setAttribute("placeholder", 'Password');

        $this->form->addPassword('password2', 'Password again', $this->formData['passwSize'])
                ->setRequired('Please retype your password')
                ->setAttribute("placeholder", 'Password again')
                ->addConditionOn($this->form['password'], Form::FILLED)
                ->addRule(Form::EQUAL, 'Passwords must be same', $this->form['password']);

        $link = \Nette\Utils\Html::el('a class="innerPage"')
                ->href($this->presenter->link(":Front:Content:terms", 'text'))
                ->setText($this->translator->translate("terms & conditions"))
                ->target("_blank");
        $agree = \Nette\Utils\Html::el()
                ->setText($this->translator->translate("I agree with the") . " ")
                ->add($link);
		if ($this->withTerms) {
			$this->form->addCheckbox("terms", $agree)
					->setRequired("Please read our terms & conditions");
		}

		$this->form->addSubmit('send', $this->formData['send'])
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        if ($form->values->password !== $form->values->password2) {
            $form->addError($this->translator->translate("Passwords are different."));
        } else if ($this->service->findByAuthMail($form->values->mail)->id !== NULL) {
            $form->addError($this->translator->translate("This e-mail is already used. You can try send lost password."));
        } else {
            $adapter = new \Model\Entity\AdapterUserEntity;
            $adapter->id = $form->values->mail;
            $adapter->username = $form->values->mail;
            $adapter->mail = $form->values->mail;
            $adapter->source = \Model\Entity\AdapterUserEntity::SOURCE_APP;
            $adapter->verified = FALSE;
            $adapter->lang = $this->lang;
            $user = $this->service->createAppAccount($adapter, $form->values->password, TRUE, $this->lang, $this->presenter);
            $this->presenter->redirect("Sign:verify", $user->id);
        }
    }
    
    public function render()
    {
        $this->template->loginLink = "#login";
		$this->template->privacy = $this->getHtmlPolicy();
        parent::render();
    }
	
	private function getHtmlPolicy()
	{
		$container = \Nette\Utils\Html::el();
        $link = \Nette\Utils\Html::el('a')
                ->href($this->presenter->link(":Front:Content:terms"))
                ->setText($this->translator->translate("Privacy Policy"))
                ->target("_blank");
        $pre = \Nette\Utils\Html::el()
                ->setText('* ' . 
						$this->translator->translate("We don't share your personal info with anyone.") 
						. " Check out our ");
		$post = \Nette\Utils\Html::el()
                ->setText(' ' . $this->translator->translate("for more information."));
		return $container->add($pre)
				->add($link)
				->add($post);
	}
    
    public function renderInline()
    {
		$this->setTemplateName('createAccountFormInline');
        $this->render();
    }
    
    public function renderRow()
    {
		$this->setTemplateName('createAccountFormRow');
        $this->render();
    }

}
