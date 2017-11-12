<?php

/*
  Schulze method implementation based on http://en.wikipedia.org/wiki/Schulze_method
  The test cases are from http://wiki.electorama.com/wiki/Schulze_method
  GNU GPL v3 or later
  (c) Árpád Magosányi 2013

  tábla és mező nevek módositása az elovalsztok2018 adatbázishoz 2016.09.18. Fogler Tibor


  FT 2015.02.13  az 123 / 321 szavazatokat a condorcet - schulze módszer döntetlenre hozza
  ezért az eljárás ki lett bővitve az "elfogadhatóság" kezelésével.
  "elfogadható" egy alternativa" ha a szavazó a lehetséges poziciók első 2/3 -ba helyezte el.
  Condorcet döntetlen esetén az az alternativa kerül előre emelyiket többen tartottak
  "elfogadható"-nak.
*/
defined('_JEXEC') or die;
/**
* A Schulze method implementation
*/
class Condorcet {
    private $organization = null; // temakor_id
    private $poll = null;  // szavazas_id
    private $candidates = null;
    private $condorcetGyoztes = array();  // van condorcet gyöztes?
    private $dMatrix = null;
    private $pMatrix = null;
    private $filter = ''; // #__szavazat.ada... mezőkre vonatkozó sql filter vagy ''
    private $fordulo = 0;
    public $c1 = 0;
    public $c2 = 0;
    public $c3 = 0;
    public $c4 = 0;
    public $c5 = 0;
    public $c6 = 0;
    public $c7 = 0;
    public $c8 = 0;
    public $c9 = 0;
    public $c10 = 0;
    public $vote_count = 0;

      // FT 2015.02.13  az egyes alternativákat (key=alternativa.id) hányan rangsorolták
      //                a lehetőségek első felébe
      private $accpted = null;

      // FT 2017.01.25. hányn sorolták első helyre?
	  private $inFirst = null;
      private $db = null;
      private $shortlist = null;


	  /**
	  * @param JDatabase
		* @param integer Témakör ID
		* @param integer Szavazás ID
		* @param string SQL nyelven megirt user_activation -ra vonatkozó filter alias: 'a.'
		* @param integer fordulo
	  */
      function __construct($db,$temakor_id=0,$szavazas_id=0,$filter='', $fordulo = 0) {
          $this->db = $db;
          $this->organization = $temakor_id;
          $this->poll = $szavazas_id;
		  $this->filter = $filter;
		  $this->fordulo = $fordulo;
          $this->getCandidates();
          $this->loadDiffMatrix();
          $this->floydWarshall();
          $this->shortlist = $this->findWinner();
      }

