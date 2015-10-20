<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ProfesiaService;

/**
 * Select Job Tags Form
 *
 * @author Petr Poupě
 */
class SelectJobTagsForm extends AppForms
{

    /** @var ProfesiaService */
    private $profesia;

    public function __construct(Presenter $presenter, ProfesiaService $profesia)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->profesia = $profesia;
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "ajax";
        
        $tags = $this->profesia->getTags($this->lang);
        $this->form->addMultiSelect('tags', 'Tags', $tags)
                ->setAttribute("data-placeholder", $this->translator->translate("Search by tags"))
                ->setAttribute("data-no_results_text", $this->translator->translate("No results match"))
                ->getControlPrototype()->setClass("chosen-select");
        
        $jobFilter = $this->presenter->context->session->getSection('jobFilter');
        $this->form["tags"]->setDefaultValue($jobFilter->tags);

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $jobFilter = $this->presenter->context->session->getSection('jobFilter');
        $jobFilter->tags = $form->values->tags;
        $this->presenter->invalidateControl("jobsList");
    }

}

?>
