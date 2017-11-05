<?php
// szavazás leadása képernyő
// be: $item->oevkId, $item->oevkNev,  $item->alternativak [{"id":szám, "nev": string},...], $user

defined('_JEXEC') or die;
function options($count) {
  $result = '';
  for ($i=1; $i <= ($count - 1); $i++) {
      $result .= '<option value="'.$i.'">'.$i.'</option>';
  }
  $result .= '<option value="'.$count.'" selected="selected">'.$count.'</option>';
  return $result;
}

$cancelUrl = JURI::root().'index.php?option=com_content&view=category&layout=articles&id='.$item->oevkId;
echo '
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
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
// FIGYELEM az UL emen belül nem lehetnek #text t elemek !!!!!!!!!
echo '
<div>
<p id="voksHelp">Rangsorolja a jelölteket. A legjobbnak tartott kerüljön felülre! Egérrel huzhatja a sorokat,
vagy a <i class="icon-up"> </i> <i class="icon-down"> </i>  gombokkal mozgathatja őket, 
<br />Több jelelöltet is azonos pozicióba sorolhat a <img src="elovalasztok/assets/eq.png" />gomb használatával.</p>
<ul id="prefList" class="sortable">';
// fontos, hogy ul-ben és a li -elemekben ne legyenek #text elemek!
$i = 0;
foreach ($item->alternativak as $res1) {
  echo '<li id="li'.$res1->id.'" class="prefList-li"><span class="pozicio">'.$i.'</span>';
  echo '<label class="jelolt-noeq" id="jelolt'.$res1->id.'">'.$res1->nev.'</label>';
  echo '<button type="button" class="btn-up" id="btnup'.$res1->id.'" onclick="btnUpClick(event)" value="'.$i.'" title="Mozgatás felfelé">
           <span>Fel</span>
	    </button>';   
  echo '<button type="button" class="btn-dwn" id="btndwn'.$res1->id.'"  onclick="btnDwnClick(event)" value="'.$i.'" title="Mozgatás lefelé">
           <span>Le</span>
	    </button>';  
  echo '<button type="button" class="btn-eq" id="btneq'.$res1->id.'" onclick="btnEqClick(event)" value="'.$i.'" title="Legyen döntetlen">
          <span>=</span>
	    </button></li>';   
  $i++;		
}
echo '</ul>
<input type="hidden" name="szavazat" value"" />
<center><button type="button" onclick="okClick();" class="btn btn-primary btn-ok">Szavazat beküldése</button>
<button type="button" onclick="location='."'$cancelUrl'".'" class="btn btn-cancel">Mégsem</button></center>
</form>
</div>
';
?>
<script type="text/javascript">

  /** két DOM elem tartalmának megcserélése
    * @param DOM element
    * @param DOM element
    * @return void
  */	
  function cserel(elem1,elem2) {
	  var s = elem1.innerHTML;
	  var s2 = elem1.id;
	  elem1.innerHTML = elem2.innerHTML;
	  elem1.id = elem2.id;
	  elem2.innerHTML = s;
	  elem2.id = s2;
  }

  /**
    * btnUp gomb rutin 
	* @param windows.event (target vagy button vagy a benne lévő span)
  */	
  function btnUpClick(event) {
	  var btn = event.target;
	  if (btn.nodeName != 'BUTTON') {
		  btn = btn.parentNode;
	  };
	  var i = Number(btn.value);
      var ul = document.getElementById('prefList');
	  var lis = ul.childNodes;
	  if (i > 0) {
		  cserel(lis[i], lis[i-1]);
	  }
	  adjust(0,0);
	  return;
  }

  /**
    * btnDwn gomb rutin 
	* @param windows.event (target vagy button vagy a benne lévő span)
  */	
  function btnDwnClick(event) {
	  var btn = event.target;
	  if (btn.nodeName != 'BUTTON') {
		  btn = btn.parentNode;
	  };
	  var i = Number(btn.value);
      var ul = document.getElementById('prefList');
	  var lis = ul.childNodes;
	  if (i < (lis.length - 1)) {
		  cserel(lis[i], lis[i+1]);
	  }
	  adjust(0,0);
	  return;
  }

  /**
    * btnEq gomb rutin 
	* @param windows.event (target vagy button vagy a benne lévő span)
  */	
  function btnEqClick(event) {
	  var btn = event.target;
	  if (btn.nodeName != 'BUTTON') {
		  btn = btn.parentNode;
	  };
	  var i = Number(btn.value);
      var ul = document.getElementById('prefList');
	  var lis = ul.childNodes;
	  if (lis[i]) {
		if (lis[i].className == 'prefList-li') {
			lis[i].className = 'prefList-li-eq';
		}  else {
			lis[i].className = 'prefList-li';
		}
	  }
	  adjust(0,0);
  }
  
  /**
    * átrendezés után prefList elemeiben a poziciok, gombok, rendbetétele
	* paramétereket nemhasználja, azért kell hogy  sortabl({"stop":function -naklehessen használni)
  */	
  function adjust(event, uli) {
    var ul = document.getElementById('prefList');
	var lis = ul.childNodes;
	var w = 0;
	var i = 0;
	var pozicio = 1;
	while (i < lis.length) {
		w = lis[i].childNodes; //w[0]:span, w[1]:label, w[2]:btnUp, w[3]:btnDwn, w[4]:btnEq
	    w[0].innerHTML = pozicio;
		w[2].value = i;
		w[3].value = i;
		w[4].value = i;
		if (lis[i].className == 'prefList-li') {
			pozicio++;
			w[4].className = 'btn-eq';
			w[4].innerHTML = '<span>=</span>';
			w[4].title="Legyen döntetlen";
		} else {
			w[4].className = 'btn-ne';
			w[4].innerHTML = '<span>ne</span>';
			w[4].title="Ne legyen döntetlen";
		}
		if (i == 0) {
			w[2].disabled = 'disabled';
		} else {
			w[2].disabled = '';
		}
		if (i == (lis.length - 1)) {
			w[3].disabled = 'disabled';
			w[4].disabled = 'disabled';
		} else {
			w[3].disabled = '';
			w[4].disabled = '';
		}
		i++;
	}
  }
  
  /**
    * kialakitja a szavazat adatot (jelolt_id=pozicio, jelolt_id=pozicio,....)
	* elküldi a formot
	* @return void
  */	
  function okClick() {
		var s = '';
		var ul = document.getElementById('prefList');
		var lis = ul.childNodes;
		var i = 0;
		while (i < lis.length) {
			w = lis[i].childNodes; //w[0]:span, w[1]:label, w[2]:btnUp, w[3]:btnDwn, w[4]:btnEq
			if (s != '') {
				s += ',';
			}
			s += lis[i].id.substr(2,10)+'='+w[0].innerHTML;
			i++;
		}	
		document.forms.szavazatForm.szavazat.value = s;
		document.forms.szavazatForm.submit();
		return;
  }
  
  // JQuery init
  jQuery(function() {
		jQuery( ".sortable" ).sortable({"stop": adjust});
        jQuery( ".sortable" ).disableSelection();
		adjust(0,0);
  });		
</script>