      /**
      * Teljes feldolgozás, és eredmény html tábla generálás
      *
      */
      public function report() {
		  
        $result = '<h2>Szavazás eredménye</h2>';
		$result .= '<p>A szavazás kiértékelése a <a href="https://en.wikipedia.org/wiki/Schulze_method" target="_new">Condorcet / Schulze módszer</a> 
		szerint történt"</p>'.$this->showResult($this->shortlist);
        $result .= '<div id="eredmenyInfo" style="display:none">
		  <h4>Az eredmény részletei<h4>
          <p>Az alábbi táblázat sorai és oszlopai is egy-egy jelöltnek
          felelnek meg. A táblázat celláiban az látható, hogy a sorban szereplő
          jelölt hány szavazónál előzte meg az oszlopban lévőt. Zöld szin jelöli, hogy a sorban lévő jelölt a szavazók 
		  többségénél megelözte az oszlopban lévőt.</p>
		  <p>Amennyiben a táblázat felső sorában nem minden mező zöld, akkor a végső eredmény megállapításához szükség volt a 
		  Schulze eljárás második lépésére is.</p>
          ';
        $result .= $this->printMatrix($this->dMatrix);
		$js = "jQuery('#eredmenyInfo2').show()";	
		$result .= '<p><span style="cursor:pointer" onclick="'.$js.'").toggle()">&nbsp;[+]&nbsp;</span>További részletek</p>';	
	    $result .= '<div id="eredmenyInfo2" style="border-style:solid; border-width:1px; padding:5px; display:none">';
        $result .= '<h4>Shulze eljárás munka táblázata</h4>
		  <p>Az alábbi táblázat sorai és oszlopai is egy-egy jelöltnek felelnek meg. A táblázat celláiban szereplő 
		  számok a sorban szereplő jelöltnek az oszlopban lévőhöz viszonyított "erejét" mutatja.</p>
          ';
        $result .= $this->printMatrix($this->pMatrix);
        $result .= '<p> <i>Condorcet - Schulze sorrend képzése:<br />Ha a második táblázatban "[sor<sub>A</sub>,oszlop<sub>B</sub>]" &gt; "[sor<sub>B</sub>,oszlop<sub>A</sub>]" akkor "A" előzi "B" -t)</i>
		</p>';
        $result .= '<h4>A hagyományos (csak egy jelölt választható ki) és a rangsorba állítást megengedő módszer szerinti szavazás 
		összehasonlítása</h4>';
        $result .= $this->showlist($this->shortlist);
        $result .= '</div></div>';
		$result.= '<div class="condorcetInfo3">Azokat a szavazatokat, amelyeket a végleges jelöltlista felállása előtt adtak le, azoknak a jelölteknek a tekintetében vesszük figyelembe, amelyek a szavazatban és a végleges jelöltlistában is szerepelnek. Amennyiben egy jelölő szervezet kicseréli a jelöltjét, a régit töröljük, és új jelöltet inditunk.</div>';
        return $result;
      }

      /**
      * Jelölt lista beolvasása az adatbázisból
      * @output $this->candidates
      */
      private function getCandidates() {
          $candidates_sql = "select title AS megnevezes,id
		      from #__content
		      where  catid=".$this->poll.' and state=1';
          $db = $this->db;
          $db->setQuery($candidates_sql);
          $this->candidates=array();
	      $this->condorcetGyoztes = array();
          foreach($db->loadObjectList() as $row) {
              $this->candidates[$row->id] = $row->megnevezes;
          }
          return $this->candidates;
      }

      /**
      * a paraméterben kapott mátrix kiirása html kodként
      * a kiirás sorrendjét a $this->candidates sorrendje vezérli
      * beállítja a condorcetGyoztes[$i] értékét is
      * @param matrix
      * @return string html kód
      */
      private function printMatrix($matrix) {
          $result= '<center>
          <table border="1" cellpadding="4" class="pollResult" width="100%">
          <tr><th>&nbsp;</th><th>&nbsp;</th>
          ';
          $c=1;
          foreach($this->candidates as $id => $name) {
              $result .= "<th>$c</th>";
              $c++;
          }
          $result .= "</tr>";
          $r = 1;
          foreach($this->candidates as $id1 => $name1) {
              $result .= "<tr><th>$r</th><td>$name1</td>";
              foreach($this->candidates as $id2 => $name2) {
                  if(array_key_exists($id1,$matrix) && array_key_exists($id2,$matrix[$id1])) {
                     if ($id1 == $id2)
                        $result .= '<td align="center"> - </td>';
                     else {
                       if ($matrix[$id1][$id2] > $matrix[$id2][$id1])
                          $class = 'green';
                       else if ($matrix[$id1][$id2] < $matrix[$id2][$id1]) {
                          $class = 'red';
						  $this->condorcetGyoztes[$id1] = false;
                       } else
                          $class = 'white';
                       $result .= '<td align="center" class="'.$class.'">'.$matrix[$id1][$id2].'</td>';
                      }    
                  } else {
                    $result .= '<td align="center"> - </td>';
                  }
              }
              $result .= "</tr>\n";
              $r++;
          }
          $result .= "</table>
          </center>
          <p>&nbsp;</p>\n";
          return $result;
      }

