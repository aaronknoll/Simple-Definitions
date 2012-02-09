<?php


add_plugin_hook('install', 'SimpleDefinitionsPlugin::install');
add_plugin_hook('uninstall', 'SimpleDefinitionsPlugin::uninstall');
add_plugin_hook('initialize', 'SimpleDefinitionsPlugin::initialize');
add_filter('admin_navigation_main', 'SimpleDefinitionsPlugin::addToNav');


class SimpleDefinitionsPlugin
{
    public static function install()
    {
        $db = get_db();
        $sql = "
        CREATE TABLE `{$db->prefix}simple_definitions` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `def_term` varchar(255) collate utf8_unicode_ci default NULL,
     		 `definitions` text collate utf8_unicode_ci,
            PRIMARY KEY (`id`),
            UNIQUE KEY `def_term` (`def_term`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);
    }
    
    public static function uninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}simple_definitions`;";
        $db->query($sql);
    }
    
    public static function initialize()
    {
      $front = Zend_Controller_Front::getInstance();
      $front->registerPlugin(new SimpleDef_Controller_Plugin_SelectFilter);
    }
    
    public static function addToNav($nav)
    {
        $nav['Definitions'] = uri('simple-definitions');
        return $nav;
    }
}

class SimpleDef_Controller_Plugin_SelectFilter extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $db = get_db();
        
        // Set NULL modules to default. Some routes do not have a default 
        // module, which resolves to NULL.
        $module = $request->getModuleName();
        if (is_null($module)) {
            $module = 'default';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        // Include all item actions that render an element form, including 
        // actions requested via AJAX.
        $routes = array(
            array('module' => 'default', 
                  'controller' => 'items', 
                  'actions' => array('add', 'edit', 'element-form', 'change-type'))
        );
        
        // Allow plugins to add routes that contain form inputs rendered by 
        // Omeka_View_Helper_ElementForm::_displayFormInput().
        $routes = apply_filters('simple_definitions_routes', $routes);
        
        // Apply filters to defined routes.
        foreach ($routes as $route) {
            if ($route['module'] === $module 
             && $route['controller'] === $controller 
             && in_array($action, $route['actions'])) {
                $simpledefinitionsTerms = $db->getTable('SimpledefinitionsTerm')->findAll();
                foreach ($simpledefinitionsTerms as $simpledefinitionsTerm) {
                    $element = $db->getTable('Element')->find($simpledefinitionsTerm->element_id);
                    $elementSet = $db->getTable('ElementSet')->find($element->element_set_id);
                    add_filter(array('Form', 
                                     'Item', 
                                     $elementSet->name, 
                                     $element->name), 
                               array($this, 'filterElement'));
                }
                // Once the filter is applied for one action it is applied
                // for all subsequent actions, so there is no need to
                // continue looping the routes.
                break;
            }
        }
    }
    
    public function filterElement($html, $inputNameStem, $value, $options, 
                                    $record, $element)
    {
        $db = get_db();
        $simpledefinitionsTerm = $db->getTable('SimpledefinitionsTerm')->findByElementId($element->id);
        $terms = explode("\n", $simpledefinitionsTerm->terms);
        $selectTerms = array('' => 'Select Below') + array_combine($terms, $terms);
        return __v()->formSelect($inputNameStem . '[text]',
                                 $value,
                                 $options,
                                 $selectTerms);
    }
	
}
