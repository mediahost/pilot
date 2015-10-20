<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\BannerService,
    Model\Entity\BannerEntity;

/**
 * EditBanner Form
 *
 * @author Petr Poupě
 */
class EditBannerForm extends AppForms
{

    /** @var BannerService */
    private $service;

    public function __construct(Presenter $presenter, BannerService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setDefaults(BannerEntity $entity)
    {
        $this->entityToForm($this->getComponent($this->name), $entity);
    }

    protected function createComponent($name)
    {
        $this->form->addHidden('id');
        $this->form->addHidden('lang', $this->lang);
        $this->form->addHidden('type');
        $this->form->addHidden('order');
        $this->form->addHidden('active');
        $this->form->addText('comment', "Comment", 35, 255);
        $this->form->addText('name', "Name", 35, 100)
                ->addRule(Form::FILLED, "Name is required");
        $this->form->addUpload('image', "Image");

        $this->form->addSubmit('send', 'Save')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $entity = $this->formToEntity($form->values);

        try {
            $entity = $this->service->save($entity);
            $filename = $this->saveImage($form->values->image, "banners", $entity->id . "_" . $entity->lang);
            if ($filename !== FALSE) {
                $entity->image = $filename;
                $this->service->save($entity, "image");
            }

            $this->presenter->flashMessage("Banner was succesfully saved", 'success');
        } catch (Exception $exc) {
            $this->presenter->flashMessage($exc->getMessage(), 'error');
        }

        $this->presenter->redirect("this");
    }

    /**
     * Return entity from form
     * @param \Nette\ArrayHash $values
     * @return BannerEntity
     */
    private function formToEntity(\Nette\ArrayHash $values)
    {
        $entity = new BannerEntity;
        if ($values->id !== "")
            $entity->id = $values->id;
        if ($values->lang !== "")
            $entity->lang = $values->lang;
        if ($values->type !== "")
            $entity->type = $values->type;
        if ($values->active !== "")
            $entity->active = $values->active;
        if ($values->order !== "")
            $entity->order = $values->order;
        if ($values->comment !== "")
            $entity->comment = $values->comment;
        if ($values->name !== "")
            $entity->name = $values->name;
        return $entity;
    }

    private function entityToForm(Form $form, BannerEntity $entity)
    {
        $form->setDefaults(array(
            'id' => $entity->id,
            'lang' => $this->lang,
            'type' => $entity->type,
            'order' => $entity->order,
            'active' => $entity->active,
            'comment' => $entity->comment,
            'name' => $entity->name,
        ));
    }

}

?>
