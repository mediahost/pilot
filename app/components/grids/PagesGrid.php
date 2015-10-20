<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\PageService,
    Model\Mapper\Dibi\PageDibiMapper;

/**
 * PagesGrid
 *
 * @author Petr Poupě
 */
class PagesGrid extends Grid
{

    protected $lang;
    protected $type;

    /** @var DibiFluent */
    private $pages;

    /** @var \Nette\Application\UI\Presenter */
    private $presenter;

    /** @var Model\Service\PageService */
    protected $service;

    /** @var \NetteTranslator\Gettext */
    protected $translator;

    public function __construct(DibiFluent $pages, Presenter $presenter, ITranslator $translator, PageService $service, $lang, $type = NULL)
    {
        parent::__construct();
        $this->pages = $pages;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
        $this->lang = $lang;
        $this->type = $type;
    }

    public function handleView($id)
    {
        $this->presenter->redirect(":Front:Content:page", $id);
    }

    public function handleMoveUp($id)
    {
        if ($this->service->moveUp($id)) {
            $this->flashMessage($this->translator->translate("Item (ID = %s) was moved up", $id), 'success');
        } else {
            $this->flashMessage($this->translator->translate("Move is not possible. This is the top item in this list"), 'danger');
        }
    }

    public function handleMoveDown($id)
    {
        if ($this->service->moveDown($id)) {
            $this->flashMessage($this->translator->translate("Item (ID = %s) was moved down", $id), 'success');
        } else {
            $this->flashMessage($this->translator->translate("Move is not possible. This is the lowest item in this list"), 'danger');
        }
    }

    public function handleToggleActive($id, $set = NULL)
    {
        $toggle = $this->service->toggleActive($id, $set);
        if ($toggle === PageDibiMapper::ACTIVATE) {
            $this->flashMessage($this->translator->translate("Item (ID = %s) was activated", $id), 'success');
        } else if ($toggle === PageDibiMapper::DEACTIVATE) {
            $this->flashMessage($this->translator->translate("Item (ID = %s) was deactivated", $id), 'success');
        } else {
            $this->flashMessage($this->translator->translate("No change with activity"), 'danger');
        }
    }

