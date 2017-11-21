<?php
 /**
  * szavazok model
  *   taskok: szavazok, szavazatEdit, szavazatDelete, eredmeny, szavazatSave
  * Licensz: GNU/GPL
  * Szerzõ: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  *
  * JRequest: oevk, task
  */
defined('_JEXEC') or die;
include_once JPATH_SITE.'/elovalasztok/accesscontrol.php';  
include_once JPATH_SITE.'/elovalasztok/funkciok.php';  
  
class szavazokModel {
	private $errorMsg = '';
	function __construct() {
		$db = JFactory::getDBO();
		$db->setQuery('CREATE TABLE IF NOT EXISTS #__szavazatok (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `temakor_id` int(11) NOT NULL COMMENT "témakör azonosító",
		  `szavazas_id` int(11) NOT NULL COMMENT "szavazás azonosító",
		  `szavazo_id` int(11) NOT NULL COMMENT "szavaó azonosító a concorde-shulze kiértékeléshez",
		  `user_id` int(11) NOT NULL COMMENT "Ha nyilt szavazás a szavazó user_id -je",
		  `alternativa_id` int(11) NOT NULL COMMENT "alternativa azonositó",
		  `pozicio` int(11) NOT NULL COMMENT "ebbe a pozicióba sorolta az adott alternativát",
		  `fordulo` tinyint NOT NULL DEFAULT 0 COMMENT "szavazási forduló",
		  `ada0` tinyint NOT NULL DEFAULT 0 COMMENT "ADA regisztrált",
		  `ada1` tinyint NOT NULL DEFAULT 0 COMMENT "ADA szem.adatokat megadta",
		  `ada2` tinyint NOT NULL DEFAULT 0 COMMENT "ADA email ellenörzött",
		  `ada3` tinyint NOT NULL DEFAULT 0 COMMENT "ADA hiteles",
		  `secret` varchar(1024) NOT NULL DEFAULT "" COMMENT "biztonsági kulcs",
		  PRIMARY KEY (`id`),
		  KEY `temakori` (`temakor_id`),
		  KEY `szavazasi` (`szavazas_id`),
		  KEY `useri` (`user_id`),
		  KEY `szavazoi` (`szavazo_id`)
		)');
		try {
			$db->query();
		} catch (Exception $e) {
			;
		}	
		
		$db->setQuery('CREATE TABLE IF NOT EXISTS #__eredmeny (
			  `organization` int(11) NOT NULL DEFAULT 0 COMMENT "témakör ID",
			  `pollid` int(11) NOT NULL DEFAULT 0 COMMENT "szavazás ID",
			  `vote_count` int(11) NOT NULL DEFAULT 0 COMMENT "szavazatok száma",
			  `report` text COMMENT "cachelt report htm kód",
			  `filter` varchar(128) NOT NULL DEFAULT "" COMMENT "szavazatok rekordra vonatkozo sql filter alias:a",
			  `fordulo` tinyint NOT NULL DEFAULT 0 COMMENT "szavazási forduló",
			  `c1` int(11) NOT NULL DEFAULT 0 COMMENT "condorce elsõ helyezet alertativa ID",
			  `c2` int(11) NOT NULL DEFAULT 0 COMMENT "condorce második helyezet alertativa ID",
			  `c3` int(11) NOT NULL DEFAULT 0 COMMENT "condorce harmadik helyezet alertativa ID",
			  `c4` int(11) NOT NULL DEFAULT 0 COMMENT "condorce negyedik helyezet alertativa ID",
			  `c5` int(11) NOT NULL DEFAULT 0 COMMENT "condorce ötödik helyezet alertativa ID",
			  `c6` int(11) NOT NULL DEFAULT 0 COMMENT "condorce hatodik helyezet alertativa ID",
			  `c7` int(11) NOT NULL DEFAULT 0 COMMENT "condorce hetedik helyezet alertativa ID",
			  `c8` int(11) NOT NULL DEFAULT 0 COMMENT "condorce nyolcadik helyezet alertativa ID",
			  `c9` int(11) NOT NULL DEFAULT 0 COMMENT "condorce kilencedik helyezet alertativa ID",
			  `c10` int(11) NOT NULL DEFAULT 0 COMMENT "condorce tizedik helyezet alertativa ID"
			)
		');
		try {
			$db->query();	
		} catch (Exception $e) {
			;
		}	
	}
	
