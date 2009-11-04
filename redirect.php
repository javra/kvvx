<?php
setlocale(LC_TIME, 'de_de');
/**
 * redirects user to search page with given parameters
 *
 * @param array $args
 * elements:	-
 * 				-
 */
function redirect($args) {
	$url = "http://213.144.24.66/kvv/XSLT_TRIP_REQUEST2";
	$params = array(
		'sessionID'					=> '0',
		'requestID'					=> '0',
		'Server'					=> '213.144.24.66',
		'language'					=> 'de',
		'execInst'					=> 'normal',
		'itdLPxx_Frameset'			=> '1',
		'place_origin'				=> $args['start_place'],
		'placeState_origin'			=> 'empty',
		// stop=Haltestelle address=Straße/Hausnummer
		'type_origin'				=> $args['start_type'],
		'name_origin'				=> $args['start_name'],
		'nameState_origin'			=> 'empty',
		'place_destination'			=> $args['dest_place'],
		'placeState_destination'	=> 'empty',
		'type_destination'			=> $args['dest_type'],
		'name_destination'			=> $args['dest_name'],
		'nameState_destination'		=> 'empty',
		'itdTimeHour'				=> strftime("%H", $args['time']),
		'itdTimeMinute'				=> strftime("%M", $args['time']),
		'itdDateDay'				=> strftime("%d", $args['time']),
		'itdDateMonth'				=> strftime("%m", $args['time']),
		'itdDateYear'				=> strftime("%Y", $args['time']),
		// arr=Ankunft dep=Abfahrt
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

if (isset($_GET['q'])) {
	redirect(array('start_place' => 'Leopoldshafen', 'start_type' => 'stop', 'start_name' => 'Leopoldstraße',
					'dest_place' => 'Karlsruhe', 'dest_type' => 'stop', 'dest_name' => $_GET['q'],
					'time' => time(), 'time_type' => 'dep'));
}
else {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Kvv</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Language" content="de-de" />
	</head>
	<body>
		<div id="page">
			<div id="container">
				<form action="index.php" method="get">
					<input type="text" name="q" id="q" value="" />
					<input type="submit" name="submit" id="submit" value="Abfrage" />
				</form>
			</div>
		</div>
	</body>
</html><?php
}