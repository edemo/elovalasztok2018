<?php
defined('_JEXEC') or die;

// el�v�lasztok rendszer glob�lis konstansok, funkci�k, objektumok

include_once dirname(__FILE__).'/config.php';


/**
* szavaz�s tipus check $szavazas_id egy adott tipus� szavaz�s azonosit�?
* @param integer $szavazas.ID
* @return boolean
*/
function isBelsoSzavazas($szavazas_id) {
	global $evConfig;
	return in_array($szavazas_id, $evConfig->belsoSzavazasok);
}
function isNyilvanosSzavazas($szavazas_id) {
	return false;
}
function isOevkSzavazas($szavazas_id) {
	global $evConfig;
	return in_array($szavazas_id, $evConfig->oevkSzavazasok);
}
function isOrszagosListaSzavazas($szavazas_id) {
	global $evConfig;
	return in_array($szavazas_id, $evConfig->OrszagosListaSzavazasok);
}
function isMiniszterelnokSzavazas($szavazas_id) {
	global $evConfig;
	return in_array($szavazas_id, $evConfig->miniszterElnokSzavazasok);
}

/**
* oevk k�d meg�llap�t�sa a jel�lt.ID -b�l
* @param integer jelolt.ID
* @return integer  (0 ha nem siker�lt oevk-t meg�llap�tani)
*/
function oevkFromJelolt($jelolt_id) {
	$result = 0;
	$db = JFactory::getDBO();
	$db->setQuery('select catid from #__content where id='.$db->quote($jelolt_id));
	$res = $db->loadObject();
	if ($res) {
		if (isOevkSzavazas($res->catid))
			$result = $res->catid;
	}
	return $result;
}

