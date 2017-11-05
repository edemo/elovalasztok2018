<?php
/**
  * szavazok component
  *   taskok: szavazok, szavazatEdit, szavazatDelete, eredmeny, szavazatSave
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  *
  * JRequest: oevk, task
  */
  defined('_JEXEC') or die;
  global $evConfig;
  include_once dirname(__FILE__).'/accesscontrol.php';
  include_once dirname(__FILE__).'/models/szavazok.php';
  include_once dirname(__FILE__).'/funkciok.php';
  include_once dirname(__FILE__).'/config.php';
  
  $user = JFactory::getUser();
  $msg = '';
  $input = JFactory::getApplication()->input;  
  $oevk = $input->get('oevk');
  $filter = $input->get('filter','','STRING');
  $task = $input->get('task','defaulttask');
  $secret = $input->get('secret');
  $id = $input->get('id',0);
  
  if ($oevk == 0) {
	  $oevk = $id;
  }
  
  function base64url_encode2($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
  } 
  
  
  // ================ controller ==============================
  class szavazoController extends JcontrollerLegacy {
	  
	/**
    * szavazó képernyő megjelenitése  - új szavazat beküldése
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
    public function szavazok($oevk, $user, $filter) {
		global $evConfig;
		
		// oevk check, repair
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromJelolt($oevk);
		}
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromUser($user);
		}
		
		$msg = '';
		if ($oevk <= 0) {
			$this->setMessage('Válassza ki a választó kerületet!','info');
			$this->setRedirect(JURI::root().'component/content/category?id=8');
			$this->redirect();
		} else if ($user->id == 0) {
			$this->setMessage('Szavazáshoz be kell jelenkezni!','info');
			$this->setRedirect(JURI::root().'component/content/category?id=8');
			$this->redirect();
		} else  {
		   if (szavazottMar($oevk, $user)) {
			  $this->setMessage('Ön már szavaztott!','error');
			  $this->setRedirect(JURI::root().'component/content/category?id='.$oevk);
			  $this->redirect();
		   } else {
		    if (teheti($oevk, $user, 'szavazas', $msg)) {
		      $model = new szavazokModel();
		      $item = $model->getItem($oevk);	
			  if (count($item->alternativak) <= 0) {
				  $this->setMessage('Nincs egyetlen jelölt sem.','warning');
				  $this->setRedirect(JURI::root().'component/content/category?id='.$oevk);
				  $this->redirect();
			  } else {
		       include JPATH_ROOT.'/elovalasztok/views/szavazoform.php'; 
			   $this->mysqlUserToken($user);
			  }  
		    } else {
			  $this->setMessage('Jelenleg nem szavazhat.','error');
			  $this->setRedirect(JURI::root().'component/content/category?id='.$oevk);
			  $this->redirect();
			}	   
		  } // szavazott már?	 
		}
	}
	
	
	/**
    * szavazat törlés megerösítő képernyő
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
	public function szavazatDelete($oevk, $user, $filter) {

		// oevk check, repair
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromJelolt($oevk);
		}
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromUser($user);
		}

		if ($oevk <= 0) {
			$this->setMessage('Válassza ki a választó kerületet!','info');
			$this->setRedirect(JURI::root().'component/content/category?id=8');
			$this->redirect();
		}
		
		if ($user->id <= 0) {
			// nincs bejelentkezve
			if (isOevkSzavazas($oevk))
				$url = JURI::root().'component/content/category?id='.$oevk;
			else
				$url = JURI::root();
			$this->setRedirect(JURI::base().'index.php?option=com_adalogin&redi='.$url);
			$this->redirect();
		}

		if (!szavazottMar($oevk, $user, 0)) {
			$this->setMessage('Ön még nem szavazott','info');
			$this->setRedirect(JURI::root().'component/content/category?id='.$oevk);
			$this->redirect();
		}
		
		$okURL = JURI::base().'index.php';
		$cancelURL = JURI::base().'index.php?option=com_content&view=category&layout=articles&id='.$oevk;
		$db = JFactory::getDBO();
		$db->setQuery('select title from #__content where id='.$db->quote($oevk));
		$res = $db->loadObject();
		echo '<div class="szavaztDelete">
		';
		if ($res) {
			echo '<h2>'.$res->title.'</h2>
			';
		}
		echo '<center>
		<h2>Szavazatom törlése</h2>
		<form action="'.$okURL.'" method="post">
			'.JHtml::_('form.token').'		
		   <input type="hidden" name="option" value="com_jumi" />
		   <input type="hidden" name="fileid" value="4" />
		   <input type="hidden" name="task" value="szavazatDelete2" />
		   <input type="hidden" name="id" value="'.$oevk.'" />
		   <p>Biztonsági kód:<input type="text" name="secret" value="'.$_COOKIE['voks_'.$oevk].'" /></p>
		   <p><button type="submit" class="btn btn-delete">Rendben, törölni akarom</button>&nbsp;
		      <button class="btn btn-cancel" type="button" onclick="location='."'$cancelURL'".'">Mégsem</button>
		   </p>
		</form>
		</center>
		</div>
		';
	}

	/**
    * szavazat törtlés végrehajtása
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
    public function szavazatDelete2($oevk, $user, $filter) {
		global $evConfig;

		if (!isOevkSzavazas($oevk)) {
			$this->setMessage('Hibás szavazás azonositó','error');
			$this->setRedirect(JURI::root());
			$this->redirect();
		}

		if ($user->id <= 0) {
			// nincs bejelentkezve
			if (isOevkSzavazas($oevk))
				$url = JURI::root().'component/jumi?task=szavazok&id='.$oevk;
			else
				$url = JURI::root();
			$this->setRedirect(JURI::base().'index.php?option=com_adalogin&redi='.$url);
			$this->redirect();
		}

		Jsession::checkToken() or die('invalid CSRF protect token');
		$input = JFactory::getApplication()->input;  
		$secret = $input->get('secret');
		$msg = '';
		if (szavazottMar($oevk, $user) == false) {
			  echo '<div class="warning">Ön még nem szavazott!</div>';
		} else {
		    if (teheti($oevk, $user, 'szavazatDelete', $msg)) {
		      $model = new szavazokModel();
			  if ($model->szavazatDelete($oevk, $user, $evConfig->fordulo, $secret)) {
				  $msg = 'szavazata törölve lett.';
				  $msgClass = 'info';
				  $cookie_name = 'voks_'.$oevk;
				  $cookie_value = '';
				  setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day					
			  } else {
				  $msg = 'Hiba a szavazat törlés közben, a szavazat nem lett törölve (lehet, hogy hibás biztonsági kulcsot adott meg)';
				  $msgClass = 'error';
			  }
		      JControllerLegacy::setMessage($msg,$msgClass);
			  JControllerLegacy::setRedirect(JURI::root().'component/content/category?id='.$oevk);
			  JControllerLegacy::redirect();
		    } else {
		      JControllerLegacy::setMessage('Nem törölheti a szavazatát','error');
			  JControllerLegacy::setRedirect(JURI::root().'component/content/category?id='.$oevk);
			  JControllerLegacy::redirect();
			}	   
		}	
	}

	/**
    * szavazás eredményének megjelenitése
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
    public function eredmeny($oevk, $user, $filter) {
		global $evConfig;

		// oevk check, repair
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromJelolt($oevk);
		}
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromUser($user);
		}

		if (!isOevkSzavazas($oevk)) {
			$this->setMessage('Válassza ki a választó kerületet!','info');
			$this->setRedirect(JURI::root().'component/content/category?id=8');
			$this->redirect();
		}
		
		$db = JFactory::getDBO();
		$this->mysqlUserToken($user);
		$model = new szavazokModel();
		include_once JPATH_ROOT.'/elovalasztok/condorcet.php';
		$backUrl = JURI::root().'/component/content/category?id='.$oevk;
		$organization = '';
		$db->setQuery('select * from #__categories where id='.$db->quote($oevk));
		$poll = $db->loadObject();
		echo '<h2>'.$poll->title.'</h2>
		<div class="pollLeiras">'.$poll->description.'</div>
		';
		$pollid = $oevk;
		
		// nézzük van-e cachelt report?
		$db->setQuery('select * from 
					   #__eredmeny 
					   where pollid='.$db->quote($pollid).' and 
							 filter='.$db->quote($filter).' and
							 fordulo = '.$db->quote($evConfig->fordulo) );
		$cache = $db->loadObject();
		
		// ha nincs meg a cache rekord akkor hozzuk most létre, üres tartalommal
		if ($cache == false) {
		  $db->setQuery('INSERT INTO #__eredmeny
		  (pollid, report,filter,fordulo ) 
		  value 
		  ('.$db->quote($pollid).',"","'.$filter.'",'.$db->quote($evConfig->fordulo).')');
		  $db->query();
		  $cache = new stdClass();
		  $cache->pollid = $pollid;
		  $cache->filter = $filter;
		  $cache->fordulo = $evConfig->fordulo;
		  $cache->report = "";
		}
		
		$cache->report = '';
		
		if ($cache->report == "") {
		  // ha nincs; most  kell condorcet/Shulze feldolgozás feldolgozás
		  $schulze = new Condorcet($db,$organization,$pollid,$filter,$evConfig->fordulo);
		  $report = $schulze->report();
		  $db->setQuery('update #__eredmeny 
		  set report='.$db->quote($report).',
			  c1 = '.$schulze->c1.',
			  c2 = '.$schulze->c2.',
			  c3 = '.$schulze->c3.',
			  c4 = '.$schulze->c4.',
			  c5 = '.$schulze->c5.',
			  c6 = '.$schulze->c6.',
			  c7 = '.$schulze->c7.',
			  c8 = '.$schulze->c8.',
			  c9 = '.$schulze->c9.',
			  c10 = '.$schulze->c10.',
			  vote_count = '.$schulze->vote_count.'
		  where pollid="'.$pollid.'" and filter='.$db->quote($filter).' and fordulo='.$db->quote($evConfig->fordulo));
		  $db->query();
		} else {  
		  // ha van akkor a cahcelt reportot jelenitjuük meg
		  $report = $cache->report; 
		}
		
		include JPATH_ROOT.'/elovalasztok/views/eredmeny.php';
	} // eredmeny function

	/**
	  * sazavazás képernyő adat tárolása
	  * JRequest: token, oevk, szavazat jelölt_id=pozicio, ......
	  */
	public function szavazatSave($oevk, $user, $filter) {
		global $evConfig;
		Jsession::checkToken() or die('invalid CSRF protect token');
		if ($user->id <= 0) die('nincs bejelentkezve, vagy lejárt a session');
		if (!isOevkSzavazas($oevk)) {
			$this->setMessage('Hibás szavazás azonosító.','error');
			$this->setRedirect(JURI::root());
			$this->redirect();
		}
		$input = JFactory::getApplication()->input;  
		$szavazat = $input->get('szavazat','','STRING');
		$msg = '';
		$msgClass = '';
		if ($oevk > 0) {
			if (szavazottMar($oevk, $user, $evConfig->fordulo))
				$akcio = 'szavazatEdit';
			else
				$akcio = 'szavazas'; 
			if (teheti($oevk, $user, $akcio, $msg)) {
				$model = new szavazokModel();
				$secret = rand(100000,999999);
				$this->mysqlUserToken($user);
				if ($model->save($oevk, $szavazat, $user, $evConfig->fordulo, $secret)) {
					$_COOCIE['voks_'.$oevk] = $secret;
					$msg = 'Köszönjük szavazatát. Szavazatát a rendszer tárolta.<br /><br />'.
					       'Szavazat biztonsági kód:<strong>'.$secret.'</strong><br /><br />'.
						   'Amennyiben böngészője tárolja a "cooki"-kat (sütiket); akkor 30 napon belül, ebből a böngészöből kezdeményezheti szavazata törlését<br />'.
                           'Ellenkező esetben, 30 nap után, illetve másik böngészőből a szavazat törléséhez a fenti biztonsági kód megadása szükséges.<br />';
					// 2017.02.09 egyenlőre nem lesz szavazat törlési lehetőség
					$msg = 'Köszönjük szavazatát.';	
				    $msgClass = 'info';
					$cookie_name = 'voks_'.$oevk;
					$cookie_value = $secret;
					setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day					
				} else {
					$msg = 'Hiba a szavazat tárolása közben. A szavazat nem lett tárolva';
					$msgClass = 'error';
				}	
			} else {
				$msgClass = 'error';
			}	
		} else {
			$msg = 'Nincs kiválasztva a választó kerület';
			$msgClass = 'error';
		}
		if ($msg != '')
		   JControllerLegacy::setMessage($msg,$msgClass);
        JControllerLegacy::setRedirect(JURI::root().'component/content/category?id='.$oevk);
		JControllerLegacy::redirect($msg, $msgClass);
	}
	
	/**
	* user aktivitás jelző token tárolása az adatbázisba és az elavultak (1 óránál régebbiek) törlése
	*/
	protected function mysqlUserToken($user) {
		$db = JFactory::getDBO();
		$db->setQuery('CREATE TABLE IF NOT EXISTS #__usertoken (
		   user_id varchar(128),
		   idopont datetime,
		   PRIMARY KEY (`user_id`)
		)');
		$db->query();
		$db->setQuery('delete from #__usertoken where idopont < "'.date('Y-m-d H:i:s', time() - 60*60).'"');
		$db->query();
		$db->setQuery('select * from #__usertoken where user_id='.$db->quote(sha1($user->id)));
		$res = $db->loadObject();
		if ($res)
			$db->setQuery('update #__usertoken set idopont="'.date('Y-m-d H:i:s').'" where user_id='.$db->quote(sha1($user->id)));
		else
			$db->setQuery('insert into #__usertoken values ('.$db->quote(sha1($user->id)).',"'.date('Y-m-d H:i:s').'")');
		$db->query();	
	}
  
    /** jelölt támogatas
    * @param integer content.id
    * @param JUser
    * @param filter
    */
    public function tamogatas($jelolt, $user, $filter) {
		//DBG echo 'tamogatas '.$jelolt.' '.$user->id; exit();  
		if ($user->id > 0) {
			$db = JFactory::getDBO();
			$db->setQuery('create table if not exists #__tamogatasok
			(jelolt_id int(11),
			 user_id int(11)
			)');
			$db->query();
			$db->setQuery('select catid from #__content where id='.$db->quote($jelolt));
			$res = $db->loadObject();
			if ($res)
				$oevk = $res->catid;
			else 
				$oevk = 0;
			$db->setQuery('select * from #__tamogatasok where user_id='.$db->quote($user->id).' and jelolt_id='.$db->quote($jelolt));
			$res = $db->loadObject();
			if ($res)
				$db->setQuery('delete from #__tamogatasok where user_id='.$db->quote($user->id).' and jelolt_id='.$db->quote($jelolt));
			else
				$db->setQuery('insert into #__tamogatasok value ('.$db->quote($jelolt).','.$db->quote($user->id).')');
			$result = $db->query();
		} 
		JControllerLegacy::setRedirect(JURI::root().'component/jumi?fileid=4&id='.$oevk);
		JControllerLegacy::redirect($msg, $msgClass);
	}
	
	/**
	* kezdolap
	*/
	public function defaulttask($oevk, $user, $filter) {
		if (!isOevkSzavazas($oevk)) {
			$oevk = oevkFromUser($user);
		}
		if (isOevkSzavazas($oevk)) {
		   JControllerLegacy::setRedirect(JURI::root().'component/content/category?id='.$oevk);
		   JControllerLegacy::redirect($msg, $msgClass);
	    } else {
		   JControllerLegacy::setRedirect(JURI::root().'component/content/category?id=8');
		   JControllerLegacy::redirect($msg, $msgClass);
		}	
	}
  }
  // ================= main program ===========================
  $controller = new SzavazoController();
  $controller->$task ($oevk, $user, $filter);
?>
