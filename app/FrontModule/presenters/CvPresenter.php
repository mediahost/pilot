<?php

namespace FrontModule;

use Nette\Application\UI\Form,
    Nette\Http\Url,
    Model\Entity\ActionLogEntity;

/**
 * Class CvPresenter
 * @package FrontModule
 *
 * @author Petr Poupě
 */
class CvPresenter extends BasePresenter
{

    /** @persistent */
    public $panel = "preview";
    protected $step = 2;
    protected $steps = array();

    /** @var \Model\Entity\CvEntity */
    protected $cv;
    protected $cvs;

    public function startup()
    {
        parent::startup();
        $this->checkAccess("cv", "view");
        $this->steps = \Model\Entity\CvEntity::steps();
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->steps = $this->steps;
    }

    public function actionAdd()
    {
        $userId = $this->user->getId();
        $cv = $this->context->cv->create($userId);
        $this->context->actionlogs->log(ActionLogEntity::NEW_CV, $userId, array($cv->id));
        $this->flashMessage("New CV was created", "success");
        $this->redirect("default");
    }

    public function actionClone($id)
    {
        $userId = $this->user->getId();
        $cv = $this->context->cv->create($userId, $id);
        $this->context->actionlogs->log(ActionLogEntity::NEW_CV, $userId, array($cv->id));
        $this->flashMessage("New CV was cloned", "success");
        $this->redirect("default");
    }

    public function actionDelete($cv)
    {
        /* @var $cvService \Model\Service\CvService */
        $cvService = $this->context->cv;

        $userId = $this->user->getId();
        $cv = $cvService->getCv($cv, $userId);

        if ($cv && $this->context->cv->delete($cv)) {
            $this->context->actionlogs->log(ActionLogEntity::DELETE_CV, $userId, array($cv->id, $cv->name));
            $this->flashMessage("Cv '{$cv->name}' was deleted", "success");
        } else {
            $this->flashMessage("Such CV wasn't found", "success");
        }

        $this->redirect("default");
    }

    public function actionDefault($cv = NULL, $step = NULL)
    {
		if ($step == 1) {
			$this->redirect('this', array('cv' => $cv, 'step' => 5));
		}
		
        /* @var $cvService \Model\Service\CvService */
        $cvService = $this->context->cv;

        /// TABS ///////////////////////////////////////////////////////////////
        // load CVs List for tabs
        $this->cvs = $cvService->findUsersCv($this->user->getId());
        if (empty($this->cvs)) {
            $this->redirect("add");
        }

        $this->template->changeNameForm = array();
        foreach ($this->cvs as $cvItem) {
            $formChange = new Form($this, "changeName" . $cvItem->id);
            $formChange->getElementPrototype()->class = "ajax";

            $formChange->addHidden('id', $cvItem->id);
            $formChange->addText('name')->setDefaultValue($cvItem->name);

            $formChange->onSuccess[] = $this->changeNameSucceeded;
            $this->template->changeNameForm[$cvItem->id] = $formChange;
        }
        ////////////////////////////////////////////////////////////////////////
        // load actual CV
        $this->cv = $cvService->getCv($cv, $this->user->getId());
        if ($this->cv === FALSE) {
            $this->flashMessage("This CV isn't exists.", "warning");
            $this->redirect("Homepage:");
        }

        $user = $this->context->users->find($this->user->getId());
        if ($user->gender !== NULL && $this->cv->gender === NULL) {
            $this->cv->gender = $user->gender === "male" ? 1 : 2;
        }
        if ($user->birthday !== NULL && $this->cv->birthday === NULL) {
            $this->cv->birthday = $user->birthday;
        }
        if ($user->mail !== NULL && $this->cv->email === NULL) {
            $this->cv->email = $user->mail;
        }

        // set current position
        $this->step = $cvService->setCurrentPosition($this->cv, $step);

        $this->template->form = $this['step' . $this->step];
        $this->template->cv = $this->cv;

        $events = array();
        foreach ($this->cv->getWorks() as $work) {
            if ($work->from === NULL && $work->to === NULL) {
                continue;
            } else {
                if ($work->from !== NULL) {
                    $from = $work->from;
                }
                $class = NULL;
                $subscribe = NULL;
                if ($work->to === NULL) {
                    $to = new \Nette\DateTime;
                    $class = "tillnow";
                    $subscribe = $this->translator->translate("till now");
                } else {
                    $to = $work->to;
                }

                $position = $work->company . " - " . $work->position . ($subscribe === NULL ? "" : " (" . $subscribe . ")");
                $events[] = new \Model\Entity\TimelineEventEntity($from, $to, $position, $class, $work->id);
            }
        }
        $this->template->events = $events;

        $this->invalidateControl("forms");
        $this->invalidateControl("formList");
        $this->invalidateControl("preview");
        $this->invalidateControl("flash");
    }

