<?php

namespace CompanyModule;

/**
 * Class ServicePresenter
 * @package CompanyModule
 *
 * @author Petr Poupě
 */
class ServicePresenter extends BasePresenter
{

    public function startUp()
    {
        parent::startUp();
        $this->redirect("Homepage:");
    }

    public function actionDefault()
    {
        $this->resaveCandidates();
        $this->terminate();
    }

    private function resaveCandidates()
    {
        $candidates = $this->candidates->getCandidates();

        foreach ($candidates as $candidate) {
            $cv = $this->cvs->getCv($candidate->cvId);
            $this->cvs->save($cv);
        }
    }

}
