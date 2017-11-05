<div class="adalogout">
<?php
echo
  '<h1>'.JText::_('ADALOGIN_LOGOUT_TITLE').'</h1>'.
  '<p>'.JText::_('ADALOGIN_LOGOUT_MESSAGE').'</p>'.
  '<div id="logoutDone" class="boxSuccess" style="display: none">'.JText::_('ADALOGIN_LOGOUT_DONE').'</div>'.
  '<div id="errorNotLoggedIn" class="boxError" style="display: none">'.JText::_('ADALOGIN_LOGOUT_ALREADY').'</div>'.
  '<div id="errorParse" class="boxError" style="display: none">'.JText::_('ADALOGIN_LOGOUT_PARSE').'</div>'.
  '<div id="errorHost" class="boxError" style="display: none">'.JText::_('ADALOGIN_LOGOUT_HOST').'</div>'.
  '<button id="adalogout" class="btn btnAdalogout" onclick="doAdaLogout()">'.JText::_('ADALOGIN_LOGOUT_YES').'</button>'.
  '<button id="homepage" class="btn btnHomepage" onclick="document.location.href=\''.JURI::base().'index.php\'">'.JText::_('ADALOGIN_LOGOUT_NO').'</button>';
?>
</div>
