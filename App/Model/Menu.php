<?php

class Menu {
    public function getMenuByNid($nid, $fields = array()) {
        return db_select('menu','m')
            ->fields('m', $fields)
            ->condition('status', TRUE)
            ->condition('nid', $nid)
            //->range(0,1)
            ->execute()
            ->fetchAssoc();
    }

    public function getMenusByPid($pid = null, $fields = array()){
    	return db_select('menu', 'm')
    		->fields('m', $fields)
            ->condition('status', TRUE)
    		->condition('parent_id', $pid)
            ->orderBy('nid','ASC')
    		->execute()
    		->fetchAllAssoc('id', PDO::FETCH_ASSOC);
    }
}

?>