      /**
      * a feldolgozási eljárás egyik lépése
      * $this->dMatrix -> $this->pMatrix
      * @return $this->pMatrix
      * @input $this->candidates, $this->dMatrix
      * cc x cc mátrix ahol cc az alternativák darabszáma
      * a cella tartalma a shulze metod szerinti max érték
      */
      private function floydWarshall() {
          $this->pMatrix = array();
          foreach($this->candidates as $i => $name1) {
              $this->pMatrix[$i] = array();
              foreach($this->candidates as $j => $name2) {
                  if($i != $j) {
                    if($this->dMatrix[$i][$j] > $this->dMatrix[$j][$i]) {
                      $this->pMatrix[$i][$j] = $this->dMatrix[$i][$j] ;
                    } else {
                      $this->pMatrix[$i][$j] = 0;
                    }
                  }
              }
          }

          /*
            Minden "i","j" párhoz a lehetséges "j" előzi "i"-t, "i" előzi "k"-t
            lehetséges hármas sorrendek közül
            kiválasztja a legnagyobb támogatottságut ezt irja be a [j][k] -ba

            "j" előzi "i"-t, "i" előzi "k" -t lehetséges hármasok közül
            a leginkább támogatott kerül [j][k] -ba
          */
          foreach($this->candidates as $i => $name1) {
              foreach($this->candidates as $j => $name2) {
                  if($i != $j) {
                    foreach($this->candidates as $k => $name3) {
                        if(($i != $k) && ($j != $k)) {
                          $this->pMatrix[$j][$k] = max($this->pMatrix[$j][$k], min ($this->pMatrix[$j][$i],$this->pMatrix[$i][$k]));
                        }
                    }
                  }
              }
          }
      }

