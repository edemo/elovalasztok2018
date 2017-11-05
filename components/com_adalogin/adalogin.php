<?php
/**
* @version 4.00
* @package	Adalogin
* @copyright	Copyright (C) 2016, Fogler Tibor. All rights reserved.
* @license #GNU/GPL
 */

//--No direct access
defined('_JEXEC') or die('=;)');
// DS has removed from J 3.0
if(!defined('DS')) {
	define('DS','/');
}
require_once( JPATH_COMPONENT.'/controller.php' );
jimport('joomla.application.component.model');
require_once( JPATH_COMPONENT.'/models/model.php' );
jimport('joomla.application.component.helper');
JHTML::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers' );
$input = JFactory::getApplication()->input;
$task = $input->get('task','loginform');

$config = array('redi' => '');
$app = JFactory::getApplication();
$menu = $app->getMenu();
if (is_object($menu )) {
	$active = $menu->getActive();
	if (is_object($active)) {
		$config['redi'] = $active->params->get('redi');
	}	
}
$controller   = new AdaloginController($config);
$controller->execute($task);
$controller->redirect();
?>