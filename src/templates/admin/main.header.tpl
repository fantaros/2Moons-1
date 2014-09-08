<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
    <title>{block name="title"} - {$gameName}{/block}</title>

    <link href="resource/lib/bootstrap/bootstrap.css" rel="stylesheet">
    <link href="resource/lib/sb-admin/sb-admin-2.css" rel="stylesheet">
    <link href="resource/lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">

    <link rel="stylesheet" href="resource/css/base/base.css?v={$REV}">
    <link rel="stylesheet" href="resource/css/admin/main.css?v={$REV}">

    <script src="resource/js/admin/main.js"></script>
</head>
<body id="{$smarty.get.page|htmlspecialchars|default:'index'}" class="{$bodyclass}">