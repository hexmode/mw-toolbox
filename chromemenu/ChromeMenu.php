<?php


# Credits	
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'ChromeMenu',
    'author'=>'Eric Fortin',
    'url'=>'',
    'description'=>'Create a menu drop-dwon',
    'version'=>'1.0'
);

$wgExtensionFunctions[] = "wfChromeMenu";

// function adds the wiki extension
function wfChromeMenu() {
    global $wgParser;
    $wgParser->setHook( "menu", "renderChromeMenu" );
}

function renderChromeMenu( $paramstring, $params = array() ){
	global $wgParser, $wgScriptPath, $wgOut;
	$wgParser->disableCache();
	
	if(isset($params['name'])) $name = $params['name'];
	if(isset($params['align'])) $align = $params['align'];
	
	// clean update newlines and extra whitespace
	$paramstring = preg_replace ('@\s+@', " ",$paramstring); 
	
	$path = $wgScriptPath . '/extensions/ChromeMenu/';
	
	$wgOut->addStyle( $path . 'chrometheme/chromestyle.css', 'screen');
	$wgOut->addScriptFile( $path . 'chromejs/chrome.js');

	$menuHead = "\n<div class='chromestyle' id='$name'><ul style='text-align:$align'>";
	$menuFoot = "</ul></div>";
	
	buildMenu($paramstring, $menu, $menuItems);

	$html = $menuHead . $menu . $menuFoot . $menuItems;
	
	$execScript = "<script type='text/javascript'>cssdropdown.startchrome('$name')</script>";
	
	return $html . $execScript;
}

function buildMenu($paramstring, &$menu, &$menuItems){

	$arr = explode("|-|", $paramstring);	
	foreach($arr as $name){
		if( trim($name) == '' ) return '';
		
		$arr = split("=",$name);
		$menuName = trim(array_shift( $arr ));
		
		$menu .= "\n<li><a href='#' rel='".$menuName."'>".$menuName."</a></li>";
		
		$menuItems .= buildMenuItems( $menuName, implode("=",$arr) );
	}
}

function buildMenuItems($menuName, $body){
	global $wgParser;	
	$menuBody = $wgParser->recursiveTagParse( $body );
	
	// remove the mediawiki class for http links as it messed up the mouse over for menu items...
	$menuBody = str_replace('class="external text"', '', $menuBody);

	$menu = "\n<div class='dropmenudiv' id='".$menuName."'>" . $menuBody . "</div>";
	
	return $menu;
}







