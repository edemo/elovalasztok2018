<?php
 /**
  * szavazok model
  *   taskok: szavazok, szavazatEdit, szavazatDelete, eredmeny, szavazatSave
  * Licensz: GNU/GPL
  * Szerz�: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzi�: V1.00  2016.09.14.
  *
  * JRequest: oevk, task
  */

include_once JPATH_SITE.'/elovalasztok/accescontrol.php';  
include_once JPATH_SITE.'/elovalasztok/funkciok.php';  
  
class szavazokModel {
	private $errorMsg = '';
	function __construct() {
		$db = JFactory::getDBO();
		$db->setQuery('CREATE TABLE IF NOT EXISTS #__szavazatok (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `temakor_id` int(11) NOT NULL COMMENT "t�mak�r azonos�t�",
		  `szavazas_id` int(11) NOT NULL COMMENT "szavaz�s azonos�t�",
		  `szavazo_id` int(11) NOT NULL COMMENT "szava� azonos�t� a concorde-shulze ki�rt�kel�shez",
		  `user_id` int(11) NOT NULL COMMENT "Ha nyilt szavaz�s a szavaz� user_id -je",
		  `alternativa_id` int(11) NOT NULL COMMENT "alternativa azonosit�",
		  `pozicio` int(11) NOT NULL COMMENT "ebbe a pozici�ba sorolta az adott alternativ�t",
		  `fordulo` tinyint NOT NULL DEFAULT 0 COMMENT "szavaz�si fordul�",
		  `ada0` tinyint NOT NULL DEFAULT 0 COMMENT "ADA regisztr�lt",
		  `ada1` tinyint NOT NULL DEFAULT 0 COMMENT "ADA szem.adatokat megadta",
		  `ada2` tinyint NOT NULL DEFAULT 0 COMMENT "ADA email ellen�rz�tt",
		  `ada3` tinyint NOT NULL DEFAULT 0 COMMENT "ADA hiteles",
		  `secret` varchar(1024) NOT NULL DEFAULT "" COMMENT "biztons�gi kulcs",
		  PRIMARY KEY (`id`),
		  KEY `temakori` (`temakor_id`),
		  KEY `szavazasi` (`szavazas_id`),
		  KEY `useri` (`user_id`),
		  KEY `szavazoi` (`szavazo_id`)
		)');
		$db->query();
		
		$db->setQuery('CREATE TABLE IF NOT EXISTS #__eredmeny (
			  `organization` int(11) NOT NULL DEFAULT 0 COMMENT "t�mak�r ID",
			  `pollid` int(11) NOT NULL DEFAULT 0 COMMENT "szavaz�s ID",
			  `vote_count` int(11) NOT NULL DEFAULT 0 COMMENT "szavazatok sz�ma",
			  `report` text COMMENT "cachelt report htm k�d",
			  `filter` varchar(128) NOT NULL DEFAULT "" COMMENT "szavazatok rekordra vonatkozo sql filter alias:a",
			  `fordulo` tinyint NOT NULL DEFAULT 0 COMMENT "szavaz�si fordul�",
			  `c1` int(11) NOT NULL DEFAULT 0 COMMENT "condorce els� helyezet alertativa ID",
			  `c2` int(11) NOT NULL DEFAULT 0 COMMENT "condorce m�sodik helyezet alertativa ID",
			  `c3` int(11) NOT NULL DEFAULT 0 COMMENT "condorce harmadik helyezet alertativa ID",
			  `c4` int(11) NOT NULL DEFAULT 0 COMMENT "condorce negyedik helyezet alertativa ID",
			  `c5` int(11) NOT NULL DEFAULT 0 COMMENT "condorce �t�dik helyezet alertativa ID",
			  `c6` int(11) NOT NULL DEFAULT 0 COMMENT "condorce hatodik helyezet alertativa ID",
			  `c7` int(11) NOT NULL DEFAULT 0 COMMENT "condorce hetedik helyezet alertativa ID",
			  `c8` int(11) NOT NULL DEFAULT 0 COMMENT "condorce nyolcadik helyezet alertativa ID",
			  `c9` int(11) NOT NULL DEFAULT 0 COMMENT "condorce kilencedik helyezet alertativa ID",
			  `c10` int(11) NOT NULL DEFAULT 0 COMMENT "condorce tizedik helyezet alertativa ID"
			)
		');
		$db->query();	
	}
	
