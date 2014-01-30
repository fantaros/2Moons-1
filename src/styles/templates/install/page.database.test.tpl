{block name="title" prepend}Somethink Cool{/block}
{block name="content"}
<table style="width:960px">
    <tr>
        <th>Blah</th>
    </tr>
    <tr>
        <td>
            <div id="main" class="left">
                <div class="{$class}"><p>{$message}</p></div>
                <div style="text-align:center;"><p>
                {if $class == 'noerror'}
                    <a href="index.php?page=install"><button>{$LNG.continue}</button></a>
                {else}
                    <a href="javascript:window.history.back()"><button>{$LNG.back}</button></a>
                {/if}
                </p></div>
            </div>
        </td>
    </tr>
{include file="ins_footer.tpl"}