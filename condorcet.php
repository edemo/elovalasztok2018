<?php

/*
  Schulze method implementation based on http://en.wikipedia.org/wiki/Schulze_method
  The test cases are from http://wiki.electorama.com/wiki/Schulze_method
  GNU GPL v3 or later
  (c) Árpád Magosányi 2013
  
  tábla és mező nevek módositása az elovalsztok2018 adatbázishoz 2016.09.18. Fogler Tibor


  FT 2015.02.13  az 123 / 321 szavazatokat a condorce - shulz módszer döntetlenre hozza
  ezért az eljárás ki lett bővitve az "elfogadhatóság" kezelésével.
  "elfogadható" egy alternativa" ha a szavazó a lehetséges poziciók első 2/3 -ba helyezte el.
  Condorcet döntetlen esetén az az alternativa kerül előre emelyiket többen tartottak
  "elfogadható"-nak.
*/

class Condorcet {
    /* A Schulze method implementation*/
      private $organization = null; // temakor_id
      private $poll = null;  // szavazas_id
      private $candidates = null;
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
      
      private $db = null;
      private $shortlist = null;

      
	  /**
	    * @param JDatavbase
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

      public function report() {
          $result = '<div id="eredmenyInfo" style="display:none">
          <p>Az alábbi táblázat sorai és oszlopai is egy-egy jelöltnek 
          felelnek meg. A táblázat celláiban az látható, hogy a sorban szereplő 
          jelölt hány szavazónál előzte meg az oszlopban lévőt.</p>
          ';
          $result .= $this->printMatrix($this->dMatrix);
          $result .= '<p>Az alábbi táblázat sorai és oszlopai is egy-egy alternatívának 
          felelnek meg. A táblázat celláiban a sorban szereplő 
          alternatívától az oszlopban lévőhöz vezető legerősebb Shulze method szerinti utvonal 
          "erejét" mutatja.</p>
          ';
          $result .= $this->printMatrix($this->pMatrix);
          $result .= '<p><i>"Elfoadható"</> adat jelentése: a jelöltet hányan sorolták a lehetséges poziciók felső 2/3 -ba.<br />
		  <i>Condorcet - Shulze sorrend képzése:<br />Ha a második táblázatban "[sor<sub>A</sub>,oszlop<sub>B</sub>]" &gt; "[sor<sub>B</sub>,oszlop<sub>A</sub>]" akkor "A" előzi "B" -t)</i>
		  , egyezőség esetén az kerül elebbre aki többeknek  "Elfogadható".</p>';
          $result .= '<h3>Condorcet / Shulze  módszer szerinti sorrend</h3></div>';
          $result .= $this->showlist($this->shortlist);
          return $result;
      }

      private function getCandidates() {
          $candidates_sql = "select title AS megnevezes,id 
		  from #__content
		  where  catid=".$this->poll;
          $db = $this->db;
          $db->setQuery($candidates_sql);
          $this->candidates=array();
          foreach($db->loadObjectList() as $row) {
              $this->candidates[$row->id] = $row->megnevezes;
          }
          return $this->candidates;
      }

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
                     else
                        $result .= '<td align="center">'.$matrix[$id1][$id2].'</td>';
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
       * @return $this->pMatrix
       *    cc x cc mátrix ahol cc az alternativák darabszáma
       *    a cella tartalma a shulze metod szerinti max érték               
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
          
          //FT 2015.02.13 a Condorce holtverseny kezeléshez szükség van arra is
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

      private function showlist($shortlist) {
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
          
          $result =  '<table class="pollResult" border="1" width="100%">'."\n".
                     '<tr><th>Megnevezés</th><th>Átlag poziió</th>
                     </th><th>Elfogadható</th>
                     <th colspan="2">Condorce-Shulze érték</th>'.
                     '</tr>'."\n";
		  $helyezes = 1;			 
          foreach($shortlist as $i) {
              if ($values[$shortlist[0]] > 0)
                $w2 = round($values[$i] * (300/$values[$shortlist[0]]));
              else
                $w2 = 0;  
              $result .= "<tr><td>".$this->candidates[$i]."</td>".
                             "<td>".$atlagok[$this->candidates[$i]]."</td>".
                             "<td>".$this->accepted[$i]."</td>".
                             "<td>".$values[$i]."</td>".
                             '<td><div style="display:inline-block; background-color:blue; width:'.$w2.'px;">&nbsp;</div>&nbsp;</td>'.
                         "</tr>\n";
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
		  $db->setQuery('select count(DISTINCT a.user_id) cc 
		  from #__szavazatok a 
		  where a.szavazas_id = '.$db->quote($szavazas_id).' and a.fordulo='.$this->fordulo.' '.$filterWhere);
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
