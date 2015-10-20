<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\CvService;

/**
 * SelectNewCvType Form
 *
 * @author Petr Poupě
 */
class SelectNewCvTypeForm extends AppForms
{

    const TYPE_ADD = 'add';
    const TYPE_CLONE = 'clone';

    private $cvId;

    /** @var CvService */
    private $service;

    public function __construct(Presenter $presenter, CvService $service, $cvId)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
        $this->cvId = $cvId;
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled innerPage";

        $types = array(
            self::TYPE_ADD => "new from scratch",
            self::TYPE_CLONE => "copy from current CV",
        );
        $this->form->addRadioList('type', "Create", $types)
                ->setDefaultValue(self::TYPE_ADD);

        $this->form->addSubmit('create', 'Create')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        if ($form->values->type === self::TYPE_CLONE) {
            $this->presenter->redirect("Cv:clone", $this->cvId);
        } else {
            $this->presenter->redirect("Cv:add");
        }
    }

}

?>
