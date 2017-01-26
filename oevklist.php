<?php
/**
  * oevk listázó komponens
  *
  * Licensz: GNU/GPL
  * Szerző: Fogler Tibor   tibor.fogler@gmail.com_addref
  * web: github.com/utopszkij/elovalasztok2018
  * Verzió: V1.00  2016.09.14.
  *
  * cikk lista URL formája: index.php?option=com_content&view=category&id=9
  */
  $db = JFactory::getDBO();
  $db->setQuery('select *
  from #__categories
  where parent_id = 8
  order by title');
  $items = $db->loadObjectList();
?>
<div id="oevklist" class="elovalasztok-page">
  <h1>Országos egyéni választó kerületek (OEVK) listája</h1>
  <p>Az OEVK megnevezésére kattintva a jelöltek listájához jut, itt lesz lehetősége - bejelentkezés után - szavazni is (ha még nem szavazott).</p>
  <?php foreach ($items as $item) : ?>
    <p>
	  <a href="<?php echo JURI::base(); ?>index.php?option=com_content&view=category&layout=articles&id=<?php echo $item->id?>">
	    <?php echo $item->title; ?>
	  </a>
	</p>
  <?php endforeach; ?>
</div>