<?php
/**
  * elovalasztok acces control          include file
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  */
  defined('_JEXEC') or die;
  include_once dirname(__FILE__).'/funkciok.php';
  include_once dirname(__FILE__).'/config.php';
  
  global $evConfig;
  if (!isset($evConfig->fordulo)) $evConfig->fordulo = 0;
  if ($evConfig->fordulo == '') $evConfig->fordulo = 0;
  
  /**
    * engedélyezett/nem egedélyezett az akció?
	* @param integer oevk (jelenleg csak formai okokból, valójában nincs használva)
	* @param Juser bejelentkezett user
	* @param string $akcio 'jeloltAdd','jeloltEdit','jeloltDelete','szavazas','szavazatEdit','szavazatDelete','eredmeny'
	* @param string $msg output parameter: tiltás oka pl: 'config'
	* @return boolean
  */
  function teheti($szavazas_id, $user, $akcio, &$msg) {
	global $evConfig;
	$result = false;
	$msg = '';
	$fordulo = $config->fordulo;
	
	// eredmény lekérdezés látogatónak is megengedett
	if ($akcio == 'eredmeny') {
	   if ($evConfig->eredmeny) {
		   $result = true;
		   $msg = '';	
	   } else {
		   $result = false;
		   $msg='config';
	   }
	   return $result;
	}
	
	// szavazás gomb legyen aktiv guestnek is (marketing)
	if (($akcio == 'szavazas') & ($user->id == 0)) {
	   if ($evConfig->szavazas) {
		   $result = true;
		   $msg = '';	
	   } else {
		   $result = false;
		   $msg='config';
	   }
	   return $result;
	}
	
	if ($user->id <= 0) {
	   $msg = 'Jelentkezzen be!';
	   return false;
	}	

	if ($akcio == 'jeloltAdd') {		
	   if ($evConfig->jeloltAdd) {
		   if ($evConfig->userAdmin($user)) {	   
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
	   } else if ((isBelsoSzavazas($szavazas_id)) & (isMozgalomTag($user))) {
		   $result = true; 
	   } else {	   
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'jeloltEdit') {
	   if ($evConfig->jeloltEdit) {
		   if ($evConfig->userAdmin($user)) {	   
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
	   if ($evConfig->jeloltDelete) {
		   if ($evConfig->userAdmin($user)) {	   
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
		  } 
	   } else {
		   $result = false;
		   $msg='config';
	   }
	} else if ($akcio == 'szavazatEdit') {
	   if ($evConfig->szavazatEdit) {
		  if (szavazottMar($szavazas_id, $use, $fordulo)) {
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
	   if ($evConfig->szavazatDelete) {
		  if (szavazottMar($szavazas_id, $user, $fordulo)) {
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
	}
	return $result;
  }
 
?>