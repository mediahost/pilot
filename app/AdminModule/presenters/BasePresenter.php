<?php

namespace AdminModule;

/**
 * AdminBasePresenter
 *
 * @author Petr Poupě
 */
abstract class BasePresenter extends \BasePresenter
{

	public function startup()
	{
		$this->langs = $this->context->langs->getBackLanguages();

		parent::startup();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->allowedFakeAccount = $this->user->isAllowed("service", "access");
		$this->template->allowedService = $this->user->isAllowed("service", "access");
	}

}
