<?php 
/**
 * @ Chess League Manager (CLM) Modul
 * @Copyright (C) 2008-2023 CLM Team.  All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.chessleaguemanager.de
 * @author Thomas Schwietert
 * @email fishpoke@fishpoke.de
*/
defined('_JEXEC') or die('Restricted access'); 

// Copy der clm-core-Funktion clm_function_request_string
if (!function_exists('clm_request_string')) {
	function clm_request_string($input, $standard = '') {
		if (isset($_GET[$input])) $value = $_GET[$input];
		elseif (isset($_POST[$input])) $value = $_POST[$input];
		else return $standard;
		if (is_string($value)) $result = $value; else $result = $standard;
		return $result;
	}
}
// Copy der clm-core-Funktion clm_function_request_int
if (!function_exists('clm_request_int')) {
	function clm_request_int($input, $standard = 0) {
		if (isset($_GET[$input])) $value = $_GET[$input];
		elseif (isset($_POST[$input])) $value = $_POST[$input];
		elseif (!class_exists('JFactory')) return $standard; // kein Joomla
		else {
			$app =JFactory::getApplication(); // nur nötig wegen Menüeintragstypen
			$xy = $app->input->getInt($input);
			if (!is_null($xy)) $value = $xy;
			else return $standard; 
		}
		//	$result = clm_core::$load->make_valid($value, 0, $standard);	
		if (!is_numeric($value) OR (intval($value) != floatval($value))) {
			$result = $standard;
		} else {
			$result = intval($value);
		}
		return $result;	
	}
}

$liga	= clm_request_string( 'liga');
$runde	= clm_request_string( 'runde');
$view	= clm_request_string( 'view' );
$dg		= clm_request_string( 'dg' );

// itemid
if($par_itemid == '' || !is_numeric($par_itemid)) {
	$itemid	= clm_request_int( 'Itemid', 1 );
} else {
	$itemid = $par_itemid;
}

$sid	= clm_request_string('saison','1');
$typeid	= clm_request_string( 'typeid' );
if (!isset($typeid)) $typeid = 21; 
 
foreach ($link as $link1) {
  if ($link1->id == $liga) {
	$runde_t = $link1->runden + 1;  
// Test alte/neue Standardrundenname bei 2 Durchgängen, nur bei Ligen/Turniere vor 2013 (Archiv!)
	if ( isset($runden[0]) AND $runden[0]->datum < '2013-01-01') {
	if ($link1->durchgang == 2) {
		if ($runden[$runde_t-1]->name == JText::_('ROUND').' '.$runde_t) {  //alt
			for ($xr=0; $xr< ($link1->runden); $xr++) { 
					$runden[$xr]->name = JText::_('ROUND').' '.($xr+1)." (".JText::_('PAAR_HIN').")";
					$runden[$xr+$link1->runden]->name = JText::_('ROUND').' '.($xr+1)." (".JText::_('PAAR_RUECK').")";
			}
		}
	}
} } }
	// Konfigurationsparameter auslesen
	$config	= JComponentHelper::getParams( 'com_clm' );
	$pdf_melde = $config->get('pdf_meldelisten',1);
	$countryversion = $config->get('countryversion',"de");
	
if (isset($link[0])) $saison = $link[0]->sid;
else {
	// current season
	$db = JFactory::getDbo();
	$db->setQuery("SELECT id FROM #__clm_saison WHERE published = 1 AND archiv = 0 ORDER BY name DESC LIMIT 1 ");
	$saison = $db->loadObject()->id;
}
//URL-Test: falls nicht belegt --> mod_clm oder mod_clm_archiv
//			falls belegt --> mod_clm_ext (parameter url version < 3.4; parameter source ab 3.4 
$url	= clm_request_string('url');
if (!isset($url) OR $url == '') $url	= clm_request_string('source');
?>
<ul class="menu">

	<?php if ( $par_vereine == 1 ) { ?>
    <li <?php if ($view == 'vereinsliste') { ?> id="current" class="active" <?php } ?>>
        <a href="index.php?option=com_clm&view=vereinsliste&saison=<?php echo $saison; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?>" <?php if ($view == 'vereinsliste') { ?> class="active_link" <?php } ?>>
        <span><?php echo JText::_('MOD_CLM_CLUBS_LABEL'); ?></span></a>
    </li>
    <?php } ?>
            
    <?php if ( $par_termine == 1 ) { ?>
    <li <?php if ($view == 'termine') { ?> id="current" class="active" <?php } ?>>
        <a href="index.php?option=com_clm&amp;view=termine&amp;categoryid=0&amp;saison=<?php echo $saison; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?>" <?php if ($view == 'termine') { ?> class="active_link" <?php } ?>>
        <span><?php echo JText::_('MOD_CLM_DATES_LABEL'); ?></span></a>
    </li>
    <?php } ?>

