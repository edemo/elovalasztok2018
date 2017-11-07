<?php
defined('_JEXEC') or die;

// elõválasztok rendszer globális konstansok, funkciók, objektumok

include_once dirname(__FILE__).'/config.php';


/**
* szavazás tipus check $szavazas_id egy adott tipusú szavazás azonositó?
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
	return in_array($szavazas_id, $evConfig->orszagosListaSzavazasok);
}
function isMiniszterelnokSzavazas($szavazas_id) {
	global $evConfig;
	return in_array($szavazas_id, $evConfig->miniszterElnokSzavazasok);
}

/**
* oevk kód megállapítása a jelölt.ID -bõl
* @param integer jelolt.ID
* @return integer  (0 ha nem sikerült oevk-t megállapítani)
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
* oevk kód megállapítása a User adatokból
* @param JUser
* @return integer  (0 ha nem sikerült oevk-t megállapítani)
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

/** user rang megállapítása
* @param JUser
* @return boolean
*/
function isElovalasztokAdmin($user) {
	global $evConfig;
	return $evConfig->userAdmin($user);
} 

/**
    * adott user, már szavazott?
	* ha nincs bejelentkezve akkor false az eredménye
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
		  // meghatározom a szavazás kategoria id-t
		  $db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
		  $res = $db->loadObject();
		  if ($res)
			$catid = $res->parent_id;
		  else
			$catid = 0;
 
	      $db->setQuery('select * 
			from #__szavazatok sz, #__categories c
			where sz.szavazas_id = c.id and 
			sz.user_id='.$db->quote($user->id).' and 
			fordulo = '.$db->quote($fordulo).' and 
			c.parent_id = '.$db->quote($catid));
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
 * a bejelentkezett user melyik oevk -ban adott le már szavazatot, és milyent?
 * a szavazas_id alapján meghatározzuk a felsőbb szintű kategoriát (pld országos evk szavazás)
 * szavazas -> CATID
 * ezután olyan szavazatokat keresünk amik ez alá tartozó szavazásra vonatkoznak
 * ha nincs ilyen akkor ''-t ad vissza, ha van akkor a megtalált
 * szavazás kategoriájának a nevét és a leadott sorrendet (html)
 * szavzat-szavazas where userid= USERID and catid = CATID
 * @param integer szavazas_id
 * @param JUser bejelentkezett user rekord
 * @param integer forduló def.0
 * @return string html
 */	
 function holSzavazott($szavazas_id, $user, $fordulo=0) {
 	$result = '';
	$defCatid = 8; // oevk szavazások
	if ($szavazas_id == $evConfig->probaSzavazas) return $result;
	$db = JFactory::getDBO();
	if ($user->id > 0) {
		  // meghatározom a szavazás kategoria id-t
		  $db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
		  $res = $db->loadObject();
		  if ($res)
			$catid = $res->parent_id;
		  else
			$catid = $defCatid;
		  if ($catid == 0) $catid = $defCatid;

		  // beolvasom a leadott szavazat rekordjait 
	      $db->setQuery('select c.title szavazas, sz.pozicio, a.title jelolt 
			from #__szavazatok sz, #__categories c, #__content a
			where sz.szavazas_id = c.id and sz.alternativa_id = a.id and 
			sz.user_id='.$db->quote($user->id).' and 
			fordulo = '.$db->quote($fordulo).' and 
			c.parent_id = '.$db->quote($catid).'
			order by pozicio');
	      $res = $db->loadObjectList();
		  if (count($res) == 0) {
			// most egyenlőre a catid=8 országgyülési választással foglalkozunk...	
	      	$db->setQuery('select c.title szavazas, sz.pozicio, a.title jelolt 
			from #__szavazatok sz, #__categories c, #__content a
			where sz.szavazas_id = c.id and sz.alternativa_id = a.id and 
			sz.user_id='.$db->quote($user->id).' and 
			fordulo = '.$db->quote($fordulo).' and 
			c.parent_id = 8
			order by pozicio');
			$res = $db->loadObjectList();
		  }
	
		  if (count($res) > 0) {
			$result = '
			Ön már szavazott a <strong>'.$res[0]->szavazas.'</strong> szavazásban&nbsp; 
		    <input id="szavazott-toggle" class="toggle" type="checkbox" />
			<label class="hider" for="szavazott-toggle">több...</label>
			<label class="shower" for="szavazott-toggle">kevesebb...</label>
			<div class="additional-content" for="szavazott-toggle">';
			foreach ($res as $res1) {
			   $result .= '<p>'.$res1->pozicio.'. '.$res1->jelolt.'</p>
			   ';
			}
			$result .= '</div>
			';
		  }
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
	   if (isBelsoSzavazas($szavazas_id)) $assurance = 'választóimozgalom';
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

// hány szavazásra jogosult van az adott szavazásban?
function szavazokSzama($szavazas_id, $assurance='') {
	global $evConfig;
   $db = JFactory::getDBO();
   if ($assurance == '') {
	   if (isBelsoSzavazas($szavazas_id)) $assurance = 'választóimozgalom';
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

// debian alkotmány szerinti szükséges javaslat
function getSzuksegesJavaslat($szavazas_id,$assurance='') {
	global $evConfig;
   if ($assurance == '') {
	   if (isBelsoSzavazas($szavazas_id)) $assurance = 'választóimozgalom';
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
