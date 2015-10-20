<?php

namespace FrontModule;

use Model\Entity\ActionLogEntity;

/**
 * Blog Presenter
 *
 * @author Petr Poupě
 */
class BlogPresenter extends BasePresenter
{

    public function actionDefault($id = NULL)
    {
        if ($id === NULL)
            $this->redirect("Blog:list");

        $blog = $this->context->blogs->getBlogByUrl($id, $this->lang);
        if ($blog->id === NULL) {
            $this->flashMessage("This page wasn't find", "warning");
            $this->redirect("Blog:list");
        } else {
            $this->context->blogs->addRead($blog);
            $this->context->actionlogs->log(ActionLogEntity::READ_BLOG, $this->user->getId(), array($blog->url));
            $this->template->page = $blog;
            $this->template->canEditContent = $this->user->isAllowed("content", "edit");
        }

        $this->loadModuleItems();
    }

    public function actionList($category = NULL)
    {
        $blogsCount = $this->context->blogs->getBlogsCount($this->lang, $category);
        $vp = new \VisualPaginator($this, 'blog');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $blogsCount;

        $this->template->blogs = $this->context->blogs->getBlogs($this->lang, NULL, $category, $paginator->itemsPerPage, $paginator->offset);
        $this->loadModuleItems();
    }

    private function loadModuleItems()
    {
        $this->template->mostRead = $this->context->blogs->getBlogs($this->lang, "read", NULL, 5);
        $this->template->recentBlogs = $this->context->blogs->getBlogs($this->lang, NULL, NULL, 5);
        $this->template->categories = $this->context->blogs->getCategories($this->lang, TRUE, 5);
    }

}
