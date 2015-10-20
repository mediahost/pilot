<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\CompanyService,
    Model\Service\CvService,
    Nette\Http\Session,
    Model\Entity\CvItScaleEntity;

/**
 * Candidates Filter Form
 *
 * @author Petr Poupě
 */
class CandidatesFilterForm extends AppForms
{

    /** @var CompanyService */
    private $company;

    /** @var CvService */
    private $cvService;

    /** @var Session */
    private $session;
    private $selectedValues = array();
    private $skills = array();

    public function __construct(Presenter $presenter, CompanyService $company, CvService $cvService, Session $session)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->company = $company;
        $this->cvService = $cvService;
        $this->session = $session;
        $this->skills = $this->cvService->buildSkills();

        $this->setSelectedValues();
    }

    private function setDefaults($erase = FALSE)
    {
        $candidatesFilter = $this->session->getSection('candidatesFilter');
        $this->form->setDefaults(array(
            'text' => $candidatesFilter->text,
            'skills' => is_array($candidatesFilter->skills) ? $candidatesFilter->skills : array(),
                ), $erase);
    }

    private function setSelectedValues()
    {
        $candidatesFilter = $this->session->getSection('candidatesFilter');
        $this->selectedValues["text"] = $candidatesFilter->text;
        $this->selectedValues["skills"] = array();
        if (is_array($candidatesFilter->skills)) {
            foreach ($candidatesFilter->skills as $skillId => $skillHash) {
                $this->selectedValues["skills"][$skillId] = $this->convertSkill($skillId, $skillHash);
            }
        }
    }

    private function convertSkill($skillId, \Nette\ArrayHash $skillHash)
    {
        $convertedArray = array(
            "id" => $skillId,
            "name" => $this->findSkillName($skillId),
            "level" => $skillHash->scale,
            "year" => (int) $skillHash->number,
        );
        return \Nette\ArrayHash::from($convertedArray);
    }

    private function findSkillName($skillId)
    {
        foreach ($this->skills as $categoryName => $group) {
            foreach ($group as $groupName => $skills) {
                if (array_key_exists($skillId, $skills)) {
                    return $skills[$skillId];
                }
            }
        }
    }

    public function handleRemoveCriterion($criterion)
    {
        $candidatesFilter = $this->session->getSection('candidatesFilter');
        if ($criterion === "text") {
            $candidatesFilter->text = NULL;
            $this->selectedValues["text"] = NULL;
        } else {
            if (is_array($candidatesFilter->skills) && array_key_exists($criterion, $candidatesFilter->skills)) {
                unset($candidatesFilter->skills[$criterion]);
            }
            if (array_key_exists($criterion, $this->selectedValues["skills"])) {
                unset($this->selectedValues["skills"][$criterion]);
            }
        }

        if ($this->presenter->isAjax()) {
            $this->invalidateControl("filter");
            $this->presenter->invalidateControl("candidates");
        } else {
            $this->presenter->redirect("this");
        }
    }

    public function handleResetCriterion()
    {
        $candidatesFilter = $this->session->getSection('candidatesFilter');
        $candidatesFilter->text = NULL;
        $candidatesFilter->skills = array();
        $this->selectedValues["text"] = NULL;
        $this->selectedValues["skills"] = array();

        if ($this->presenter->isAjax()) {
            $this->invalidateControl("filter");
            $this->presenter->invalidateControl("candidates");
        } else {
            $this->presenter->redirect("this");
        }
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "ajax";
        $this->form->setMethod("post");

        $this->form->addText('text', 'Contain')
                ->setAttribute("placeholder", "input what you search")
                ->setAttribute("class", "search");

        $skills = $this->skills;
        $scale = CvItScaleEntity::scale();
        $skillsContainer = $this->form->addContainer("skills");
        foreach ($skills as $skillCategory => $skillGroups) {
            foreach ($skillGroups as $skillGroup) {
                foreach ($skillGroup as $skillId => $skillName) {
                    $container = $skillsContainer->addContainer($skillId);
                    $container->addSelect('scale', 'Scale', $scale)
                            ->setAttribute("class", "slider live");
                    $container->addText('number', "Years")
                            ->setAttribute("type", "number")
                            ->setAttribute("min", "0")
                            ->setAttribute("class", "number")
                            ->setDefaultValue(0);
                }
            }
        }

        $this->form->addSubmit('search', 'Search');

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $candidatesFilter = $this->session->getSection('candidatesFilter');

        $candidatesFilter->text = !empty($form->values->text) ? $form->values->text : NULL;
        $this->selectedValues["text"] = $candidatesFilter->text;

        $skills = array();
        foreach ($form->values->skills as $skillId => $skillHash) {
            if ($skillHash->scale !== "" && $skillHash->scale !== NULL) {
                $skills[$skillId] = $skillHash;
                $this->selectedValues["skills"][$skillId] = $this->convertSkill($skillId, $skillHash);
            }
        }
        $candidatesFilter->skills = $skills;

        if ($this->presenter->isAjax()) {
            $this->invalidateControl("selected");
            $this->presenter->invalidateControl("candidates");
        } else {
            $this->presenter->redirect("this");
        }
    }

    public function render()
    {
        $this->template->selectedText = $this->selectedValues["text"];
        $this->template->selectedSkills = $this->selectedValues["skills"];
        $this->template->skills = $this->skills;
        parent::render();
    }

}
