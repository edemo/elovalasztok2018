<?php
/**
<<<<<<< HEAD
  * előválasztási web oldal gombok modul
  *
  * telepitve kell lennie a com_pvoks -nak
  * a JUMI fielid=1 tartalmazza a config beállítást JSON formában
=======
  * gombok modul
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  * Jrequest: option, view,  fileid, (id  | oevk)
  */

  global $config; 
<<<<<<< HEAD
  include_once JPATH_SITE.'/elovalasztok/accesscontrol.php';
  include_once JPATH_SITE.'/elovalasztok/funkciok.php';
  include_once JPATH_SITE.'/elovalasztok/config.php';


  $user = JFactory::getUser();
  $msg = '';
  $input = JFactory::getApplication()->input;  
  $szavazas_id = $input->get('szavazas_id',0);
  $id = $input->get('id',0);
=======
  include_once JPATH_ROOT.'/elovalasztok/accesscontrol.php';
  include_once JPATH_ROOT.'/elovalasztok/models/szavazok.php';
  $user = JFactory::getUser();
  $msg = '';
  $model = new szavazokModel();  // tábla kreálások
  $input = JFactory::getApplication()->input;  
  $oevk = $input->get('oevk',0);
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  $option = $input->get('option');
  $view = $input->get('view');
  $fileid = $input->get('fileid');
  $id = $input->get('id');
<<<<<<< HEAD
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
=======
  if ($view == 'category') $oevk = $id;
  
    // ha egy jelölt adatlapján van, akkor megállapitható az oevk...
  if (($option == 'com_content') & ($view == 'article')) {
    $oevk = $model->getOevkFromJelolt($id,$config);	  
  }

  
  $userToken = JSession::getFormToken();
  $logoutLink = JURI::base(). 'index.php?option=com_users&task=user.logout&' . $userToken . '=1';

  if (szavazottMar($oevk, $user)) {
	  echo '<h3 class="szavazottMar">Ön már szavazott.</h3>';
  }
 
  function base64url_encode($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
  } 
  
?>
<div id="elovalasztok-gombok" class="elovalasztok-gombok">
  <?php if ($user->id <= 0) : ?>
  <button class="btn btn-primary btn-login" type="button" 
     onclick="adaLogin('<?php echo base64url_encode('http://elovalasztok.oldal.cloud'); ?>');">
     ADA Bejelentkezés
  </button><br />
  <button class="btn btn-primary btn-regist" type="button" 
     onclick="adaLogin('<?php echo base64url_encode('http://elovalasztok.oldal.cloud'); ?>');">
     ADA Regisztáció
  </button><br />
  <?php endif; ?>
    
  <?php if ($user->id > 0) : ?>
  <button class="btn btn-primary btn-logout" onclick="location='<?php echo $logoutLink?>';">Kijelentkezés</button><br />
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
  <?php endif; ?>
  
  
  <?php 
<<<<<<< HEAD
    if (teheti($szavazas_id, $user, 'szavazas', $msg)) {
=======
    if (teheti($oevk, $user, 'szavazas', $msg)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		  $c = ' btn-primary'; $d = ''; $t = '';
	} else {
		$c = ''; $d = ' disabled="disabled"';
		$t = $msg;
		if ($t == 'config') {
			$d = ' disabled="disabled"'; $t = 'Jelenleg nem lehet szavazni';
		}
	}	
  ?>
<<<<<<< HEAD
  <div class="jeloltkereses">
  <h2>Egyéni jelölt keresés</h2>
  <button class="btn<?php echo $c; ?> btn-szavazok"<?php echo $d; ?> title="<?php echo $t; ?>"
    type="button" onclick="location='index.php?option=com_jumi&fileid=4&task=szavazok&id=<?php echo $szavazas_id; ?>';">
	Szavazok
  </button><br />
  
  <?php 
    if (teheti($szavazas_id, $user, 'eredmeny', $msg)) {
=======
  <button class="btn<?php echo $c; ?> btn-szavazok"<?php echo $d; ?> title="<?php echo $t; ?>"
    type="button" onclick="location='index.php?option=com_jumi&view=application&fileid=5&oevk=<?php echo $oevk; ?>&task=szavazok';">
	Szavazok
  </button><br />
  
  
  <?php 
    if (teheti($oevk, $user, 'eredmeny', $msg)) {
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
		$c = ' btn-primary'; $d = ''; $t = '';
	} else {
		$c = ''; $d = ' disabled="disabled"'; $t = 'jelenleg még nem nézhető meg az eredmény';
	}	
  ?>
  <button class="btn<?php echo $c; ?> btn-eredmeny"<?php echo $d; ?> title="<?php echo $t; ?>"
<<<<<<< HEAD
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
  
  
=======
    type="button" onclick="location='index.php?option=com_jumi&fileid=5&oevk=<?php echo $oevk; ?>&task=eredmeny';">
    Eredmény
  </button><br />
  
  
  <?php if (teheti($oevk, $user, 'szavazatDelete', $msg) & ($oevk > 0)) : ?>
  <button class="btn btn-delete"
    type="button" onclick="location='index.php?option=com_jumi&fileid=5&oevk=<?php echo $oevk; ?>&task=szavazatDelete';">
    Szavazatom törlöm
  </button><br />
  <?php endif; ?>

  <?php if (teheti($oevk, $user, 'szavazatEdit', $msg) & ($oevk > 0)): ?>
  <button class="btn btn-edit"
    type="button" onclick="location='index.php?option=com_jumi&fileid=5&oevk=<?php echo $oevk; ?>&task=szavazatEdit';">
    Szavazatom módosítom
  </button><br />
  <?php endif; ?>
  
  <button class="btn btn-primary btn-oevk"
    type="button" onclick="location='<?php echo JURI::root(); ?>valaszto-keruletek';">
	Választó kerületek
  </button>	
</div>
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0
