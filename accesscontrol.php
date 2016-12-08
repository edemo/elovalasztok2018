<?php
/**
  * elovalasztok acces control  és szavazott már? funkciók         include file
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  */
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
  function teheti($oevk, $user, $akcio, &$msg) {
	global $config;
	$result = false;
	$msg = '';
	$fordulo = $config->fordulo;

	if ($user->id <= 0) {
	   $msg = 'Jelentkezzen be!';
	   return false;
	}	

	if ($akcio == 'jeloltAdd') {		
	   if ($config->jeloltAdd) {
		   if (($user->groups[8] == 8) | ($user->groups[10] == 10)) {
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
	} else if ($akcio == 'jeloltEdit') {
	   if ($config->jeloltEdit) {
		   if (($user->groups[8] == 8) | ($user->groups[10] == 10)) {
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
	   if ($config->jeloltDelete) {
		   if (($user->groups[8] == 8) | ($user->groups[10] == 10)) {
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
	   if ($config->szavazas) {
		  if (szavazottMar($oevk, $user, $fordulo)) {
			  $result = false;
			  $msg = 'Ön már szavazott';
		  }  else {
			  $result = true;
			  $msg = '';
		  } 
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'szavazatEdit') {
	   if ($config->szavazatEdit) {
		  if (szavazottMar($oevk, $use, $fordulo)) {
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
	   if ($config->szavazatDelete) {
		  if (szavazottMar($oevk, $user, $fordulo)) {
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
	   if ($config->eredmeny) {
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