/**
* oevk k�d meg�llap�t�sa a User adatokb�l
* @param JUser
* @return integer  (0 ha nem siker�lt oevk-t meg�llap�tani)
*/
function oevkFromUser($user) {
	$result = 0;
	$db = JFactory::getDBO();
	$db->setQuery('SELECT c.id 
	FROM #__user_usergroup_map AS ug, 
	     #__usergroups AS g,
	     #__categories AS c
	WHERE g.id = ug.group_id AND c.alias = g.title AND ug.user_id='.$db->quote($user->id));
	$res = $db->loadObjectList();
	if (count($res) == 1) {
		if (isOevkSzavazas($res[0]->id))
			$result = $res[0]->id;
	}
	//TEST if ($user->id > 0) $result = 9;
	return $result;
}

/** user rang meg�llap�t�sa
* @param JUser
* @return boolean
*/
function isElovalasztokAdmin($user) {
	global $evConfig;
	return $evConfig->userAdmin($user);
} 

/**
    * adott user, m�r szavazott?
	* ha nincs bejelentkezve akkor false az eredm�nye
	* @param integer $szavazas_id 
	* @param JUser $user
	* @param integer $fordulo
 */	
 function szavazottMar($szavazas_id, $user, $fordulo = 0) {
	  global $evConfig;
	  if ($szavazas_id == $evConfig->probaSzavazas) return false;
	  $db = JFactory::getDBO();
	  $result = false;
	  if ($user->id > 0) {
		if (isOEVKszavazas($szavazas_id)) {  
	      $db->setQuery('select * from #__szavazatok 
		  where user_id='.$db->quote($user->id).' and fordulo = '.$db->quote($fordulo).'
		  and szavazas_id = '.$db->quote($szavazas_id));
	      $res = $db->loadObjectList();
	      $result = (count($res) >= 1);
		}
		if (isBelsoSzavazas($szavazas_id)) {  
	      $db->setQuery('select * from #__szavazatok where user_id='.$db->quote($user->id).' and szavazas_id = '.$db->quote($szavazas_id));
	      $res = $db->loadObjectList();
	      $result = (count($res) >= 1);
		}
	  } else {
		$result = false;  
	  }	
	  return $result;
 }


/**
* a user tagja a valasztoimozgalom -nak?
* @param JUser
* @return boolean
*/
function isMozgalomTag($user) {
	global $evConfig;
	$result = false;
	$db = JFactory::getDBO();
	$db->setQuery('select profile_value
	from #__user_profiles
	where user_id='.$db->quote($user->id).' and profile_key="profile.mozgalom"');
	$res = $db->loadObject();
	if ($res) {
		$result = ($res->profile_value == '"Igen"');
	}
	if (isElovalasztokAdmin($user)) $result = true;
	// TEST if (strpos('magyar',$user->params) <= 0) $result = false;
	return $result;
}

function szavazasraJogosult($user, $szavazas_id, $assurance='') {
   global $evConfig;
   if ($szavazas_id == $evConfig->probaSzavazas) return true;
   $db = JFactory::getDBO();
   if ($assurance == '') { 
	   if (isBelsoSzavazas($szavazas_id)) $assurance = 'v�laszt�imozgalom';
	   if (isOEVKSzavazas($szavazas_id)) {
		   $db->setQuery('select title from #__categories where id='.$db->quote($szavazas_id));
		   $res = $db->loadObject();
		   if ($res) $assurance = $res->title;
	   }	   
	   if (isNyilvanosSzavazas($szavazas_id)) $assurance = 'magyar';
	   if (isOrszagosListaSzavazas($szavazas_id)) $assurance = 'magyar';
	   if (isMiniszterelnokSzavazas($szavazas_id)) $assurance = 'magyar';
   }
   if ($evConfig->canAssurance) {
	   $db->setQuery('select count(id) cc
	   from #__users 
	   where id='.$db->quote($user->id).' and params like "%'.$assurance.'%"' );   
   } else {
	   $db->setQuery('select count(id) cc
	   from #__users 
	   where id='.$db->quote($user->id));   
   }
   return $db->loadObject()->cc > 0;
}

// h�ny szavaz�sra jogosult van az adott szavaz�sban?
function szavazokSzama($szavazas_id, $assurance='') {
	global $evConfig;
   $db = JFactory::getDBO();
   if ($assurance == '') {
	   if (isBelsoSzavazas($szavazas_id)) $assurance = 'v�laszt�imozgalom';
	   if (isOEVKSzavazas($szavazas_id)) {
		   $db->setQuery('select title from #__categories where id='.$db->quote($szavazas_id));
		   $res = $db->loadObject();
		   if ($res) $assurance = $res->title;
	   }	   
	   if (isNyilvanosSzavazas($szavazas_id)) $assurance = 'magyar';
	   if (isOrszagosListaSzavazas($szavazas_id)) $assurance = 'magyar';
	   if (isMiniszterelnokSzavazas($szavazas_id)) $assurance = 'magyar';
   }
   $db->setQuery('select count(id) cc
   from #__users
   ');   
   $res = $db->loadObject();
   return $res->cc;
}

// debian alkotm�ny szerinti sz�ks�ges javaslat
function getSzuksegesJavaslat($szavazas_id,$assurance='') {
	global $evConfig;
   if ($assurance == '') {
	   if (isBelsoSzavazas($szavazas_id)) $assurance = 'v�laszt�imozgalom';
	   if (isOEVKSzavazas($szavazas_id)) {
		   $db->setQuery('select title from &__categories where id='.$db->quote($szavazas_id));
		   $res = $db->loadObject();
		   if ($res) $assurance = $res->title;
	   }	   
	   if (isNyilvanosSzavazas($szavazas_id)) $assurance = 'magyar';
	   if (isOrszagosListaSzavazas($szavazas_id)) $assurance = 'magyar';
	   if (isMiniszterelnokSzavazas($szavazas_id)) $assurance = 'magyar';
   }
   $letszam = szavazokSzama($szavazas_id,$assurance);
   return round(sqrt($letszam) / 2) + 1;
}

?>