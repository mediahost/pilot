<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\PageService,
    Model\Entity\PageEntity;

/**
 * EditAllPages Form
 *
 * @author Petr Poupě
 */
class EditAllPagesForm extends AppForms
{

    private $lang;
    private $type;

    /** @var PageService */
    private $service;

    public function __construct(Presenter $presenter, PageService $service, $type = NULL)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
        $this->type = $type;
    }

    private function setDefaults(array $pages)
    {
        $defaults = array("main" => array());
        foreach ($pages as $page) {
            $defaults["main"][$page->id] = $this->entityToForm($page);
        }
        $this->form->setDefaults($defaults);
    }

    protected function createComponent($name)
    {
        switch ($this->type) {
            case PageService::DATASOURCE_MODULES:
            case PageService::DATASOURCE_SLIDES:
            case PageService::DATASOURCE_OTHER:
                $pages = $this->service->getPagesArray($this->lang, $this->type);
                break;
            case PageService::DATASOURCE_ALL:
            default:
                $pages = $this->service->getPagesArray($this->lang);
                break;
        }

        $mainContainer = $this->form->addContainer("main");
        foreach ($pages as $page) {
            $pageContainer = $mainContainer->addContainer($page->id);
            $pageContainer->addHidden('id');
            $pageContainer->addHidden('lang', $this->lang);
            $pageContainer->addHidden('type', $this->type);
            $pageContainer->addHidden('order');
            $pageContainer->addHidden('active');

            $pageContainer->addUpload('image', "Icon");

            switch ($this->type) {
                case PageService::DATASOURCE_OTHER:
                    $positions = $this->service->getPagePositions();
                    $parents = $this->service->getPageParents($this->lang, $page->id);
                    $pageContainer->addSelect('position', "Position", $positions)
                            ->setPrompt("--- nowhere ---");
                    $pageContainer->addSelect('parent_id', "Parent page", $parents)
                            ->setPrompt("--- none ---");
                    break;
            }

            $pageContainer->addText('comment', "Comment", 35, 255);
            $pageContainer->addText('name', "Name", 35, 100)
                    ->addRule(Form::FILLED, "Name is required");
            $pageContainer->addText('link', "Link", 98, 255);
            $pageContainer->addText('perex', "Perex", 98, 100);
//                    ->addRule(Form::FILLED, "Perex is required");
            $pageContainer->addTextArea('text', "Text", 100, 20)
//                            ->addRule(Form::FILLED, "Text is required")
                            ->getControlPrototype()->class = "ckeditor";

            $pageContainer->addSubmit('send', 'Save All');
        }

        $this->setDefaults($pages);

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        try {

            foreach ($form->values->main as $values) {
                $entityUnsaved = $this->formToEntity($values);

                $entitySaved = $this->service->save($entityUnsaved);
                $filename = $this->saveImage($values->image, "pages", $entitySaved->id);
                if ($filename !== FALSE) {
                    $entitySaved->image = $filename;
                    $this->service->save($entitySaved, "image");
                }

                // lang versions
                $langs = array_keys($this->presenter->context->langs->getBackLanguages());
                // Save other language versions if NULL
                foreach ($langs as $lang) {
                    if ($lang !== $entitySaved->lang) {
                        $tmpEntity = $this->service->find($entitySaved->id, $lang);
                        if ($tmpEntity->name === NULL)
                            $tmpEntity->name = $entitySaved->name;
                        if ($tmpEntity->perex === NULL)
                            $tmpEntity->perex = $entitySaved->perex;
                        if ($tmpEntity->text === NULL)
                            $tmpEntity->text = $entitySaved->text;
                        if ($tmpEntity->link === NULL)
                            $tmpEntity->link = $entitySaved->link;

                        $tmpEntity->lang = $lang;
                        $this->service->save($tmpEntity);
                    }
                }
            }

            $this->presenter->flashMessage("Pages were succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }

        $this->presenter->redirect("this");
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return \Model\Entity\PageEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = new PageEntity;
        if ($values->id !== "")
            $entity->id = $values->id;
        if ($values->lang !== "")
            $entity->lang = $values->lang;
        if ($values->type !== "")
            $entity->type = $values->type;
        if (isset($values->position) && $values->position !== "")
            $entity->position = $values->position;
        if (isset($values->parent_id) && $values->parent_id !== "")
            $entity->parentId = $values->parent_id;
        if ($values->active !== "")
            $entity->active = $values->active;
        if ($values->order !== "")
            $entity->order = $values->order;
//        if ($values->code !== "")
//            $entity->code = $values->code;
        if ($values->comment !== "")
            $entity->comment = $values->comment;
        if ($values->name !== "")
            $entity->name = $values->name;
        if ($values->perex !== "")
            $entity->perex = $values->perex;
        if ($values->link !== "")
            $entity->link = $values->link;
        if ($values->text !== "")
            $entity->text = $values->text;
        return $entity;
    }

    private function entityToForm(PageEntity $entity)
    {
        $data = array(
            'id' => $entity->id,
            'lang' => $this->lang,
            'type' => $entity->type,
            'position' => $entity->position,
            'parent_id' => $entity->parentId,
            'order' => $entity->order,
            'active' => $entity->active,
            'code' => $entity->code,
            'comment' => $entity->comment,
            'name' => $entity->name,
            'perex' => $entity->perex,
            'link' => $entity->link,
            'text' => $entity->text,
        );
        return $data;
    }

}

?>
