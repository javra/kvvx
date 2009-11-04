<?php
setlocale(LC_TIME, 'de_De');
define(TIME_EXP, "([0-9]{1,2}(:|\.)[0-9]{2}(( )*(U|u)hr)?|jetzt|[0-9]{1,2}( )*(U|u)hr)");
define(DATE_EXP, "([0-9]{1,2}(\.)[0-9]{1,2}(\.)([0-9]{2,4}|))");
//define(PLACES_EXP, "karlsruhe|eggenstein|rheinstetten|ettlingen|leopoldshafen");
//define(NAMES_EXP, "straÃ&#65533;e|strasse|tor|platz|weg|gasse|allee|ring|sÃ&#188;d|west|zentrum|mitte|stelle|schul|haus|halle|park|gebiet");
//$names = explode("|",NAMES_EXP);
//$places = array("karlsruhe", "eggenstein", "rheinstetten", "ettlingen", "leopoldshafen", "linkenheim", "hochstetten", "eggenstein-leopoldshafen", "linkenheim-hochstetten", "graben", "neudorf", "graben-neudorf","grÃ&#188;nwettersbach");
	
function redirect($args) {
	$url = "http://213.144.24.66/kvv/XSLT_TRIP_REQUEST2";
	$params = array(
		'sessionID'			=> '0',
		'requestID'			=> '0',
		'Server'			=> '213.144.24.66',
		'language'			=> 'de',
		'execInst'			=> 'normal',
		'itdLPxx_Frameset'		=> '1',
		'place_origin'			=> $args['start_place'],
		'placeState_origin'		=> 'empty',
		// stop=Haltestelle, address=StraÃ&#65533;e/Hausnummer
		'type_origin'			=> $args['start_type'],
		'name_origin'			=> $args['start_name'],
		'nameState_origin'		=> 'empty',
		'place_destination'		=> $args['dest_place'],
		'placeState_destination'	=> 'empty',
		'type_destination'		=> $args['dest_type'],
		'name_destination'		=> $args['dest_name'],
		'nameState_destination'		=> 'empty',
		'itdTimeHour'			=> $args['hour'],
		'itdTimeMinute'			=> $args['minute'],
		'itdDateDay'			=> $args['day'],
		'itdDateMonth'			=> $args['month'],
		'itdDateYear'			=> $args['year'],
		// arr=Ankunft, dep=Abfahrt
		'itdTripDateTimeDepArr'		=> $args['time_type']
	);
	$str = $url . "?";
	$vars = array_keys($params);
	for ($i = 0; $i < count($vars); $i++) {
		$str = $str . $vars[$i] . "=" . urlencode($params[$vars[$i]]) . "&";
	}
	header('Location: ' . $str);
	die();
}

// returns an array: array(place, name)
function explode_place($arg) {
	global $all_names;
	$arg = trim($arg, " ,");
	$parts = explode(" ", $arg);
	$parts = array_map(trim, $parts);
	$allParts = array();
	for($i = 0; $i < count($parts); $i++){
		if(!empty($parts[$i])){
			$allParts[] = $parts[$i];
		}
	}
	$parts = $allParts;
	unset($allParts);
	while(TRUE){
		$c1 = count($parts);
		for($i = 0; $i < $c1; $i++) {
			if($c1 >= $i + 1){
				if(is_name($parts[$i]." ".$parts[$i+1])){
					// dieses Array-Element mit dem vorherigen zusammenfÃ&#188;hren
					$parts[$i] .= " " . $parts[$i+1];
					array_splice($parts, $i+1, 1);
					break;
				}
				if(is_name($parts[$i]."-".$parts[$i+1])){
					// dieses Array-Element mit dem vorherigen zusammenfÃ&#188;hren
					$parts[$i] .= "-" . $parts[$i+1];
					array_splice($parts, $i+1, 1);
					break;
				}
			}
			if($c1 >= $i + 2){
				if(is_name($parts[$i]." ".$parts[$i+1]." ".$parts[$i+2])){
					// dieses Array-Element mit dem vorherigen zusammenfÃ&#188;hren
					$parts[$i] .= " " . $parts[$i+1] ." ".$parts[$i+2];
					array_splice($parts, $i+1, 2);
					break;
				}
				if(is_name($parts[$i]."-".$parts[$i+1]."-".$parts[$i+2])){
					// dieses Array-Element mit dem vorherigen zusammenfÃ&#188;hren
					$parts[$i] .= "-" . $parts[$i+1]."-".$parts[$i+2];
					array_splice($parts, $i+1, 2);
					break;
				}
			}
		}
		if(count($parts) == $c1){
			break;
		}
	}
	if(count($parts) == 2){
		if(is_place($parts[0])){
			return array($parts[0], $parts[1]);
		}
		elseif(is_place($parts[1])){
			return array($parts[1], $parts[0]);
		}
		elseif(is_name($parts[0])){
			return array($parts[1], $parts[0]);
		}
		elseif(is_name($parts[1])){
			return array($parts[0], $parts[1]);
		}
		else{
			return array($parts[0], $parts[1]);
		}
	}
	elseif(count($parts) == 1){
		if(is_place($parts[1])){
			return array($parts[0], "");
		}
		elseif(is_name($parts[0])){
			return array("", $parts[0]);
		}
		else{
			return array("", $parts[0]);
		}
	}
	elseif(count($parts) > 2){
		return array($parts[0], $parts[1]);
	}
}

