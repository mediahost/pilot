<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\BlogService,
    Model\Mapper\Dibi\BlogDibiMapper;

/**
 * Blogs Grid
 *
 * @author Petr Poupě
 */
class BlogsGrid extends Grid
{

    protected $lang;

    /** @var DibiFluent */
    private $blogs;

    /** @var Presenter */
    private $presenter;

    /** @var BlogService */
    protected $service;

    /** @var ITranslator */
    protected $translator;

    public function __construct(DibiFluent $blogs, Presenter $presenter, ITranslator $translator, BlogService $service, $lang)
    {
        parent::__construct();
        $this->blogs = $blogs;
        $this->presenter = $presenter;
        $this->translator = $translator;
        $this->service = $service;
        $this->lang = $lang;
    }

    public function handleView($url)
    {
        $this->presenter->redirect(":Front:Blog:", $url);
    }

    public function handleToggleActive($id, $set = NULL)
    {
        $toggle = $this->service->toggleActive($id, $set);
        if ($toggle === BlogDibiMapper::ACTIVATE) {
            $this->flashMessage($this->translator->translate("Item (ID = %s) was activated", $id), 'success');
        } else if ($toggle === BlogDibiMapper::DEACTIVATE) {
            $this->flashMessage($this->translator->translate("Item (ID = %s) was deactivated", $id), 'success');
        } else {
            $this->flashMessage($this->translator->translate("No change with activity"), 'danger');
        }
    }

    public function handleActivate($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleToggleActive($item, TRUE);
            }
        } else {
            $this->handleToggleActive($id, TRUE);
        }
    }

    public function handleDeactivate($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->handleToggleActive($item, FALSE);
            }
        } else {
            $this->handleToggleActive($id, FALSE);
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
                $this->flashMessage($this->translator->translate("Blog '%s' was succesfull deleted", $entity->name), "success");
            } else {
                $this->flashMessage($this->translator->translate("Blog '%s' wasn't deleted", $entity->name), "danger");
            }
        }
    }

    protected function configure($presenter)
    {
        //Vytvoříme si zdroj dat pro Grid
        //Při výběru dat vždy vybereme id
        //Předáme zdroj
        $source = new NiftyGrid\DibiFluentDataSource($this->blogs, "id");
        $this->setDataSource($source);

        //defaultní řazení
        $this->setDefaultOrder("publish_date DESC");

        $this->setTranslator($this->translator);
        $this->setWidth('100%');

        $self = $this;
        $service = $this->service;
        $lang = $this->lang;
        $translator = $this->translator;
        
        $image = $this->addColumn('image', $this->translator->translate('Image'), '55px');
        $name = $this->addColumn('name', $this->translator->translate('Name'), '200px');
        $perex = $this->addColumn('perex', $this->translator->translate('Perex'), NULL, 30);
        $text = $this->addColumn('text', $this->translator->translate('Text'), NULL, 60);
        $publish = $this->addColumn('publish_date', $this->translator->translate('Published'), '105px');
        $active = $this->addColumn('active', $this->translator->translate('Active'), '55px');
        $read = $this->addColumn('read', $this->translator->translate('Read'), '55px');

        // filtery
        $name->setTextFilter();
        $perex->setTextFilter();
        $text->setTextFilter();
        $publish->setDateFilter();
        $active->setBooleanFilter();
        $read->setNumericFilter();

        // fast-edit
        $name->setTextEditable();
        $active->setBooleanEditable();

        // renders
        $basePath = $this->presenter->context->httpRequest->getUrl()->basePath;
        $image->setRenderer(function($row) use($basePath) {
                    return \Nette\Utils\Html::el('img')
                                    ->alt($row['url'])
                                    ->src($basePath . "foto/24-24/blog/" . ($row['image'] === NULL ? "default.png" : $row['image']));
                });
        $active->setRenderer(function($row) use($self, $translator) {
                    return \Nette\Utils\Html::el('a')
                                    ->href($self->link("toggleActive!", $row['id']))
                                    ->class(array("grid-ajax"))
                                    ->setHtml(
                                            \Nette\Utils\Html::el('span')
                                            ->title($self->link("toggleActive!", $row['id']))
                                            ->title($translator->translate($row['active'] ? "active" : "inactive"))
                                            ->class(array("icon", $row['active'] ? "active" : "inactive"))
                    );
                });
        $perex->setRenderer(function($row) {
                    echo CommonHelpers::getShortText(strip_tags($row['perex']), 100);
                });
        $text->setRenderer(function($row) {
                    echo CommonHelpers::getShortText(strip_tags($row['text']), 100);
                });
        $publish->setRenderer(function($row) {
                    $now = new \Nette\DateTime;
                    $publishFrom = new \Nette\DateTime($row['publish_date']);
                    $published = $now >= $publishFrom;
                    $formated = $publishFrom->format("d.m.Y");
                    $date = \Nette\Utils\Html::el("span")
                            ->setText($formated)
                            ->addAttributes(array("title" => ($published ? "published" : "unpublished")));
                    $date->class[] = ($published ? "green" : "red");
                    $date->class[] = ($published ? NULL : "bold");
                    return $date;
                });

        // buttons
        $this->addButton("view", $this->translator->translate("View"))
                ->setClass("view")
                ->setTarget("_blank")
                ->setAjax(FALSE)
                ->setLink(function($row) use ($self) {
                            return $self->link("view!", $row['url']);
                        });
        $this->addButton(Grid::ROW_FORM, $this->translator->translate("Fast edit"))
                ->setClass("fast-edit");
        $this->setRowFormCallback(function($values) use ($self, $service, $translator, $lang) {
                    $entity = $service->find($values['id'], $lang);
                    $entity->lang = $lang;
                    $entity->name = $values['name'];
                    $entity->active = array_key_exists('active', $values) ? (bool) $values['active'] : 0;
                    try {
                        $service->save($entity, "name,active");
                        $self->flashMessage($translator->translate("Blog '%s' was succesfully saved", $entity->name), 'success');
                    } catch (Exception $exc) {
                        $self->flashMessage($translator->translate($exc->getMessage()), 'danger');
                    }
                });

        $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(function($row) use ($self) {
                            return $self->presenter->link("editBlog", $row['id']);
                        })
                ->setAjax(FALSE);

        $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink(function($row) use ($self) {
                            return $self->link("delete!", $row['id']);
                        })
                ->setConfirmationDialog(function($row) use ($translator) {
                            return $translator->translate("Are you sure to delete blog '%s'?", $row['name']);
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
                ->setConfirmationDialog($this->translator->translate("Are you sure to delete all selected blogs?"));
    }

}

?>
