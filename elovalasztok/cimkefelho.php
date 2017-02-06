<?php
/**
  * cimke felhő modul  JUMI -ban van használva
*/  
defined('_JEXEC') or die;
$db = JFactory::getDBO();
$db->setQuery('select t.title, count(m.core_content_id) darab
from #__tags t
left outer join #__contentitem_tag_map m on m.tag_id = t.id
group by title
having (count(m.core_content_id) > 0)
order by title');
$items = $db->loadObjectList();
$maximum = 0;
foreach ($items as $item) {
	if ($item->darab > $maximum) $maximum = $item->darab;
}

foreach ($items as $item) {
	$s = '10';
	if ($item->darab > (3 * $maximum /4)) $s = '16';
	if ($item->darab > (2 * $maximum /4)) $s = '14';
	if ($item->darab > (1 * $maximum /4)) $s = '12';
	echo '<a href="component/search/?searchword='.urlencode($item->title).'&ordering=newest&searchphrase=exact&limit=200&areas[0]=tags">
	  <span style="font-size:'.$s.'px">
	    '.str_replace(' ','&nbsp;',$item->title).'('.$item->darab.')
	  </span></a>&nbsp;';
}

$db->setQuery('select count(distinct szavazo_id) as cc from #__szavazatok');
$res = $db->loadObject();
echo '<p> </p>
<h3>Statisztika</h3>
<p class="osszesSzavazat">
  <lable>Összes szavazat:</label>
  <var>'.$res->cc.'</var>
</p>
';

$db->setQuery('select count(id) as cc from #__content where catid > 8 and catid < 116');
$res = $db->loadObject();
echo '<p class="osszesJelolt">
  <lable>Összes jelölt:</label>
  <var>'.$res->cc.'</var>
</p>
';

?>
