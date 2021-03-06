<?php
include 'lib.php' ;
html_head(
	'Deck builder',
	array(
		'style.css'
		, 'deckbuilder.css'
		, 'menu.css'
	),
	array(
		'lib/jquery.js',
		'image.js',
		'math.js',
		'menu.js',
		'deck.js',
		'html.js',
		'../variables.js.php',
		'deckbuilder.js',
		'stats.js',
		'lib/Flotr2/flotr2.min.js'
	)
) ;
?>

 <body onload="load(this, '<?php echo $_GET['deck'] ; ?>' );">

<?php
html_menu() ;
?>

  <!-- Search form -->
  <div id="search" class="section">
   <h1>Cards</h1>
   <form id="search_cards" method="get">
    <input type="hidden" name="page" value="1">
    <input id="cardname" type="text" name="name" placeholder="name" autocomplete="off" title="Search inside card name, use % as a joker">
    <div id="hidden_form" class="hidden">
     <input id="cardtypes" type="text" name="types" placeholder="supertype - type - subtype" autocomplete="off" title="Search inside card supertypes (legendary, basic, snow), types (creature, land ...) or subtypes (elf, equipment, aura), use % as a joker">
     <input id="cardtext" type="text" name="text" placeholder="text" autocomplete="off" title="Search inside card text, use % as a joker">
     <input id="cardcost" type="text" name="cost" placeholder="cost" autocomplete="off" title="Search inside card cost, use % as a joker">
     <label>Cards per page : 
     <input id="cardlimit" type="text" name="limit" value="30" title="Number of cards to display" size="2" maxlength="2" title="Number of results to display per page"></label>
    </div>
    <input type="submit" name="submit" value="Search" title="Search all cards matching all criteria">
    <span id="advanced_search" title="Show/hide advanced search parameters"></span>
    <div id="pagination"></div>
   </form>

   <!-- List of all cards found under search form -->
   <table id="cardlist">
    <thead>
     <tr>
      <th>Ext</th>
      <th>Card</th>
     </tr>
    </thead>
    <tbody id="search_result">
    </tbody>
   </table>
  </div>

  <!-- Buttons, between search form's results and deck -->
  <div id="buttons" class="section">
   <button id="add_md" accesskey="a" title="Add card to deck"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/1rightarrow.png" alt="=&gt;"></button>
   <button id="add_sb" accesskey="b" title="Add card to sideboard"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/2rightarrow.png" alt="&gt;&gt;"></button>
   <button id="del" accesskey="d" title="Remove card from deck / sideboard"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/1leftarrow.png" alt="&lt;="></button>
   <hr>
   <button id="up" accesskey="u" title="up card"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/1uparrow.png" alt="&gt;&gt;"></button>
   <button id="down" accesskey="j" title="down card"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/1downarrow.png" alt="&lt;="></button>
   <hr>
   <button id="comment" accesskey="c" title="Add a comment over selected line"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/edit.png" alt="comment"></button>
   <hr>
   <button id="save" accesskey="s" title="Save deck"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/filesave.png" alt="save"></button>
   <button id="saveas" accesskey="v" title="Save deck giving it another name"><img src="/themes/<?php echo $theme ; ?>/deckbuilder/filesaveas.png" alt="save"></button>
   <label title="Save as .dec, meaning no extension information is saved"><input id="noextensions" type="checkbox">.dec</label>
  </div>

  <!-- List of cards in current deck, middle of the page -->
  <div id="decksection" class="section">
   <h1>Deck</h1>
   <table id="deck">
    <thead>
     <tr>
      <th>.</th>
      <th>Ext</th>
      <th>Card</th>
      <th class="buttonlist">Act.</th>
     </tr>
    </thead>
    <tbody id="maindeck">
    </tbody>
    <tbody id="sideboard">
    </tbody>
   </table>
  </div>

  <!-- Zoom image on top right of the page -->
  <div id="infos" class="section">
   <h1>Infos</h1>
   <img id="zoom" src="<?php echo $cardimages_default ; ?>back.jpg">
   
   <div id="actions">
    <fieldset>
     <legend>Sort</legend>
     Sort deck by type, then converted cost
     <label><input id="sort_comments" type="checkbox" checked="checked">Add "types" comments</label>
     <button id="sort">Sort</button>
    </fieldset>
   </div>

   <!-- Deck stats on bottom right of the page -->
   <div id="stats_leftcol">
    <div id="stats_color"></div>
    <div id="stats_cost"></div>
   </div>
   <div id="stats_rightcol">
    <div id="stats_typelist"></div>
    <div id="stats_type"></div>
   </div>
  </div>

  <!-- Logs -->
  <textarea id="log"></textarea>

<?php
if ( is_file('footer.php') )
	include 'footer.php' ;
?>
 </body>
</html>
