<?php

namespace AdminModule;

use Model\Entity\BlogEntity,
    Model\Entity\BlogCategoryEntity,
    Model\Service\BlogService;

/**
 * Blog Presenter
 *
 * @author Petr Poupě
 */
class BlogPresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
        $this->checkAccess("backend", "access");
    }

// <editor-fold defaultstate="collapsed" desc="actions">

    public function actionAddBlog()
    {
        $entity = new BlogEntity;
        $entity->lang = $this->lang;
        $entity->publishDate = time();

        $this["blogForm"]->setDefaults($entity);
        $this->setView("editBlog");
    }

    public function actionEditBlog($id)
    {
        $entity = $this->context->blogs->find($id, $this->lang);

        $form = $this["blogForm"];

        $form->setId($id);
        $form->setDefaults($entity);
    }

    public function actionAddCategory()
    {
        $entity = new BlogCategoryEntity;
        $entity->lang = $this->lang;

        $this["categoryForm"]->setDefaults($entity);
        $this->setView("editCategory");
    }

    public function actionEditCategory($id)
    {
        $entity = $this->context->blogcategories->find($id, $this->lang);

        $form = $this["categoryForm"];

        $form->setId($id);
        $form->setDefaults($entity);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="renders">
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="handlers">
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="grid components">

    protected function createComponentBlogsGrid()
    {
        $dataSource = $this->context->blogs->getDataGrid($this->lang);
        $grid = new \BlogsGrid($dataSource, $this, $this->translator, $this->context->blogs, $this->lang);
        return $grid;
    }

    protected function createComponentCategoriesGrid()
    {
        $dataSource = $this->context->blogcategories->getDataGrid($this->lang);
        $grid = new \BlogCategoriesGrid($dataSource, $this, $this->translator, $this->context->blogcategories, $this->lang);
        return $grid;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="form components">

    protected function createComponentBlogForm()
    {
        $form = new \AppForms\EditBlogForm($this, $this->context->blogs);
        return $form;
    }

    protected function createComponentCategoryForm()
    {
        $form = new \AppForms\EditBlogCategoryForm($this, $this->context->blogcategories);
        return $form;
    }

// </editor-fold>
}
