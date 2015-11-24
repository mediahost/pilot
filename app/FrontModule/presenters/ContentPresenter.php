<?php

namespace FrontModule;

/**
 * ContentPresenter - pages for admin informations
 *
 * @author Petr Poupě
 */
class ContentPresenter extends BasePresenter
{

    public function renderDefault($id)
    {
        $page = $this->context->pages->getPage($id, $this->lang);
        $this->printPage($page);
    }

    public function renderPage($id)
    {
        $page = $this->context->pages->getPage($id, $this->lang);
        $this->printPage($page);
    }

    public function renderPages($id)
    {
        switch ($id) {
            case \Model\Service\PageService::DATASOURCE_MODULES: // modules
            case \Model\Service\PageService::DATASOURCE_SLIDES: // slides
                $pages = $this->context->pages->getPagesArray($this->lang, $id);
                break;
            default:
                $pages = $this->context->pages->getPagesArray($this->lang, \Model\Service\PageService::DATASOURCE_MODULES);
                break;
        }
        if (empty($pages)) {
            $this->flashMessage('Sorry – this page does not exist.', 'warning');
            $this->redirect('Homepage:');
        } else {
            $this->template->pages = $pages;
        }
    }

    public function renderBlog($id = NULL)
    {
        
    }

    public function renderModule($id)
    {
        $page = $this->context->pages->getModule($id, $this->lang);
        $this->printPage($page);
    }

    public function renderSlide($id)
    {
        $page = $this->context->pages->getSlide($id, $this->lang);
        $this->printPage($page);
    }

    public function renderTerms($type = NULL)
    {
        $page = $this->context->pages->getTermPage($this->lang);
        switch ($type) {
            case 'text':
                $this->printText($page);
                break;
            default:
                $this->printPage($page);
                break;
        }
    }

    private function printPage(\Model\Entity\PageEntity $page)
    {
        if ($page->id === NULL) {
            $this->flashMessage('Sorry – this page does not exist.', 'warning');
            $this->redirect('Homepage:');
        } else {
            $this->template->page = $page;
            $this->template->canEditContent = $this->user->isAllowed('content', 'edit');
        }
        $this->setView('page');
    }

    private function printText(\Model\Entity\PageEntity $page)
    {
        if ($page->id === NULL) {
            $page->name = 'Sorry – this page does not exist.';
        }
        $this->template->page = $page;
        $this->setView('text');
    }

}
