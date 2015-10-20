<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\UserService,
    Model\Mapper\Dibi\UserDibiMapper;

/**
 * Users Grid
 *
 * @author Stephen Monaghan
 */
class UsersGrid extends Grid
{

    /** @var DibiFluent */
    private $users;

    /** @var Presenter */
    private $presenter;

    /** @var UserService */
    protected $service;

    /** @var ITranslator */
    protected $translator;
    
    /** @var Model\Service\TagService */
    protected $tagService;

    public function __construct(DibiFluent $users, Presenter $presenter, ITranslator $translator, UserService $service, \Model\Service\TagService $tagService)
    {
        parent::__construct();
        $this->users = $users;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
        $this->tagService = $tagService;
    }

    protected function configure($presenter)
    {
        $source = new NiftyGrid\DibiFluentDataSource($this->users, "id");
        $this->setDataSource($source);

        //defaultní řazení
        $this->setDefaultOrder("created DESC, mail ASC");

        $this->setTranslator($this->translator);
        $this->setWidth('100%');

        $self = $this;
        $presenter = $this->presenter;
        $translator = $this->translator;

        $this->addColumn('mail', $translator->translate('Mail'), '250px')
                ->setTextFilter();

//        $this->addColumn('username', $translator->translate('Username'), '100px')
//                ->setTextFilter();

        $this->addColumn('first_name', $translator->translate('First name'), '100px')
                ->setTextFilter();

        $this->addColumn('last_name', $translator->translate('Last name'), '100px')
                ->setTextFilter();

        $this->addColumn('country', $translator->translate('Country'), '90px')
                ->setTextFilter();

        $this->addColumn('source_arr', $translator->translate('Accounts'), '150px')
                ->setTableName("source")
                ->setSelectFilter(array(
                    "app" => "application",
                    "facebook" => "facebook",
                    "twitter" => "twitter",
                ))
                ->setRenderer(function ($row) {
                    $sourceArr = preg_split("~\,~", $row["source_arr"]);
                    $sources = array();
                    foreach ($sourceArr as $source) {
                        $sources[$source] = $source;
                    }
                    sort($sources);
                    return CommonHelpers::concatStrings(", ", $sources);
                });

        $this->addColumn('key_arr', $translator->translate('Login'), '150px')
                ->setTableName("key")
                ->setTextFilter()
                ->setRenderer(function ($row) use ($presenter) {
                    $keyArr = preg_split("~\,~", $row["key_arr"]);
                    $sourceArr = preg_split("~\,~", $row["source_arr"]);
                    $div = Nette\Utils\Html::el("div");
                    foreach ($sourceArr as $key => $source) {
                        if ($source === "app" && array_key_exists($key, $keyArr)) {
                            if ($div->count()) {
                                $div->add(Nette\Utils\Html::el("span")->setHtml(",&nbsp;"));
                            }
                            $div->add(Nette\Utils\Html::el("a")
                                    ->setText($keyArr[$key])
                                    ->href($presenter->link("editPassword", $keyArr[$key]))
                            );
                        }
                    }
                    return $div;
                });
                
        $this->addColumn('created', $translator->translate('Registered'), '150px')
                ->setRenderer(function($row) {
                    $date = new \Nette\DateTime($row['created']);
                    return $date->format("d.m.Y");
                });

        $this->addColumn('is_completed', $translator->translate('Is Completed'), '100px')
                ->setBooleanFilter()
                ->setRenderer(function($row) use ($translator) {
                    return $row['is_completed'] == '1' ? $translator->translate('YES') : $translator->translate('NO');
                });
                
        $tagService = $this->tagService;
        $this->addColumn('tag_id', 'Tags')
                ->setRenderer(function($row) use ($tagService) {
                    return implode(', ', $tagService->getUserTagNames($row->id));
                })
                ->setFormRenderer(function($row) use ($tagService) {
                    return implode(',', $tagService->getUserTagNames($row->id));
                })
                ->setSelectFilter($this->tagService->findAll())
                ->setTextEditable();

        $this->addButton("show", $this->translator->translate("Show profile"))
            ->setClass("view")
            ->setLink(function($row) use ($presenter) {
               return $presenter->link(':Profile:Homepage:', $row->id);
            })
            ->setAjax(FALSE);
            
        $this->addButton(Grid::ROW_FORM, $translator->translate('Fast edit'))
            ->setClass('fast-edit');
            
        $this->addButton("edit", $translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("editUser", $row['id']);
                })
                ->setAjax(FALSE);

        $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink(function($row) use ($presenter) {
                    return $presenter->link("delete!", $row['id']);
                })
                ->setConfirmationDialog(function($row) use ($translator) {
                    return $translator->translate("Are you sure to delete user '%s'?", $row['username']);
                })
                ->setAjax(FALSE);
                
                
        $this->setRowFormCallback(function($values) use ($tagService, $self, $translator) {
            
            $newTags = explode(',', $values['tag_id']);
            $tags = [];
            foreach ($newTags as $newTag) {
                $newTag = \Nette\Utils\Strings::trim($newTag);
                if (!empty($newTag)) {
                    $tags[] = $tagService->saveTag($newTag);
                }
            }
            $tagService->saveUserTags($values['id'], $tags);
        });
                
        $this->presenter->invalidateControl('taglist');
    }

}
