<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\JobCategoryService;

/**
 * Description of JobCategoryForm
 *
 * @author Radim Křek
 */
class JobCategoryForm extends AppForms
{

    /** @var JobService * */
    private $service;

    public function __construct(Presenter $presenter, JobCategoryService $service)
    {
        parent::__construct(get_class($this), $presenter);
        $this->service = $service;
    }

    public function setDefaults($row)
    {
        $form = $this->getComponent($this->name);

        $data = array();
        if ($row) {
			$data["id"]   = $row["id"];
            $data["lang"] = $row["lang"];
            $data["name"] = $row["name"];
        }
        $form->setDefaults($data);
    }

    public function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        
        $form = $this->form;
		$form->addHidden('id');
        $langs = array(
            "en" => "EN",
            "cs" => "CZ",
            "sk" => "SK",
        );
        $form->addSelect('lang', 'Language', $langs)
                ->setDefaultValue($this->lang);
        $form->addText('name', 'Category');
        $this->form->addSubmit('send', 'Save');

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $values = $form->getValues();
		
		$entity = new \Model\Entity\JobCategoryEntity($values);

        $this->service->save($entity);

		$this->redirect('this');       
    }

}
