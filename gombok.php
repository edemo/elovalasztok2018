<?php
/**
  * gombok modul
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  * Jrequest: option, view,  fileid, (id  | oevk)
  */

  global $config; 
  include_once JPATH_ROOT.'/elovalasztok/accesscontrol.php';
  include_once JPATH_ROOT.'/elovalasztok/models/szavazok.php';
  $user = JFactory::getUser();
  $msg = '';
  $model = new szavazokModel();  // tábla kreálások
  $input = JFactory::getApplication()->input;  
  $oevk = $input->get('oevk',0);
  $option = $input->get('option');
  $view = $input->get('view');
  $fileid = $input->get('fileid');
  $id = $input->get('id');
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
  <?php endif; ?>
  
  
  <?php 
    if (teheti($oevk, $user, 'szavazas', $msg)) {
		  $c = ' btn-primary'; $d = ''; $t = '';
	} else {
		$c = ''; $d = ' disabled="disabled"';
		$t = $msg;
		if ($t == 'config') {
			$d = ' disabled="disabled"'; $t = 'Jelenleg nem lehet szavazni';
		}
	}	
  ?>
  <button class="btn<?php echo $c; ?> btn-szavazok"<?php echo $d; ?> title="<?php echo $t; ?>"
    type="button" onclick="location='index.php?option=com_jumi&view=application&fileid=5&oevk=<?php echo $oevk; ?>&task=szavazok';">
	Szavazok
  </button><br />
  
  
  <?php 
    if (teheti($oevk, $user, 'eredmeny', $msg)) {
		$c = ' btn-primary'; $d = ''; $t = '';
	} else {
		$c = ''; $d = ' disabled="disabled"'; $t = 'jelenleg még nem nézhető meg az eredmény';
	}	
  ?>
  <button class="btn<?php echo $c; ?> btn-eredmeny"<?php echo $d; ?> title="<?php echo $t; ?>"
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