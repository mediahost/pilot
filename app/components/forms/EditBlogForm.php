<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\BlogService,
    Model\Entity\BlogEntity;

/**
 * Edit Blog Form
 *
 * @author Petr Poupě
 */
class EditBlogForm extends AppForms
{

    private $id = NULL;

    /** @var BlogService */
    private $service;

    public function __construct(Presenter $presenter, BlogService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDefaults(BlogEntity $entity)
    {
        $this->entityToForm($this->getComponent($this->name), $entity);
    }

    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        
        $this->form->addHidden('id');
        $this->form->addHidden('lang', $this->lang);
        $this->form->addHidden('active');

        $this->form->addDatePicker('publish', "Public from")
                ->setAttribute("readonly", "readonly")
                ->setAttribute("data-date-format", "yyyy-mm-dd")
                ->addRule(Form::FILLED, 'Date is required');
        $this->form['publish']->getControlPrototype()->class = "birthDate date-picker form-control input-medium";

        $this->form->addUpload('image', "Image");
        $this->form->addCheckbox('image_delete', "Delete image");
        $this->form->addText('url', "Url", 35, 255)
                ->setAttribute("placeholder", "text will be transofm to web format");
        $this->form->addText('name', "Name", 35, 100)
                ->addRule(Form::FILLED, "Name is required");
        $this->form->addTextArea('perex', "Perex", 100, 10)
                        ->getControlPrototype()->class = "ckeditor";
        $this->form->addTextArea('text', "Text", 100, 20)
                        ->getControlPrototype()->class = "ckeditor";
        $this->form->addTextArea("tags", "Tags", 70, 3)
                ->setOption("description", "separate by comma or enter");
        
        $categories = $this->service->getCategories($this->lang);
        $this->form->addMultiSelect("categories", "Categories", $categories, 10);
        

        $this->form->addSubmit('back', 'Save & Back');
        $this->form->addSubmit('send', 'Save');

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
            $entitySaved = $this->service->save($entity);
            if (isset($form->values->image) && $form->values->image instanceof \Nette\Http\FileUpload && $form->values->image->isImage()) {
                $filename = $this->saveImage($form->values->image, "blog", $entitySaved->id);
                if ($filename !== FALSE) {
                    $entitySaved->image = $filename;
                    $this->service->save($entitySaved, "image");
                }
            } else if ($form->values->image_delete) {
                $entitySaved->image = NULL;
                $this->service->save($entitySaved, "image");
            }

            // lang versions
            $langs = array_keys($this->presenter->context->langs->getBackLanguages());

            // Save other language versions if NULL
            foreach ($langs as $lang) {
                if ($lang !== $entity->lang) {
                    $tmpEntity = $this->service->find($entitySaved->id, $lang);
                    if ($tmpEntity->name === NULL)
                        $tmpEntity->name = $entity->name;
                    if ($tmpEntity->perex === NULL)
                        $tmpEntity->perex = $entity->perex;
                    if ($tmpEntity->text === NULL)
                        $tmpEntity->text = $entity->text;
                    if ($tmpEntity->url === NULL)
                        $tmpEntity->url = $entity->url;

                    $tmpEntity->lang = $lang;
                    $this->service->save($tmpEntity);
                }
            }


            $this->presenter->flashMessage("Blog was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }
        
        if ($form['back']->submittedBy) {
            $this->presenter->redirect("default");
        } else {
            $this->presenter->redirect("this"); // this is better for languages editation
        }
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return BlogEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = new BlogEntity;
        if ($values->id !== "")
            $entity->id = $values->id;
        if ($values->lang !== "")
            $entity->lang = $values->lang;
        if ($values->active !== "")
            $entity->active = $values->active;
        if ($values->name !== "")
            $entity->name = $values->name;
        if ($values->perex !== "")
            $entity->perex = $values->perex;
        if ($values->text !== "")
            $entity->text = $values->text;
        if ($values->publish !== "")
            $entity->publishDate = $values->publish;
        if ($values->url !== "")
            $entity->url = $values->url;
        else
            $entity->url = $values->name;
        
        $entity->categoryIds = $values->categories;
        $entity->tags = $values->tags;
        
        return $entity;
    }

    private function entityToForm(Form $form, BlogEntity $entity)
    {
        $form->setDefaults(array(
            'id' => $entity->id,
            'lang' => $this->lang,
            'active' => $entity->active,
            'url' => $entity->url,
            'name' => $entity->name,
            'perex' => $entity->perex,
            'text' => $entity->text,
            'publish' => $entity->publishDate,
            'categories' => $entity->categoryIds,
            'tags' => $entity->tagsString,
        ));
    }

}

?>
