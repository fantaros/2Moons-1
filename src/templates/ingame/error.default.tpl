{block name="title" prepend}{$LNG.fcm_info}{/block}
{block name="content"}
<table class="table519">
	<tr>
		<th>{$LNG.fcm_info}</th>
	</tr>
	<tr>
		<td><p>{$message}</p>{if !empty($redirectButtons)}<p id="infoButtons">{foreach $redirectButtons as $button}<a href="{$button.url}"><button>{$button.label}</button></a>{/foreach}</p>{/if}</td>
	</tr>
</table>
{/block}