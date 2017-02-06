<?php
?>
<div class="adaregist">
<?php 
echo '
<form name="adminForm" method="post" action="'.JURI::base().'index.php">
	<input type="hidden" name="option" value="com_adalogin" />
	<input type="hidden" name="task" value="processform" />
	<input type="hidden" name="Itemid" value="0" />
	<input type="hidden" name="adaid" value="'.$this->adaid.'" />
	<input type="hidden" name="adaemail" value="'.$this->adaemail.'" />
	<input type="hidden" name="assurance" value="'.str_replace('"','',$this->assurance).'" />
	<input type="hidden" name="redi" value="'.$this->redi.'" />
	<div class="help">'.JText::_('ADALOGIN_NICKHELP').'</div>
	<div class="mezok">
		<p class="button">'.JText::_('ADALOGIN_JOOMLA_NICK').':<input type="text" id="nick" name="nick" value="" size="60" />
		  <button id="submit" type="submit">'.JText::_('ADALOGIN_OK').'</button>
		</p>
	</div>
	'.JHtml::_('form.token').'
	</form>
';
?>	
</div>
