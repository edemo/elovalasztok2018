<?php
/**
  * előválasztási web oldal gombok modul
  *
  * telepitve kell lennie a com_pvoks -nak
  * a JUMI fielid=1 tartalmazza a config beállítást JSON formában
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  * Jrequest: option, view,  fileid, (id  | oevk)
  */
  defined('_JEXEC') or die;

  global $config; 
  include_once dirname(__FILE__).'/accesscontrol.php';
  include_once dirname(__FILE__).'/funkciok.php';
  include_once dirname(__FILE__).'/config.php';


  $user = JFactory::getUser();
  $msg = '';
  $input = JFactory::getApplication()->input;  

  $szavazas_id = $input->get('szavazas_id',0);
  $id = $input->get('id',0);
  $option = $input->get('option');
  $view = $input->get('view');
  $fileid = $input->get('fileid');
  $id = $input->get('id');
  
  if ($option == 'com_content') {
	  if ($view == 'article') {
		  $szavazas_id = oevkFromJelolt($id);
	  }
  }
  
  if ($szavazas_id == 0)  $szavazas_id = $id;
  
$userToken = JSession::getFormToken();
$logoutLink = JURI::base(). 'index.php?option=com_adalogin&task=dologout';

if ($user->id > 0) {
    echo '<div class="userInfo">
	<p><img src="'.JURI::root().'media/system/images/notice-info.png" />
	<var>'.$user->username.'</var></p>
	';
	if (szavazottMar($szavazas_id, $user)) {
		  echo '<p class="szavazottMar">Ön már szavazott ebben a szavazásban.</p>';
	}
	echo '</div>
	';
}

?>

<div id="elovalasztok-gombok" class="elovalasztok-gombok">
  <center>
  <div class="gombok1">
  <div class="gombok2">
  <?php if ($user->id <= 0) : ?>
  <button id="loginBtn" type="button" title="Bejelentkezés" 
     onclick="open('<?php JURI::base(); ?>index.php?option=com_adalogin&redi=<?php echo base64url_encode(JURI::root()); ?>','ADA','width=370,height=600,left=100,top=100');">
     <i class="icon-login"> </i> <label>ADA Bejelentkezés</label>
  </button>
  <br />
  <?php endif; ?>
    
  <?php if ($user->id > 0) : ?>
    <button onclick="location='<?php echo $logoutLink; ?>';" title="Kijelentkezés">
       <i class="icon-logout"> </i><label>Kijelentkezés</label>
    </button><br />
  <?php endif; ?>
  
  
  <?php 
    if (teheti($szavazas_id, $user, 'szavazas', $msg)) {
		  $c = ' btn-primary'; $d = ''; $t = 'Szavazás';
	} else {
		$c = ''; $d = ' disabled="disabled"';
		$t = $msg;
		if ($t == 'config') {
			$d = ' disabled="disabled"'; $t = 'Jelenleg nem lehet szavazni';
		}
	}	
  ?>
  <button <?php echo $d; ?> title="Szavazok"
    type="button" onclick="location='<?php echo JURI::root(); ?>component/jumi?fileid=4&task=szavazok&id=<?php echo $szavazas_id; ?>';">
	<i class="icon-szavazok"> </i><label>Szavazok</label>
  </button><br />
  
  <?php 
    if (teheti($szavazas_id, $user, 'eredmeny', $msg)) {
		$c = ' btn-primary'; $d = ''; $t = 'Eredmény';
	} else {
		$c = ''; $d = ' disabled="disabled"'; $t = 'jelenleg még nem nézhető meg az eredmény';
	}	
  ?>
  <button <?php echo $d; ?> title="Eredmény"
    type="button" onclick="location='<?php echo JURI::root(); ?>component/jumi?fileid=4&task=eredmeny&id=<?php echo $szavazas_id; ?>';">
    <i class="icon-eredmeny"> </i><label>Eredmény</label>
  </button><br />
    
  <?php if (teheti($szavazas_id, $user, 'szavazatDelete', $msg) & ($szavazas_id > 0)) : ?>
  <button title="Szavazatom törlöm"
    type="button" onclick="location='<?php echo JURI::root(); ?>component/jumi?fileid=4&task=szavazatdelete&id=<?php echo $szavazas_id; ?>';">
    <i class="icon-delete"> </i><label>Szavazatom törlöm</label>
  </button><br />
  <?php endif; ?>
  
  <button title="választó kerületek" 
    type="button" onclick="location='<?php echo JURI::root(); ?>component/content/category?id=8';">
	<i class="icon-oevk"> </i><label>Választó kerületek</label>
  </button>	
  </div>
  </div>
  </center>
</div>
  
  
