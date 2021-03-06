<?php
include '../../lib.php' ;
include '../../includes/db.php' ;
//include '../../includes/deck.php' ;
//include '../../includes/card.php' ;
include '../lib.php' ;
$id = intval(param_or_die($_GET, 'id')) ;
$result = object() ;
$result->msg = '' ;
/*
// Get tournament data (for time left)
if ( $result->tournament = mysql_fetch_object(query("SELECT *, 
	TIMESTAMPDIFF(SECOND, NOW(), `due_time`) as timeleft
	FROM `tournament` WHERE `id` = '$id' ;")) ) {
} else 
	$result->msg = 'Tournament #'.$id.' not found' ;
// Get user data (for order)
if ( $result->player = mysql_fetch_object(query("SELECT * FROM `registration` WHERE `tournament_id` = '$id' AND `player_id` = '$player_id' ;")) ) {
} else
	$result->msg = 'Player '.$player_id.' not registered to tournament #'.$id ;
// Get booster
if ( $result->booster = mysql_fetch_object(
	query("SELECT * FROM `booster` WHERE `tournament` = '$id' AND `player` = '".$result->player->order."' ;")
) ) {
} else if ( $result->tournament->status == 3 )
	$result->msg = 'Unable to get booster '.$result->tournament->round.' for tournament #'.$id.' (player '.$result->player->order.')'+gettype($result->tournament->status) ;
// Pick
$content = json_decode($result->booster->content) ;
// Get card data
$card_connection = card_connect() ;
for ( $i = 0 ; $i < count($content->cards) ; $i++ )
	$content->cards[$i] = card2obj($card_connection, $content->cards[$i], $content->ext) ;
$result->booster->content = json_encode($content) ;
if ( count($content->cards) == 1 ) { // 1 card left in booster
	$pick = 1 ;
	$updatepick = true ; // and shunt 'update pick by user' process
} else {
	$pick = intval(param($_GET, 'pick', '0')) ;
	$updatepick = false ;
}
if ( ( $pick != 0 ) && ( $pick != intval($result->booster->pick) ) ) { // If a pick was sent by user and it's not recorded one
	if ( $result->tournament->timeleft > 0 ) // And tournament accepts picks
		$updatepick = true ;
	else
		$result->msg = 'Too late to pick' ;
} else { // No pick sent by user
	if ( ( $result->booster->pick == 0 ) && ( $result->tournament->timeleft <= 0 ) ) { // On a booster without a pick and tournament doesn't accept picks
		$pick = -rand(1, count($content->cards)) ;
		$updatepick = true ;
	}
}
if ( $updatepick ) {
	$query = "UPDATE `booster` SET `pick` = $pick WHERE `tournament` = '$id' AND `player` = '".$result->player->order."' ;" ;
	query($query) ;
	if ( mysql_affected_rows() > 1 ) 
		$result->msg = mysql_affected_rows().' boosters updated : '.$query ;
}
*/
// Readyness
if ( array_key_exists('ready', $_GET) ) {
	query("UPDATE `registration` SET `ready` = '".$_GET['ready']."' WHERE `tournament_id` = '$id' AND `player_id` = '$player_id' ;") ;
	if ( mysql_affected_rows() == 1 )
		tournament_log($id, $player_id, 'ready', $_GET['ready']) ;
	else
		$result->msg = mysql_affected_rows().' affected rows' ;
} else 
	$result->msg = 'No readyness in param' ;

die(json_encode($result)) ;
?>
