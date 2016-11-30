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
  include JPATH_ROOT.'/elovalasztok/accesscontrol.php';
  include JPATH_ROOT.'/elovalasztok/models/szavazok.php';
  
  $user = JFactory::getUser();
  $msg = '';
  $input = JFactory::getApplication()->input;  
  $oevk = $input->get('oevk');
  $filter = $input->get('filter','','STRING');
  $task = $input->get('task');
  
  
  function base64url_encode2($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
  } 
  
  
  // ================ controller ==============================
  class szavazoController {
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

    public function szavazatDelete($oevk, $user, $filter) {
		global $config;
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
			  if ($model->szavazatDelete($oevk, $user, $config->fordulo)) {
				  $msg = 'szavazata törölve lett.';
				  $msgClass = 'info';
			  } else {
				  $msg = $model->getErrorMsg();
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

    public function eredmeny($oevk, $user, $filter) {
		global $config;
		$db = JFactory::getDBO();
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
				if ($model->save($oevk, $szavazat, $user, $config->fordulo)) {
					$msg = 'Köszönjük szavazatát.';
				    $msgClass = 'info';
				} else {
					$msg = $model->getErrorMsg();
					$msgClass = 'error';
				}	
			} else {
				$msgClass = 'error';
			}	
		} else {
			$msg = 'Nincs kiválasztva az OEVK';
			$msgClass = 'error';
			
		}
		
		if ($msg != '')
		   JControllerLegacy::setMessage($msg,$msgClass);
        JControllerLegacy::setRedirect('index.php?option=com_content&view=category&layout=articles&id='.$oevk);
		JControllerLegacy::redirect();
	}
  }
  
  
  // ================= main program ===========================
  $controller = new SzavazoController();
  $controller->$task ($oevk, $user, $filter);
?>
