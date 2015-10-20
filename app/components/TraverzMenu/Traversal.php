<?php

namespace Pupek\TraversalMenu;

use Nette\Application\UI\Control;

/**
 * TraverzMenu
 *
 * @author Petr Poupě
 * @license MIT
 */
class Traversal extends Control
{
    
    /** @var Model\ITraversalMapper */
    private $mapper;

    /** @var string */
    private $treeTemplate;

    /** @var string */
    private $breadcrumbsTemplate;

    /** @var string */
    private $mapTemplate;

    /** @var int */
    private $root = 0;

    /** @var int */
    private $leaf = 0;
    
    
    public function __construct(\DibiConnection $conn, $table)
    {
        $this->mapper = new Model\TraversalDibiMapper($conn, $table);
    }
    
    /**
     * Id of actual root of tree
     * @param type $rootId
     */
    public function setRoot($rootId)
    {
        $this->root = $rootId;
    }
    
    /**
     * Id of actual node for print breadscrums
     * @param type $leafId
     */
    public function setLeaf($leafId)
    {
        $this->leaf = $leafId;
    }

    /**
     * Render full menu
     */
    public function render()
    {
        $this->renderTree();
    }

    /**
     * Render tree menu
     */
    public function renderTree($level = 0)
    {
        $template = $this->createTemplate()
                ->setFile($this->treeTemplate ? : __DIR__ . '/templates/tree.latte');
        $template->items = $this->getTree($this->root, $level);
        $template->render();
    }

    /**
     * Render breadcrumbs
     */
    public function renderBreadcrumbs($separator = " > ")
    {
        $template = $this->createTemplate()
                ->setFile($this->breadcrumbsTemplate ? : __DIR__ . '/templates/breadcrumbs.latte');
        $template->separator = $separator;
        $template->items = $this->getBreadcrums($this->leaf);
        $template->render();
    }

    /**
     * Render site map
     */
    public function renderMap($level = 0)
    {
        $template = $this->createTemplate()
                ->setFile($this->mapTemplate ? : __DIR__ . '/templates/map.latte');
        $template->render();
    }

    /**
     * @param string $treeTemplate
     */
    public function setTreeTemplate($treeTemplate)
    {
        $this->treeTemplate = $treeTemplate;
    }

    /**
     * @param string $breadcrumbsTemplate
     */
    public function setBreadcrumbsTemplate($breadcrumbsTemplate)
    {
        $this->breadcrumbsTemplate = $breadcrumbsTemplate;
    }

    /**
     * @param string $mapTemplate
     */
    public function setMapTemplate($mapTemplate)
    {
        $this->mapTemplate = $mapTemplate;
    }

    private function getTree($parentId = NULL, $deep = NULL)
    {
        $children = $this->mapper->getChildren($parentId, $deep);
        if ($children)
            return new Model\LazyCollection($this->mapper, $children);
        else
            return array();
    }
    
    private function getBreadcrums($nodeId)
    {
        $path = $this->mapper->getPath($nodeId);
        if ($path)
            return new Model\LazyCollection($this->mapper, $path);
        else
            return array();
    }

}
