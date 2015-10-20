<?php
namespace AppForms;

use \Nette\Application\UI\Form,
    \Nette\Application\UI\Presenter,
	Model\Entity\LocationEntity,
	Model\Service\LocationService;
		

/**
 * Description of EditLocationForm
 *
 * @author Radim Křek
 */
class EditLocationForm extends AppForms
{
	/** @var LocationService */
    private $service;
	
	public function __construct(Presenter $presenter, LocationService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }
	
	public function setDefaults(LocationEntity $entity)
	{
		$form = $this->getComponent($this->name);
		$form->setDefaults($entity->to_array());
	}
	
	public function createComponent($name)
	{
		$this->setStyle(AppForms::STYLE_METRONIC);
        $form = $this->form;
		
		$form->addHidden('id');
		$form->addText('name', 'Name');
		$form->addSelect('parent_id', 'Parent location', $this->getLocations());
		
		$this->form->addSubmit('back', 'Save & Back');
        $this->form->addSubmit('send', 'Save');
		
		$this->form->onSuccess[] = $this->onSuccess;
        return $this->form;		
	}
	
	public function onSuccess(Form $form)
	{
		$values = $form->getValues();
		if (intval($values->parent_id) == -1)
		{
			$values->parent_id = NULL;
		}
		
		$entity = new LocationEntity($values);
		
		$this->service->save($entity);
		
		if ($form['back']->submittedBy) {
            $this->presenter->redirect("locations");
        } else {
            $this->presenter->redirect("this");
        }
	}
	
	private function getLocations()
    {
        $parents = $this->service->find('parent_id IS', NULL);
        $items = array(-1 => '-------');
        foreach ($parents as $parent)
		{
			$items[$parent->id]=$parent->name;
		}
        return $items;
    }
}
