<?php
// input adat: $oevk, $report, $filter
?>
    <form action="index.php?option_com_jumi&view=application&fileid=5" method="get">
	<input type="hidden" name="option" value="com_jumi" />
	<input type="hidden" name="view" value="application" />
	<input type="hidden" name="fileid" value="5" />
	<input type="hidden" name="oevk" value="<?php echo $oevk; ?>" />
	<input type="hidden" name="task" value="eredmeny" />
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
	</form>';

	<p><button type="button" onclick="infoClick()" id="infoBtn">+</button>
		Kiértékelés részletei
	</p>	
	<?php echo $report ?>
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
