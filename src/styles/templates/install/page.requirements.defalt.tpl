{block name="title" prepend}Somethink Cool{/block}
{block name="content"}
<table style="width:960px">
	<tr>
		<th>Blah</th>
	</tr>
	<tr>
		<td class="left">
			<h2>{$LNG.req_head}</h2>
			<p>{$LNG.req_desc}</p>
			{foreach $requirements as $requirement}
			<table class="req border">
			<tr>
				<td class="transparent left"><p>{$requirement.name}</p>{if isset($requirement.description)}<p class="desc">{$requirement.description}</p>{/if}</td>
				<td class="transparent">{$requirement.value}</td>		</tr>
			{/foreach}
			{if !$isError}
			<tr class="noborder">
				<td colspan="2" class="transparent">
					<a href="index.php?page=database"><button style="cursor: pointer;">{$LNG.continue}</button></a>
				</td>
			</tr>
			{/if}
			</table>
		</td>
	</tr>
	{if $writeError}
	<tr>
		<td class="transparent"><p>&nbsp;</p></td>
	</tr>
	<tr>
		<th>{$LNG.req_ftp_head}</th>
	</tr>
	<tr>
		<td>
			<form name="ftp" id="ftp" action="index.php?page=setPermission&amp;mode=ftp">
			<table class="req">
				<tr>
					<td class="transparent left" colspan="2">
						<p>{$LNG.req_ftp_desc}</p>
					</td>
				</tr>
				<tr>
					<td class="transparent left">{$LNG.req_ftp_host}:</td>
					<td class="transparent"><input type="text" name="host"></td>
				</tr>
				<tr>
					<td class="transparent left">{$LNG.req_ftp_username}:</td>
					<td class="transparent"><input type="text" name="user"></th>
				</tr>
				<tr>
					<td class="transparent left">{$LNG.req_ftp_password}:</td>
					<td class="transparent"><input type="password" name="pass"></td>
				</tr>
				<tr>
					<td class="transparent left">{$LNG.req_ftp_dir}:</td>
					<td class="transparent"><input type="text" name="path"></td>
				</tr>
				<tr class="noborder">
					<td class="transparent right" colspan="2"><input type="button" value="{$LNG.req_ftp_send}"></td>
				</tr>
				</table>
			</form>
		</td>
	</tr>
	{/if}
</table>
{/block}