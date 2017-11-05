<?php
/** 
 * ADA login interface object
 * @Licence: GNU/GPL
 * @Author: Tibor Fogler 
 * @Author email: tibor.fogler@gmail.com
 * @Verzsion: 4.00   
*/ 



class JoomlaInterface {
	public function remoteCall($url,$method,$data,$extraHeader='') {
		$result = '';
		if ($extraHeader != '') {
			$extraHeader .= "\r\n";
		}	
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n".$extraHeader,
				'method'=> $method,
				'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		return file_get_contents($url, false, $context);
		return $result;
	}
}

global $theJoomlaInterface;
$theJoomlaInterface = new JoomlaInterface();

class AdaloginModelAda_obj {
	public $joomla_psw;

	protected $ADA_AUTH_URI; 
	protected $ADA_USER_URI; 
	protected $ADA_TOKEN_URI; 
	protected $appkey; 
	protected $secret; 
	protected $myURI; 
	protected $home;
	protected $interface;

    function __construct($iface = false) {
        global $theJoomlaInterface;
		if($iface)
		{
			$this->interface = $iface;
		} else {
			$this->interface = $theJoomlaInterface;
        }
		$db = JFactory::getDBO();
		$db->setQuery('select * from #__adalogin order by id limit 1');
		$res = $db->loadObject();
		foreach ($res as $fn => $fv) {
			$this->$fn = $fv;
		}	
		$this->myURI = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$i = strpos($this->myURI,'?');
		if ($i > 0) $this->myURI = substr($this->myURI,0,$i);
		$this->home = str_replace('/components/com_adalogin','','https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$i = strpos($this->home,'?');
		if ($i > 0) $this->home = substr($this->home,0,$i);
		$this->home = str_replace('/ssologin','',$this->home); // for old ssologin interface enviroment
		$this->home = str_replace('/adalogin','',$this->home); // for old adalogin interface enviroment
    }

	/**
	* redirect ADA server, get loginform. Call this method only in joomla component.controller
	*/
	public function getLoginURI($redi='') {
	  $redirectURI = JURI::base().'components/com_adalogin/index.php';	
	  // $redirectURI = JURI::base().'ssologin/index.php';	uncomment this line in old ssologin enviroment
	  // $redirectURI = JURI::base().'adalogin/index.php';	uncomment this line in old adalogin enviroment
	  $redirectURI = str_replace('http:','https:',$redirectURI);
	  if ($redi != '') $redirectURI .= '?redi='.base64_encode($redi);
	  $url = $this->ADA_AUTH_URI.'?response_type=code&client_id='.$this->appkey.'&redirect_uri='.urlencode($redirectURI);
	  return $url;
	}
	
	/**
	  * call remote service
	  * @param string url
	  * @param string 'GET' or 'POST'
	  * @param array data  
	  * @param string extra header sor (optional)
	  * @return string
	*/
	public function remoteCall($url,$method,$data,$extraHeader='') {
        return $this->interface->remoteCall($url,$method,$data,$extraHeader);
	}
	
	/**
	  * get token from ADA server
	  * @param string code
	  * @return object  token {"access_token":"xxxxxxxx",......}
	*/  
	protected function getADAtoken($code) {
		$result = '';
		$token = new stdClass();
		$userdata = new stdClass();
		$url = $this->ADA_TOKEN_URI;
		$data = array('timeout' => 30,
						'redirection' => 10,
						'httpversion' => '1.0',
						'code' => $code, 
						'grant_type' => 'authorization_code',
						'client_id' => $this->appkey,
						'client_secret' => $this->secret,
						'redirect_uri' => $this->myURI
						);
		$result = $this->remoteCall($url,'POST',$data);
		if ($result != '') {
		   $token = JSON_decode($result);
		} 
		return $token;
	}
	
	/**
	  * get userData from ADA server
	  * @param object token  {"access_token":"xxxxxxxx",......}
	  * @return object  {"userid":"xxxxxxxx", "email":"xxxxxxxx",......}
	*/  
	protected function getADAuserData($token) {
		$userData = new stdClass();
		$url = $this->ADA_USER_URI;
		$data = array('timeout' => 30,
					'redirection' => 10,
					'httpversion' => '1.0',
				    'blocking' => true,
					'cookies' => array(),
				    'sslverify' => 'yes' 
	    );
		$extraHeader = 'Authorization: Bearer '.$token->access_token;
		$result = $this->remoteCall($url,'GET',$data,$extraHeader);
		if ($result != '') {
			$userData = JSON_decode($result);
		}
		return $userData;	
	}
	
	/**
	  * callback funtion for ADA server: redict into com_adalogin task=dologin by spec. CSRtoken
	  * @JRequest string code  ADA auth code
	  * @JRequest base64_ecoded redi  redirect URL after success joomla login base64_encoded
	  * @return void
	*/ 
	public function callback() {
		$input = JFactory::getApplication()->input;
		$db = JFactory::getDBO();
		$token = $this->getADAtoken($input->get('code'));
		$CSRtoken = '';
		// get user data
		if (isset($token->access_token)) {
			$userData = $this->getADAuserData($token);
		}
		if (is_array($userData->assurances)) {
			$userData->assurances = JSON_encode($userData->assurances);
		}
		//$this->home = str_replace('https:','http:',$this->home); //uncomment if https: not supported
		
		if (isset($userData->userid)) {
			$session = JFactory::getSession();
			$config = JFactory::getConfig();
			$CSRtoken = md5($userData->userid.$config->secret);
			echo '<html>
			<body>
			<h2>ADA login client ...</h2>
			<form name="form1" method="post" action='.$db->quote($this->home).' target="_top">
			<input type="hidden" name="option" value="com_adalogin" />
			<input type="hidden" name="task" value="dologin" />
			<input type="hidden" name="Itemid" value="0" />
			<input type="hidden" name="adaid" value='.$db->quote($userData->userid).' />
			<input type="hidden" name="adaemail" value='.$db->quote($userData->email,'','string').' />
			<input type="hidden" name="assurance" value='.$db->quote($userData->assurances,'','string').' />
			<input type="hidden" name="redi" value='.$db->quote($input->get('redi','','string')).' />
			<input type="hidden" name="'.$CSRtoken.'" value="1" />
			</form>
			<script type="text/javascript">
			  if (window.opener) {
			    window.opener.location="'.$this->home.'?option=com_adalogin"+
			    "&task=dologin&Itemid=0"+
			    "&'.$CSRtoken.'=1"+
			    "&adaid='.$userData->userid.'"+
			    "&adaemail='.urlencode($userData->email).'"+
			    "&assurance='.urlencode($userData->assurances).'"+
			    "&redi='.$input->get("redi","","string").'";
			    window.close();
			  } else {
			    document.forms.form1.submit();
			  }	
			</script>
			</body>
			</html>
			';
		} else {
			echo '<html>
			<body>
			<h2>ADA login client Fatal error, not get userData from ADA server.</h2>
			</body>
			</html>
			';
		}
	} // callback 	
} // class

?>
