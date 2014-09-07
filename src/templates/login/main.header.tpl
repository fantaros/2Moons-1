<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
    {include 'headInclude.tpl' fancybox=1}

    <title>{block name="title"} - {$gameName}{/block}</title>
    <link rel="stylesheet" href="resource/css/base/base.css?v={$REV}">
    <link rel="stylesheet" href="resource/css/login/main.css?v={$REV}">
    <script src="resource/js/login/main.js"></script>
    <script>{if isset($code)}var loginError = {$code|json};{/if}</script>
</head>
<body id="{$smarty.get.page|htmlspecialchars|default:'overview'}" class="{$bodyclass}">
	<div id="page">