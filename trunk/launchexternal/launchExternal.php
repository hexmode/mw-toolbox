<?php
/**
 * MediaWiki launchExternal extension
 *
 * @version 1.1
 * @author Eric Fortin
 */
 
$wgExtensionCredits['parserhook'][] = array(
  'name' => 'launchExternal',
  'author' => 'Eric Fortin',
  'version' => '1.1',
  'url' => 'http://www.mediawiki.org/wiki/Extension:LaunchExternal',
  'description' => 'Opens local media/image files or http links in a new window',
);

$wgExtensionFunctions[] = 'wfExternalHandler';
 
function wfExternalHandler () {
  global $wgParser;
  $wgParser->setHook( 'ext', 'launchExternal' );
}

function launchExternal ( $input, $params=array() ) {
	global $wgServer;
	global $wgAddServerName_EXT;
	
	$arrInput = split("::",$input);	// use a different display then the file reference...
	
	$reference = $input;
	$display = "";
	$server = "";
	
	if(count($arrInput) > 1){
		$reference = $arrInput[0];		
		$display = $arrInput[1];
	}
	
	$reference = str_replace('\\', '/', $reference);
	$reference = str_replace('////', '//', $reference); //fix just incase we have "file://\\server"

	if( strpos($reference,"://") !== false ){
		$section1 = explode("/", $reference);
		$section2 = explode(".", $section1[2]); //parse the dots (sub-domains)
		
		if( $wgAddServerName_EXT && strpos($reference,"file://") !== false) {
			$server = " <i>($section2[0])</i>";
		}
			
		if( $display == "" ) $display = $section1[count($section1) -1]; //default the file name

		return  "<a href='" . $reference . "' target='new'>" . ($display == "" ? $reference : $display) . "$server</a>"; 	
	}

	// wiki uploaded media or image reference
	$img = Image::newFromName( $reference );	
	if( $img ->exists() ) 
		return buildLink($wgServer . $img->getURL(), ($display == "" ? $reference : $display) );
		
	return $input . " <i>(bad reference)</i>";
}

function buildLink ($filepath, $input){
	return "<a href='" . $filepath . "' target='new'>" . $input . "</a>";
}
?>