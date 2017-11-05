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
$logoutLink = JURI::root(). 'index.php?option=com_adalogin&task=dologout';

if ($user->id > 0) {
    //echo '<div class="userInfo">
	//<p><img src="'.JURI::root().'media/system/images/notice-info.png" />
	//<var>Bejelentkezve</var></p>
	//';
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
     onclick="open('<?php echo JURI::root(); ?>index.php?option=com_adalogin&redi=<?php echo base64_encode(JURI::root()); ?>','ADA','width=370,height=600,left=100,top=100');">
     <i class="icon-login"> </i> <label>Bejelentkezés</label>
  </button>
  <br />
  <?php endif; ?>
    
  <?php if ($user->id > 0) : ?>
    <button id="logoutBtn" onclick="location='<?php echo $logoutLink; ?>';" title="Kijelentkezés">
       <i class="icon-logout"> </i><label>Kijelentkezés</label>
    </button><br />
  <?php endif; ?>
  
  <?php if (isOevkSzavazas($szavazas_id)) : ?>
	  <?php if (teheti($szavazas_id, $user, 'szavazas', $msg)) : ?>
		  <button id="szavazokBtn" title="Szavazok"
			type="button" onclick="location='<?php echo JURI::root(); ?>component/jumi?fileid=4&task=szavazok&id=<?php echo $szavazas_id; ?>';">
			<i class="icon-szavazok"> </i><label>Szavazok</label>
		  </button><br />
	  <?php else : ?>
		  <div class="nemszavazhat">
			<i class="icon-nemszavazhat"> </i>
			<label><?php echo $msg; ?></label>
		  </div><br />
	  <?php endif; ?>
  <?php endif; ?>
  
  <?php if (teheti($szavazas_id, $user, 'eredmeny')) : ?>
  <button id="eredmenyBtn" <?php echo $d; ?> title="Eredmény"
    type="button" onclick="location='<?php echo JURI::root(); ?>component/jumi?fileid=4&task=eredmeny&id=<?php echo $szavazas_id; ?>';">
    <i class="icon-eredmeny"> </i><label>Eredmény</label>
  </button><br />
  <?php endif; ?>
     
  <button id="keruletekBtn" title="választó kerületek" 
    type="button" onclick="location='<?php echo JURI::root(); ?>component/content/category?id=8';">
	<i class="icon-oevk"> </i><label>Választó kerületek</label>
  </button>	
  </div>
  </div>
  </center>
</div>
  
  
