<?php
/**
<<<<<<< HEAD
  * elovalasztok acces control  és további globális funkciók         include file
=======
  * elovalasztok acces control  és szavazott már? funkciók         include file
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  */
<<<<<<< HEAD
  include_once JPATH_SITE.'/elovalasztok/funkciok.php';
  include_once JPATH_SITE.'/elovalasztok/config.php';
  
=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  global $config;
  $db = Jfactory::getDBO();
  $db->setQuery('select * from #__jumi where id=1');
  $res = $db->loadObject();
  $s = $res->custom_script;
  $config = JSON_decode($s);
  if (!isset($config->fordulo)) $config->fordulo = 0;
  if ($config->fordulo == '') $config->fordulo = 0;
  
  
  /**
    * adott user, már szavazott?
	* ha nincs bejelentkezve akkor false az eredménye
<<<<<<< HEAD
	* @param integer $szavazas_id 
	* @param JUser $user
	* @param integer $fordulo
  */	
  function szavazottMar($szavazas_id, $user, $fordulo = 0) {
	  global $evConfig;
	  $db = JFactory::getDBO();
	  if ($user->id > 0) {
		if (isOEVKszavazas($szavazas_id)) {  
	      $db->setQuery('select * from #__szavazatok 
		  where user_id='.$db->quote($user->id).' and fordulo = '.$db->quote($fordulo).'
		  and szavazas_id in ('.implode(',', $evConfig->oevkSzavazasok).')');
	      $res = $db->loadObjectList();
	      $result = (count($res) >= 1);
		}
		if (isBelsoSzavazas($szavazas_id)) {  
	      $db->setQuery('select * from #__szavazatok where user_id='.$db->quote($user->id).' and szavazas_id = '.$db->quote($szavazas_id));
	      $res = $db->loadObjectList();
	      $result = (count($res) >= 1);
		}
=======
	* @param integer $oevk (jelenleg csak formai okokból, valójában nincs használva)
	* @param JUser $user
	* @param integer $fordulo
  */	
  function szavazottMar($oevk, $user, $fordulo = 0) {
	  if ($user->id > 0) {
	    $db = JFactory::getDBO();
	    $db->setQuery('select * from #__szavazatok where user_id='.$db->quote($user->id).' and fordulo = '.$db->quote($fordulo));
	    $res = $db->loadObjectList();
	    $result = (count($res) >= 1);
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
	  } else {
		$result = false;  
	  }	
	  return $result;
  }

  /**
    * engedélyezett/nem egedélyezett az akció?
	* @param integer oevk (jelenleg csak formai okokból, valójában nincs használva)
	* @param Juser bejelentkezett user
	* @param string $akcio 'jeloltAdd','jeloltEdit','jeloltDelete','szavazas','szavazatEdit','szavazatDelete','eredmeny'
	* @param string $msg output parameter: tiltás oka pl: 'config'
	* @return boolean
  */
<<<<<<< HEAD
  function teheti($szavazas_id, $user, $akcio, &$msg) {
	global $evConfig;
=======
  function teheti($oevk, $user, $akcio, &$msg) {
	global $config;
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
	$result = false;
	$msg = '';
	$fordulo = $config->fordulo;

	if ($user->id <= 0) {
	   $msg = 'Jelentkezzen be!';
	   return false;
	}	

	if ($akcio == 'jeloltAdd') {		
<<<<<<< HEAD
	   if ($evConfig->jeloltAdd) {
		   if ($evConfig->userAdmin($user)) {	   
=======
	   if ($config->jeloltAdd) {
		   if (($user->groups[8] == 8) | ($user->groups[10] == 10)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			   $result = true;
			   $msg = '';
		   } else {
			   $result = false;
			   if ($user->id <= 0) {
				   $msg = 'Jelentkezzen be!';
			   } else {
				   $msg = 'Nincs ehhez joga!';
			   }
		   }
<<<<<<< HEAD
	   } else if ((isBelsoSzavazas($szavazas_id)) & (isMozgalomTag($user))) {
		   $result = true; 
	   } else {	   
=======
	   } else {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'jeloltEdit') {
<<<<<<< HEAD
	   if ($evConfig->jeloltEdit) {
		   if ($evConfig->userAdmin($user)) {	   
=======
	   if ($config->jeloltEdit) {
		   if (($user->groups[8] == 8) | ($user->groups[10] == 10)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			   $result = true;
			   $msg = '';
		   } else {
			   $result = false;
			   if ($user->id <= 0) {
				   $msg = 'Jelentkezzen be!';
			   } else {
				   $msg = 'Nincs ehhez joga!';
			   }
		   }
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'jeloltDelete')  {
<<<<<<< HEAD
	   if ($evConfig->jeloltDelete) {
		   if ($evConfig->userAdmin($user)) {	   
=======
	   if ($config->jeloltDelete) {
		   if (($user->groups[8] == 8) | ($user->groups[10] == 10)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			   $result = true;
			   $msg = '';
		   } else {
			   $result = false;
			   if ($user->id <= 0) {
				   $msg = 'Jelentkezzen be!';
			   } else {
				   $msg = 'Nincs ehhez joga!';
			   }
		   }
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'szavazas') {
<<<<<<< HEAD
	   if ($evConfig->szavazas) {
		  if (szavazottMar($szavazas_id, $user, $fordulo)) {
			  $result = false;
			  $msg = 'Ön már szavazott';
		  }  else {
			  if (szavazasraJogosult($user, $szavazas_id, '')) {
			    $result = true;
			    $msg = '';
			  } else {
			    $result = false;
			    $msg = 'Ön ebben a szavazásban nem szavazhat';
			  }	
=======
	   if ($config->szavazas) {
		  if (szavazottMar($oevk, $user, $fordulo)) {
			  $result = false;
			  $msg = 'Ön már szavazott';
		  }  else {
			  $result = true;
			  $msg = '';
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		  } 
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'szavazatEdit') {
<<<<<<< HEAD
	   if ($evConfig->szavazatEdit) {
		  if (szavazottMar($szavazas_id, $use, $fordulo)) {
=======
	   if ($config->szavazatEdit) {
		  if (szavazottMar($oevk, $use, $fordulo)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			  $result = true;
			  $msg = '';
		  }  else {
			  $result = false;
			  $msg = 'Ön még nem szavazott';
		  } 
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'szavazatDelete') {
<<<<<<< HEAD
	   if ($evConfig->szavazatDelete) {
		  if (szavazottMar($szavazas_id, $user, $fordulo)) {
=======
	   if ($config->szavazatDelete) {
		  if (szavazottMar($oevk, $user, $fordulo)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			  $result = true;
			  $msg = '';
		  }  else {
			  $result = false;
			  $msg = 'Ön még nem szavazott';
		  } 
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'eredmeny') {
<<<<<<< HEAD
	   if ($evConfig->eredmeny) {
=======
	   if ($config->eredmeny) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		   $result = true;
		   $msg = '';	
	   } else {
		   $result = false;
		   $msg='config';
	   }
	}
	return $result;
  }
 
?>
