<?php

namespace FrontModule;

use Model\Entity\AdapterUserEntity;
use Model\Service\UserService;
use Nette\Forms\Form;
use Nette\Utils\Validators;

class RegistrationPresenter extends BasePresenter
{

	/**
	 * @autowire
	 * @var UserService
	 */
	protected $userService;

	public function actionDefault()
	{
		$form = new Form();
		$form->addText('email');
		$form->addPassword('password');

		if (!$form->submitted) {
		    $this->error();
		}

		$values = $form->values;
		if (!Validators::isEmail($values->email)) {
		    $this->error();
		}
		if ($this->userService->findByAuthMail($values->email)->id !== NULL) {
			$this->error();
		}
		if ($values->password === "") {
		    $this->error();
		}

		$adapter = new AdapterUserEntity();
		$adapter->id = $values->email;
		$adapter->username = $values->email;
		$adapter->mail = $values->email;
		$adapter->source = AdapterUserEntity::SOURCE_APP;
		$adapter->verified = FALSE;
		$adapter->lang = $this->lang;
		$user = $this->userService->createAppAccount($adapter, $values->password, TRUE, $this->lang, $this);
		$this->redirect("Sign:verify", $user->id);
	}

}