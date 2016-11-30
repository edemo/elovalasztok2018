<?php
// szavazás leadása képernyő
// be: $item->oevkId, $item->oevkNev,  $item->alternativak [{"id":szám, "nev": string},...], $user

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
<style type="text/css">
#prefList {border-style:solid; border-width:1px; border-color:black;}
#prefList {list-style:none; text-align:right}
#prefList label {display:inline-block; width:80%; text-align:left}
#prefList .prefList-li {border-style:solid; border-width:0px 0px 1px 0px; border-color:black;}
#prefList .prefList-li-eq {border-style:solid; border-width:0px 0px 1px 0px; border-color:#C0C0C0;}
#prefList .pozicio {padding:5px;}

#prefList button {height:32px; width:32px; background-color:#b4d8ea;}
#prefList button span {display:none}
#prefList .btn-up {background-image:url('.JURI::root().'elovalasztok/assets/up.png);}
#prefList .btn-up:disabled {background-image:none; background-color:#D0D0D0}
#prefList .btn-dwn {background-image:url('.JURI::root().'elovalasztok/assets/down.png);}
#prefList .btn-dwn:disabled {background-image:none; background-color:#D0D0D0}
#prefList .btn-eq {background-image:url('.JURI::root().'elovalasztok/assets/eq.png);}
#prefList .btn-eq:disabled {background-image:none; background-color:#D0D0D0}
#prefList .btn-ne {background-image:url('.JURI::root().'elovalasztok/assets/ne.png);}
#prefList .btn-ne:disabled {background-image:none; background-color:#D0D0D0}

</style>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
';

   
echo '<h2>'.$item->oevkNev.'</h2>
<div id="divTurelem" style="display:none; background-color:transparent; cursor:default;"></div>
<form method="post" action="'.JURI::root().'index.php?option=com_jumi" name="szavazatForm" id="szavazatForm">
<input type="hidden" name="view" value="application" />
<input type="hidden" name="fileid" value="5"/>
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
<p>Rangsorolja a jelölteket. A legjobbnak tartott kerüljön felülre! Egérrel huzhatja a sorokat, vagy a fel/legombokkal mozgathatja őket, 
<br />több jelelöltet is azonos pozicióba sorolhat a <img src="elovalasztok/assets/eq.png" />gombbal.</p>
<ul id="prefList" class="sortable" width="100%">';
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
  echo '<button type="button" class="btn-eq" id="btneq'.$res1->id.'" onclick="btnEqClick(event)" value="'.$i.'" title="Legyen az alatta lévő is ezbbe a pozicióba sorolva">
          <span>=</span>
	    </button></li>';   
  $i++;		
}
echo '</ul>
<input type="hidden" name="szavazat" value"" />
<center><button type="button" onclick="okClick();" class="btn btn-primary btn-ok">Szavazat beküldése</button>
<button type="button" onclick="location='."'$cancelUrl'".'" class="btn btn-cancel">Mégsem</button></center>
</form>
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
  function btnEqClick(i) {
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
			w[4].title="Legyen az alatta lévő is ebbe a pozicióba sorolva";
		} else {
			w[4].className = 'btn-ne';
			w[4].innerHTML = '<span>ne</span>';
			w[4].title="Ne legyen az alatta lévő ebbe a pozicióba sorolva";
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
  $(function() {
		$( ".sortable" ).sortable({"stop": adjust});
        $( ".sortable" ).disableSelection();
		adjust(0,0);
  });		
</script>

