<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\JobCategoryService;

/**
 * Description of JobCategoriesGrid
 *
 * @author Petr Poupě
 */
class JobCategoriesGrid extends Grid
{

    protected $lang;

    /** @var DibiFluent */
    private $jobs;

    /** @var Presenter */
    private $presenter;

    /** @var JobsService */
    protected $service;

    /** @var ITranslator */
    protected $translator;

    public function __construct(DibiFluent $jobs, Presenter $presenter, ITranslator $translator, JobCategoryService $service, $lang)
    {
        parent::__construct();
        $this->jobs = $jobs;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
        $this->lang = $lang;
    }

    public function handleDelete($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleDelete($item);
            }
        } else {
            $entity = $this->service->find($id, $this->lang);
            if ($this->service->delete($entity["id"])) {
                $this->flashMessage($this->translator->translate("Category '%s' was succesfull deleted", $entity["name"]), "success");
            } else {
                $this->flashMessage($this->translator->translate("Category '%s' wasn't deleted", $entity["name"]), "danger");
            }
        }
    }

    protected function configure($presenter)
    {
        //Vytvoříme si zdroj dat pro Grid
        //Při výběru dat vždy vybereme id
        //Předáme zdroj
        $source = new NiftyGrid\DibiFluentDataSource($this->jobs, "id");
        $this->setDataSource($source);

        //defaultní řazení
        $this->setDefaultOrder("id DESC");

        $this->setTranslator($this->translator);
        $this->setWidth('100%');

        $self = $this;
        $translator = $this->translator;

        $this->addColumn('name', $this->translator->translate('Name'), '300px')
                ->setTextFilter();
        
        // buttons
        $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("editCategory", $row['id']);
                })
                ->setAjax(FALSE);

        $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("deleteCategory!", $row['id']);
                })
                ->setConfirmationDialog(function($row) use ($translator) {
                    return $translator->translate("Are you sure to delete category '%s'?", $row['name']);
                })
                ->setAjax(FALSE);

        $this->addAction("delete", $this->translator->translate("Delete"))
                ->setCallback(function($id) use ($self) {
                    return $self->handleDelete($id);
                })
                ->setConfirmationDialog($this->translator->translate("Are you sure to delete all selected pages?"));
    }

}
