<div id="leftmenu">
	<ul id="menu">
		<li class="menu-head"><a href="{"game.php?page=changelog"|link}">{$LNG.lm_changelog}</a></li>
		<li class="menucat1-head"></li>
		<li><a href="{"game.php?page=overview"|link}">{$LNG.lm_overview}</a></li>
		{if isModulAvalible($smarty.const.MODULE_IMPERIUM)}<li><a href="{"game.php?page=imperium"|link}">{$LNG.lm_empire}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_RESEARCH)}<li><a href="{"game.php?page=research"|link}">{$LNG.lm_research}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_BUILDING)}<li><a href="{"game.php?page=buildings"|link}">{$LNG.lm_buildings}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_SHIPYARD_FLEET)}<li><a href="{"game.php?page=shipyard&amp;mode=fleet"|link}">{$LNG.lm_shipshard}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_SHIPYARD_DEFENSIVE)}<li><a href="{"game.php?page=shipyard&amp;mode=defense"|link}">{$LNG.lm_defenses}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_OFFICIER) || isModulAvalible($smarty.const.MODULE_DMEXTRAS)}<li><a href="{"game.php?page=officier"|link}">{$LNG.lm_officiers}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_TRADER)}<li><a href="{"game.php?page=trader"|link}">{$LNG.lm_trader}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_FLEET_TRADER)}<li><a href="{"game.php?page=fleetDealer"|link}">{$LNG.lm_fleettrader}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_TRADER)}<li><a href="{"game.php?page=fleetTable"|link}">{$LNG.lm_fleet}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_TECHTREE)}<li><a href="{"game.php?page=techtree"|link}">{$LNG.lm_technology}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_RESSOURCE_LIST)}<li><a href="{"game.php?page=resources"|link}">{$LNG.lm_resources}</a></li>{/if}
		<li class="menucat2-head"></li>
		{if isModulAvalible($smarty.const.MODULE_GALAXY)}<li><a href="{"game.php?page=galaxy"|link}">{$LNG.lm_galaxy}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_ALLIANCE)}<li><a href="{"game.php?page=alliance"|link}">{$LNG.lm_alliance}</a></li>{/if}
        {if !empty($hasBoard)}<li><a href="{"game.php?page=board"|link} target="forum">{$LNG.lm_forums}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_STATISTICS)}<li><a href="{"game.php?page=statistics"|link}">{$LNG.lm_statistics}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_RECORDS)}<li><a href="{"game.php?page=records"|link}">{$LNG.lm_records}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_BATTLEHALL)}<li><a href="{"game.php?page=battleHall"|link}">{$LNG.lm_topkb}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_SEARCH)}<li><a href="{"game.php?page=search"|link}">{$LNG.lm_search}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_CHAT)}<li><a href="{"game.php?page=chat"|link}">{$LNG.lm_chat}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_SUPPORT)}<li><a href="{"game.php?page=ticket"|link}">{$LNG.lm_support}</a></li>{/if}
		<li><a href="{"game.php?page=questions"|link}">{$LNG.lm_faq}</a></li>
		{if isModulAvalible($smarty.const.MODULE_BANLIST)}<li><a href="{"game.php?page=banList"|link}">{$LNG.lm_banned}</a></li>{/if}
		<li><a href="index.php?page=rules" target="rules">{$LNG.lm_rules}</a></li>
		{if isModulAvalible($smarty.const.MODULE_SIMULATOR)}<li><a href="{"game.php?page=battleSimulator"|link}">{$LNG.lm_battlesim}</a></li>{/if}
		<li class="menucat3-head"></li>
		{if isModulAvalible($smarty.const.MODULE_MESSAGES)}<li><a href="{"game.php?page=messages"|link}">{$LNG.lm_messages}{nocache}{if $new_message > 0}<span id="newmes"> (<span id="newmesnum">{$new_message}</span>)</span>{/if}{/nocache}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_NOTICE)}<li><a href="javascript:OpenPopup('game.pjp?page=notes', 'notes', 720, 300);">{$LNG.lm_notes}</a></li>{/if}
		{if isModulAvalible($smarty.const.MODULE_BUDDYLIST)}<li><a href="{"game.php?page=buddyList"|link}">{$LNG.lm_buddylist}</a></li>{/if}
		<li><a href="{"game.php?page=settings"|link}">{$LNG.lm_options}</a></li>
		<li><a href="{"game.php?page=logout"|link}">{$LNG.lm_logout}</a></li>
		{if $authlevel > 0}<li><a href="./admin.php" style="color:lime">{$LNG.lm_administration} ({$VERSION})</a></li>{/if}
		<li class="menu-footer"></li>
	</ul>
	<div id="disclamer"><a href="index.php?page=disclamer" target="_blank">{$LNG.lm_disclamer}</a></div>
</div>