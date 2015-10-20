<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\LocationService;

/**
 * Description of LocationsGrid
 *
 * @author Radim Křek
 */
class LocationsGrid extends Grid
{
	protected $lang;

    /** @var DibiFluent */
    private $locations;

    /** @var Presenter */
    private $presenter;

    /** @var LocationService */
    protected $service;

    /** @var ITranslator */
    protected $translator;
	
	/** @var boolean */
	protected $main;

    public function __construct(DibiFluent $locations, Presenter $presenter, ITranslator $translator, LocationService $service, $lang, $main=TRUE)
    {
        parent::__construct();
        $this->locations = $locations;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
        $this->lang = $lang;
		$this->main = $main;
    }
	
	public function handleDelete($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleDelete($item);
            }
        } else {
            $entity = $this->service->findById($id);
            if ($this->service->delete($entity["id"])) {
                $this->flashMessage($this->translator->translate("Location '%s' was succesfull deleted", $entity["name"]), "success");
            } else {
                $this->flashMessage($this->translator->translate("Location '%s' wasn't deleted", $entity["name"]), "danger");
            }
        }
    }
	
	protected function configure($presenter)
    {
        //Vytvoříme si zdroj dat pro Grid
        //Při výběru dat vždy vybereme id
        //Předáme zdroj
        $source = new NiftyGrid\DibiFluentDataSource($this->locations, "id");
        $this->setDataSource($source);

        //defaultní řazení
        $this->setDefaultOrder("id ASC");

        $this->setTranslator($this->translator);

		$this->setWidth('100%');

        $self = $this;
        $translator = $this->translator;

		if ($this->main)
		{
			$this->addColumn('name', $this->translator->translate('Name'), '300px')
                ->setTextFilter();
			$this->addSubGrid("subLocations", "Show Sub-Locations")
			 ->setGrid(new LocationsGrid($this->service->getSubDataGrid($this->activeSubGridId), $this->presenter, $this->translator, $this->service, $this->lang, FALSE));
		}
		else
		{
			$this->addColumn('name', $this->translator->translate('Name'), '300px');
		}
        
        // buttons
        $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("editLocation", $row['id']);
                })
                ->setAjax(FALSE);
		if ($this->main)
		{
			$this->addButton("add", $this->translator->translate("Add SubLocation"))
                ->setClass("grid-add-row")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("editLocation", array('parent'=>$row['id']));
                })
                ->setAjax(FALSE);
		}		
		

        $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("deleteLocation!", $row['id']);
                })
                ->setConfirmationDialog(function($row) use ($translator) {
                    return $translator->translate("Are you sure to delete location '%s'?", $row['name']);
                })
                ->setAjax(FALSE);

        $this->addAction("delete", $this->translator->translate("Delete"))
                ->setCallback(function($id) use ($self) {
                    return $self->handleDelete($id);
                })
                ->setConfirmationDialog($this->translator->translate("Are you sure to delete all selected pages?"));
    }
}
