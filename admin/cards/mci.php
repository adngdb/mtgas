<?php
include_once '../../includes/lib.php' ;
include_once '../../config.php' ;
include_once '../../lib.php' ;
include_once '../../includes/db.php' ;
include_once '../../includes/card.php' ;
include_once 'lib.php' ;

$ext = param_or_die($_GET, 'ext') ;
$apply = param($_GET, 'apply', false) ;

html_head(
	'Admin > Cards > MCI Parser',
	array(
		'style.css'
		, 'admin.css'
	)
) ;
?>
 <body>
<?php
html_menu() ;
?>
  <div class="section">
   <h1>Get extension info from MCI</h1>
   <a href="../">Return to admin</a>
<?php
if ( ! $apply )
	echo '  <p>Changes will NOT be applied <a href="?ext='.$ext.'&apply=1">apply</a></p>'."\n" ;

// Get page content, and parse
$ext = strtolower($ext) ;
$cache_file = 'cache/'.$ext ;
$url = 'http://magiccards.info/'.$ext.'/en.html' ;
if ( file_exists($cache_file) ) {
	$html = file_get_contents($cache_file) ;
} else {
	$html = file_get_contents($url) ;
	file_put_contents($cache_file, $html) ;
}
?><div>Getting data from <?php echo $url ; ?></div><?php
$nb = preg_match_all('#  <tr class="(even|odd)">
    <td align="right">(?<id>\d*[ab]?)</td>
    <td><a href="(?<url>/'.$ext.'/en/\d*a?b?\.html)">(?<name>.*)</a></td>
    <td>(?<type>.*)</td>
    <td>(?<cost>.*)</td>
    <td>(?<rarity>.*)</td>
    <td>(?<artist>.*)</td>
    <td><img src="http://magiccards.info/images/en.gif" alt="English" width="16" height="11" class="flag2">(?<ext>.*)</td>
  </tr>#', $html, $matches, PREG_SET_ORDER) ;
if ( $nb < 1)
	die('URL '.$url.' does not seem to be a valid MCI card list : '.count($matches)) ;

echo '<p>'.count($matches).' cards detected</p>'."\n\n" ;

// Comparison with extension in DB
$ext = strtoupper($ext) ;
$query = query("SELECT * FROM extension WHERE `se` = '$ext' OR `sea` = '$ext' ; ") ;
if ( $res = mysql_fetch_object($query) ) {
	$ext_id = $res->id ;
	if ( $apply) {
		query("DELETE FROM `card_ext` WHERE `ext` = '$ext_id'") ;
		echo '  <p>'.mysql_affected_rows().' cards unlinked from '.$ext."</p>\n\n" ;
	}
} else {
	$query = query("INSERT INTO extension (`se`, `name`) VALUES ('$ext', '".$matches[0]['ext']."')") ;
	echo '<p>Extension not existing, creating</p>' ;
	$ext_id = mysql_insert_id() ;
}
?>
  <table>
   <tr>
    <th>Name</th>
    <th>Rarity</th>
    <th>Card</th>
    <th>Extension</th>
   </tr>
<?php
foreach ( $matches as $match ) {
	$log = '' ;
	$name = str_replace('á', 'a', $match['name']) ;
	$name = str_replace('é', 'e', $name) ;
	$name = str_replace('í', 'i', $name) ;
	$name = str_replace('ú', 'u', $name) ;
	$name = str_replace('û', 'u', $name) ;
	$name = str_replace('Æ', 'AE', $name) ;
	$name = mysql_real_escape_string($name) ;
	$rarity = substr($match['rarity'], 0, 1) ;
	// Parse card itself
	$cache_file = 'cache/'.str_replace('/', '_', $match['url']) ;
	if ( file_exists($cache_file) ) {
		$html = file_get_contents($cache_file) ;
	} else {
		$html = file_get_contents(dirname($url).'/..'.$match['url']) ;
		file_put_contents($cache_file, $html) ;
	}
	$nb = preg_match('#<p>(?<typescost>.*)</p>
        <p class="ctext"><b>(?<text>.*)</b></p>.*http\://gatherer.wizards.com/Pages/Card/Details.aspx\?multiverseid=(?<multiverseid>\d*)#s', substr($html, 0, 10240), $card_matches) ;
	// Double cards : recompute name, mark as being second part (in which case card will be added, not replaced)
	$second = false ;
	if ( preg_match('/(.*) \((\1)\/(.*)\)/', $name, $name_matches) )
		$name = $name_matches[2] . ' / ' . $name_matches[3] ;
	if ( preg_match('/(.*) \((.*)\/(\1)\)/', $name, $name_matches) ) {
		$second = true ;
		$name = $name_matches[2] . ' / ' . $name_matches[3] ;
	}
	// Base checks
	if ( $nb < 1 ) {
		echo '<td colspan="4">Unparsable : <textarea>'.$html.'</textarea></td></tr>' ;
		continue ;
	}
	if ( ( ! $second ) && ( intval($card_matches['multiverseid']) < 1 ) ) { // On MCI, second part of a card has no multiverseID
		echo '<td>No multiverseID</td></tr>' ;
		continue ;
	}
	// Text
	$text = mysql_real_escape_string($card_matches['text']) ;
	$text = str_replace('<br><br>', "\n", $text) ; // Un-HTML-ise text
	$text = trim($text) ;
	// Types / cost
	$typescost = $card_matches['typescost'] ;
	if ( preg_match('#(?<types>.*)(, \n(?<cost>.*))#', $typescost, $typescost_matches) ) {
		$types = $typescost_matches['types'] ;
		$cost = trim($typescost_matches['cost']) ;
	} else { // No 'cost' in 'types + cost', it's a land
		if ( preg_match('#(?<types>.*)\n#', $typescost, $land_matches) )
			$types = $land_matches['types'] ;
		else
			$types = $typescost ; // 'typescost' only contains 'type'
		$cost = '' ; // 'cost' is empty
	}
	$types = str_replace('—', '-', $types) ; 
	$types = mysql_real_escape_string($types) ;
	// Cost
	if ( preg_match('/(?<cost>.*) \((?<cc>\d*)\)/', $cost, $cost_matches) )
		$cost = $cost_matches['cost'] ;
	// Types
		// Creature
	if ( preg_match('/(?<types>.*) (?<pow>[^\s]*)\/(?<tou>[^\s]*)/', $types, $types_matches) ) {
		$types = $types_matches['types'] ;
		$text = $types_matches['pow'].'/'.$types_matches['tou']."\n".$text ;
	}
		// Planeswalker
	if ( preg_match('/(?<types>.*) \(Loyalty: (?<loyalty>)\d\)/', $types, $types_matches) ) {
		$types = $types_matches['types'] ;
		$text = $types_matches['loyalty']."\n".$text ;
	}
	$qs = query("SELECT * FROM card WHERE `name` = '$name' ; ") ;
	echo "   <tr>\n" ;
	echo "    <td>$name</td>\n" ;
	echo "    <td>$rarity</td>\n" ;
	if ( $arr = mysql_fetch_array($qs) ) {
		if ( $second ) { // Second part of a dual card
			$add = "\n----\n$cost\n$types\n$text" ;
			echo "    <td>Second part : $add</td>" ;
			if ( $apply)
				$q = query("UPDATE card SET `text` = CONCAT(`text`, '$add') WHERE `id` = $card_id ;") ;
		} else {
			echo "    <td>Existing<br>" ;
			$card_id = $arr['id'] ;
			// Update
			$updates = array() ;
			if ( $arr['cost'] != $cost ) {
				$log .= '<li>Cost : ['.$arr['cost'].'] -> ['.$cost.']</li>' ;
				$updates[] = "`cost` = '$cost'" ;
			}
			if ( $arr['types'] != $types ) {
				$log .= '<li>Types : ['.$arr['types'].'] -> ['.$types.']</li>' ;
				$updates[] = "`types` = '$types'" ;
			}
			if ( trim($arr['text']) != $text ) {
				$log .= '<li><acro title="'.htmlspecialchars($arr['text']."\n->\n".$text).'">Text</acro></li>' ;
				$updates[] = "`text` = '".mysql_real_escape_string($text)."'" ;
			}
			if ( $log == '' )
				$log = 'up to date' ;
			else {
				$log = 'Updates : <ul>'.$log.'</ul>' ;
				if ( $apply) {
					$q = query("UPDATE card SET ".implode(', ', $updates)." WHERE `id` = $card_id ;") ; //`rarity` = '$rarity', `nbpics` = '$nbpics' WHERE `card` = $card_id AND `ext` = $ext_id ;"
				}
			}
			echo $log.'</td>' ;
			// Link with extension
			$query = query("SELECT * FROM card_ext WHERE `card` = '$card_id' AND `ext` = '$ext_id' ;") ;
			if ( $res = mysql_fetch_object($query) ) {
				echo "<td>Already in extension</td>\n" ;
				/*
				if ( $apply) {
					$nbpics = $res->nbpics + 1  ;
					query("UPDATE card_ext SET `rarity` = '$rarity', `nbpics` = '$nbpics' WHERE `card` = $card_id AND `ext` = $ext_id ;") ;
					if ( mysql_affected_rows() > 0 ) {
						$log .= 'Updated ('.mysql_affected_rows().') for '.$ext.' (' ;
						if ( $res->rarity != $rarity )
							$log .= 'rarity : '.$res->rarity.' -> '.$rarity ;
						if ( $res->nbpics != $nbpics )
							$log .= $res->nbpics.' -> '.$nbpics.' pics' ;
						$log .= ')' ;
					} else
						$log .= 'Nothing' ;
				}
				*/
			} else {
				echo "<td>Not in extension</td>\n" ;
				if ( $apply)
					query("INSERT INTO card_ext (`card`, `ext`, `rarity`, `nbpics`) VALUES ('$card_id', '$ext_id', '$rarity', '1') ;") ;
			}
		}
	} else {
		echo "    <td>Not existing</td>\n" ;
		if ( $apply) {
			// Insert card
			query("INSERT INTO `mtg`.`card`
			(`name` ,`cost` ,`types` ,`text`)
			VALUES ('$name', '$cost', '$types', '$text');") ;
			$card_id = mysql_insert_id($mysql_connection) ;
			// Link to extension
			query("INSERT INTO card_ext (`card`, `ext`, `rarity`, `nbpics`) VALUES ('$card_id', '$ext_id', '$rarity', '1') ;") ;
			$log .= 'Created and linked to '.$ext.' ('.$card_id.')' ;
		} else
			$log .= '<b>Insert</b> : <ul><li>'.$name.'</li><li>'.$typescost.'</li><li>'.$types.'</li><li>'.$cost.'</li><li><pre>'.$text.'</pre></li></ul><b>Link</b> : <ul><li>'.$ext_id.'</li><li>'.$rarity.'</li>' ;
	}
	echo "   </tr>\n" ;
}
?>
  </table>
 </body>
</html>
