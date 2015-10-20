<?php

namespace Pupek\TraversalMenu\Model;

/**
 * ITraversalMapper Interface
 *
 * @author Petr Poupě
 */
interface ITraversalMapper
{
    
    /**
     * Find item by ID
     * @param type $id
     */
    function find($id);
    
    /**
     * Get tree from parent node
     * @param type $parent
     * @param type $deep
     */
    function getChildren($parentId, $deep);
    
    /**
     * Get breadcrumbs
     * @param type $node
     */
    function getPath($nodeId);
    
    /**
     * Insert new item to end of tree
     * @param \Pupek\TraversalMenu\Model\NodeEntity $node
     */
    function insertNew(NodeEntity $node);
    
    /**
     * Add child for inserted node
     * @param type $parent
     * @param \Pupek\TraversalMenu\Model\NodeEntity $node
     */
    function addChild($parentId, NodeEntity $node);

    /**
     * Delete subtree with inserted node
     * @param type $node
     */
    function deleteSubtree($nodeId);
    
    /**
     * Move node up or down in its level
     * @param type $node
     * @param type $dir
     */
    function moveEdge($nodeId, $dir);

    /**
     * Recalculate indexef for left and right
     * @param type $parent
     * @param type $left
     */
    function rebuildTree($parentId, $parentLeft);
    
    /**
     * Load needed data
     * @param type $data
     */
    function load($data);
    
}

?>