<?php 
// $link=$this->link;
foreach ($link as $link) {
	//Liga-Parameter aufbereiten
	$paramsStringArray = explode("\n", $link->params);
	$lparams = array();
	foreach ($paramsStringArray as $value) {
		$ipos = strpos ($value, '=');
		if ($ipos !==false) {
			$lparams[substr($value,0,$ipos)] = substr($value,$ipos+1);
		}
	}	
	if (!isset($lparams['firstView'])) $lparams['firstView']= 0;
// Haupttlinks des Menüs
?>
	<li <?php if ($liga == $link->id AND $typeid == 21) { ?> id="current" class="first_link" <?php } ?>>
	<?php $typeid = 21; 
		$view21 = 'rangliste';
		if ($lparams['firstView'] == 0) { $viewA = "rangliste"; }
		elseif ($lparams['firstView'] == 1) { $viewA = "tabelle"; }
		elseif ($lparams['firstView'] == 2) { $viewA = "paarungsliste"; }
		else { $viewA = "teilnehmer"; }
		if ($link->runden_modus == 1 OR $link->runden_modus == 2 OR $link->runden_modus == 3) $view21 = $viewA;
	    if ($link->runden_modus == 4 OR $link->runden_modus == 5) $view21 = 'paarungsliste'; ?>
	<a href="index.php?option=com_clm&amp;view=<?php echo $view21;?>&amp;saison=<?php echo $link->sid;?>&amp;liga=<?php echo $link->id;?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php echo "&typeid=".$typeid; ?>"
	<?php if ($liga == $link->id AND $view == $view21 ) {echo ' class="active_link"';} ?>>
	<span><?php echo $link->name; ?></span>
	</a>

        
<?php 
// Unterlinks falls Link angeklickt
if ($par_links AND $liga == $link->id AND $view == $view21 AND (!isset($url) OR $url == '')) { ?>
	<ul>
		<?php if ( $link->liga_mt == 0 ) { ?>
		<li class="first_link liga<?php echo $liga; ?>" <?php if ($view == 'aktuell_runde') { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=aktuell_runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>">
		<span><?php echo JText::_('MOD_CLM_CURRENT_LABEL'); ?></span></a>
		</li>
		<?php } ?>
		<?php $typeid = 22; 
		if ($link->runden_modus == 1 OR $link->runden_modus == 2 OR $link->runden_modus == 3) { ?>
		<li>
		<a href="index.php?option=com_clm&amp;view=paarungsliste&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>">
		<span><?php echo JText::_('MOD_CLM_PAIRINGLIST_LABEL'); ?></span></a>
		</li>
		<?php } ?>
	<?php for ($y=0; $y < $link->runden; $y++) { ?>
		<li>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=1<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>">
		<span><?php if ($runden[$y]->published =="0") { ?><s><?php } echo $runden[$y]->name; ?><?php if ($runden[$y]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php } $cnt = $y;
	if ($link->durchgang > 1) {
	for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=2<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y+$cnt]->published =="0") { ?><s><?php } echo $runden[$y+$cnt]->name; ?><?php if ($runden[$y+$cnt]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php }} 
	if ($link->durchgang > 2) {
	for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=3<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y+(2 * $cnt)]->published =="0") { ?><s><?php } echo $runden[$y+(2 * $cnt)]->name; ?><?php if ($runden[$y+(2 * $cnt)]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php }} 
	if ($link->durchgang > 3) {
	for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=4<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y+(3 * $cnt)]->published =="0") { ?><s><?php } echo $runden[$y+(3 * $cnt)]->name; ?><?php if ($runden[$y+(3 * $cnt)]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php }} ?>
    
        <?php if ( $par_dwzliga == 1 ) { ?>
		<li <?php if ($view == 'dwz_liga') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=dwz_liga&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'dwz_liga') { ?> class="active_link" <?php } ?>>
		<span><?php if ($countryversion == "de") echo JText::_('MOD_CLM_PARAM_DWZ_LABEL'); else echo JText::_('MOD_CLM_PARAM_GRADES_LABEL'); ?></span></a>
		</li>
		<?php } ?>

        <?php if ( $par_statistik == 1 ) { ?>
		<li <?php if ($view == 'statistik') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=statistik&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'statistik') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_PARAM_STATS_LABEL'); ?></span></a>
		</li>
		<?php } ?>

        <?php if ( $par_ligainfo == 1 ) { ?>
		<li <?php if ($view == 'liga_info') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=liga_info&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'liga_info') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_PARAM_LIGAINFO_LABEL'); ?></span></a>
		</li>
		<?php } ?>
		
		<?php 
		// Abfrage Konfigurationsparameter
		if ($pdf_melde == 1 AND $par_booklet == 1) {
		?>
		<li <?php if ($view == 'rangliste') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=rangliste&amp;format=pdf&amp;layout=heft&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'rangliste') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_BOOKLET_LABEL'); ?></span></a>
		</li>
		<?php } ?>
		
	</ul>
	<?php } ?>
	</li>
