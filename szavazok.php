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
  global $config;
<<<<<<< HEAD
  include_once JPATH_ROOT.'/elovalasztok/accesscontrol.php';
  include_once JPATH_ROOT.'/elovalasztok/models/szavazok.php';
  include_once JPATH_ROOT.'/elovalasztok/funkciok.php';
  include_once JPATH_ROOT.'/elovalasztok/config.php';
=======
  include JPATH_ROOT.'/elovalasztok/accesscontrol.php';
  include JPATH_ROOT.'/elovalasztok/models/szavazok.php';
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  
  $user = JFactory::getUser();
  $msg = '';
  $input = JFactory::getApplication()->input;  
  $oevk = $input->get('oevk');
  $filter = $input->get('filter','','STRING');
  $task = $input->get('task');
<<<<<<< HEAD
  $secret = $input->get('secret');
  $id = $input->get('id',0);
  
  if ($oevk == 0) {
	  $oevk = $id;
  }
=======
  
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  
  function base64url_encode2($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
  } 
  
  
  // ================ controller ==============================
  class szavazoController {
<<<<<<< HEAD
	  
	/**
    * szavazó képernyő megjelenitése  - új szavazat beküldése
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
    public function szavazok($oevk, $user, $filter) {
		global $config;
		$msg = '';
		if ($oevk <= 0) {
			echo '<div class="warning">Válassza ki az Egyéni országygyülési választó kerületet!</div>';
			include JPATH_ROOT.'/elovalasztok/oevklist.php';
		} else if ($user->id > 0) {
		   if (szavazottMar($oevk, $user)) {
			  echo '<div class="warning">Ön már szavazott!</div>';
		   } else {
		    if (teheti($oevk, $user, 'szavazas', $msg)) {
		      $model = new szavazokModel();
		      $item = $model->getItem($oevk);	
			  if (count($item->alternativak) <= 0) {
			    echo '<div class="warning">Nincs egyetlen jelölt sem.</div>';
			  } else {
		       include JPATH_ROOT.'/elovalasztok/views/szavazoform.php'; 
<<<<<<< HEAD
			   $this->mysqlUserToken($user);
=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			  }  
		    } else {
			  echo '<div class="error">Jelenleg nem szavazhat</div>';
			}	   
		  }	 
		} else {
			// nincs bejelentkezve
			$url = JURI::root().'index.php?option=com_jumi&view=application&fileid=5&task=szavazok&oevk='.$oevk;
			echo '
			<script type="text/javascript">
			  adaLogin("'.base64url_encode2($url).'");
			</script>
			';
		}
	}
	
<<<<<<< HEAD
	/**
    * szavazó képernyő megjelenitése - szavazat modosítás
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
    public function szavazatEdit($oevk, $user, $filter) {
		global $config;
		$msg = '';
		if ($oevk <= 0) {
			echo '<div class="warning">Válassza ki az Egyéni országygyülési választó kerületet!</div>';
			include JPATH_ROOT.'/elovalasztok/oevklist.php';
		} else if ($user->id > 0) {
		   if (szavazottMar($oevk, $user) == false) {
			  echo '<div class="warning">Ön még nem szavazott!</div>';
		   } else {
		    if (teheti($oevk, $user, 'szavazatEdit', $msg)) {
		      $model = new szavazokModel();
		      $item = $model->getItem($oevk);	
			  if (count($item->alternativak) <= 0) {
			    echo '<div class="warning">Nincs egyetlen jelölt sem.</div>';
			  } else {
		       include JPATH_ROOT.'/elovalasztok/views/szavazoform.php'; 
<<<<<<< HEAD
			   $this->mysqlUserToken($user);
=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
			  }  
		    } else {
			  echo '<div class="error">Jelenleg nem módosíthatja szavazatát</div>';
			}	   
		  }	 
		} else {
			$url = JURI::root().'index.php?option=com_jumi&view=application&fileid=5&task=szavazok&oevk='.$oevk;
			echo '
			<script type="text/javascript">
			  adaLogin("'.base64url_encode2($url).'");
			</script>
			';
		}
	}
<<<<<<< HEAD
	
	/**
    * szavazat törlés megerösítő képernyő
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
	public function szavazatDelete($oevk, $user, $filter) {
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
		   <p><button type="submit" class="btn-delete">Rendben, törölni akarom</button>&nbsp;
		      <button class="btn-cancel" type="button" onclick="location='."'$cancelURL'".'">Mégsem</button>
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
		global $config;
		Jsession::checkToken() or die('invalid CSRF protect token');
		$input = JFactory::getApplication()->input;  
		$secret = $input->get('secret');
=======

    public function szavazatDelete($oevk, $user, $filter) {
		global $config;
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$msg = '';
		if ($oevk <= 0) {
			echo '<div class="warning">Válassza ki az Egyéni országygyülési választó kerületet!</div>';
			include JPATH_ROOT.'/elovalasztok/oevklist.php';
		} else if ($user->id > 0) {
		   if (szavazottMar($oevk, $user) == false) {
			  echo '<div class="warning">Ön még nem szavazott!</div>';
		   } else {
		    if (teheti($oevk, $user, 'szavazatDelete', $msg)) {
		      $model = new szavazokModel();
<<<<<<< HEAD
			  if ($model->szavazatDelete($oevk, $user, $config->fordulo, $secret)) {
				  $msg = 'szavazata törölve lett.';
				  $msgClass = 'info';
				  $cookie_name = 'voks_'.$oevk;
				  $cookie_value = '';
				  setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day					
			  } else {
				  $msg = 'Hiba a szavazat törlés közben, a szavazat nem lett törölve (lehet, hogy hibás biztonsági kulcsot adott meg)';
=======
			  if ($model->szavazatDelete($oevk, $user, $config->fordulo)) {
				  $msg = 'szavazata törölve lett.';
				  $msgClass = 'info';
			  } else {
				  $msg = $model->getErrorMsg();
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
				  $msgClass = 'error';
			  }
		      JControllerLegacy::setMessage($msg,$msgClass);
			  JControllerLegacy::setRedirect('index.php?option=com_content&view=category&layout=articles&id='.$oevk);
			  JControllerLegacy::redirect();
		    } else {
			  echo '<div class="error">Jelenleg nem módosíthatja szavazatát</div>';
			}	   
		  }	 
		} else {
			$url = JURI::root().'index.php?option=com_jumi&view=application&fileid=5&task=szavazok&oevk='.$oevk;
			echo '
			<script type="text/javascript">
			  adaLogin("'.base64url_encode2($url).'");
			</script>
			';
		}
	}

<<<<<<< HEAD
	/**
    * szavazás eredményének megjelenitése
	* @param integer szavazás azonosító
    * @param JUser user 
    * @param string filter
    */ 	
    public function eredmeny($oevk, $user, $filter) {
		global $config;
		$db = JFactory::getDBO();
		$this->mysqlUserToken($user);
=======
    public function eredmeny($oevk, $user, $filter) {
		global $config;
		$db = JFactory::getDBO();
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$model = new szavazokModel();
		if ($oevk <= 0) {
			echo '<div class="warning">Válassza ki az Egyéni országygyülési választó kerületet!</div>';
			include JPATH_ROOT.'/elovalasztok/oevklist.php';
		} else {
			include_once JPATH_ROOT.'/elovalasztok/condorcet.php';
			$backUrl = JURI::base().'/index.php?option=com_content&view=category&layout=articles&id='.$oevk;
			$organization = '';
			$db->setQuery('select * from #__categories where id='.$db->quote($oevk));
			$poll = $db->loadObject();
			echo '<h2>'.$poll->title.'</h2>
			';
			$pollid = $oevk;
			
			// nézzük van-e cachelt report?
			$db->setQuery('select * from 
			               #__eredmeny 
						   where pollid='.$db->quote($pollid).' and 
			                     filter='.$db->quote($filter).' and
								 fordulo = '.$db->quote($config->fordulo) );
			$cache = $db->loadObject();
			
			// ha nincs meg a cache rekord akkor hozzuk most létre, üres tartalommal
			if ($cache == false) {
			  $db->setQuery('INSERT INTO #__eredmeny
			  (pollid, report,filter,fordulo ) 
			  value 
			  ('.$db->quote($pollid).',"","'.$filter.'",'.$db->quote($config->fordulo).')');
			  $db->query();
			  $cache = new stdClass();
			  $cache->pollid = $pollid;
			  $cache->filter = $filter;
			  $cache->fordulo = $config->fordulo;
			  $cache->report = "";
			}
			
			// test $cache->report = '';
			
			if ($cache->report == "") {
			  // ha nincs; most  kell condorcet/Shulze feldolgozás feldolgozás
			  $schulze = new Condorcet($db,$organization,$pollid,$filter,$config->fordulo);
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
			  where pollid="'.$pollid.'" and filter='.$db->quote($filter).' and fordulo='.$db->quote($config->fordulo));
			  $db->query();
			} else {  
			  // ha van akkor a cahcelt reportot jelenitjuük meg
			  $report = $cache->report; 
			}
			
			include JPATH_ROOT.'/elovalasztok/views/eredmeny.php';
		} // oevk adott
	} // eredmeny function

	/**
	  * sazavazás képernyő adat tárolása
	  * JRequest: token, oevk, szavazat jelölt_id=pozicio, ......
	  */
	public function szavazatSave($oevk, $user, $filter) {
		global $config;
		Jsession::checkToken() or die('invalid CSRF protect token');
		if ($user->id <= 0) die('nincs bejelentkezve, vagy lejárt a session');
		$input = JFactory::getApplication()->input;  
		$szavazat = $input->get('szavazat','','STRING');
		$msg = '';
		$msgClass = '';
		if ($oevk > 0) {
			if (szavazottMar($oevk, $user, $config->fordulo))
				$akcio = 'szavazatEdit';
			else
				$akcio = 'szavazas'; 
			if (teheti($oevk, $user, $akcio, $msg)) {
				$model = new szavazokModel();
<<<<<<< HEAD
				$secret = rand(100000,999999);
				$this->mysqlUserToken($user);
				if ($model->save($oevk, $szavazat, $user, $config->fordulo, $secret)) {
					$_COOCIE['voks_'.$oevk] = $secret;
					$msg = 'Köszönjük szavazatát. Szavazatát a rendszer tárolta.<br /><br />'.
					       'Szavazat biztonsági kód:<strong>'.$secret.'</strong><br /><br />'.
						   'Amennyiben böngészője tárolja a "cooki"-kat (sütiket); akkor 30 napon belül, ebből a böngészöből kezdeményezheti szavazata törlését<br />'.
                           'Ellenkező esetben, 30 nap után, illetve másik böngészőből a szavazat törléséhez a fenti biztonsági kód megadása szükséges.<br />';
				    $msgClass = 'info';
					$cookie_name = 'voks_'.$oevk;
					$cookie_value = $secret;
					setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day					
				} else {
					$msg = 'Hiba a szavazat tárolása közben. A szavazat nem lett tárolva';
=======
				if ($model->save($oevk, $szavazat, $user, $config->fordulo)) {
					$msg = 'Köszönjük szavazatát.';
				    $msgClass = 'info';
				} else {
					$msg = $model->getErrorMsg();
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
					$msgClass = 'error';
				}	
			} else {
				$msgClass = 'error';
			}	
		} else {
			$msg = 'Nincs kiválasztva az OEVK';
			$msgClass = 'error';
<<<<<<< HEAD
		}
		if ($msg != '')
		   JControllerLegacy::setMessage($msg,$msgClass);
        JControllerLegacy::setRedirect('index.php?option=com_content&view=category&layout=articles&id='.$oevk);
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
=======
			
		}
		
		if ($msg != '')
		   JControllerLegacy::setMessage($msg,$msgClass);
        JControllerLegacy::setRedirect('index.php?option=com_content&view=category&layout=articles&id='.$oevk);
		JControllerLegacy::redirect();
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
	}
  }
  
  
  // ================= main program ===========================
  $controller = new SzavazoController();
  $controller->$task ($oevk, $user, $filter);
?>
