<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\CvService;

/**
 * ChangeCvTemplateForm Form
 *
 * @author Petr Poupě
 */
class ChangeCvTemplateForm extends AppForms
{

    private $templates;

    /** @var CvService */
    private $service;

    public function __construct(Presenter $presenter, CvService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;

        $this->templates = array(
            "default" => "Default",
            "standard1" => "Standard 1",
            "standard2" => "Standard 2",
            "euroPass" => "EuroPass"
        );
    }

    public function setDefaults($cv, $templateName = NULL)
    {
        if ($templateName === NULL) {
            $templateName = "default";
        }
        parent::setDefaultValues(array(
            'cv' => (int) $cv,
            'template' => $templateName,
        ));
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled innerPage";

        $this->form->addHidden('cv');

        $this->form->addRadioList('template', "Choose a template", $this->templates);

        $this->form->addSubmit('send', 'Change')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $this->service->changeTemplateName($form->values->cv, $form->values->template);
        $this->presenter->redirect("Cv:", array(
            'cv' => $form->values->cv,
            'templateName' => $form->values->template,
        ));
    }

}

?>
