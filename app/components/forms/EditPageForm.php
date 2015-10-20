<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\PageService,
    Model\Entity\PageEntity;

/**
 * EditPage Form
 *
 * @author Petr Poupě
 */
class EditPageForm extends AppForms
{

    private $type;
    private $id = NULL;

    /** @var PageService */
    private $service;

    public function __construct(Presenter $presenter, PageService $service, $type = NULL)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
        $this->type = $type;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDefaults(PageEntity $entity)
    {
        $this->entityToForm($entity);
    }

    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        
        $this->form->addHidden('id');
        $this->form->addHidden('lang', $this->lang);
        $this->form->addHidden('type', $this->service->mapDatasourceType($this->type));
        $this->form->addHidden('order');
        $this->form->addHidden('active');

        $editIcon = TRUE;
        $editPositions = FALSE;
        $editParents = FALSE;
        $editLink = TRUE;
        switch ($this->type) {
            case PageService::DATASOURCE_BLOGS:
//                $editIcon = FALSE;
//                $editParents = TRUE;
                $editLink = FALSE;
                break;
            case PageService::DATASOURCE_OTHER:
                $editPositions = TRUE;
                $editParents = TRUE;
                break;
        }

        if ($editIcon) {
            $this->form->addUpload('image', "Image");
        }

        if ($editPositions) {
            $positions = $this->service->getPagePositions();
            $this->form->addSelect('position', "Position", $positions)
                    ->setPrompt("--- nowhere ---");
        }
        if ($editParents) {
            $parents = $this->service->getPageParents($this->lang, $this->id, $this->type);
            $this->form->addSelect('parent_id', "Parent page", $parents)
                    ->setPrompt("--- none ---");
        }


//        $this->form->addText('code', "Code", 35, 100)
//                ->addRule(Form::PATTERN, "Fill code without spaces and special characters (example: code-without-spaces).", "([\w\d-]+)?");
        $this->form->addText('comment', "Comment", 35, 255);
        $this->form->addText('name', "Name", 35, 100)
                ->addRule(Form::FILLED, "Name is required");
        if ($editLink) {
            $this->form->addText('link', "Link", 98, 255);
        }
        $this->form->addTextArea('perex', "Perex", 100, 10)
                        ->getControlPrototype()->class = "ckeditor";
        $this->form->addTextArea('text', "Text", 100, 20)
                        ->getControlPrototype()->class = "ckeditor";

        $this->form->addSubmit('send', 'Save')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);

        try {
            $isNew = FALSE;
            if ($entity->id === NULL) {
                $isNew = TRUE;
            }
            $entity = $this->service->save($entity);
            if (isset($form->values->image)) {
                $filename = $this->saveImage($form->values->image, "pages", $entity->id);
                if ($filename !== FALSE) {
                    $entity->image = $filename;
                    $this->service->save($entity, "image");
                }
            }

            // lang versions
            $langs = array_keys($this->presenter->context->langs->getBackLanguages());

            // Create other language versions - only if new (this or version lower
//            if ($isNew) {
//                foreach ($langs as $lang) {
//                    if ($lang !== $entity->lang) {
//                        $tmpEntity = clone $entity;
//                        $tmpEntity->lang = $lang;
//                        $this->service->save($tmpEntity);
//                    }
//                }
//            }
            // Save other language versions if NULL
            foreach ($langs as $lang) {
                if ($lang !== $entity->lang) {
                    $tmpEntity = $this->service->find($entity->id, $lang);
                    if ($tmpEntity->name === NULL)
                        $tmpEntity->name = $entity->name;
                    if ($tmpEntity->perex === NULL)
                        $tmpEntity->perex = $entity->perex;
                    if ($tmpEntity->text === NULL)
                        $tmpEntity->text = $entity->text;
                    if ($tmpEntity->link === NULL)
                        $tmpEntity->link = $entity->link;

                    $tmpEntity->lang = $lang;
                    $this->service->save($tmpEntity);
                }
            }


            $this->presenter->flashMessage("Page was succesfully saved", 'success');
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
        if (isset($values->link) && $values->link !== "")
            $entity->link = $values->link;
        if ($values->text !== "")
            $entity->text = $values->text;
        return $entity;
    }

    private function entityToForm(PageEntity $entity)
    {
        parent::setDefaultValues(array(
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
        ));
    }

}

?>
