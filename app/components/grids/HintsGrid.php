<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\HintService;

/**
 * HintsGrid
 *
 * @author Petr Poupě
 */
class HintsGrid extends Grid
{

    protected $lang;

    /** @var DibiFluent */
    private $hints;

    /** @var \Nette\Application\UI\Presenter */
    private $presenter;

    /** @var Model\Service\HintService */
    protected $service;

    /** @var \NetteTranslator\Gettext */
    protected $translator;

    public function __construct(DibiFluent $banners, Presenter $presenter, ITranslator $translator, HintService $service, $lang)
    {
        parent::__construct();
        $this->hints = $banners;
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
            $entity = $this->service->find($id);
            if ($this->service->delete($entity)) {
                $this->flashMessage($this->translator->translate("Hint '%s' was succesfull deleted", $entity->comment), "success");
            } else {
                $this->flashMessage($this->translator->translate("Hint '%s' wasn't deleted", $entity->comment), "danger");
            }
        }
    }

    protected function configure($presenter)
    {
        //Vytvoříme si zdroj dat pro Grid
        //Při výběru dat vždy vybereme id
        //Předáme zdroj
        $source = new NiftyGrid\DibiFluentDataSource($this->hints, "id");
        $this->setDataSource($source);

        //defaultní řazení
        $this->setDefaultOrder("form ASC");

        $this->setTranslator($this->translator);
        $this->setWidth('100%');

        $column = array();
        $column['id'] = $this->addColumn('id', $this->translator->translate('ID'), '30px');
        $column['form'] = $this->addColumn('form', $this->translator->translate('Form number'), '150px');
        $column['comment'] = $this->addColumn('comment', $this->translator->translate('Comment'), '200px', 30);
        $column['text'] = $this->addColumn('text', $this->translator->translate('Text'), NULL, 100);

        // filtery
        $column['form']->setNumericFilter();
        $column['comment']->setTextFilter();
        $column['text']->setTextFilter();

        // fast-edit
        $column['comment']->setTextEditable();

        // akce
        $self = $this;
        $service = $this->service;
        $lang = $this->lang;
        $translator = $this->translator;
        $this->addButton(Grid::ROW_FORM, $this->translator->translate("Fast edit"))
                ->setClass("fast-edit");
        $this->setRowFormCallback(function($values) use ($self, $service, $translator, $lang) {
                    $entity = $service->find($values['id'], $lang);
                    $entity->lang = $lang;
                    $entity->comment = $values['comment'];
                    try {
                        $service->save($entity);
                        $self->flashMessage($translator->translate("Hint '%s' was succesfully saved", $entity->id), 'success');
                    } catch (Exception $exc) {
                        $self->flashMessage($translator->translate($exc->getMessage()), 'danger');
                    }
                });

        $this->addButton("edit", $this->translator->translate("Edit"))
                ->setClass("edit")
                ->setLink(function($row) use ($self) {
                            return $self->presenter->link("editTip", $row['id']);
                        })
                ->setAjax(FALSE);

        $this->addButton("delete", $this->translator->translate("Delete"))
                ->setClass("delete")
                ->setLink(function($row) use ($self) {
                            return $self->link("delete!", $row['id']);
                        })
                ->setConfirmationDialog(function($row) use ($translator) {
                            return $translator->translate("Are you sure to delete hint with ID '%s'?", $row['id']);
                        });

        $this->addAction("delete", $this->translator->translate("Delete"))
                ->setCallback(function($id) use ($self) {
                            return $self->handleDelete($id);
                        })
                ->setConfirmationDialog($this->translator->translate("Are you sure to delete all selected banners?"));
    }

}

?>
