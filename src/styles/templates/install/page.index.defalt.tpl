{block name="title" prepend}Somethink Cool{/block}
{block name="content"}
<table style="width:960px">
    <tr>
        <th>Blah</th>
    </tr>
    <tr>
        <td>
            <div id="lang" class="right"><label for="lang">{$LNG.intro_lang}</label>:&nbsp;<select id="lang"  name="lang" onchange="document.location = '?lang='+$(this).val();">{html_options options=$languageSelect}</select></div>
            <div id="main" class="left">
                <h2>{$LNG.intro_welcome}</h2>
                <p>{$LNG.intro_text}</p>
            </div>
            <div><p><a href="index.php?page=licence"><button>{$LNG.continue}</button></a></p></div>
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
            <div><p><a href="index.php?page=upgrade"><button>{$LNG.continueUpgrade}</button></a></p></div>
        </td>
    </tr>
    {/if}
</table>
{/block}