<?php 
/**
* @version		4.00
* @package		Adalogin
* @subpackage 	Models
* @copyright	Copyright (C) 2016, Fogler Tibor. All rights reserved.
* @license #GNU/GPL
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Model
 * @author Michael Liebler
 */
 
jimport( 'joomla.application.component.model' ); 

/**
* this component not use model
*/
class AdaloginModelAdalogin extends JModelLegacy  { 
  protected $controller;
  protected $errorMsg;
  public $PSW;
  
  /**
  * get user object from jooma
  * @param string adaid
  * @param string adaemail
  * @return if exists JUser else {id:0}
  */
  public function getUser($adaid, $adaemail) {
	$usr = 0;
	$result = new stdClass();
	$result->id = 0;
	$db = JFactory::getDBO();
	$db->setQuery('select * from #__users where email = '.$db->quote($adaemail));
	$res = $db->loadObject();
	if ($res) {
		$result = JFactory::getUser($res->id);
	} else {	
	  $db->setQuery('select * from #__users where username = '.$db->quote($adaid));
	  $res = $db->loadObject();
	  if ($res) {
		$result = JFactory::getUser($res->id);
	  } else {
		$db->setQuery('select * from #__users where params like "{%\"ADA\":\"'.$db->quote($adaid).'\"%"');
		$res = $db->loadObject();
		if ($res) {
		  $result = JFactory::getUser($res->id);
		}  
	  } 
    }	  
	if ($result->id == 0)
		$this->setError(JText::_('ADALOGIN_JOOMLA_LOGIN_ERROR'));
	return $result;
  }
  
  /**
  * check new user nick
  * @param string nick
  * @return boolean and set errorMsg
  */
  public function checkNewNick($nick) {
	$result = true;  
	$db = JFactory::getDBO();
	$db->setQuery('select * from #__users where username = '.$db->quote($nick));
	$res = $db->loadObject();
	if ($res) {
		$result = false;
		$this->setError(JText::_('ADALOGIN_NICK_USED'));
	}
	if ($nick == '') {
		$result = false;
		$this->setError(JText::_('ADALOGIN_NICK_REQUED'));
	}
	return $result;
  }	

  /**
  * create joomla account
  * @param string adaid
  * @param string nick
  * @param string adaemail
  * @param JSON_encoded assurance
  * @return boolean and set errorMsg  
  */
  public function save($adaid, $nick, $adaemail, $assurance) {
	$result = true;
	$data = array(
          "name"=>$nick,
          "username"=>$nick,
          "password"=>$this->PSW,
          "password2"=>$this->PSW,
		  "params"=>JSON_decode('{"ADA":"'.$adaid.'","ASSURANCE":"'.$assurance.'"}'),
		  "activation"=>"",
          "email"=>$adaemail,
          "block"=>0,
          "groups"=>array("1","2")
    );
    $user = new JUser();
    if(!$user->bind($data)) {
		  $result = false;
          $this->setError(JText::_('ADALOGIN_ERROR').' '. $user->getError());
    }
    if (!$user->save()) {
		  $result = false;
          $this->setError(JText::_('ADALOGIN_ERROR').' '. $user->getError());
    }
	return $result;	
  }

  /**
  * login to joomla 
  * @param string adaid
  * @param string $adaemail
  * @return boolean and set errorMsg
  */
  public function loginToJoomla($adaid, $adaemail) {
    $user = $this->getUser($adaid, $adaemail); 
	$credentials = array();
	$credentials['username'] = $user->username;
	$credentials['password'] = $this->PSW;
	$user->id = 0; 
	$result = JFactory::getApplication()->login($credentials);
	if ($result == false) $this->setError('Error in Joomla login');
	return $result;
  }
  /**
  * user assurance tárolása a joomla adatbázisba
  * @param JUser
  * @param string assurance
  * return void  
  */  
  public function setUserAssurances($user, $assurance) {
	if (is_object($user)) {  
		if ($assurance != $user->getParam('ASSURANCE')) {
			$user->setParam('ASSURANCE',$assurance);
			$user->save();
		}  
	}
  }	
}
?> 