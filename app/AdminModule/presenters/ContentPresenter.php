<?php

namespace AdminModule;

use Model\Entity\PageEntity,
    Model\Service\PageService;

/**
 * Content Presenter
 *
 * @author Petr Poupě
 */
class ContentPresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
        $this->checkAccess("backend", "access");
    }

// <editor-fold defaultstate="collapsed" desc="actions">
    public function actionDefault()
    {
        $this->redirect("banners");
    }

    public function actionEditBanner($id = NULL)
    {
        $form = $this->getComponent("slidesForm");

        if ($id === NULL) {
            $entity = new PageEntity;
            $entity->type = PageEntity::TYPE_SLIDE;
        } else {
            $entity = $this->context->pages->find($id, $this->lang);
            $form->setId($id);
        }

        $form->setDefaults($entity);
    }

    public function actionEditModule($id = NULL)
    {
        $form = $this->getComponent("modulesForm");

        if ($id === NULL) {
            $entity = new PageEntity;
            $entity->type = PageEntity::TYPE_MODULE;
        } else {
            $entity = $this->context->pages->find($id, $this->lang);
            $form->setId($id);
        }

        $form->setDefaults($entity);
    }

    public function actionEditInformation($id = NULL)
    {
        $form = $this->getComponent("informationsForm");

        if ($id === NULL) {
            $entity = new PageEntity;
            $entity->type = PageEntity::TYPE_OTHER;
        } else {
            $entity = $this->context->pages->find($id, $this->lang);
            $form->setId($id);
        }

        $form->setDefaults($entity);
    }

    public function actionEditTip($id = NULL)
    {
        $form = $this->getComponent("tipsForm");

        if ($id === NULL) {
            $this->flashMessage("You cannot add tips", "warning");
            $this->redirect("tips");
        } else {
            $entity = $this->context->hints->find($id, $this->lang);
        }

        $form->setDefaults($entity);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="renders">
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="handlers">
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="grid components">
    protected function createComponentSlidesGrid()
    {
        $dataSource = $this->context->pages->getPages($this->lang, PageService::DATASOURCE_SLIDES);
        $grid = new \PagesGrid($dataSource, $this, $this->translator, $this->context->pages, $this->lang, PageService::DATASOURCE_SLIDES);
        return $grid;
    }

    protected function createComponentModulesGrid()
    {
        $dataSource = $this->context->pages->getPages($this->lang, PageService::DATASOURCE_MODULES);
        $grid = new \PagesGrid($dataSource, $this, $this->translator, $this->context->pages, $this->lang, PageService::DATASOURCE_MODULES);
        return $grid;
    }

    protected function createComponentTipsGrid()
    {
        $dataSource = $this->context->hints->getHints($this->lang);
        $grid = new \HintsGrid($dataSource, $this, $this->translator, $this->context->hints, $this->lang);
        return $grid;
    }

    protected function createComponentBlogsGrid()
    {
        $dataSource = $this->context->pages->getPages($this->lang, PageService::DATASOURCE_BLOGS);
        $grid = new \PagesGrid($dataSource, $this, $this->translator, $this->context->pages, $this->lang, PageService::DATASOURCE_BLOGS);
        return $grid;
    }

    protected function createComponentInformationsGrid()
    {
        $dataSource = $this->context->pages->getPages($this->lang, PageService::DATASOURCE_OTHER);
        $grid = new \PagesGrid($dataSource, $this, $this->translator, $this->context->pages, $this->lang, PageService::DATASOURCE_OTHER);
        return $grid;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="form components">

    protected function createComponentSlidesForm()
    {
        $form = new \AppForms\EditPageForm($this, $this->context->pages, PageService::DATASOURCE_SLIDES);
        return $form;
    }

    protected function createComponentModulesForm()
    {
        $form = new \AppForms\EditPageForm($this, $this->context->pages, PageService::DATASOURCE_MODULES);
        return $form;
    }

    protected function createComponentTipsForm()
    {
        $form = new \AppForms\EditHintForm($this, $this->context->hints);
        return $form;
    }

    protected function createComponentBlogsForm()
    {
        $form = new \AppForms\EditPageForm($this, $this->context->pages, PageService::DATASOURCE_BLOGS);
        return $form;
    }

    protected function createComponentInformationsForm()
    {
        $form = new \AppForms\EditPageForm($this, $this->context->pages, PageService::DATASOURCE_OTHER);
        return $form;
    }

// </editor-fold>
}
