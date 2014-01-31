{block name="title" prepend}{$LNG.fcm_info}{/block}
{block name="content"}
<table>
    <tr>
        <th>{$LNG.fcm_info}</th>
    </tr>
    <tr>
        <td>
            <div id="main" class="left">
                {$message}{if !empty($redirectButtons)}<p>{foreach $redirectButtons as $button}<a href="{$button.url}"><button>{$button.label}</button></a>{/foreach}</p>{/if}
            </div>
        </td>
    </tr>
</table>
{/block}