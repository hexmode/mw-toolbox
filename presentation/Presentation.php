<?php

if (isset($_POST['presentation_info']) ){

	$temp = split("`",  $_POST['presentation_info']);
	
	$title = $temp[0];
	$slideNumber = $temp[1];

	if( isset($_POST['selectPage']) ) 	$slideNumber = $_POST['selectPage'];
	if( isset($_POST['slideBack']) ) 	$slideNumber = $temp[1] -1;
	if( isset($_POST['slideForward']) ) $slideNumber = $temp[1] +1;
		
	$cookie_name = str_replace(" ", "_" , "wiki_presentation_$title");
	setcookie($cookie_name, trim($slideNumber));
	
	header("Location: " . $_SERVER['REQUEST_URI']);
}

$gPresentationVersion = "v0.4";

# Credits	
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'Presentation',
    'author'=>'Eric Fortin',
    'url'=>'http://www.mediawiki.org/wiki/Extension:Presentation',
    'description'=>'MediaWiki Page/Slide Presentation ',
    'version'=>$gPresentationVersion
);

$wgExtensionFunctions[] = "wfPresentation";

// function adds the wiki extension
function wfPresentation() {
    global $wgParser;
    $wgParser->setHook( "presentation", "launchPresentation" );
}

function launchPresentation( $paramstring, $params = array() ){
	global $wgTitle, $wgParser;

	$wgParser->disableCache();
	$title = $wgTitle->getText(); //only returns the title, not the namespace
	
	$slideNumber = 0;
	$notoc = "";
	$delimiter = ",";
	
	if(trim($paramstring) == "") 
		return "<br><font color=red><b>Please define at least one page to use this extention.</b></font>\n<hr>";
	
	// set default then check for user supplied name...
	$name = "Wiki Presentation";
	if(isset($params['name'])) $name = $params['name'];
	
	//disable toc per tag params
	if(isset($params['notoc'])) $notoc = "__NOTOC__";
	
	if(isset($params['delimiter'])){
		$delimiter = $params['delimiter'];
		//$delimiter = str_replace("\n",chr(13),$delimiter);
	}
	
	// clean up the string; replace whitespace with a single space
	$paramstring = preg_replace ('@\s+@', " ",$paramstring); 
	$slides = split($delimiter, trim($paramstring));
	
	// remove any blank array values
	foreach($slides as $slide){
		if(trim($slide) != "") 
			$arr[] = trim($slide);
	}

	// cookie is set via back or forward arrows
	$cookie_name = str_replace(" ", "_" , "wiki_presentation_$title");
	if(isset($_COOKIE[$cookie_name])){
		$slideNumber = $_COOKIE[$cookie_name];
	}

	// generate the Presentation class
	$cPresentation = new Presentation($arr, $slideNumber, $name, $notoc);

	// dislay the presentation
	return $cPresentation->getHTML();
}

class Presentation {

	var $slideNumber = 0;
	var $slideBody = "";
	var $slideCount = 0;
	var $disableBack = "";
	var $disableForward = "";
	var $currentPageLink = "";
	var $name;

	function Presentation($arrPages, $slide, $name, $notoc) {
		
		$this->name = $name;
		$cnt = $this->slideCount = count($arrPages);
	
		// make sure the cookie page number isn't higher then the array
		if($cnt > $slide)
			$this->slideNumber = $slide;
		
		$this->buildDropBox($arrPages, $this->slideNumber);
		
		// initalize
		if($this->slideNumber  == 0) 
			$this->disableBack = 'disabled';
			
		if($this->slideNumber  == ($cnt -1)) 
			$this->disableForward = 'disabled';

		$this->buildCurrentSlide($arrPages[$this->slideNumber], $notoc);
	}
	
