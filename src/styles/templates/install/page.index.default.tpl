{block name="title" prepend}{$LNG.menu_intro}{/block}
{block name="content"}
<table>
    <tr>
        <th>{$LNG.menu_intro}</th>
    </tr>
    <tr>
        <td>
            <div id="lang" class="right"><label for="lang">{$LNG.intro_lang}</label>:&nbsp;<select id="lang"  name="lang" onchange="document.location = '?lang='+$(this).val();">{html_options options=$languageSelect}</select></div>
            <div id="main" class="left">
                <h2>{$LNG.intro_welcome}</h2>
                <p>{$LNG.intro_text}</p>
            </div>
            <div><p><a href="install/index.php?page=licence"><button>{$LNG.continue}</button></a></p></div>
        </td>
    </tr>
    {if $canUpgrade}
    <tr>
        <th>{$LNG.menu_upgrade}</th>
    </tr>
    <tr>
        <td>
            <div id="main" class="left">
                <h2>{$LNG.intro_upgrade_head}</h2>
                <p>{$LNG.intro_upgrade_text}</p>
            </div>
            <div><p><a href="install/index.php?page=upgrade"><button>{$LNG.continueUpgrade}</button></a></p></div>
        </td>
    </tr>
    {/if}
</table>
{/block}