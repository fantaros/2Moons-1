{block name="title" prepend}{$pageTitle}{/block}
{block name="content"}
{if isset($Info)}
<table style="width:100%">
	<tr>
		<td class="transparent" style="width:40%;font-size:22px;font-weight:bold;padding:10px 0 30px;color:{if $Report.result == "a"}lime{elseif $Report.result == "r"}red{else}white{/if}">{$Info.0}</td>
		<td class="transparent" style="font-size:22px;font-weight:bold;padding:10px 0 30px;">VS</td>
		<td class="transparent" style="width:40%;font-size:22px;font-weight:bold;padding:10px 0 30px;color:{if $Report.result == "r"}lime{elseif $Report.result == "a"}red{else}white{/if}">{$Info.1}</td>
	</tr>
</table>
{/if}
<div style="width:100%;text-align:center">
{if $Report.mode == 1}{$LNG.sys_destruc_title}{else}{$LNG.sys_attack_title}{/if}
{$Report.time}:<br><br>
{foreach $Report.rounds as $Round => $RoundInfo}
<table>
	<tr>
		{foreach $RoundInfo.attacker as $Player}
		{$PlayerInfo = $Report.players[$Player.userID]}
		<td class="transparent">
			<table>
				<tr>
					<td>
						{$LNG.sys_attack_attacker_pos} {$PlayerInfo.name} {if isset($Info)}([XX:XX:XX]){else}([{$PlayerInfo.koords[0]}:{$PlayerInfo.koords[1]}:{$PlayerInfo.koords[2]}]{if isset($PlayerInfo.koords[3])} ({$LNG.type_planet_short[$PlayerInfo.koords[3]]}){/if}){/if}<br>
						{$LNG.sys_ship_weapon} {$PlayerInfo.tech[0]}% - {$LNG.sys_ship_shield} {$PlayerInfo.tech[1]}% - {$LNG.sys_ship_armour} {$PlayerInfo.tech[2]}%
						<table width="100%">
						{if !empty($Player.ships)}
							<tr>
								<td class="transparent">{$LNG.sys_ship_type}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$LNG.shortNames.{$ShipID}}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_count}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[0]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_weapon}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[1]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_shield}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[2]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_armour}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[3]|number}</td>
								{/foreach}
							</tr>
						{else}
							<tr>
								<td class="transparent">
									<br>{$LNG.sys_destroyed}<br><br>
								</td>
							</tr>
						{/if}
						</table>
					</td>
				</tr>
			</table>
		</td>
		{/foreach}
	</tr>
</table>
<br><br>
<table>
	<tr>
		{foreach $RoundInfo.defender as $Player}
		{$PlayerInfo = $Report.players[$Player.userID]}
		<td class="transparent">
			<table>
				<tr>
					<td>
						{$LNG.sys_attack_defender_pos} {$PlayerInfo.name} {if isset($Info)}([XX:XX:XX]){else}([{$PlayerInfo.koords[0]}:{$PlayerInfo.koords[1]}:{$PlayerInfo.koords[2]}]{if isset($PlayerInfo.koords[3])} ({$LNG.type_planet_short[$PlayerInfo.koords[3]]}){/if}){/if}<br>
						{$LNG.sys_ship_weapon} {$PlayerInfo.tech[0]}% - {$LNG.sys_ship_shield} {$PlayerInfo.tech[1]}% - {$LNG.sys_ship_armour} {$PlayerInfo.tech[2]}%
						<table width="100%">
						{if !empty($Player.ships)}
							<tr>
								<td class="transparent">{$LNG.sys_ship_type}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$LNG.shortNames.{$ShipID}}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_count}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[0]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_weapon}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[1]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_shield}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[2]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_armour}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[3]|number}</td>
								{/foreach}
							</tr>
						{else}
							<tr>
								<td class="transparent">
									<br>{$LNG.sys_destroyed}<br><br>
								</td>
							</tr>
						{/if}
						</table>
					</td>
				</tr>
			</table>
		</td>
		{/foreach}
	</tr>
</table>
{if !$RoundInfo@last}
{$LNG.fleet_attack_1} {$RoundInfo.info[0]|number} {$LNG.fleet_attack_2} {$RoundInfo.info[3]|number} {$LNG.damage}<br>
{$LNG.fleet_defs_1} {$RoundInfo.info[2]|number} {$LNG.fleet_defs_2} {$RoundInfo.info[1]|number} {$LNG.damage}<br><br>
{/if}
{/foreach}
<br><br>
{if $Report.result == "a"}
{$LNG.sys_attacker_won}<br>
{$LNG.sys_stealed_ressources} {foreach $Report.steal as $elementID => $amount}{$amount|number} {$LNG.tech.$elementID}{if ($amount@index + 2) == count($Report.steal)} {$LNG.sys_and} {elseif !$amount@last}, {/if}{/foreach}
{elseif $Report.result == "r"}
{$LNG.sys_defender_won}
{else}
{$LNG.sys_both_won}
{/if}
<br><br>
{$LNG.sys_attacker_lostunits} {$Report['units'][0]|number} {$LNG.sys_units}<br>
{$LNG.sys_defender_lostunits} {$Report['units'][1]|number} {$LNG.sys_units}<br>
{$LNG.debree_field_1} {foreach $Report.debris as $elementID => $amount}{$amount|number} {$LNG.tech.$elementID}{if ($amount@index + 2) == count($Report.debris)} {$LNG.sys_and} {elseif !$amount@last}, {/if}{/foreach}{$LNG.debree_field_2}<br><br>
{if $Report.mode == 1}
	{* Destruction *}
	{if $Report.moon.moonDestroySuccess == -1}
		{* Attack not win *}
		{$LNG.sys_destruc_stop}<br>
	{else}
		{* Attack win *}
		{sprintf($LNG.sys_destruc_lune, "{$Report.moon.moonDestroyChance}")}<br>{$LNG.sys_destruc_mess1}
		{if $Report.moon.moonDestroySuccess == 1}
			{* Destroy success *}
			{$LNG.sys_destruc_reussi}
		{elseif $Report.moon.moonDestroySuccess == 0}
			{* Destroy failed *}
			{$LNG.sys_destruc_null}			
		{/if}
		<br>
		{sprintf($LNG.sys_destruc_rip, "{$Report.moon.fleetDestroyChance}")}
		{if $Report.moon.fleetDestroySuccess == 1}
			{* Fleet destroyed *}
			<br>{$LNG.sys_destruc_echec}
		{/if}			
	{/if}
{else}
	{* Normal Attack *}
	{$LNG.sys_moonproba} {$Report.moon.moonChance} %<br>
	{if !empty($Report.moon.moonName)}
		{if isset($Info)}
			{* Moon created (HoF Mode) *}
			{sprintf($LNG.sys_moonbuilt, "{$Report.moon.moonName}", "XX", "XX", "XX")}
		{else}
			{* Moon created *}
			{sprintf($LNG.sys_moonbuilt, "{$Report.moon.moonName}", "{$Report.koords[0]}", "{$Report.koords[1]}", "{$Report.koords[2]}")}
		{/if}
	{/if}
{/if}

{$Report.additionalInfo}
</div>
{/block} 