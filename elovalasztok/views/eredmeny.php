<?php
// input adat: $oevk, $report, $filter

defined('_JEXEC') or die;
$db = JFactory::getDBO();
$db->setQuery('select count(user_id) as cc from #__szavazatok where szavazas_id = '.$db->quote($oevk));
$res = $db->loadObject();
$voksDarab = $res->cc;

?>
    <form action="index.php?option_com_jumi&view=application&fileid=5" method="get">
	<input type="hidden" name="option" value="com_jumi" />
	<input type="hidden" name="view" value="application" />
	<input type="hidden" name="fileid" value="5" />
	<input type="hidden" name="oevk" value="<?php echo $oevk; ?>" />
	<input type="hidden" name="task" value="eredmeny" />

	<div style="display:none">
	Szűrés <select name="filter" style="width:390px">
	   <option value=""<?php if ($filter=='') echo ' selected="selected"'; ?>>
	      Minden szavazó
	   </option> 
	   <option value="a.ada0=1"<?php if ($filter=="a.ada0=1") echo ' selected="selected"'; ?>>
	      ADA regisztrált szavazók
	   </option> 
	   <option value="a.ada1=1"<?php if ($filter=="a.ada1=1") echo ' selected="selected"'; ?>>
	      ADA megadták személyi adataikatat
	   </option> 
	   <option value="a.ada2=1"<?php if ($filter=="a.ada2=1") echo ' selected="selected"'; ?>>
	      ADA e-mail aktiváltak
	   </option> 
	   <option value="a.ada3=1"<?php if ($filter=="a.ada3=1") echo ' selected="selected"'; ?>>
	      ADA ellenörzöttek
	   </option> 
	   
	</select>
	<button type="submit" class="btn btn-primary">Szűrést módosít</button>
	</div>   
	
	</form>

	<?php if ($voksDarab > 0) : ?>
		<?php echo $report ?>
		<?php $url = JURI::root().'component/jumi?fileid=4&task=szavazatok&id='.$oevk; ?>
		<p><button type="button" onclick="infoClick()" id="infoBtn">+</button>
			Az eredmény részletei&nbsp;
			<a href="<?php echo $url; ?>">szavazatok</a>
		</p>
	<?php else : ?>
		<div class="noVoksInfo">Nincsenek szavazatok ebben a szavazásban.</div>	
	<?php endif; ?>
	
	<center><br />
	<button type="button" onclick="location='<?php echo $backUrl; ?>';" style="height:34px" class="btn btn-primary btn-back">Vissza</button>
	</center>
	<script type="text/javascript">
	  function infoClick() {
		  var d = document.getElementById("eredmenyInfo");
		  if (d.style.display=="block") {
			  d.style.display="none";
			  document.getElementById("infoBtn").innerHTML="+";
		  } else {
			  d.style.display="block";
			  document.getElementById("infoBtn").innerHTML="-";
		  }
	  }
	</script>