      /**
      * A feldolgozási eljárás egyik lépése
      * $this->dmatrix képzése az adatbázisból
      * @output $this->dmatrix
      * @return $dMatrix
      */
      private function loadDiffMatrix() {
		  if ($this->filter == '')
			  $filterWhere = '';
		  else
			  $filterWhere = ' and ('.$this->filter.' and '.str_replace('a.','b.',$this->filter).')';
          $diff_sql = "select c1.id as id1, c2.id as id2, count(a.id) as d
                       from #__szavazatok a,
                            #__szavazatok b,
                            #__content c1,
                            #__content c2
                       where  a.szavazas_id=".$this->poll." and
                             b.szavazas_id=a.szavazas_id and
                             a.szavazo_id=b.szavazo_id and
                             a.alternativa_id=c1.id and
                             b.alternativa_id=c2.id and
                             a.pozicio < b.pozicio and
                             c1.id != c2.id and
                             c1.catid=a.szavazas_id and
                             c2.catid=a.szavazas_id  and
							 a.fordulo = ".$this->fordulo." and b.fordulo = ".$this->fordulo." ".$filterWhere."
                       group by c1.id, c2.id";
          $this->db->setQuery($diff_sql);
          $this->dMatrix=array();
          foreach($this->db->loadObjectList() as $row ) {
              $id1 = $row->id1;
              $id2 = $row->id2;
              $d = $row->d;
              if(!array_key_exists($id1,$this->dMatrix)) {
                  $this->dMatrix[$id1] = array();
              }
              $this->dMatrix[$id1][$id2] = $d;
          }
          foreach($this->candidates as $id1 => $name1) {
              if(!array_key_exists($id1,$this->dMatrix)) {
                  $this->dMatrix[$id1] = array();
              }
              foreach($this->candidates as $id2 => $name2) {
                  if(!array_key_exists($id2,$this->dMatrix[$id1])) {
                      $this->dMatrix[$id1][$id2] = 0;
                  }
              }
          }

          //FT 2015.02.13 a Condorcet holtverseny kezeléshez szükség van arra is
          //   hogy az adott alternativát hány szavazó sorolta az "elfogadható"
          //   kategoriába, azaz a lehetséges poziciók első felébe.
          $this->accepted = array();
		  if ($this->filter == '')
			  $filterWhere = '';
		  else
			  $filterWhere = ' and ('.$this->filter.')';
          $this->db->setQuery('select a.alternativa_id, count(a.szavazo_id) cc
          from #__szavazatok a
          where a.szavazas_id = '.$this->db->quote($this->poll).' and
                a.pozicio <= '.(count($this->candidates)*2/3).' and a.fordulo = '.$this->db->quote($this->fordulo).' '.$filterWhere.'
          group by a.alternativa_id
          ');
          $res = $this->db->loadObjectList();
          foreach ($res as $row) {
            $this->accepted[$row->alternativa_id] = $row->cc;
          }

	  // FT 2017.01.25. hányn sorolták első helyre?
          $this->inFirst = array();
		  if ($this->filter == '')
			  $filterWhere = '';
		  else
	  $filterWhere = ' and ('.$this->filter.')';
          $this->db->setQuery('select a.alternativa_id, count(a.szavazo_id) cc
          from #__szavazatok a
          where a.szavazas_id = '.$this->db->quote($this->poll).' and
                a.pozicio = 1 and a.fordulo = '.$this->db->quote($this->fordulo).' '.$filterWhere.'
          group by a.alternativa_id
          ');
          $res = $this->db->loadObjectList();
          foreach ($res as $row) {
            $this->inFirst[$row->alternativa_id] = $row->cc;
          }

          return $this->dMatrix;
      }

      // rendezéshez compare rutin
      private function beatsP($id1,$id2) {
          $result = $this->pMatrix[$id2][$id1] - $this->pMatrix[$id1][$id2];
          if ($result == 0) {
            $result = $this->accepted[$id2] - $this->accepted[$id1];
          }
          return $result;
      }

      /**
      * eredmény értékek számolása
      * @param array $shortlist
      * @return html kód
      */
      private function showlist($shortlist) {
          $values = array();
          $i = 0;
          $id1 = 0;
          $id2 = 0;
          $i = count($shortlist) - 1;
          $values[$shorlist[$i]] = 0;
          $lastValue = 0;
          for ($i=count($shortlist) - 2; $i >=0; $i--) {
            $id1 = $shortlist[$i];
            $id2 = $shortlist[$i+1];
            $values[$shortlist[$i]] = $lastValue + $this->pMatrix[$id1][$id2] - $this->pMatrix[$id2][$id1];
            $lastValue = $values[$shortlist[$i]];
          }

          // atlag poziok számítása
		  if ($this->filter == '')
			  $filterWhere = '';
		  else
			  $filterWhere = ' and ('.$this->filter.')';
          $szavazas_id = $this->poll;
          $db = JFactory::getDBO();
          $db->setQuery('select c.title AS megnevezes, avg(a.pozicio) pozicio
          from #__szavazatok a, #__content c
          where a.alternativa_id = c.id and
		        a.szavazas_id='.$db->quote($szavazas_id).' and a.fordulo='.$this->fordulo.' '.$filterWhere.'
          group by c.title
          order by 2,1');
          $res = $db->loadObjectList();
          $atlagok = array();
          foreach($res as $res1) {
            $atlagok[$res1->megnevezes] = $res1->pozicio;
          }

          $result =  '<table class="pollResultDetails" border="1" width="100%">
		     <th width="50%">Név</th>	
                     <th>A jelöltet első<br />helyre sorolók<br />száma</th>
                     <th>A rangsor állítást<br />engedő szavazás<br />eredménye</th>
                     </tr>'."\n";
		  $helyezes = 1;
          foreach($shortlist as $i) {
              if ($values[$shortlist[0]] > 0)
                $w2 = round($values[$i] * (300/$values[$shortlist[0]]));
              else
                $w2 = 0;
              $result .= '<tr><td class="txt">'.$this->candidates[$i].'</td>
                             <td class="right">'.$this->inFirst[$i].'</td>
                             <td class="right">'.$helyezes.'</td>
                         </tr>
						 ';
              if ($values[$i] > $maxValue) $maxValue = $values[$i];
			  if ($helyezes == 1) $this->c1 = $i;
			  if ($helyezes == 2) $this->c2 = $i;
			  if ($helyezes == 3) $this->c3 = $i;
			  if ($helyezes == 4) $this->c4 = $i;
			  if ($helyezes == 5) $this->c5 = $i;
			  if ($helyezes == 6) $this->c6 = $i;
			  if ($helyezes == 7) $this->c7 = $i;
			  if ($helyezes == 8) $this->c8 = $i;
			  if ($helyezes == 9) $this->c9 = $i;
			  if ($helyezes == 10) $this->c10 = $i;
			  $helyezes++;
          }
          $result .= "</table>\n";
      return $result;
      }

	  /**
	  *@param array a candidates.id -ket tartalmazza az eredménynek megfelelő sorrendben
	  */ 
      private function showResult($shortlist) {
          $db = JFactory::getDBO();
          $szavazas_id = $this->poll;

          // eredmény értékek számolása
          $values = array();
          $i = 0;
          $id1 = 0;
          $id2 = 0;
          $i = count($shortlist) - 1;
          $values[$shorlist[$i]] = 0;
          $lastValue = 0;
          for ($i=count($shortlist) - 2; $i >=0; $i--) {
            $id1 = $shortlist[$i];
            $id2 = $shortlist[$i+1];
            $values[$shortlist[$i]] = $lastValue + $this->pMatrix[$id1][$id2] - $this->pMatrix[$id2][$id1];
            $lastValue = $values[$shortlist[$i]];
          }


		  //+ 2017.02.24 most a végeredménynek megfelelően át kell
		  //  rendezni a $this->candidates táblázatokat
		  //  sorrend: shortlist[0], shortlist[1]... ezek az értékek a candidates
		  //  tábla indexei
		  $w = array();
		  foreach ($shortlist as $i) {
			$w[$i] = $this->candidates[$i];
		  }
		  $this->candidates = $w;

		  // az első helyzett condorcet gyöztes?
		  $i = $this->shortlist[0]; // első helyezett canidates->id
		  $this->condorcetGyoztes1 = true;
		  foreach  ($this->candidates as $j => $name) {
			if ($this->dMatrix[$i][$j] < $this->dMatrix[$j][$i]) $this->condorcetGyoztes1 = false;
		  }

          $result .=  '<table class="pollResult" border="1" width="100%">
                     <tr><th>Helyezés</th><th>Név</th></tr>'."\n";
		  $helyezes = 0;
          foreach($shortlist as $j => $i) {
					 // $cimkek (jelölő szervezet logok képzése 
					 $cimkek = '';
					 $db->setQuery('select * 
					 from #__contentitem_tag_map
					 where content_item_id='.$i.' and type_alias="com_content.article"');
					 $resCimkek = $db->loadObjectList();
					 foreach  ($resCimkek as $resCimke) {
						$cimkek .= '<li class="tag-list0 tag-'.$resCimke->tag_id.'">&nbsp;</li>';
					 }
					 if ($cimkek != '') 
						$cimkek = '<ul class="inline">'.$cimkek.'</ul>';

					 // condorcet gyöztes?
			         if ($j == 0)
				           $helyezes = 1;
			         else if ($values[$i] < $values[$shortlist[$j-1]])
				           $helyezes++;
			         $info = '';
			         if (($this->condorcetGyoztes1) & ($j==0)) {
				          $info .= '&nbsp;-&nbsp;<strong style="color:orange">Condorcet gyöztes</strong>';
			         }
			         if ($j > 0) {
				        if ($values[$i] === $values[$shotlist[$j-1]])
					        $info .= 'döntetlen';
			         }

					 // jelölt sor megjelenítése
					 $result .= '<tr><td class="pozicio">'.$helyezes.'</td>
			         <td class="nev">
							 <a href="'.JURI::root().'component/content/article/'.$i.'">
							 '.$this->candidates[$i].$cimkek.' '.$info.'
							 </a></td>
					</tr>
					';
          }
          $result .= "</table>\n";
    	  $db->setQuery('select count(DISTINCT a.szavazo_id) cc
    		  from #__szavazatok a
    		  where a.szavazas_id = '.$db->quote($this->poll));
          $res = $db->loadObject();
    	  $result .= '<p class="szavazatokSzama">Szavazatok száma:<var>'.$res->cc.'</var></p>
    		  ';
    	  $this->vote_count = $res->cc;

      return $result;
      }

      private function findWinner() {
          $shortlist = array_keys($this->candidates);
          $newlist = usort($shortlist,array('Condorcet','beatsP'));
          return $shortlist;
      }

}

?>
