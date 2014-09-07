{block name="title" prepend}{$LNG['step1_head']}{/block}
{block name="content"}
<table>
    <tr>
        <th>{$LNG.step1_head}</th>
    </tr>
    <tr>
        <td>
            <div id="main" class="left">
                <div class="{$class}"><p>{$message}</p></div>
                <div style="text-align:center;"><p>
                {if $class == 'noerror'}
                    <a href="install/index.php?page=install"><button>{$LNG.continue}</button></a>
                {else}
                    <a href="javascript:window.history.back()"><button>{$LNG.back}</button></a>
                {/if}
                </p></div>
            </div>
        </td>
    </tr>
</table>
{/block}