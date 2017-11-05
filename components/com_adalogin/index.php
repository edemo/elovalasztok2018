<?php
/** 
 * ADA login interface for joomla 3.x - ADA client main script
 * @Licence: GNU/GPL
 * @Author: Tibor Fogler 
 * @Author email: tibor.fogler@gmail.com
 * @Version: 4.00  
*/ 

// init joomla framwork
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
$s = str_replace(DS.'components'.DS.'com_adalogin','',dirname(__FILE__) );
$s = str_replace(DS.'ssologin','',$s); // for old ssologin enviroment
$s = str_replace(DS.'adalogin','',$s); // for old adalogin enviroment
define('JPATH_BASE', $s);
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_BASE .DS.'components'.DS.'com_adalogin'.DS.'models'.DS.'ada_obj.php');
JDEBUG ? $_PROFILER->mark( 'afterLoad' ) : null;
$mainframe = JFactory::getApplication('site');
$mainframe->initialise();
jimport('joomla.plugin.helper');
jimport('joomla.user.helper');
$input = JFactory::getApplication()->input;

// process code
if ($input->get('code','','string')) {
  $ada = new AdaloginModelAda_obj();
  $ada->callback();
}
?>
