<?php
?>
<div class="adaregist">
<?php 
echo '
<style type="text/css">

</style>
<br /><br />
<form name="adminForm" method="post" action="'.JURI::base().'index.php" id="registform">
	<input type="hidden" name="option" value="com_adalogin" />
	<input type="hidden" name="task" value="processform" />
	<input type="hidden" name="Itemid" value="0" />
	<input type="hidden" name="adaid" value="'.$this->adaid.'" />
	<input type="hidden" name="adaemail" value="'.$this->adaemail.'" />
	<input type="hidden" name="assurance" value="'.str_replace('"','',$this->assurance).'" />
	<input type="hidden" name="redi" value="'.$this->redi.'" />
	<h2>Első belépés ADA loginnal</h2>
	<p>'.JText::_('ADALOGIN_ADA_EMAIL').':&nbsp;&nbsp;&nbsp;<var>'.$this->adaemail.'</var></p>
	<div class="help">'.JText::_('ADALOGIN_NICKHELP').'</div>
	<table border="0" class="fields" style="width:auto">
	  <tbody>
	    <tr>
		  <td align="right"><label>'.JText::_('ADALOGIN_JOOMLA_NICK').':</label></td>
		  <td><input type="text" id="nick" name="nick" value="" size="60" /></td>
		</tr>
		<tr>
		  <td colspan="2" align="center">
			<button id="submit" type="submit">'.JText::_('ADALOGIN_OK').'</button>
		  </td>
		</tr>
	  </tbody>
	</table>
	'.JHtml::_('form.token').'
	</form>
';
?>	
</div>