function is_place($str) {
	global $all_places;
	if(in_array(strtolower($str), $all_places)){
		return true;
	}
	else{
		return false;
	}
}

function is_name($str) {
	global $all_names;
	if(in_array(strtolower($str), $all_names) || eregi(NAMES_EXP, $str)){
		return true;
	}
	else {
		return false;
	}
}

if(isset($_GET["q"])){
	$now = time();
	require_once "names.php";
	$q = $_GET["q"];
	$qBackup = $q;
	$q = str_replace(","," ",$q);
	$q = str_replace("  "," ",$q);
	$q = str_replace("strasse", "straÃ&#65533;e", $q);
	$q = str_replace("Strasse", "StraÃ&#65533;e", $q);
	$q = str_replace("str.", "straÃ&#65533;e", $q);
	$q = str_replace("Str.", "StraÃ&#65533;e", $q);
	$q = str_replace("str ",  "straÃ&#65533;e ", $q);
	$q = str_replace("Str ",  "StraÃ&#65533;e ", $q);
	$q = str_replace("uhr",  "Uhr"   , $q);
	$q = str_replace("#", "", $q);
	$sdarr = spliti('( zu | nach | bis |[\-]*>)',$q,2);
	if(preg_match(DATE_EXP,$sdarr[0])){
		$sdate = preg_replace("/(.*?)".DATE_EXP."(.*?)$/si",'\\2',$sdarr[0]);
	}else{
		$sdate = "";
	}
	$sdarr[0] = str_replace($sdate, "", $sdarr[0]);
	
	if(preg_match(TIME_EXP,$sdarr[0])){
		$stime = preg_replace("/(.*?)".TIME_EXP."(.*?)$/si",'\\2',$sdarr[0]);
	}else{
		$stime = "";
	}
	$sdarr[0] = str_replace($stime, "", $sdarr[0]);
		
	if(preg_match(DATE_EXP,$sdarr[1])){
		$ddate = preg_replace("/(.*?)".DATE_EXP."(.*?)$/si",'\\2',$sdarr[1]);
	}else{
		$ddate = "";
	}
	$sdarr[1] = str_replace($ddate, "", $sdarr[1]);
	
	if(preg_match(TIME_EXP,$sdarr[1])){
		$dtime = preg_replace("/(.*?)".TIME_EXP."(.*?)$/si",'\\2',$sdarr[1]);
	}else{
		$dtime = "";
	}
	$sdarr[1] = str_replace($dtime, "", $sdarr[1]);

	if(empty($stime) && empty($dtime)){
		if(empty($sdate) && empty($ddate)){
			$type = "dep";
		}
		elseif(empty($sdate) && !empty($ddate)){
			$type = "arr";
		}
		else{
			$type = "dep";
		}
	}
	elseif(empty($stime) && !empty($dtime)){
		$type = "arr";
	}
	else{
		$type = "dep";
	}

	if($type == "arr"){
		$rTime = $dtime;
		$rDate = $ddate;
	}else{
		$rTime = $stime;
		$rDate = $sdate;
	}
	
	//parse time
	
	if(empty($rTime) || $rTime == "jetzt"){
		$hour = date('H',$now);
		$minute = date('i',$now);
	}
	else{
		$rTime = str_replace("Uhr","",$rTime);
		$rTime = str_replace(" ","",$rTime);
		$rTime = explode(":",$rTime);
		if(count($rTime)<2){
			$minute = 0;
		}
		else{
			$minute = $rTime[1];
		}
		$hour = $rTime[0];
	}
	
	//parse date
	
	if(empty($rDate) || $rDate == "jetzt"){
		$day = date('d',$now);
		$month = date('m',$now);
		$year = date('y',$now);
	}
	else{
		$rDate = trim($rDate, " .");
		$rDate = explode(".",$rDate);
		$day = $rDate[0];
		$month = $rDate[1];
		if(count($rDate)>2){
			$year = $rDate[2];
		}
		else{
			$year = date('y',$now);
		}
	}	
	
	//parse place and name
	$start_place = "";
	$start_name = "";
	$dest_place = "";
	$dest_name = "";
	
	$start_result = explode_place($sdarr[0]);
	$start_place = $start_result[0];
	$start_name = $start_result[1];
	
	$dest_result = explode_place($sdarr[1]);
	$dest_place = $dest_result[0];
	$dest_name = $dest_result[1];
	
	if(empty($start_place) && empty($dest_place)){
		$start_place = "Karlsruhe";
	}
	elseif(empty($start_place) && !empty($dest_place)){
		$start_place = $dest_place;
	}
	if(empty($dest_place)){
		$dest_place = $start_place;
	}
	
	$rArray = array('start_place' => $start_place,
		'start_type' => 'stop',
		'start_name' => $start_name,
		'dest_place' => $dest_place,
		'dest_type' => 'stop',
		'dest_name' => $dest_name,
		'hour' => $hour,
		'minute' => $minute,
		'day' => $day,
		'month' => $month,
		'year' => $year,
		'time_type' => $type);
		
	if($_GET["logging"]=="true") {
		file_put_contents("kvvx.log",
			"[".date('r',$now)."] \"".$qBackup."\" => ".implode(";",$rArray)."\n",FILE_APPEND);
	}
	
	if($_GET["redirect"]=="true") {
		redirect($rArray);
	}
	header('Content-Type: text/html; charset=utf-8');
	$keys = array_keys($rArray);
	for ($i = 0; $i < count($keys); $i++) {
		echo $keys[$i] . " = '" . $rArray[$keys[$i]] . "'<br/>";
	}
	$q = htmlspecialchars($q);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>KVV Express</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="search" type="application/opensearchdescription+xml" 
			title="KVV Express" href="firefox-search.xml">
		<script type="text/javascript" src="bookmark.js"></script>
	</head>
	<body onload="document.getElementById('q').focus()">
		<h1>KVV Express</h1>
		<div id="formdiv">
			<form action="index.php" method="get" accept-charset="UTF-8">
				<input class="inpline" type="text" name="q" id="q" <?php if(isset($q)) echo "value=\"$q\""; ?>/>
				<input class="button" id="formbutton" type="submit" value="&rArr; Abschicken" /><br/>
				<label for="redirect">
					<input type="checkbox" value="true" name="redirect" id="redirect" checked="checked"/>
					Weiterleiten
				</label>
				<label for="logging">
					<input type="checkbox" value="true" name="logging" id="logging" checked="checked"/>
					Loggen der Daten erlauben
				</label>
			</form>
		</div>
		<div id="bmdiv">
			<input class="button" type="button" value="Gew&ouml;hnliches Lesezeichen hinzuf&uuml;gen"
				onclick="bookmarksite();" />
			<input class="button" type="button" value="Lesezeichen f&uuml;r Schnellsuche hinzuf&uuml;gen (nur f&uuml;r Mozilla Firefox)"
				onclick="quickbm();" />
		</div>
	</body>
</html>

