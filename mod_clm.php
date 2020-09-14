<?php
/**
 * @ Chess League Manager (CLM) Modul
 * @Copyright (C) 2008-2020 CLM Team.  All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.chessleaguemanager.de
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined("DS")){define('DS', DIRECTORY_SEPARATOR);} // fix for Joomla 3.2
// angemeldet
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');

$par_dwzliga = $params->def('dwzliga', 1);
$par_statistik = $params->def('statistik', 1);
$par_ligainfo = $params->def('ligainfo', 0);
$par_booklet = $params->def('booklet', 1);
$par_termine = $params->def('termine', 1);
$par_vereine = $params->def('vereine', 1);
$par_ids = $params->def('ids', '');
$par_links = $params->def('links', 1);
$par_itemid = $params->def('itemid', '');

$link	= modCLMHelper::getLink($params);
$count	= modCLMHelper::getCount($params);
$runden	= modCLMHelper::getRunde($params);

require(JModuleHelper::getLayoutPath('mod_clm'));