    public function handleActivate($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleToggleActive($item, PageDibiMapper::ACTIVATE);
            }
        } else {
            $this->handleToggleActive($id, PageDibiMapper::ACTIVATE);
        }
    }

    public function handleDeactivate($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleToggleActive($item, PageDibiMapper::DEACTIVATE);
            }
        } else {
            $this->handleToggleActive($id, PageDibiMapper::DEACTIVATE);
        }
    }

    public function handleDelete($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleDelete($item);
            }
        } else {
            $entity = $this->service->find($id);
            if ($this->service->delete($entity)) {
                $this->flashMessage($this->translator->translate("Page '%s' was succesfull deleted", $entity->code), "success");
            } else {
                $this->flashMessage($this->translator->translate("Page '%s' wasn't deleted", $entity->code), "danger");
            }
        }
    }

    protected function configure($presenter)
    {
        //Vytvoříme si zdroj dat pro Grid
        //Při výběru dat vždy vybereme id
        //Předáme zdroj
        $source = new NiftyGrid\DibiFluentDataSource($this->pages, "id");
        $this->setDataSource($source);

        $showParent = (bool) ($this->type === PageService::DATASOURCE_OTHER);
        $parents = $this->service->getPageParents($this->lang, NULL, $this->type);

        $showPosition = (bool) ($this->type === PageService::DATASOURCE_OTHER);
        $positions = $this->service->getPagePositions();

        $showLink = (bool) ($this->type !== PageService::DATASOURCE_BLOGS);

        //defaultní řazení
        $this->setDefaultOrder("position ASC");

        $this->setTranslator($this->translator);
        $this->setWidth('100%');

        $self = $this;
        $service = $this->service;
        $lang = $this->lang;
        $translator = $this->translator;
        $basePath = $this->presenter->context->httpRequest->getUrl()->basePath;

        $this->addColumn('image', $this->translator->translate('Image'), '55px')
                ->setSortable(FALSE)
                ->setRenderer(function($row) use($basePath) {
                    return \Nette\Utils\Html::el('img')
                            ->alt($row['code'])
                            ->src($basePath . "foto/24-24/pages/" . ($row['image'] === NULL ? "default.png" : $row['image']));
                });

        $this->addColumn('comment', $this->translator->translate('Comment'), '180px', 30)
                ->setTextFilter()
                ->setTextEditable();

        if ($showPosition) {
            $this->addColumn('position', $this->translator->translate('Position'), '65px')
                    ->setSelectFilter($positions)
                    ->setRenderer(function($row) use($positions, $translator) {
                        return \Nette\Utils\Html::el('span', (isset($positions[$row['position']]) ? $translator->translate($positions[$row['position']]) : $row['position']));
                    });
        }

        if ($showParent) {
            $this->addColumn('parent_id', $this->translator->translate('Parent'), '100px')
                    ->setRenderer(function($row) use($parents) {
                        return \Nette\Utils\Html::el('span', (isset($parents[$row['parent_id']]) ? $parents[$row['parent_id']] : $row['parent_id']));
                    });
        }

        $this->addColumn('name', $this->translator->translate('Name'), '200px')
                ->setTextFilter()
                ->setTextEditable();

        $this->addColumn('perex', $this->translator->translate('Perex'), NULL, 30)
                ->setTextFilter()
                ->setSortable(FALSE)
                ->setRenderer(function($row) {
                    echo CommonHelpers::getShortText(strip_tags($row['perex']), 50);
                });

        $column['text'] = $this->addColumn('text', $this->translator->translate('Text'), NULL, 60)
                ->setTextFilter()
                ->setSortable(FALSE)
                ->setRenderer(function($row) {
            echo CommonHelpers::getShortText(strip_tags($row['text']), 50);
        });

        if ($showLink) {
            $column['link'] = $this->addColumn('link', $this->translator->translate('Link'), NULL, 30)
                    ->setTextFilter()
                    ->setSortable(FALSE)
                    ->setTextEditable();
        }

        $column['active'] = $this->addColumn('active', $this->translator->translate('Active'), '55px')
                ->setBooleanFilter()
                ->setRenderer(function($row) use($self, $translator) {
            return \Nette\Utils\Html::el('a')
                    ->href($self->link("toggleActive!", $row['id']))
                    ->class(array("grid-ajax"))
                    ->setHtml(
                            \Nette\Utils\Html::el('span')
                            ->title($translator->translate($row['active'] ? "active" : "inactive"))
                            ->class(array("icon", $row['active'] ? "active" : "inactive"))
            );
        });

        // buttons
        $this->addButton("view", $this->translator->translate("View"))
                ->setClass("view")
                ->setTarget("_blank")
                ->setAjax(FALSE)
                ->setLink(function($row) use ($self) {
                    return $self->link("view!", $row['id']);
                });

        $this->addButton("up", $this->translator->translate("Up"))
                ->setClass("up ajax")
                ->setLink(function($row) use ($self) {
                    return $self->link("moveUp!", $row['id']);
                });

        $this->addButton("down", $this->translator->translate("Down"))
                ->setClass("down ajax")
                ->setLink(function($row) use ($self) {
                    return $self->link("moveDown!", $row['id']);
                });
        $this->addButton(Grid::ROW_FORM, $this->translator->translate("Fast edit"))
                ->setClass("fast-edit");
        $this->setRowFormCallback(function($values) use ($self, $service, $translator, $lang) {
            $entity = $service->find($values['id'], $lang);
            $entity->lang = $lang;
            $entity->comment = $values['comment'];
            $entity->name = $values['name'];
            $entity->active = array_key_exists('active', $values) ? (bool) $values['active'] : 0;
            try {
                $service->save($entity);
                $self->flashMessage($translator->translate("Page '%s' was succesfully saved", $entity->code), 'success');
            } catch (Exception $exc) {
                $self->flashMessage($translator->translate($exc->getMessage()), 'danger');
            }
        });

        $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(function($row) use ($self) {
                    switch ($row['type']) {
                        case \Model\Entity\PageEntity::TYPE_SLIDE:
                            $method = "editBanner";
                            break;
                        case \Model\Entity\PageEntity::TYPE_MODULE:
                            $method = "editModule";
                            break;
                        default:
                        case \Model\Entity\PageEntity::TYPE_OTHER:
                            $method = "editInformation";
                            break;
                    }
                    return $self->presenter->link($method, $row['id']);
                })
                ->setAjax(FALSE);

        $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink(function($row) use ($self) {
                    return $self->link("delete!", $row['id']);
                })
                ->setConfirmationDialog(function($row) use ($translator) {
                    return $translator->translate("Are you sure to delete page '%s'?", $row['name']);
                });

        // actions
        $this->addAction("activate", $this->translator->translate("Activate"))
                ->setCallback(function($id) use ($self) {
                    return $self->handleActivate($id);
                });

        $this->addAction("deactivate", $this->translator->translate("Deactivate"))
                ->setCallback(function($id) use ($self) {
                    return $self->handleDeactivate($id);
                });

        $this->addAction("delete", $this->translator->translate("Delete"))
                ->setCallback(function($id) use ($self) {
                    return $self->handleDelete($id);
                })
                ->setConfirmationDialog($this->translator->translate("Are you sure to delete all selected pages?"));
    }

}

?>
