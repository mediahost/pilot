<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ProfesiaService;

/**
 * Edit Job Tags Form
 *
 * @author Petr Poupě
 */
class EditJobTagsForm extends AppForms
{

    /** @var ProfesiaService */
    private $profesia;

    public function __construct(Presenter $presenter, ProfesiaService $profesia)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->profesia = $profesia;
    }

    public function setDefaults($id, array $tags = array())
    {
        $form = $this->getComponent($this->name);
        $form->setDefaults(array(
            'id' => $id,
            'tags' => \CommonHelpers::concatArray($tags, ", "),
        ));
    }

    protected function createComponent($name)
    {
        $this->form->addHidden("id");
        $this->form->addTextArea('tags', 'Tags', 100, 5);

        $this->form->addSubmit('save', 'Save');

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $tags = preg_split("~[,\s]~", $form->values->tags, NULL, PREG_SPLIT_NO_EMPTY);
        $job = $this->profesia->find($form->values->id);
        $job->tags = $tags;
        $this->profesia->save($job);
        
        $this->presenter->redirect("this");
    }

}

?>
