<?php
include '../../lib.php' ;
include '../../includes/db.php' ;
include '../lib.php' ;
$id = intval(param_or_die($_GET, 'id')) ;
$value = param_or_die($_GET, 'msg') ;
die('{"nb":'.tournament_log($id, $player_id, 'msg', $value).'}') ;
?>
