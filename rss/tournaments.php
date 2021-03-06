<?php
include '../lib.php' ;
include '../includes/db.php' ;
$status = param($_GET, 'status', '') ;
echo '<?xml version="1.0" encoding="iso-8859-1" ?>' ;
?>
<rss version="2.0">
	<channel>
		<title>MOGG.fr Tournaments</title>
		<link>http://mogg.fr</link>
		<description>Tournaments on mogg.fr</description>
<?php

// List pending tournaments
if ( $status != '' )
	$q_status = "WHERE `status` = '$status'" ;
else
	$q_status = '' ;
$query = query("SELECT id, name, type, creation_date FROM `tournament` $q_status ORDER BY `id` DESC LIMIT 0, 10") ;
while ( $row = mysql_fetch_object($query) ) {
	// List this tournament's registered players
	$players = query_as_array("SELECT nick FROM `registration` WHERE `tournament_id`='".$row->id."' ; ") ;
	if ( count($players) > 0 ) {
		$players_names = '' ;
		foreach ( $players as $player )
			$players_names .= $player->nick.' ' ;
	} else
		$players_names = 'nobody' ;
	// Display item
?>
		<item>
			<guid><?php echo $row->id ; ?></guid>
			<pubDate><?php echo date("D, d M Y H:i:s", strtotime($row->creation_date)); ?></pubDate>
			<title><?php echo $row->type . ' ' . $row->name . ' #' . $row->id ; ?></title>
			<link><?php echo $url . '/tournament/?id='.$row->id  ; ?></link>
			<description><?php echo $row->type . ' ' . $row->name . ' with ' . $players_names ; ?></description>
		</item>
<?php
}
?>
	</channel>
</rss>
