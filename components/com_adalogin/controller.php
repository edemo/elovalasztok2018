<?php
/**
* @version		4.00
* @package		Adalogin
* @subpackage 	Controllers
* @copyright	Copyright (C) 2016, Fogler Tibor. All rights reserved.
* @license #GNU/GPL
*
* ADA authoraze service integarttion
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
require_once (JPATH_COMPONENT.DS.'models'.DS.'ada_obj.php');

/**
 * Variant Controller
 *
 * @package    
 * @subpackage Controllers
 */
class AdaloginController extends JControllerLegacy
{
	protected $redi;
	protected $_viewname = 'adalogin';
	protected $_mainmodel = 'adalogin';
	protected $_context = "com_adalogin";
	protected $ADA_AUTH_URI; 
	protected $ADA_USER_URI; 
	protected $ADA_TOKEN_URI; 
	protected $appkey; 
	protected $secret; 
	protected $joomla_psw;

	/**
	* Constructor
	*/
	public function __construct($config = array ()) {
		parent :: __construct($config);
		$this->redi = '';
		$input = JFactory::getApplication()->input;
		$this->redi = $input->get('redi','');
		if (strpos($this->redi,'.') <= 0)
		   $this->redi = base64_decode($this->redi); // 
		if ($this->redi == '') $this->redi = JURI::base();
		$input->set('view', $this->_viewname);
	}

	/**
	* only techical. Joomla MVC requed
	*/
	public function display() {
		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view = & $this->getView($this->_viewname,$viewType);
		$model = & $this->getModel($this->_mainmodel);
		$view->setModel($model,true);		
		$view->display();
	}

	/**
	* default task, call ADA login form
	*/
	public function loginform() {
	  $ada = new AdaloginModelAda_obj();	
	  $url = $ada->getLoginURI($this->redi);
	  $host = $_SERVER['HTTP_HOST'];
	  $config = JFactory::getConfig();
	  // lokális teszthez
	  if (($host == 'robitc') | ($host == 'localhost')) {
	    $url = JURI::root().'index.php?option=com_adalogin&task=dologin'.
	           '&'.md5('123456'.$config->secret).'=1'.
	           '&adaid=123456&adaemail=123456@adatom.hu&assurance=magyar,email'.
		       '&redi='.base64_encode($this->redi);
		  ?>	   
		  <script type="text/javascript">
			jQuery(function() {
				if (window.opener) {
					// popup ablakban fut
					window.opener.location = "<?php echo $url; ?>";
					window.close();
				} else {
					// normál ablakban fut
					window.location = "<?php echo $url; ?>";
				}
			});
		  </script>
		  <?php
	  } else {		   
		  ?>
		  <script type="text/javascript">
			jQuery(function() {
				if (window.opener) {
					// popup ablakban fut
					window.location = "<?php echo $url; ?>";
				} else {
					// normál ablakban fut
					open('<?php echo $url; ?>','ADA','width=370,height=600,left=100,top=100');
				}
			});
		  </script>
		  <?php
	  }
	}
	
	/**
	* display ada_user_regist form
	*/
	protected function displayRegistForm($view, $adaid, $adaemail, $assurance, $redi) {
		$view->set('adaid',$adaid);
		$view->set('adaemail',$adaemail);
		$view->set('assurance',$assurance);
		$view->set('redi',base64_encode($redi));
		$view->setLayout('regist');
		$view->display();
	}
	
	/**
	* process adaid, adaemail, assurance, redi , CSRFtoken data from components/com_adalogin/index.php
	* CSRtoken = md5($adait.$config->secret)
	*/
    public function dologin() {
		$config = JFactory::getConfig();
		$input = JFactory::getApplication()->input;
		$adaid = $input->get('adaid');
		$adaemail = $input->get('adaemail','','string');
	    $assurance = $input->get('assurance','','string');
		$redi = base64_decode($input->get('redi','','string'));
		if ($input->get(md5($adaid.$config->secret)) != 1) {
			echo 'invalid token';
			exit();
		};
		if ($redi == '') $redi = JURI::root();
		
		// nem jó a redi képzés :(
		//$redi = JURI::root();

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView($this->_viewname,$viewType);
		$model = $this->getModel($this->_mainmodel);
	    $ada = new AdaloginModelAda_obj();	
		$model->set('PSW',$ada->joomla_psw);
		$view->setModel($model,true);
		$user = $model->getUser($adaid, $adaemail);
		if ($user->id > 0) {
			$model->setUserAssurances($user, $assurance);
			// login to joomla 
			if ($model->loginToJoomla($adaid, $adaemail)) {
				// goto $redi
				$this->setRedirect($redi);
				$this->redirect();
			} else {
				echo '<p class="errorMsg">'.$model->getError().'</p>';
			}
		} else {
			//+ 2017.02.08 no ask nickname
			// $this->displayRegistForm($view, $adaid, $adaemail, $assurance, $redi);
			//- 2017.02.08 no ask nickname

			//+ 2017.02.08 no ask nickname start new code
			$nick = $adaid;
			if ($model->save($adaid, $nick, $adaemail, $assurance)) {
				$user = $model->getUser($adaid, $adaemail);
				// login to joomla 
				if ($model->loginToJoomla($adaid, $adaemail)) {
					$model->setUserAssurances($user, $assurance);
					// goto $redi
					$this->setRedirect($redi);
					$this->redirect();
				} else {
					echo '<p class="errorMsg">'.$model->getError().'</p>';
				}
			} else {
					echo '<p class="errorMsg">'.$model->getError().'</p>';
			}	
			//- 2017.02.08 no ask nickname end new code
			
		}	
	}	// dologin

	/**
	 * process logout
	 */
	public function dologout() {
		$app = JFactory::getApplication();
		$app->logout();

		$document = JFactory::getDocument();
		$view = $this->getView($this->_viewname, $document->getType());
		$model = &$this->getModel($this->_mainmodel);
		$view->setModel($model,true);
		$view->setLayout('logout');
		$view->display();
	}
	
	/**
	* process registform  adaid, adaemail, nick, assurance, redi , CSRF_token data from components/com_adalogin/index.php
	*/
	public function processform() {
		JSession::checkToken() or die( 'Invalid Token' );
		$input = JFactory::getApplication()->input;
		$adaid = $input->get('adaid');
		$adaemail = $input->get('adaemail','','string');
	    $assurance = $input->get('assurance','','string');
		$redi = base64_decode($input->get('redi','','string'));
		if ($redi == '') $redi = JURI::base();	
	    $nick = $input->get('nick');
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView($this->_viewname,$viewType);
		$model = $this->getModel($this->_mainmodel);
	    $ada = new AdaloginModelAda_obj();	
		$model->set('PSW',$ada->joomla_psw);
		$view->setModel($model,true);
		if ($model->checkNewNick($nick)) {
			if ($model->save($adaid, $nick, $adaemail, $assurance)) {
				// login to joomla 
				if ($model->loginToJoomla($adaid, $adaemail)) {
					$model->setUserAssurances($user, $assurance);
					// goto $redi
					$this->setRedirect($redi);
					$this->redirect();
				} else {
					echo '<p class="errorMsg">'.$model->getError().'</p>';
				}
			} else {
				echo '<p class="errorMsg">'.$model->getError().'</p>';
			}		
		} else {
			// display regist form
			echo '<p class="errorMsg">'.$model->getError().'</p>';
			$this->displayRegistForm($view, $adaid, $adaemail, $assurance, $redi);
		}
	} // processform
}// class
?>
