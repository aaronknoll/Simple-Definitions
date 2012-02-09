<?php
class SimpleDefinitions_IndexController extends Omeka_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('element-texts', 'html')
                    ->addActionContext('element-terms', 'html');
        // I have no idea why I have to force the HTML context here. The AJAX 
        // context switch doesn't work otherwise. This may prove to be 
        // problematic in the future.
        // See: http://framework.zend.com/manual/en/zend.controller.actionhelpers.html
        $ajaxContext->initContext('html');
    }
    
    public function indexAction()
    {
       // $this->view->terms = $this->getTable('SimpledefinitionsTerm')->findAll();
        //$this->view->formSelectOptions = $this->_getFormSelectOptions();
		 $this->view->DefSelectOptions = $this->_getDefSelectOptions();
    }
    
    public function editElementTermsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $terms = $this->getRequest()->getParam('terms');
        
        // Don't process the empty element select option.
        if ('' == $elementId) {
            $this->redirect->goto('index');
        }
        
        $simpledefinitionsTerm = $this->getTable('SimpledefinitionsTerm')->findByElementId($elementId);
        
        // Handle an existing term record.
        if ($simpledefinitionsTerm) {
             // Delete term record if there are no terms.
             if ('' == trim($terms)) {
                 $simpledefinitionsTerm->delete();
                 $this->flashSuccess('Successfully deleted the element\'s definitionsulary terms.');
                 $this->redirect->goto('index');
             }
             $simpledefinitionsTerm->terms = $this->_sanitizeTerms($terms);
             $this->flashSuccess('Successfully edited the element\'s definitionsulary terms.');
        
        // Handle a new term record.
        } else {
            // Do not save a new term record without terms.
            if ('' == trim($terms)) {
                $this->redirect->goto('index');
            }
            $simpledefinitionsTerm = new SimpledefinitionsTerm;
            $simpledefinitionsTerm->element_id = $elementId;
            $simpledefinitionsTerm->terms = $this->_sanitizeTerms($terms);
            $this->flashSuccess('Successfully added the element\'s definitionsulary terms.');
        }
        $simpledefinitionsTerm->save();
        $this->redirect->goto('index');
    }
    
    public function elementTermsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $simpledefinitionsTerm = $this->getTable('SimpledefinitionsTerm')->findByElementId($elementId);
        if ($simpledefinitionsTerm) {
            $terms = $simpledefinitionsTerm->terms;
        } else {
            $terms = '';
        }
        $this->view->terms = $terms;
    }
    
    public function elementTextsAction()
    {
        $db = get_db();
        $elementId = $this->getRequest()->getParam('element_id');
        $select = $db->select()
                     ->from(array('et' => $db->ElementText), 
                            array('text', 'COUNT(*) AS count'))
                     ->group('text')
                     ->where('element_id = ?', $elementId)
                     ->order('count DESC');
        $elementTexts = $this->getTable('ElementText')->fetchObjects($select);
        
        $simpledefinitionsTerm = $this->getTable('SimpledefinitionsTerm')->findByElementId($elementId);
        if ($simpledefinitionsTerm) {
            $terms = explode("\n", $simpledefinitionsTerm->terms);
        } else {
            $terms = array();
        }
        $this->view->elementTexts    = $elementTexts;
        $this->view->simpledefinitionsTerm = $simpledefinitionsTerm;
        $this->view->terms           = $terms;
    }
	
	private function _getDefSelectOptions()
    {
    	//TO-DO need to obtain the proper names for the item types
        $db = get_db();
        $select = $db->select()
			->from('aas_omeka__simple_vocab_terms');
	    $getallitems = $db->fetchAll($select);
	  	$options = array('' => 'Select Below');
		foreach ($getallitems as $singleitem)
			{
				$optGroup = $singleitem['element_id'] ? 'Item Type: ' . $singleitem['id'] : $singleitem['terms'];
			    $termsArray = explode("\n", $singleitem['terms']);
				//counter var, instantiate
				$j = 0;
				//okay, with $termsArray, let's make a select for each one
				foreach ($termsArray as $terminarray)
					{		
					$options[$optGroup][$j] = $terminarray;
					$j++;
					}
			}
        return $options;
    }
    
    
    private function _getFormSelectOptions()
    {
        $db = get_db();
        $select = $db->select()
                     ->from(array('rt' => $db->RecordType), 
                            array())
                     ->join(array('es' => $db->ElementSet), 
                            'rt.id = es.record_type_id', 
                            array('element_set_name' => 'name'))
                     ->join(array('e' => $db->Element), 
                            'es.id = e.element_set_id', 
                            array('element_id' =>'e.id', 
                                  'element_name' => 'e.name'))
                     ->joinLeft(array('ite' => $db->ItemTypesElements), 
                                'e.id = ite.element_id',
                                array())
                     ->joinLeft(array('it' => $db->ItemType), 
                                'ite.item_type_id = it.id', 
                                array('item_type_name' => 'it.name'))
                     ->joinLeft(array('svt' => $db->SimpledefinitionsTerm), 
                                'e.id = svt.element_id', 
                                array('simple_definitions_term_id' => 'svt.id'))
                     ->where('rt.name = "All" OR rt.name = "Item"')
                     ->order(array('es.name', 'it.name', 'e.name'));
        $elements = $db->fetchAll($select);
        $options = array('' => 'Select Below');
        foreach ($elements as $element) {
            $optGroup = $element['item_type_name'] ? 'Item Type: ' . $element['item_type_name']   : $element['element_set_name'];
            $value = $element['element_name'];
            if ($element['simple_definitions_term_id']) {
                $value .= ' *';
            }
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }
    
	private function _sanitizeDefListandReturnArray($deflist)
	{
		//the result is an array!!!
	    $termsArr = explode("\n", $deflist);
        $termsArr = array_map('trim', $termsArr);// trim all values
        $termsArr = array_filter($termsArr); // remove empty values
        $termsArr = array_unique($termsArr); // remove duplicate values	
	    return $termsArr;
	}
	
    private function _sanitizeTerms($terms)
    {
        $termsArr = explode("\n", $terms);
        $termsArr = array_map('trim', $termsArr);// trim all values
        $termsArr = array_filter($termsArr); // remove empty values
        $termsArr = array_unique($termsArr); // remove duplicate values
        $terms = implode("\n", $termsArr);
        $terms = trim($terms);
        return $terms;
    }

}