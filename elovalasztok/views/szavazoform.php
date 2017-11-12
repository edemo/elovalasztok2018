<?php
// szavazás leadása képernyő
// be: $item->oevkId, $item->oevkNev,  $item->alternativak [{"id":szám, "nev": string},...], $user

defined('_JEXEC') or die;

$cancelUrl = JURI::root().'index.php?option=com_content&view=category&layout=articles&id='.$item->oevkId;
echo '
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
<script src="elovalasztok/views/vote.js" type="text/javascript"></script>
';
   
echo '<h2>'.$item->oevkNev.'</h2>
<div id="divTurelem" style="display:none; background-color:transparent; cursor:default;"></div>
<form method="post" action="'.JURI::root().'index.php?option=com_jumi" name="szavazatForm" id="szavazatForm">
<input type="hidden" name="view" value="application" />
<input type="hidden" name="fileid" value="4"/>
<input type="hidden" name="task" value="szavazatSave" /> 
<input type="hidden" name="task" value="szavazatSave" />
<input type="hidden" name="nick" value="'.$user->username.'" />
<input type="hidden" name="oevk" value="'.$item->oevkId.'" />
'.JHtml::_('form.token').'
';
if (count($item->alternativak)==0) {
  echo '
  <div class="msg">Nincs egyetlen jelölt sem</div>';
  echo '<center><button type="button" onclick="location='."'$cancelUrl'".'" class="btnCancel">Vissza</button></center>';
  return;
}

echo '
<div>
<p id="voksHelp">Rangsorolja a jelölteket. A legjobbnak tartott kerüljön felülre! Egérrel huzhatja a sorokat,
vagy használhatja a lenyiló menüt. Több jelelöltet is azonos pozicióba sorolhat</p>
';
echo '
<table id="preftable" width="100%" border="1">
<thead><tr><th></th><th>Név</th><th></th><th>Név</th></tr></thead>
<tbody>';
// fontos, hogy ul-ben és a tr elemekben ne legyenek #text elemek!
$i = 0;
foreach ($item->alternativak as $res1) {
  echo '<tr>';
  echo '<td style="width:30px; text-align:center"><button type="button" class="up">&uarr;</button></td>';	
  echo '<td id="jelolt'.$res1->id.'" style="cursor:pointer"><var>'.$res1->nev.'</var></td>';
  echo '<td style="width:30px; text-align:center"><button type="button" class="down">&darr;</button>';
  echo '</td><td style="width:65px;"><select style="width:60px;" onchange="sort_rows()">';
  for ($j=1; $j<=count($item->alternativak); $j++) {
	  if ($j == $i+1) {
		  echo '<option value="'.$j.'" selected="selected">'.$j.'</option>';
	  } else {
		  echo '<option value="'.$j.'">'.$j.'</option>';
	  }
  }
  echo '</select></td></tr>';
  $i++;		
}
echo '</tbody>
</table>
<input type="hidden" name="szavazat" value"" />
<center><button id="okBtn" type="button" class="btn btn-primary btn-ok">Szavazat beküldése</button>
<button type="button" onclick="location='."'$cancelUrl'".'" class="btn btn-cancel">Mégsem</button></center>
<div class="szavazashelp">
A választást megelőző harmincadik napig új jelöltek jelenhetnek meg. Jelöltek visszaléphetnek, a jelöltekről szoló infok változhatnak. Ezért javasljuk, hogy idönként látogasson vissza ide és szükség esetén módosítsa szavazatát!
</div>
</form>
</div>
';
?>

