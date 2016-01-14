<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\JobService;

/**
 * Description of JobsGrid
 *
 * @author Radim Křek
 */
class JobsGrid extends Grid
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
    
    /** @var Nette\Callback */
    protected $viewLinkFactory;
    
    /** @var Nette\Callback */
    protected $editLinkFactory;
    
    /** @var Nette\Callback */
    protected $deleteLinkFactory;
    
    /** @var Nette\Callback */
    protected $deleteCallback;

    /** @var int */
    protected $companyId;
    
    public function __construct(DibiFluent $jobs, Presenter $presenter, ITranslator $translator, JobService $service, $lang)
    {
        parent::__construct();
        $this->jobs = $jobs;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
        $this->lang = $lang;
    }
    
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        $this->jobs->where('company_id = %i', $companyId);
    }
    
    public function setViewLinkFactory($viewLinkFactory)
    {
        $this->viewLinkFactory = $viewLinkFactory;
    }

    public function setEditLinkFactory($editLinkFactory)
    {
        $this->editLinkFactory = $editLinkFactory;
    }

    public function setDeleteLinkFactory($deleteLinkFactory)
    {
        $this->deleteLinkFactory = $deleteLinkFactory;
    }
    
    public function setDeleteCallback($deleteCallback)
    {
        $this->deleteCallback = $deleteCallback;
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

        $translator = $this->translator;

        $this->addColumn('name', $this->translator->translate('Job title'))
                ->setTableName('job.name')
                ->setTextFilter();

        $this->addColumn('company', $this->translator->translate('Company'))
                ->setTextFilter();
        
        if (!isset($this->companyId)) {
            $this->addColumn('companyUser', $this->translator->translate('Company user'));        
        }

        $this->addColumn('ref', $this->translator->translate('Job administrator'), NULL)
                ->setTextFilter();
        
        $this->addColumn('candidates', $this->translator->translate('Matched candidates'))
            ->setRenderer(function($row) use ($presenter) {
                return \Nette\Utils\Html::el('a')
                    ->setText($row->candidates)
                    ->href($presenter->link(':Admin:Jobs:candidates', $row->id));
            });
        
        $this->addColumn('applyed_candidates', $this->translator->translate('Applied candidates'))
            ->setRenderer(function($row) use ($presenter) {
                return $row->applyed_candidates . ' (' . (int) $row->applyed_completed_candidates . ')';
            });

        $self = $this;
        // buttons
        if (isset($this->viewLinkFactory)) {
            $this->addButton("view", $this->translator->translate("View"))
                ->setClass("view")
                ->setTarget('_blank')
                ->setLink($this->viewLinkFactory)
                ->setAjax(FALSE);
        }
        
        if (isset($this->editLinkFactory)) {
            $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink($this->editLinkFactory)
                ->setAjax(FALSE);
        }

        if (isset($this->deleteLinkFactory)) {
            
            $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink($this->deleteLinkFactory)
                ->setConfirmationDialog(function($row) use ($translator) {
                    return $translator->translate("Are you sure to delete job '%s'?", $row['name']);
                })
                ->setAjax(FALSE);
        }

        $this->addAction("delete", $this->translator->translate("Delete"))
                ->setCallback(function($id) use ($self) {
                    return $self->deleteCallback->invokeArgs(array($id));
                })
                ->setConfirmationDialog($this->translator->translate("Are you sure to delete all selected pages?"));
    }

}