	/**
	  * egy adott oevk jel�ltjeinek beolvas�sa
	  * @param integer oevk_id
	  * @return {"oevkId":sz�m, "oevkNev":string, "alternativak":[{"id":sz�m,"nev":string},....]}
	*/  
	public function getItem($szavazas_id) {
		$db = JFactory::getDBO();
		$result = new stdClass();
		$result->oevkId = $szavazas_id;
		$result->oevkNev = '';
		$result->alternativak = array();
		$db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
		$res = $db->loadObject(); 
		if ($res) {
			$result->oevkNev = $res->title;
			$db->setQuery('select rnd(10) as rnd, *
			from #__content
			where catid = '.$db->quote($szavazas_id).' 
			order by 1');
			$res = $db->loadObjectList();
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
	  * get OEVK ID jel�lt ID alapj�n
	  * @param integer jel�lt ID
	  * @return integer oevk ID
	  */
	public function getOevkFromJelolt($jeloltId,$config) {
		$db = JFactory::getDBO();
		$result = 0;
		$db->setQuery('select * from #__content where id='.$db->quote($jeloltId));
		$res = $db->loadObject();
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
	  * szavazat t�rol�sa adatb�zisba
	  * @param integer oevk id
	  * @param string jelolt_id=pozicio,....
	  * @param JUser
	  * @param integer fordulo
	  * @param integer secret
	  * @return boolean
	*/  
	public function save($szavazas_id, $szavazat, $user, $fordulo, $secret) {
		$result = true;
		$msg = '';
		if (teheti($szavazas_id, $user, 'szavazas', $msg) == false) {
			  $this->errorMsg .= $msg;
			  $result = false;
		}
		$db = JFactory::getDBO();
		$db->setQuery('START TRANSACTION');
		$db->queery();
		
		// el� t�rl�s
		$db->setQuery('delete from #__szavazatok 
		where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo).' and szavazas_id = '.$db->quote($szavazas_id));
		$db->query();
		// ada hitelesit�si szint
		$ada0 = 0;
		$ada1 = 0;
		$ada2 = 0;
		$ada3 = 0;
		if (substr($user->params,0,1)=='[') $ada0 = 1;   // ADA
		if (strpos($user->params,'hash') > 0) $ada1 = 1; // ADA szem�lyes adatok alapj�n
		if (strpos($user->params,'email') > 0) $ada2 = 1; // ADA email aktiv�l�s
		if (strpos($user->params,'magyar') > 0) $ada3 = 1; // ADA szem�lyesen ellen�rz�tt
		// string r�szekre bont�sa �s t�rol�s ciklusban
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
				(8, 
				'.$db->quote($szavazas_id).', 
				'.$db->quote($user->id).', 
				'.$db->quote($user->id).', 
				'.$db->quote($w2[0]).', 
				'.$db->quote($w2[1]).',
				'.$ada0.','.$ada1.','.$ada2.','.$ada3.',
				'.$db->quote($fordulo).','.$secret.'
				)
			');
			if ($db->query() != true) {
			  $this->errorMsg .= $db->getErrorMsg().'<br />';
			  $result = false;
			}  
		}
		
		// delete cached report
		$db->setQuery('UPDATE #__eredmeny 
		SET report="" 
		WHERE pollid='.$db->quote($szavazas_id).' and fordulo='.$db->quote($fordulo) );
		$db->query();

		if ($result) 
			$db->setQuery('COMMIT');
		else
			$db->setQuery('ROLLBACK');
		$db->queery();

		return $result;
	}	
	
	/**
	* biztons�gos t�rl�s: s
	* 1. update szavazat_id =0 - ezt az egy modosit�st engedi meg a trigger (ha a megfelel� secret van megadva)
	* 2. fizikai t�rl�s - a trigger csk szavazat_id=0 -t enged t�r�lni
	*/
	public function szavazatDelete($szavazas_id, $user, $fordulo, $secret) {
		$result = true;
		$db = JFactory::getDBO();

		$db->setQuery('START TRANSACTION');
		$db->queery();

		$db->setQuery('update #__szavazatok 
		set szavazas_id=0, user_id = 0, secret='.$secret.'
		where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo).' and szavazas_id='.$db->quote($szavazas_id));
		$result = $db->query();
		$this->errorMsg = $db->getErrorMsg();
		if ($result) {
			$db->setQuery('delete from #__szavazatok where szavazas_id = 0');
			$result = $db->query();
			$this->errorMsg = $db->getErrorMsg();
		}
		// delete cached report
		$db->setQuery('UPDATE #__eredmeny 
		SET report="" 
		WHERE pollid='.$db->quote($szavazas_id).' and fordulo='.$db->quote($fordulo) );
		$db->query();

		if ($result) 
			$db->setQuery('COMMIT');
		else
			$db->setQuery('ROLLBACK');
		$db->queery();

		return $result;  
	}
}  
?>