<?php

define( 'ROOT_PATH', __DIR__ );

require_once( ROOT_PATH . '/core/Tickets.php');
$filename = 'logs/'.date('Y-m-d').'.txt';

$fromStation = trim($_GET['f_s']);
$toStation = trim($_GET['t_s']);
$date = trim($_GET['date']);

$tickets = new Tickets($fromStation, $toStation, $date);
$content = $fromStation.'-'.$toStation. '  '.$date;
file_put_contents($filename, $content, FILE_APPEND);

$json = $tickets->run();

echo json_encode($json);