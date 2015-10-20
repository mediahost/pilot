<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\CompanyService;

/**
 * Company Users Grid
 *
 * @author Petr Poupě
 */
class CompanyUsersGrid extends Grid
{

    /** @var DibiFluent */
    private $users;

    /** @var Presenter */
    private $presenter;

    /** @var UserService */
    protected $service;

    /** @var ITranslator */
    protected $translator;

    /**
     * @param DibiFluent $users
     * @param Presenter $presenter
     * @param ITranslator $translator
     * @param CompanyService $service
     */
    public function __construct(DibiFluent $users, Presenter $presenter, ITranslator $translator, CompanyService $service)
    {
        parent::__construct();
        $this->users = $users;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
    }

    /**
     * @param $presenter
     */
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DibiFluentDataSource($this->users, "id");
        $this->setDataSource($source);

        //defaultní řazení
        $this->setDefaultOrder("id ASC");

        $this->setTranslator($this->translator);
        $this->setWidth('100%');

        $self = $this;

        $this->addColumn('id', $this->translator->translate('Id'), '55px');
        $this->addColumn('username', $this->translator->translate('Username'), '300px')->setTextFilter();
        $this->addColumn('company_name', $this->translator->translate('Company name'), '300px')->setTextFilter();
        $this->addColumn('email', $this->translator->translate('Mail'), '300px');

        // buttons
        $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(
                        function ($row) use ($self) {
                    return $self->presenter->link("edit", $row['id']);
                }
                )
                ->setAjax(FALSE);
    }

}
