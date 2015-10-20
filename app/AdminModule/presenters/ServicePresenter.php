<?php

namespace AdminModule;

/**
 * Service Presenter
 *
 * @author Petr Poupě
 */
class ServicePresenter extends BasePresenter
{

	public function startup()
	{
		parent::startup();
		$this->checkAccess("service", "access");
	}

// <editor-fold defaultstate="collapsed" desc="actions">

	public function actionDefault()
	{
		
	}

	public function handleActualizeCvs()
	{
		foreach ($this->context->users->findAll() as $user) {
			$defaultCv = $this->context->cv->getDefaultCv($user->id);
			foreach ($this->context->cv->findUsersCv($user->id) as $rowCv) {
				if ($rowCv->id != $defaultCv->id) {
					$cv = $this->context->cv->findUserItem($rowCv->id, $user->id);
					$cv->importProfileData($defaultCv);
					$this->context->cv->save($cv);
				}
			}
		}
		$this->redirect('this');
	}

// </editor-fold>
}
