<?php
class SimpledefinitionsTermTable extends Omeka_Db_Table
{
    public function findByElementId($elementId)
    {
    	exit();
        $select = $this->getSelect()->where('def_term; = ?', $elementId);
        //return $this->fetchObject($select);
        return $elementId;
    }
}
?>