<!-- Unterlink angeklickt -->
<?php if ($par_links AND $liga == $link->id AND $view != $view21 AND (!isset($url) OR $url == '')){ ?>
	<li class="parent active">
	<ul>
		<?php if ( $link->liga_mt == 0 ) { ?>
		<li class="first_link liga<?php echo $liga; ?>" <?php if ($view == 'aktuell_runde') { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=aktuell_runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>">
		<span><?php echo JText::_('MOD_CLM_CURRENT_LABEL'); ?></span></a>
		</li>
		<?php } ?>
		
		<li <?php if ($view == 'paarungsliste') { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=paarungsliste&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'paarungsliste') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_PAIRINGLIST_LABEL'); ?></span></a>
		</li>
	<?php for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde' AND $dg == 1 AND ($runde == $y+1)) { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=1<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y]->published =="0") { ?><s><?php } echo $runden[$y]->name; ?><?php if ($runden[$y]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php } $cnt = $y;
	if ($link->durchgang > 1) {
	for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde' AND $dg == 2 AND ($runde == $y+1)) { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=2<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y+$cnt]->published =="0") { ?><s><?php } echo $runden[$y+$cnt]->name; ?><?php if ($runden[$y+$cnt]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php }} 
	if ($link->durchgang > 2) {
	for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde' AND $dg == 3 AND ($runde == $y+1)) { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=3<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y+(2 * $cnt)]->published =="0") { ?><s><?php } echo $runden[$y+(2 * $cnt)]->name; ?><?php if ($runden[$y+(2 * $cnt)]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php }} 
	if ($link->durchgang > 3) {
	for ($y=0; $y < $link->runden; $y++) { ?>
		<li <?php if ($view == 'runde' AND $dg == 4 AND ($runde == $y+1)) { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=runde&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?>&amp;runde=<?php echo $y+1; ?>&amp;dg=4<?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'runde' AND $runde == ($y+1)) { ?> class="active_link" <?php } ?>>
		<span><?php if ($runden[$y+(3 * $cnt)]->published =="0") { ?><s><?php } echo $runden[$y+(3 * $cnt)]->name; ?><?php if ($runden[$y+(3 * $cnt)]->published =="0") { ?></s><?php } ?></span></a>
		</li>
	<?php }} ?>
    
        <?php if ( $par_dwzliga == 1 ) { ?>
		<li <?php if ($view == 'dwz_liga') { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=dwz_liga&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'dwz_liga') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_PARAM_DWZ_LABEL'); ?></span></a>
		</li>
        <?php } ?>

        <?php if ( $par_statistik == 1 ) { ?>
		<li <?php if ($view == 'statistik') { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=statistik&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'statistik') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_PARAM_STATS_LABEL'); ?></span></a>
		</li>
        <?php } ?>
		
        <?php if ( $par_ligainfo == 1 ) { ?>
		<li <?php if ($view == 'liga_info') { ?> id="current" class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=liga_info&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'liga_info') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_PARAM_LIGAINFO_LABEL'); ?></span></a>
		</li>
        <?php } ?>
		
        
		<?php 
		// Abfrage Konfigurationsparameter
		if ($pdf_melde == 1 AND $par_booklet == 1) {
		?>
		<li <?php if ($view == 'rangliste') { ?> class="active" <?php } ?>>
		<a href="index.php?option=com_clm&amp;view=rangliste&amp;format=pdf&amp;layout=heft&amp;saison=<?php echo $link->sid; ?>&amp;liga=<?php echo $liga; ?><?php if ($itemid <>'') { echo "&Itemid=".$itemid; } ?><?php if ($typeid <>'') { echo "&typeid=".$typeid; } ?>" <?php if ($view == 'rangliste') { ?> class="active_link" <?php } ?>>
		<span><?php echo JText::_('MOD_CLM_BOOKLET_LABEL'); ?></span></a>
		</li>
		<?php } ?>
		
	</ul>
	</li>
<?php							}
			} ?>
            
</ul>
