<?php
// el�v�lasztok rendszer glob�lis konstansok, funkci�k, objektumok

include_once dirname(__FILE__).'/config.php';

// ez a kategoria egy bels� szavaz�s?
function isBelsoSzavazas($szavazas_id) {
	global $evConfig;
	return in_array($szavazas_id, $evConfig->belsoSzavazasok);
}

// ez a kategoria egy nyilv�nos szavaz�s?
function isNyilvanosSzavazas($szavazas_id) {
	return false;
}

// szavaz�s kategori�kez

function isOEVKszavazas($szavazas_id) {
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
// user rang

function isElovalasztokAdmin($user) {
	global $evConfig;
	return $evConfig->userAdmin($user);
} 

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