	/**
	  * egy adott oevk jelöltjeinek beolvasása
	  * @param integer oevk_id
	  * @return {"oevkId":szám, "oevkNev":string, "alternativak":[{"id":szám,"nev":string},....]}
	*/  
	public function getItem($szavazas_id) {
		$db = JFactory::getDBO();
		$result = new stdClass();
		$result->oevkId = $szavazas_id;
		$result->oevkNev = '';
		$result->alternativak = array();
		$db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
		try {
		  $res = $db->loadObject(); 
		} catch (Exception $e) {
		  ;	
		}  
		if ($res) {
			$result->oevkNev = $res->title;
			$db->setQuery('select RAND(10) as rnd, c.*
			from #__content c
			where c.catid = '.$db->quote($szavazas_id).' and c.state=1 
			order by 1');
			try {
				$res = $db->loadObjectList();
			} catch (Exception $e) {
			  ;	
			}  
			foreach ($res as $res1) {
				$w = new stdClass();
				$w->id = $res1->id;
				$w->nev = $res1->title;
				$w->introtext = $res1->introtext;
				$result->alternativak[] = $w;
			}
		}
		return $result;
	}
	
	/**
	  * get OEVK ID jelölt ID alapján
	  * @param integer jelölt ID
	  * @return integer oevk ID
	  */
	public function getOevkFromJelolt($jeloltId,$config) {
		$db = JFactory::getDBO();
		$result = 0;
		$db->setQuery('select * from #__content where id='.$db->quote($jeloltId));
		try {
			$res = $db->loadObject();
		} catch (Exception $e) {
			;
		}	
		if ($res) {
			if (($res->catid >= $config->oevk_min) & ($res->catid <= $config->oevk_max))
			    $result = $res->catid;
		}
		return $result;
	}
	
	public function getErrorMsg() {
	  return $this->errorMsg;	
	}
	
	/**
	  * szavazat tárolása adatbázisba - oevk szavazásnál előtörléssel
	  * @param integer oevk id
	  * @param string jelolt_id=pozicio,....
	  * @param JUser
	  * @param integer fordulo
	  * @param integer secret
	  * @return boolean
	*/  
	public function save($szavazas_id, $szavazat, $user, $fordulo, $secret) {
		global $evConfig;
		if ($szavazas_id == $evConfig->probaSzavazas) return true;
		$result = true;
		$msg = '';

		// jososultság ellenörzés
		if (teheti($szavazas_id, $user, 'szavazas', $msg) == false) {
			  $this->errorMsg .= $msg;
			  return false;	
		}

		$db = JFactory::getDBO();
		$db->setQuery('START TRANSACTION');
		$db->query();

		// oevk szavazásoknál töröljük a user esetleges már meglévő szavazatait
		if (teheti($szavazas_id, $user, 'szavazas', $msg)) {
			if (isOevkSzavazas($szavazas_id)) {
 				$result = $this->szavazatDelete($szavazas_id, $user, $evConfig->fordulo, 0);
			}		
		}
		
		// ada hitelesitési szint
		$ada0 = 0;
		$ada1 = 0;
		$ada2 = 0;
		$ada3 = 0;
		if (substr($user->params,0,1)=='[') $ada0 = 1;   // ADA
		if (strpos($user->params,'hash') > 0) $ada1 = 1; // ADA személyes adatok alapján
		if (strpos($user->params,'email') > 0) $ada2 = 1; // ADA email aktiválás
		if (strpos($user->params,'magyar') > 0) $ada3 = 1; // ADA személyesen ellenörzött

		// szavazás kategoria megállapitása
		$db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
		$res = $db->loadObject();
		if ($res)
			$catid = $res->parent_id;
		else
			$catid = 0;

		if ($result) {
			// string részekre bontása és tárolás ciklusban
			$w1 = explode(',',$szavazat);
			foreach ($w1 as $item) {
				$w2 = explode('=',$item);
				$db->setQuery('INSERT INTO #__szavazatok 
					(`temakor_id`, 
					`szavazas_id`, 
					`szavazo_id`, 
					`user_id`, 
					`alternativa_id`, 
					`pozicio`,
					`ada0`, `ada1`, `ada2`, `ada3`,
					`fordulo`,`secret`
					)
					VALUES
					('.$db->quote($catid).', 
					'.$db->quote($szavazas_id).', 
					'.$db->quote($user->id).', 
					'.$db->quote($user->id).', 
					'.$db->quote($w2[0]).', 
					'.$db->quote($w2[1]).',
					'.$ada0.','.$ada1.','.$ada2.','.$ada3.',
					'.$db->quote($fordulo).','.$secret.'
					)
				');
				try {
				  if ($db->query() != true) {
					$this->errorMsg .= $db->getErrorMsg().'<br />';
					$result = false;
				  }
				} catch (Exception $e) {
					$result = false;
				}	
			}
		}

		// delete cached report
		$db->setQuery('UPDATE #__eredmeny 
		SET report="" 
		WHERE pollid='.$db->quote($szavazas_id).' and fordulo='.$db->quote($fordulo) );
		try {
		  $db->query();
		} catch (Exception $e) {
		  ;
		}	

		if ($result) 
			$db->setQuery('COMMIT');
		else
			$db->setQuery('ROLLBACK');
		$db->query();

		return $result;
	}	
	
	/**
	* biztonságos törlés:  célszerü transaction -ban hívni!
	* 1. update szavazat_id =0 - ezt az egy modositást engedi meg a trigger 
	* 2. fizikai törlés - a trigger csak szavazat_id=0 -t enged törölni
    * FIGYELEM!!!!! ez csak az oevk és önkormányzati szavazásokhoz jó!
    *   ugyanis törli az azonos tulajdonos kategoriához tartozó összes szavazatot,
    *   ez az oevk -nál, jó is, MÁSHOL AZONBAN NEM!
	*/
	public function szavazatDelete($szavazas_id, $user, $fordulo, $secret = 0) {
		if (!isOevkSzavazas($szavazas_id)) {
			$this->errorMsg = 'Ez nem OEVK szavazás';
			return false;
		}
		$defCatid = 8; // oevk szavazások		
		$result = true;
		$db = JFactory::getDBO();

		// meghatározom a catid -t a $szavazas_id alapján (oevk szavazás, önkormányzati szavazás,....
		$db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
		$res = $db->loadObject();
		if ($res)
			$catid = $res->parent_id;
		else
			$catid = $defCatid;

		/* secret kód kiolvasása - ezt a triggeres ellenörzést jelenleg nem használjuk 2017.11.6
		if ($secret == 0) {
			$db->setQuery('select * from #__szavazatok where user_id='.$db->quote($user->id));
			$res = $db->loadObjectList();
			if (count($res) > 0) {
				$secret = $res[0]->secret;
			}
		}
		$db->query();
		*/

		try {
			$db->setQuery('update #__szavazatok 
			set szavazas_id=0, user_id = 0, secret = 0 
			where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo).'
			and temakor_id='.$db->quote($catid));
			$result = $db->query();
			$this->errorMsg = $db->getErrorMsg();
		} catch (Exception $e) {
			$this->errorMsg = $db->getErrorMsg();
			$result = false;
		}
		
		$this->errorMsg = $db->getErrorMsg();
		if ($result) {
			$db->setQuery('delete from #__szavazatok where szavazas_id = 0');
			try {
			   $result = $db->query();
			   $this->errorMsg = $db->getErrorMsg();
			} catch (Exception $e) {
				$this->errorMsg = $db->getErrorMsg();
				$result = false;
			}
		}
		// delete cached report
		$db->setQuery('UPDATE #__eredmeny 
		SET report="" 
		WHERE pollid='.$db->quote($szavazas_id).' and fordulo='.$db->quote($fordulo) );
		try {
		  $db->query();
		} catch (Exception $e) {
			;
		}

		return $result;  
	}
}  
?>
