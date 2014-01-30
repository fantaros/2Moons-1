<!DOCTYPE html>

<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
    {include 'headInclude.tpl' jqueryui=1 fancybox=1 validEngine=1}

	<title>{block name="title"} - {$uni_name} - {$game_name}{/block}</title>

    <link rel="stylesheet" type="text/css" href="resource/css/ingame/main.css?v={$REV}">
	<link rel="stylesheet" type="text/css" href="{$themeName}formate.css?v={$REV}">

	<script type="text/javascript">
	var ServerTimezoneOffset = {$Offset};
	var serverTime 	= new Date({$date.0}, {$date.1 - 1}, {$date.2}, {$date.3}, {$date.4}, {$date.5});
	var startTime	= serverTime.getTime();
	var localTime 	= serverTime;
	var localTS 	= startTime;
	var Gamename	= document.title;
	var Ready		= "{$LNG.ready}";
	var Skin		= "{$themeName}";
	var Lang		= "{$lang}";
	var head_info	= "{$LNG.fcm_info}";
	var auth		= {$authlevel|default:'0'};
	var days 		= {$LNG.week_day|json}
	var months 		= {$LNG.months|json} ;
	var tdformat	= "{$LNG.js_tdformat}";
	var queryString	= "{$queryString|escape:'javascript'}";

	setInterval(function() {
		serverTime.setSeconds(serverTime.getSeconds()+1);
	}, 1000);
	</script>
	<script type="text/javascript" src="resource/js/base/tooltip.js?v={$REV}"></script>
	<script type="text/javascript" src="resource/js/game/base.js?v={$REV}"></script>

	{foreach item=scriptname from=$scripts}
	<script type="text/javascript" src="resource/js/game/{$scriptname}.js?v={$REV}"></script>
	{/foreach}
	<script type="text/javascript">
	$(function() {
		{$execscript}
	});
	</script>
</head>
<body id="{$smarty.get.page|htmlspecialchars|default:'overview'}" class="{$bodyclass}">
	<div id="tooltip" class="tip"></div>