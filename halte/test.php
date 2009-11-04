<?php
header('Content-Type: text/html; charset=utf-8');
    // read the XML database of aminoacids
    $data = file_get_contents("haltestellen.xml");
    //var_dump($data);die();
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $data, $values, $tags);
    xml_parser_free($parser);
    
    $places = array();
    $names = array();
    
    $output_str = "";
   	$not = array("a77","a73","a71", "a35", "BUS", "Haltestellenverzeichnis", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "zero");
	$not2 = array("Tarifzonen","Haltestellen", "Linien","R", "S", "/","N","Tram");
   	$parse = array(
		"space" => " ",
		"comma" => ",",
		"hyphen" => "-",
		"period" => ".",
		"Odieresis" => "Ö",
		"odieresis" => "ö",
		"udieresis" => "ü",
		"Udieresis" => "Ü",
		"adieresis" => "ä",
		"Adieresis" => "Ä",
		"parenleft" => "(",
		"parenright" => ")",
		"equal" => "=",
		"germandbls" => "ß",
		"slash" => "/",
   		"eacute" => "é");
	
$level_in_line = 0;
$level_in_column = 0;
    
for ($i=0; $i < count($values); ) {
	
	//echo "check item ".$i."<br/>";
	
	$item = $values[$i];
	
	if($item["tag"] == "column"){
		$level_in_column = 0;
	}
	
	if($item["tag"] == "line"){
		$level_in_line = 0;
		$level_in_column++;
	}
	
	if($item["tag"] == "frag"){
		$level_in_line++;
		
		if($item["type"] == "complete"){
			// output
			check($item["value"]); out();
			$i++;
		}
		elseif($item["type"] == "open"){
			
			check($item["value"]);
			
			// output all values until next frag/close arrives, include possible 'specialchar's
			for ($j=$i+1; ; $j++){
	            			
	            $elem = $values[$j];
	            if(in_array($elem["tag"], array("frag", "specialchar"))){
	            	if(!($elem["tag"] == "frag" && $elem["type"] == "close")){
	            		if(isset($elem["value"])){
	            			 check($elem["value"]);
	            		}
	            	}
	            	else{
	            		out();
	            		break;
	            	}
	            }
	            			
			}	// end for
	            		
			$i = $j+1;
		}	// end if
	            	
	}
	else{
		$i++;
	}	// end if
            	
}	// end for

$places = array_unique($places);
$names = array_unique($names);

sort($places, SORT_STRING);
sort($names, SORT_STRING);

$fileStr = "<?php\n";
$fileStr .= '$all_places = array(';
for($i=0; $i < count($places); $i++){
	$fileStr .= "\"".$places[$i]."\"";
	if($i != count($places) - 1){
		$fileStr .= ", ";
	}
	if(bcmod($i+1, 10) == 0){
		$fileStr .= "\n";
	}
}
$fileStr .= ");\n";
$fileStr .= '$all_names = array(';
for($i=0; $i < count($names); $i++){
	$fileStr .= "\"".$names[$i]."\"";
	if($i != count($names) - 1){
		$fileStr .= ", ";
	}
	if(bcmod($i+1, 10) == 0){
		$fileStr .= "\n";
	}
}
$fileStr .= ");\n";
file_put_contents("codegen.php",$fileStr);

function check($str) {
	global $output_str, $not, $parse, $level_in_column;
	if(in_array($str, $not) || $level_in_column == 1 || $level_in_column == 2){
		return;
	}
	elseif(in_array($str, array_keys($parse))){
		$output_str .= $parse[$str];
	}
	else{
		$output_str .= $str;
	}
}
function out() {
	global $output_str, $not, $not2, $places, $names;
	$output_str = trim($output_str, " ,/");
	$output_strs = array();
	if(!empty($output_str) && !in_array($output_str, $not2) && strpos($output_str, "= ") === FALSE && strpos($output_str, "noch ") === FALSE) {
		
		if(!is_place($output_str)){
			$is_place = FALSE;
		}
		else{
			$is_place = TRUE;
		}
		
		// am Anfang vieler Ortsnamen
		$output_str = trim($output_str, " -");
		
		// nur den ersten Ortsnamen übernehmen
		$pos = strpos($output_str, "siehe");
		if($pos !== FALSE){
			$length = strlen($output_str);
			$output_str = substr($output_str, 0, $pos - 1);
		}
		
		$output_str = str_replace("Str.","Straße",$output_str);
		$output_str = str_replace("str.","straße",$output_str);
		
		$output_str = strtolower($output_str);
		
		// den Namen so wie er ist speichern
		$output_strs[] = $output_str;
		
		// den Namen auch ohne geklammerte Dinge speichern
		$output_strs[] = eregi_replace(" \(.*\)", "", $output_str);
		
		// den Namen auch ohne Punkte speichern
		$output_strs[] = str_replace(".", "", $output_str);
		
		for($i = 0; $i < count($output_strs); $i++){
			if($is_place){
				$places[] = $output_strs[$i];
			}
			else{
				$names[] = $output_strs[$i];
			}
		}
		
	}
	$output_str = "";
}
function is_place($str) {
	global $level_in_line;
	if(strpos($str, "- ") === 0 || strpos($str, "siehe") !== FALSE || $level_in_line == 1) {
		return TRUE;
	}
	return FALSE;
}