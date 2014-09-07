<base href="{$basePath}">

{if !empty($metaRefresh)}
    <meta http-equiv="refresh" content="{$metaRefresh.seconds};URL={$metaRefresh.url}">
{/if}

<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

{if !empty($boilerplate)}
    <link rel="stylesheet" href="resource/lib/boilerplate/boilerplate.css?v={$REV}">
{/if}
{if !empty($jqueryui)}
    <link rel="stylesheet" href="resource/lib/jquery-ui/css/jquery-ui-1.10.3.custom.min.css?v={$REV}">
{/if}
{if !empty($fancybox)}
    <link rel="stylesheet" href="resource/lib/fancybox/jquery.fancybox-1.3.4.css?v={$REV}">
{/if}
{if !empty($validEngine)}
    <link rel="stylesheet" href="resource/css/base/validationEngine.jquery.css?v={$REV}">
{/if}

<meta name="generator" content="2Moons {$VERSION}">
<!--
    This website is powered by 2Moons {$VERSION}
    2Moons is a free space browsergame initially created by Jan Kröpke and licensed under GNU/GPL.
    2Moons is copyright 2009-2014 of Jan Kröpke. Extensions are copyright of their respective owners.
    Information and contribution at http://2moons.cc/
-->
<meta name="keywords" content="Weltraum Browsergame, XNova, 2Moons, Space, Private, Server, Speed">
<meta name="description" content="2Moons Browsergame powerd by http://2moons.cc/"> <!-- Noob Check :) -->
<!--[if lt IE 9]>
<script src="resource/js/base/html5.js"></script>
<![endif]-->
<script src="resource/lib/jquery/jquery-1.11.0.min.js?v={$REV}"></script>
<script src="resource/lib/jquery/jquery-migrate-1.2.1.min.js?v={$REV}"></script>
<script src="resource/js/base/jquery.cookie.js?v={$REV}"></script>

{if !empty($jqueryui)}
<script src="resource/lib/jquery-ui/js/jquery-ui-1.10.3.custom.min.js?v={$REV}"></script>
{/if}
{if !empty($fancybox)}
<script src="resource/lib/fancybox/jquery.fancybox-1.3.4.pack.js?v={$REV}"></script>
{/if}
{if !empty($validEngine)}
    <script src="resource/js/base/jquery.validationEngine.js?v={$REV}"></script>
    <script src="resource/js/l18n/validationEngine/jquery.validationEngine-{$lang}.js?v={$REV}"></script>
{/if}

{block name="script"}{/block}