<?php

class Structure {

    private static $structure;
    private static $lookup;
    // Nodes can be any objects that have IDs (id) and a parent IDs (parent)
    public static function buildTree($nodes,$id='id',$parent_id='parent_id'){
        // Initialize structure and lookup arrays
        self::$structure = [];
        self::$lookup = [];
        // Walk through nodes
        array_walk($nodes, 'Structure::addNode',['id'=>$id, 'parent_id'=>$parent_id]);
        // Return the hierarchical (tree) structure
        return self::$structure;
    }
    private static function addNode($obj,$count, $id_and_parent_id_identifier) {
        // Convert string ids returned by PDO to integers
        $nid = (int)$obj->$id_and_parent_id_identifier['id'];
        $pid = (int)$obj->$id_and_parent_id_identifier['parent_id'];
        // Initialize child array
        $obj->children = [];
        // If top level node, set parent child array to a reference to whole structure
        if($pid === 0){
            // If top level node, set parent child array to whole structure
            $parent = &self::$structure;
        } else {
            // Else, set it to reference to the parent child array
            $parent = &self::$lookup[$pid]->children;
        }
        // Get ID of new node it child array
        $id = count($parent);
        // Add node to child array
        $parent[] = $obj;
        // Add reference to node in parent child array to the lookup table
        self::$lookup[$nid] = &$parent[$id];
    }

}