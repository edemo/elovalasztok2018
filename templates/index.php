<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.file');

// Check modules
$showRightColumn = ($this->countModules('position-3') or $this->countModules('position-6') or $this->countModules('position-8'));
$showbottom      = ($this->countModules('position-9') or $this->countModules('position-10') or $this->countModules('position-11'));
$showleft        = ($this->countModules('position-4') or $this->countModules('position-7') or $this->countModules('position-5'));

if ($showRightColumn == 0 and $showleft == 0)
{
	$showno = 0;
}

JHtml::_('behavior.framework', true);

// Get params
$color          = $this->params->get('templatecolor');
$logo           = $this->params->get('logo');
$navposition    = $this->params->get('navposition');
$headerImage    = $this->params->get('headerImage');
$config         = JFactory::getConfig();
$bootstrap      = explode(',', $this->params->get('bootstrap'));
$option         = JFactory::getApplication()->input->getCmd('option', '');

function base64url_encode($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 


// Output as HTML5
$this->setHtml5(true);
// JHtml::_('bootstrap.framework');

if ($color == 'image')
{
	$this->addStyleDeclaration("
	.logoheader {
		background: url('" . $this->baseurl . "/" . htmlspecialchars($headerImage) . "') no-repeat right;
	}
	body {
		background: " . $this->params->get('backgroundcolor') . ";
	}");
}

// Check for a custom CSS file
$userCss = JPATH_SITE . '/templates/' . $this->template . '/css/user.css';

$this->addScript($this->baseurl . '/templates/' . $this->template . '/javascript/hide.js');
$this->addScript($this->baseurl . '/templates/' . $this->template . '/javascript/respond.src.js');

require __DIR__ . '/jsstrings.php';
$input = JFactory::getApplication()->input;

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes"/>
		<meta name="HandheldFriendly" content="true" />
		<meta name="apple-mobile-web-app-capable" content="YES" />
		<jdoc:include type="head" />
		<!--[if IE 7]><link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/ie7only.css" rel="stylesheet" /><![endif]-->
		<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js" type="text/javascript"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>		
		<script type="text/javascript">
		  /*
		  jQuery(function() {
			  if (window.innerWidth >= 1200) {
				  jQuery('#right').show();
				  jQuery('#wrapper').width('72%');
			  } else {
				  jQuery('#right').hide();
				  jQuery('#wrapper').width('89%');
			  }
		  });
		  */
		</script>
		
		<!-- Barják Lászól javaslata  start 
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Bevan">
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto Slab">
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		Barják Lászól javaslata  stop -->

		<link href="<?php echo JURI::root(); ?>templates/elovalasztok-2/css/template.css" rel="stylesheet" />
		
	</head>
	<body id="shadow">
		<?php if (($input->get('option') == 'com_adalogin') & ($input->get('task') != 'dologout')) : ?>
			<jdoc:include type="component" />
		<?php else : ?>	
		<center>
		<div id="all">
			<div id="back">
				<header id="header">
					<a href="index.php">
					<div class="logoheader">
						<h1 id="logo">
						  <img src="images/banners/elovalasztok_head_720x210.png" />
						</h1>
					</div><!-- end logoheader -->
					</a>	
					
					<jdoc:include type="modules" name="position-1" />
					<div id="line">
						<div id="fontsize"></div>
						<h3 class="unseen"><?php echo JText::_('TPL_BEEZ3_SEARCH'); ?></h3>
						<jdoc:include type="modules" name="position-0" />
					</div> <!-- end line -->
				</header><!-- end header -->
				
				<div id="<?php echo $showRightColumn ? 'contentarea2' : 'contentarea'; ?>">
					<div id="breadcrumbs">
						<jdoc:include type="modules" name="position-2" />
						<div id="menuIcon"  onclick="jQuery('#right').toggle();">
						   <i class="icon-menu"> </i>
						</div>
					</div>

					<?php if ($navposition == 'left' and $showleft) : ?>
						<nav class="left1 <?php if ($showRightColumn == null) { echo 'leftbigger';} ?>" id="nav">
							<jdoc:include type="modules" name="position-7" style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-4" style="beezHide" headerLevel="3" state="0 " />
							<jdoc:include type="modules" name="position-5" style="beezTabs" headerLevel="2"  id="3" />
						</nav><!-- end navi -->
					<?php endif; ?>

					<div id="<?php echo $showRightColumn ? 'wrapper' : 'wrapper2'; ?>" <?php if (isset($showno)){echo 'class="shownocolumns"';}?>>
						<div id="main">
<?php if ($this->countModules('position-12')) : ?>
								<div id="top">
									<jdoc:include type="modules" name="position-12" />
								</div>
							<?php endif; ?>

							<jdoc:include type="message" />
							<div id="content">
								<jdoc:include type="component" />
							</div>
							<div class="alsoGombok">
							<jdoc:include type="modules" name="position-4" />
							</div>
						</div><!-- end main -->
					</div><!-- end wrapper -->

					<?php if ($showRightColumn) : ?>
						<aside id="right">
							<h2 class="unseen"><?php echo JText::_('TPL_BEEZ3_ADDITIONAL_INFORMATION'); ?></h2>
							<jdoc:include type="modules" name="position-6" style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-8" style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-3" style="beezDivision" headerLevel="3" />
						</aside><!-- end right -->
					<?php endif; ?>
					
					<div id="rightMobil">
						<jdoc:include type="modules" name="position-8" style="beezDivision" headerLevel="3" />
					</div>

					<?php if ($navposition == 'center' and $showleft) : ?>
						<nav class="left <?php if ($showRightColumn == null) { echo 'leftbigger'; } ?>" id="nav" >

							<jdoc:include type="modules" name="position-5" style="beezTabs" headerLevel="2"  id="3" />

						</nav><!-- end navi -->
					<?php endif; ?>
					<div style="clear:both"></div>

					<div class="wrap"></div>
				</div> <!-- end contentarea -->
				<center>
				  <a href="<?php echo JURI::base(); ?>index.php">Vissza a kezdő lapra</a>
				</center>
			</div><!-- back -->
			<div id="lablec">
						<jdoc:include type="modules" name="position-7" />
			</div>
		</div><!-- all -->
		<div id="footer-outer">
			<?php if ($showbottom) : ?>
				<div id="footer-inner" >

					<div id="bottom">
						<div class="box box1"> <jdoc:include type="modules" name="position-9" style="beezDivision" headerlevel="3" /></div>
						<div class="box box2"> <jdoc:include type="modules" name="position-10" style="beezDivision" headerlevel="3" /></div>
						<div class="box box3"> <jdoc:include type="modules" name="position-11" style="beezDivision" headerlevel="3" /></div>
					</div>

				</div>
			<?php endif; ?>

			<div id="footer-sub">
				<footer id="footer">
					<jdoc:include type="modules" name="position-14" />
				</footer><!-- end footer -->
			</div>
		</div>
		</center>
		<?php endif; // com_adalogin ? ?>
		<jdoc:include type="modules" name="debug" />
	</body>
</html>
