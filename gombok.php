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
  if ($szavazas_id == 0)  $szavazas_id = $id;
  
$userToken = JSession::getFormToken();
$logoutLink = JURI::base(). 'index.php?option=com_adalogin&task=dologout';

if (szavazottMar($szavazas_id, $user)) {
	  echo '<h3 class="szavazottMar">Ön már szavazott.</h3>';
}

 
function base64url_encode($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 
?>
<div id="elovalasztok-gombok" class="elovalasztok-gombok">
  <?php if ($user->id <= 0) : ?>
  <!-- button class="btn btn-primary btn-login" type="button" 
     onclick="jQuery('#loginPopup').show(); jQuery('#loginIfrm').attr('src','index.php?option=com_adalogin&redi=<?php echo base64url_encode('http://valasztoimozgalom.hu'); ?>');">
     ADA Bejelentkezés
  </button -->
  <button class="btn btn-primary btn-login" type="button" 
     onclick="open('<?php JURI::base(); ?>index.php?option=com_adalogin&redi=<?php echo base64url_encode('http://valasztoimozgalom.hu'); ?>','ADA','width=370,height=600,left=100,top=100');">
     ADA Bejelentkezés
  </button>
  
  <br />
  <!-- button class="btn btn-primary btn-regist" type="button" 
     onclick="location='index.php?option=com_adalogin&redi=<?php echo base64url_encode('http://valasztoimozgalom.hu'); ?>';">
     ADA Regisztáció
  </button><br / -->
  <?php endif; ?>
    
  <?php if ($user->id > 0) : ?>
  <a href="<?php echo JURI::base(); ?>index.php?option=com_users&view=profile&layout=edit">
    <strong style="font-size:1.4em"><i class="icon-user"></i>&nbsp;<?php echo $user->username; ?></strong>
  </a><br />
  <button class="btn btn-primary btn-logout" onclick="location='<?php echo $logoutLink; ?>';">Kijelentkezés</button><br />
  <?php endif; ?>
  
  
  <?php 
    if (teheti($szavazas_id, $user, 'szavazas', $msg)) {
		  $c = ' btn-primary'; $d = ''; $t = '';
	} else {
		$c = ''; $d = ' disabled="disabled"';
		$t = $msg;
		if ($t == 'config') {
			$d = ' disabled="disabled"'; $t = 'Jelenleg nem lehet szavazni';
		}
	}	
  ?>
  <div class="jeloltkereses">
  <h2>Egyéni jelölt keresés</h2>
  <button class="btn<?php echo $c; ?> btn-szavazok"<?php echo $d; ?> title="<?php echo $t; ?>"
    type="button" onclick="location='index.php?option=com_jumi&fileid=4&task=szavazok&id=<?php echo $szavazas_id; ?>';">
	Szavazok
  </button><br />
  
  <?php 
    if (teheti($szavazas_id, $user, 'eredmeny', $msg)) {
		$c = ' btn-primary'; $d = ''; $t = '';
	} else {
		$c = ''; $d = ' disabled="disabled"'; $t = 'jelenleg még nem nézhető meg az eredmény';
	}	
  ?>
  <button class="btn<?php echo $c; ?> btn-eredmeny"<?php echo $d; ?> title="<?php echo $t; ?>"
    type="button" onclick="location='index.php?option=com_jumi&fileid=4&task=eredmeny&id=<?php echo $szavazas_id; ?>';">
    Eredmény
  </button><br />
    
  <?php if (teheti($szavazas_id, $user, 'szavazatDelete', $msg) & ($szavazas_id > 0)) : ?>
  <button class="btn btn-delete"
    type="button" onclick="location='index.php?option=com_jumi&fileid=4&task=szavazatdelete&id=<?php echo $szavazas_id; ?>';">
    Szavazatom törlöm
  </button><br />
  <?php endif; ?>
  
  <button class="btn btn-primary btn-oevk"
    type="button" onclick="location='index.php?option=com_jumi&fileid=3';">
	Választó kerületek
  </button>	
  </div>
</div>
  
  