    public function renderDefault($cv = NULL, $panel = NULL)
    {
		if ($this->step == 1) {
			$this->redirect('this', array('cv' => $cv, 'step' => 5));
		}
        if ($this->isAjax()) {
            /* @var $cvService \Model\Service\CvService */
            $cvService = $this->context->cv;
            $this->cv = $cvService->getCv($cv, $this->user->getId());
            $this->cvs = $cvService->findUsersCv($this->user->getId());
        }

        switch ($panel) {
            case "tips":
                /* @var $hintService \Model\Service\HintService */
                $hintService = $this->context->hints;
                $this->template->hint = $hintService->getHint($this->step, $this->lang)->getText();
                break;

            case "preview":
            default:
                break;
        }
        $this->panel = $panel;

        $templateName = $this->cv->templateName;
        if ($templateName === NULL) {
            $templateName = "default";
        }

        $this->template->cv = $this->cv;
        $this->template->cvs = $this->cvs;

        $this->template->step = $this->step;
        $this->template->preview = $this->panel;
        $this->template->headline = $this->steps[$this->step];
        $this->template->cvTemplatePath = "../Pdf/templates/{$templateName}.latte";
        $this->template->cvTemplateName = $templateName;

        if ($this->step == 1) {
            $this->template->headline2 = "Photo";
            $this->template->form2 = $this['step12'];
        }

        $this->template->translator = $this->translator;
        $this->template->registerHelper("CvFullName", "\Model\Entity\CvEntity::helperGetFullName");
        $this->template->registerHelper("CvYearsOld", "\Model\Entity\CvEntity::helperGetYearsOld");
        $this->template->registerHelper("CvAdress", "\Model\Entity\CvEntity::helperGetAddress");
        $this->template->registerHelper("CvLanguage", "\Model\Entity\CvEntity::helperGetLanguage");
        $this->template->registerHelper("CvNationality", "\Model\Entity\CvEntity::helperGetNationality");
        $this->template->registerHelper("CvSector", "\Model\Entity\CvEntity::helperGetSector");
        $this->template->registerHelper("CvLicenses", "\Model\Entity\CvEntity::helperGetLicenses");
        $this->template->registerHelper("CvLangLevel", "\Model\Entity\CvLangEntity::helperGetScale");
        $this->template->registerHelper("CvLangLevelHtm1", "\Model\Entity\CvLangEntity::helperGetScaleHtm1");
        $this->template->registerHelper("CvEducInstitution", "\Model\Entity\CvEducEntity::helperGetInstitution");
        $this->template->registerHelper("CvEducDates", "\Model\Entity\CvEducEntity::helperGetDates");
        $this->template->registerHelper("CvWorkDates", "\Model\Entity\CvWorkEntity::helperGetDates");
        $this->template->registerHelper("CvWorkReferences", "\Model\Entity\CvWorkEntity::helperGetReferences");
        $this->template->registerHelper("currency", "\CommonHelpers::currency");


        $this->template->registerHelper("CvItSkillLang", "\Model\Entity\CvItScaleEntity::helperGetLanguage");
        $this->template->registerHelper("CvItSkillLScale", "\Model\Entity\CvItScaleEntity::helperGetScale");
    }

    public function changeNameSucceeded(Form $form)
    {
        $this->cv = $this->context->cv->changeName($form->values->id, $form->values->name);

        if ($this->ajax) {
            $this->validateControl();
            $this->invalidateControl("tabs");
            $this->invalidateControl("preview");
        } else {
            $this->redirect('this');
        }
    }

    /**
     * Form for Personal Details
     * @return Form
     */
    protected function createComponentStep1()
    {
        $form = new \AppForms\Step1Form($this, $this->context->cv, $this->cv, 1);

        return $form;
    }

    /**
     * Form for Carrer objective
     * @return Form
     */
    protected function createComponentStep2()
    {
        $form = new \AppForms\Step2Form($this, $this->context->cv, $this->cv, 2);

        return $form;
    }

    /**
     * Form for Desired Employment
     * @return Form
     */
    protected function createComponentStep3()
    {
        $form = new \AppForms\Step3Form($this, $this->context->cv, $this->cv, 3);

        return $form;
    }

    /**
     * Form for Career Sumarry
     * @return Form
     */
    protected function createComponentStep4()
    {
        $form = new \AppForms\Step4Form($this, $this->context->cv, $this->cv, 4);

        return $form;
    }

    /**
     * Form for Work Experience
     * @return Form
     */
    protected function createComponentStep5()
    {
        $form = new \AppForms\Step5Form($this, $this->context->cv, $this->cv, 5);

        return $form;
    }

    /**
     * Form for Other Personal Experience
     * @return Form
     */
    protected function createComponentStep6()
    {
        $form = new \AppForms\Step5Form($this, $this->context->cv, $this->cv, 6);

        return $form;
    }

    /**
     * Form for Education
     * @return Form
     */
    protected function createComponentStep7()
    {
        $form = new \AppForms\Step7Form($this, $this->context->cv, $this->cv, 7);

        return $form;
    }

    /**
     * Form for Language Skills
     * @return Form
     */
    protected function createComponentStep8()
    {
        $form = new \AppForms\Step8Form($this, $this->context->cv, $this->cv, 8);

        return $form;
    }

    /**
     * Form for Personal Skills
     * @return Form
     */
    protected function createComponentStep9()
    {
        $form = new \AppForms\Step9Form($this, $this->context->cv, $this->cv, 9);

        return $form;
    }

    /**
     * Form for Additional Information
     * @return Form
     */
    protected function createComponentStep10()
    {
        $form = new \AppForms\Step10Form($this, $this->context->cv, $this->cv, 10);

        return $form;
    }

    /**
     * Form for Additional Information
     * @return Form
     */
    protected function createComponentStep11()
    {
        $form = new \AppForms\Step11Form($this, $this->context->cv, $this->cv, 11);

        return $form;
    }

    /**
     * Form for Profile Pictures (Personal info)
     * @return Form
     */
    protected function createComponentStep12()
    {
        $form = new \AppForms\Step12Form($this, $this->context->cv, $this->cv, 12);

        return $form;
    }
	
    protected function createComponentDefault()
    {
        $form = new \AppForms\Step1DefaultForm($this, $this->context->cv, $this->cv, FALSE);

        return $form;
    }

}