	function buildCurrentSlide($page, $notoc) {
		global $wgScript, $wgParser;
		
		if(trim($page) == "") return "";
		
		$link = $wgScript . "?title=$page";
		$article = new Article(Title::newFromText($page));

		while( $article->isRedirect() ){
			 $redirectedArticleTitle = Title::newFromRedirect( $article->getContent() );
			 $article = new Article($redirectedArticleTitle);
		 }
		
		if( $article->exists() ) {
			$slideBody = $article->getContent() . $notoc . " __NOEDITSECTION__"; 
		}
		else {
			$slideBody =  "The [[$page]] slide does not exist. Did you want to <b>[[$page | create]]</b> it?";
		}
		
		$this->currentPageLink = "<a href='$link'>$page</a>"; //html
		
		$this->slideBody = $wgParser->recursiveTagParse($slideBody); //wiki markup converted to html
	}
	
	function getHTML(){
		global $wgTitle;
		
		$title = $wgTitle->getText(); //only pulls title, not namespace
		$slide = $this->slideNumber +1;
		$presentation_info = "$title`$this->slideNumber"; //send this data when we POST

		$headerStyle = "style='background-color:#EEEEEE;'";

		$html = "<html><head></head><body><form name=frmPresentation method='POST'>
		  <table width=100% border=1 cellpadding=0> 
			<tr><td>
			  <table width=100% cellpadding=2 cellspacing=0>
				<tr $headerStyle>
					<td width=20% rowspan=2 align=center nowrap>&nbsp;$this->dropbox</td>
					<td width=60% align=center style='font-size: 20px;'><b>$this->name</b><br><small><i>$this->currentPageLink</i></small></td>					<td width=20% rowspan=2 align=center nowrap><b>Slide $slide of $this->slideCount&nbsp;&nbsp;</b></td>
				</tr>
				<tr $headerStyle>
					<td align=center nowrap>
					  <input style='width:40px' name='slideBack' $this->disableBack type='submit' value='<<'>&nbsp;&nbsp;
					  <input style='width:40px' name='slideForward' $this->disableForward type='submit' value='>>'>
					  <input name='presentation_info' type='hidden' value='$presentation_info'>
					</td>	
				</tr>
			  </table>
		    </td></tr>
		    <tr><td>
			  <table width=100% cellpadding=10>
				<tr height=100 valign=top>
				  <td width=100%>[[slideBody]]<br></td>
				</tr>
			  </table>
		    </td></tr>
		  </table>
		  </form></body></html>";
		
		//clean HTML whitespace, but not wiki page whitespace
		$html = $this->stripLeadingSpace($html); 
		
		// copy in the slide variable
		$html = str_replace('[[slideBody]]', $this->slideBody, $html);
		
		return $html . $this->helpLink();
	}
	
	function buildDropBox($arrPages, $currentPage){
	    $tag = "<select name='selectPage' method='post' onChange='javascript:this.form.submit()'>";

		$selected = ""; $i = 0;
		foreach($arrPages as $slide) {
			//$arr = split(":", $slide);
			
			if($i == $currentPage) $selected = "selected='true'";
				
			$tag .= "<option $selected value='$i'>" . $slide . "</option>";
			$selected = ""; $i++;
		}
		
		$tag .= "</select>";
		
		$this->dropbox = $tag;
	}
	
	function helpLink(){
		global $gPresentationVersion;
		
		$ret = "<small><a href='http://www.mediawiki.org/wiki/Extension:Presentation' target=new>Presentation $gPresentationVersion</a></small>";
		return $ret;
	}
	
	// this tightens up the code by removing <p> tags that
	// were created from white space
    function stripLeadingSpace($html) {
		
    	$index = 0;
    	
    	$temp = split("\n", $html);
    	
    	$tempString = "";
    	while ($index < count($temp)) {
	    while (strlen($temp[$index]) > 0 
		   && (substr($temp[$index], 0, 1) == ' ' || substr($temp[$index], 0, 1) == '\t')) {
		$temp[$index] = substr($temp[$index], 1);
	    }
			$tempString .= $temp[$index];
			$index += 1;    		
		}
    	
    	return $tempString;	
    }	
}