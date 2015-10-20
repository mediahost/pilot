<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\BlogCategoryService,
    Model\Entity\BlogCategoryEntity;

/**
 * Edit Blog Category Form
 *
 * @author Petr Poupě
 */
class EditBlogCategoryForm extends AppForms
{

    private $id = NULL;

    /** @var BlogCategoryService */
    private $service;

    public function __construct(Presenter $presenter, BlogCategoryService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDefaults(BlogCategoryEntity $entity)
    {
        $this->entityToForm($this->getComponent($this->name), $entity);
    }

    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        
        $this->form->addHidden('id');
        $this->form->addHidden('lang', $this->lang);
        $this->form->addHidden('active');
        $this->form->addText('name', "Name", 35, 100)
                ->addRule(Form::FILLED, "Name is required");

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

            // lang versions
            $langs = array_keys($this->presenter->context->langs->getBackLanguages());

            // Save other language versions if NULL
            foreach ($langs as $lang) {
                if ($lang !== $entity->lang) {
                    $tmpEntity = $this->service->find($entitySaved->id, $lang);
                    if ($tmpEntity->name === NULL)
                        $tmpEntity->name = $entity->name;

                    $tmpEntity->lang = $lang;
                    $this->service->save($tmpEntity);
                }
            }


            $this->presenter->flashMessage("Blog was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }
        
        if ($form['back']->submittedBy) {
            $this->presenter->redirect("category");
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
        $entity = new BlogCategoryEntity;
        if ($values->id !== "")
            $entity->id = $values->id;
        if ($values->lang !== "")
            $entity->lang = $values->lang;
        if ($values->active !== "")
            $entity->active = $values->active;
        if ($values->name !== "")
            $entity->name = $values->name;
        return $entity;
    }

    private function entityToForm(Form $form, BlogCategoryEntity $entity)
    {
        $form->setDefaults(array(
            'id' => $entity->id,
            'lang' => $this->lang,
            'active' => $entity->active,
            'name' => $entity->name,
        ));
    }

}

?>
