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
<<<<<<< HEAD

include_once JPATH_SITE.'/elovalasztok/accescontrol.php';  
include_once JPATH_SITE.'/elovalasztok/funkciok.php';  
  
=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
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
		  PRIMARY KEY (`id`),
		  KEY `temakori` (`temakor_id`),
		  KEY `szavazasi` (`szavazas_id`),
		  KEY `useri` (`user_id`),
		  KEY `szavazoi` (`szavazo_id`)
		)');
		$db->query();
		
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
		$db->query();	
	}
	
	/**
	  * egy adott oevk jelöltjeinek beolvasása
	  * @param integer oevk_id
	  * @return {"oevkId":szám, "oevkNev":string, "alternativak":[{"id":szám,"nev":string},....]}
	*/  
<<<<<<< HEAD
	public function getItem($szavazas_id) {
		$db = JFactory::getDBO();
		$result = new stdClass();
		$result->oevkId = $szavazas_id;
		$result->oevkNev = '';
		$result->alternativak = array();
		$db->setQuery('select * from #__categories where id='.$db->quote($szavazas_id));
=======
	public function getItem($oevk) {
		$db = JFactory::getDBO();
		$result = new stdClass();
		$result->oevkId = $oevk;
		$result->oevkNev = '';
		$result->alternativak = array();
		$db->setQuery('select * from #__categories where id='.$db->quote($oevk));
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$res = $db->loadObject(); 
		if ($res) {
			$result->oevkNev = $res->title;
			$db->setQuery('select *
			from #__content
<<<<<<< HEAD
			where catid = '.$db->quote($szavazas_id).' 
=======
			where catid = '.$db->quote($oevk).'
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			order by title');
			$res = $db->loadObjectList();
			foreach ($res as $res1) {
				$w = new stdClass();
				$w->id = $res1->id;
				$w->nev = $res1->title;
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
	  * szavazat tárolása adatbázisba
	  * @param integer oevk id
	  * @param string jelolt_id=pozicio,....
	  * @param JUser
	  * @param integer fordulo
	  * @return boolean
	*/  
<<<<<<< HEAD
	public function save($szavazas_id, $szavazat, $user, $fordulo) {
		$result = true;
		$msg = '';
		if (teheti($szavazas_id, $user, 'szavazas', $msg) == false) {
			  $this->errorMsg .= $msg;
			  $result = false;
		}
		// elõ törlés
		$db = JFactory::getDBO();
		$db->setQuery('delete from #__szavazatok 
		where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo).' and szavazas_id = '.$db->quote($szavazas_id));
=======
	public function save($oevk, $szavazat, $user, $fordulo) {
		$result = true;
		
		// elõõ törlés
		$db = JFactory::getDBO();
		$db->setQuery('delete from #__szavazatok where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo));
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$db->query();
		// ada hitelesitési szint
		$ada0 = 0;
		$ada1 = 0;
		$ada2 = 0;
		$ada3 = 0;
<<<<<<< HEAD
		if (substr($user->params,0,1)=='[') $ada0 = 1;   // ADA
		if (strpos($user->params,'hash') > 0) $ada1 = 1; // ADA személyes adatok alapján
		if (strpos($user->params,'email') > 0) $ada2 = 1; // ADA email aktiválás
		if (strpos($user->params,'magyar') > 0) $ada3 = 1; // ADA személyesen ellenörzött
=======
		if (substr($user->activation,0,1)=='[') $ada0 = 1;   // ADA
		if (strpos($user->activation,'hash') > 0) $ada1 = 1; // ADA személyes adatok alapján
		if (strpos($user->activation,'email') > 0) $ada2 = 1; // ADA email aktiválás
		if (strpos($user->activation,'magyar') > 0) $ada3 = 1; // ADA személyesen ellenörzött
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
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
				`fordulo`
				)
				VALUES
				(8, 
<<<<<<< HEAD
				'.$db->quote($szavazas_id).', 
=======
				'.$db->quote($oevk).', 
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
				'.$db->quote($user->id).', 
				'.$db->quote($user->id).', 
				'.$db->quote($w2[0]).', 
				'.$db->quote($w2[1]).',
				'.$ada0.','.$ada1.','.$ada2.','.$ada3.',
				'.$db->quote($fordulo).'
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
<<<<<<< HEAD
		WHERE pollid='.$db->quote($szavazas_id).' and fordulo='.$db->quote($fordulo) );
=======
		WHERE pollid='.$db->quote($oevk).' and fordulo='.$db->quote($fordulo) );
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$db->query();
		return $result;
	}	
	
<<<<<<< HEAD
	public function szavazatDelete($szavazas_id, $user, $fordulo) {
=======
	public function szavazatDelete($oevk, $user, $fordulo) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$result = true;
		$db = JFactory::getDBO();
		$db->setQuery('delete 
		from #__szavazatok 
<<<<<<< HEAD
		where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo).' and szavazas_id='.$db->quote($szavazas_id));
=======
		where user_id='.$db->quote($user->id).' and fordulo='.$db->quote($fordulo));
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$result = $db->query();
		$this->errorMsg = $db->getErrorMsg();
		// delete cached report
		$db->setQuery('UPDATE #__eredmeny 
		SET report="" 
<<<<<<< HEAD
		WHERE pollid='.$db->quote($szavazas_id).' and fordulo='.$db->quote($fordulo) );
		$db->query();
		return $result;  
	}
=======
		WHERE pollid='.$db->quote($oevk).' and fordulo='.$db->quote($fordulo) );
		$db->query();
		return $result;  
	}
		
	
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
}  
?>