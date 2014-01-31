<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
    {include 'headInclude.tpl' boilerplate=1}
    <title>{block name="title"} - 2Moons{/block}</title>

    <link rel="stylesheet" type="text/css" href="resource/css/ingame/main.css?v={$REV}">
    <link rel="stylesheet" type="text/css" href="resource/css/install/main.css?v={$REV}">
    <link rel="stylesheet" type="text/css" href="{$themePath}formate.css?v={$REV}">

    {block name="script"}{/block}
</head>
<body id="{$smarty.get.page|htmlspecialchars|default:'index'}" class="{$bodyclass}">