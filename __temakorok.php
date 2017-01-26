<?php
/*

Ez a file az előválasztók rendszerben nem kell
Esetleges továbbfejlesztésekhez kellhet, ebben van 
a likvid demokrácia model a képviseleti szavazás feldolgozás

*/





/**
 * @version		$Id: #component#.php 125 2012-10-09 11:09:48Z michel $
 * @package		Joomla.Framework
 * @subpackage	temakorok
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

define ('TEMAKOR_TREE_LIMIT',10);

class TemakorokHelper {
  /** getConfig
   *  @return object $config
   */
  public function getConfig($temakor_id=0) {
    $res = false;
    $db = JFactory::getDBO();
    if ($temakor_id != 0) {
      $db->setQuery('select * from #__beallitasok where id = (10+'.$temakor_id.')');
      $res = $db->loadObject();
      if (($res->json == '') | ($res->json == '[]')) {
        $res = false;
      }
    }  
    if ($res == false) {
      $db->setQuery('select * from #__beallitasok where id = 1');
      $res = $db->loadObject();
    }
    if ($res) {
      $result = JSON_decode($res->json);
    } else {
      $result = JSON_decode('{
      "temakor_felvivok":1,
      "tobbszintu_atruhazas":1,
      "atruhazas_lefele_titkos":0
      }');
    }
    return $result;
  }        
  /**
   * the user is admin?
   * @param JUser $user
   * @return boolean      
   */    
  public function isAdmin($user=false)	{
		jimport( 'joomla.user.helper' );
		if ($user == false) $user = JFactory::getUser();
    $result = false;
    if ($user) {
      $groups = JUserHelper::getUserGroups($user->id);
      //DBG foreach($groups as $fn => $fv) echo '<p>'.$fn.'='.$fv.'</p>'; exit();
  		$admin_groups = array(); //put all the groups that you consider to be admins
      $admin_groups[] = "Super Users";
      $admin_groups[] = "Administrator";
      $admin_groups[] = "Manager";
      $admin_groups[] = "8";
      $admin_groups[] = "7";
      $admin_groups[] = "6";
  		foreach($admin_groups as $temp)	{
  			if(!empty($groups[$temp])) {
  				$result = true;
  			}
  		}
		}
    return $result;
	}
  /**
   * a megadott témakörnek tagja a megadott user?
   * @param integer $temakor_id
   * @param JUser $user
   * @param boolean $orokolt Felsőbb szintű témakör tagok is tagnak számítanak?
   * @return boolean
  */      
  public function userTag($temakor_id,$user,$orokolt=true) {
      if ($user->id > 0) {
        $db = JFactory::getDBO();
        $db->setQuery('select * 
        from #__tagok 
        where temakor_id="'.$temakor_id.'" and user_id='.$user->id);
        $res = $db->loadObjectList();
      } else {
        $res = array();
      }
	  $i = 0;
      while ((count($res)==0) & 
             ($temakor_id > 0) &
             ($user->id > 0) &
             ($orokolt) &
			 ($i < TEMAKOR_TREE_LIMIT)
			 ) {
        // nézzük meg a felsőbb szintek témaköreinek tagja-e?
        // ha igen, akkor itt is  tagnak tekintjük
        $db->setQuery('select t.*,te.szulo 
        from #__temakorok te, #__tagok t 
        where te.id= "'.$temakor_id.'" and t.temakor_id=te.szulo and t.user_id='.$user->id);
        $res = $db->loadObjectList();
        if (res)
          $temakor_id = $res->szulo;
        else
          $temakor_id = 0;  
	    $i++;
      }
      return (count($res)>0);
  } 
  /** user témakör admin?
   * @return boolean
   * @param integer $temakor_id
   * @param Juser $user
   */             
  function temakorAdmin($temakor_id,$user) {
    $result = false;
    $db = JFactory::getDBO();
    $db->setQuery('select admin
    from #__tagok
    where temakor_id = "'.$temakor_id.'" and user_id="'.$user->id.'"');
    $res = $db->loadObject();
    if ($res) {
      $result = ($res->admin == 1);
    }
    return $result;
  } 
  
  /**
   * get temakorGroupId
   * @return integer
   * @param integer $temakor_id
   */
  public function getTemakorGroupId($temakor_id) {
    if ($temakor_id > 0) {
        $db = JFactory::getDBO();
        $db->setQuery('select id 
        from #__usergroups
        where title like "['.$temakor_id.']%" 
        limit 1'); 
        $res = $db->loadObject();
        if ($res)
          $result = $res->id;
        else
          $result = 0;  
    } else {
      $result = 0;
    }
    //DBG echo '<p>getTemakorGroupIs='.$result.' '.$db->getQuery().'</p>';
    return $result;
  }             
  /**
   * automatikus szavazás állapot változtatás
   */     
  public function setSzavazasAllapot() {
    $db = JFactory::getDBO();

	/* szavazásra nem javasoltaknál határidő csusztatás */
    $db->setQuery('update #__szavazasok sz, ekh_szavazasok_igennem w
    set  vita1_vege = adddate(vita1_vege, 10),
         vita2_vege = adddate(vita2_vege, 10),
         szavazas_vege = adddate(szavazas_vege, 10) 	
    where  w.szavazas_id = sz.id and 
	  sz.vita1=1 and sz.vita1_vege < CURDATE() and w.nem > w.igen');
    if ($db->query()==false) {
      echo '<div class="errorMsg">'.$db->getErrorMsg().'</div>';
    }
	
	/* vita1 vége */
    $db->setQuery('update #__szavazasok
    set vita1=0,vita2=1 
    where vita1=1 and vita1_vege < CURDATE()');
    if ($db->query()==false) {
      echo '<div class="errorMsg">'.$db->getErrorMsg().'</div>';
    }
	
	/* vita2 vége */
    $db->setQuery('update #__szavazasok
    set vita2=0,szavazas=1 
    where vita2=1 and vita2_vege < CURDATE()');
    if ($db->query()==false) {
      echo '<div class="errorMsg">'.$db->getErrorMsg().'</div>';
    }
	
	/* szavazas vége */
    $db->setQuery('update #__szavazasok
    set szavazas=0,lezart=1 
    where szavazas=1 and szavazas_vege < CURDATE()');
    if ($db->query()==false) {
      echo '<div class="errorMsg">'.$db->getErrorMsg().'</div>';
    }
  }
  /**
   * li-de feldolgozó
   * feladata a lezárt szavazásokban személyesen nem szavazókhoz a
   * képviselőik szavazatainak generálása
   * @return integer generált szavazat rekordok száma
   * @param integer temakor_id
   * @param integer $szavazas_id
   * @param integer $kepviselo_filter amikor a témakör képviselőket kell feldolgozni
   *             akkor azonos a temakor_id -vel
   *             amikor az általános képviselőket akkor 0                  
   */
   public function lideFeldolgozo($temakor_id, $szavazas_id, $kepviseloFilter) {
     //DBG echo '<p>lideFeldolgozo start temakor_id='.$temakor_id.' kepviselo_filter='.$kepviselo_filter.'</p>';
     $db = JFactory::getDBO();
     $darab = 0;
     
     // esetleg meglévő munkatábla törlése
     $db->setQuery('drop table if exists wkepviseloszavazat'.$szavazas_id.';');
     if ($db->query()==false) $db->sdterr();
      
     // munkatábla létrehozása, a személyesen nemszavazók hoz a képviselőjük 
     //szavazata lemásolva 
     $db->setQuery('
create table wkepviseloszavazat'.$szavazas_id.' 
select sz.temakor_id, sz.szavazas_id, 0 szavazo_id, nemszavaztak.id user_id, 
       sz.alternativa_id, sz.pozicio, k.kepviselo_id kepviselo_id 
from (
   select u.id
   from #__users u
   left outer join #__szavazok szavazok 
        on szavazok.user_id=u.id and szavazok.szavazas_id = "'.$szavazas_id.'"
   left outer join #__szavazasok szavazasok 
        on szavazasok.id="'.$szavazas_id.'"
   where szavazok.id is null and szavazasok.szavazok=1
   union
   select t.user_id
   from #__tagok t
   left outer join #__szavazok szavazok 
        on szavazok.user_id=t.user_id and szavazok.szavazas_id = "'.$szavazas_id.'"
   left outer join #__szavazasok szavazasok 
        on szavazasok.id="'.$szavazas_id.'"
   where szavazok.id is null and szavazasok.szavazok=2 and t.temakor_id="'.$temakor_id.'"
) nemszavaztak
inner join #__kepviselok k 
      on k.temakor_id="'.$kepviseloFilter.'" and k.user_id = nemszavaztak.id 
inner join #__szavazatok sz 
      on sz.szavazas_id = "'.$szavazas_id.'" and sz.user_id = k.kepviselo_id;
');
     if ($db->query()==false) $db->sdterr();
     //DBG echo '<hr>'.$db->getQuery().'<hr>';

    // hány darab szavazatot generál?
    $db->setQuery('select count(*) cc from wkepviseloszavazat'.$szavazas_id);
    $res = $db->loadObject();
    $darab = $res->cc;


   // eddigi max id a szavazok táblából
   $db->setQuery('
select max(id) maxid
from #__szavazok
where szavazas_id="'.$szavazas_id.'";
');
   $res = $db->loadObject();
   if ($db->getErrorMsg() != '') $db->stderr();
   if ($res)
     $eddigiMaxId = $res->maxid;
   else
     $eddigiMaxId = 0;

   // szavozok táblába másol a munkatáblából
   $db->setQuery('
insert into #__szavazok
(temakor_id,szavazas_id,user_id,idopont, kepviselo_id)
select distinct temakor_id, szavazas_id, user_id, now(), kepviselo_id
from wkepviseloszavazat'.$szavazas_id);
   if ($db->Query()==false) $db->sdterr();
    //DBG echo '<hr>'.$db->getQuery().'<hr>';

   // eddigi max(szavazo_id) a szavazatok táblából 
   $db->setQuery('
select max(szavazo_id) maxszavazo_id 
from #__szavazatok;
');
   $res = $db->loadObject();
   if ($db->getErrorMsg() != '') $db->stderr();
   if ($res)
     $maxSzavazaoId = $res->maxszavazo_id;
   else
     $maxSzavazoId = 0;

   // update wkepviseloszavazat maxSzavazo_id, eddigiMaxId és szavazok tábla alapján
   $konstans = $maxSzabvazoId - $eddigiMaxId;
   $db->setQuery('
update wkepviseloszavazat'.$szavazas_id.' w, #__szavazok sz
set w.szavazo_id = sz.id + '.$konstans.'
where w.user_id = sz.user_id and sz.szavazas_id = "'.$szavazas_id.'"
');
   if ($db->Query()==false) $db->sdterr();
     //DBG echo '<hr>'.$db->getQuery().'<hr>';

   // wkepviseoszavazat --> szavazatok 
   // HA TITKOS szavazás akkor user_id = 0
   $db->setQuery('
insert into #__szavazatok
select 0,w.temakor_id, w.szavazas_id, w.szavazo_id, 
       if (sz.titkos=0,w.user_id,0), 
       w.alternativa_id, w.pozicio
from wkepviseloszavazat'.$szavazas_id.' w
inner join #__szavazasok sz on sz.id = w.szavazas_id; 
');
     if ($db->Query()==false) $db->sdterr();
   
     $db->setQuery('drop table if exists wkepviseloszavazat'.$szavazas_id);
     if ($db->query()==false) $db->sdterr();
   
     // rekurziv feldolgozás a témakör->szulő képviselőkkel
     if ($kepviselo_filter != 0) {
       $db->setQuery('select szulo from #__temakorok where id="'.$kepviselo_filter.'" limit 1');
       $res = $db->loadObject();
       if ($res) {
         if ($res->szulo != 0) {
           $darab = $darab + $this->lideFeldolgozo($temakor_id, $szavazas_id, $res->id);
         }
       }
     }
     //DBG  echo '<p>lideFeldolgozo stop</p>';
     return $darab;
   } // lideFeldolgozo function
   /**
    * témakör szülö(k) utvonat kattintható linkek
    * @return string html kód
    * @JRequest integer temakor vagy szulo
    */
    public function getSzulok() {
      $s = '';
      $szulo = 0;
      $db = JFactory::getDBO();
      
      if (JRequest::getVar('szulo',0) > 0) {
        $szulo = JRequest::getVar('szulo',0);
      } else {
        $db->setQuery('select id,megnevezes,szulo from #__temakorok where id="'.JRequest::getVar('temakor',0).'"');
        $res = $db->loadObject();
        if ($res) $szulo = $res->szulo;
      }  
      while ($szulo != 0) {
          $db->setQuery('select id,megnevezes,szulo from #__temakorok where id="'.$szulo.'"');
          $res = $db->loadObject();
          if ($res) { 
            $szuloLink = JURI::base().'/index.php?option=com_szavazasok&view=szavazasoklist&temakor='.$res->id;
            $s = '<a href="'.$szuloLink.'">'.$res->megnevezes.'</a>&nbsp;&gt;&nbsp;'.$s;
            $szulo = $res->szulo;
          } else {
            $szulo = 0;
          }  
      }
      if ($s!='') $s = '<p class="szulok">'.$s.'</p>';
      return $s;
    }
    /**
     * redirect to login screen if succes goto this page
     */
    public function getLogin($msg='') {
        $myuri      = JFactory::getURI();
        $return     = $myuri->toString();
        $loginUrl  = 'index.php?option=com_users&view=login&return='.urlencode(base64_encode($return));
        $app = JFactory::getApplication();
        $app->redirect($loginUrl, $msg);        
    }           
	/**
	  * Témakör fa beolvasása
	  * @param integer szülő témakör
	  * @param string 'options' | 'ul'
	  * @return string htm
	*/  
	public function getTemakorTree($szulo=0,$mod='options',$level=0,$selected=0) {
		$result = '';
		$levelStr = '';
		for ($i=0; $i<$level; $i++) $levelStr .= '--'; 
		$db = JFactory::getDBO();
		$db->setQuery('select id, megnevezes, szulo
		from #__temakorok
		where szulo = '.$szulo.'
		order by megnevezes'
		);
		$res = $db->loadObjectList();
		if ($res) {
			foreach ($res as $res1) {
				if ($res1->id == $selected)
					 $selectedAttr = ' selected="selected"';
				 else
					 $selectedAttr = '';
				if ($mod == 'options')
				  $result .= '<option value="'.$res1->id.'"'.$selectedAttr.'>'.
			      $levelStr.
			      $res1->megnevezes.'</option>'."\n";
				else {
				  $result .= '<li>'.
			      $levelStr.
			      $res1->megnevezes.'</li>'."\n";
				}  
				if ($level < TEMAKOR_TREE_LIMIT)
				   $result .= $this->getTemakorTree($res1->id, $mod, (1+$level),$selected);
			}
		}
		return $result;
	}
}   
?>