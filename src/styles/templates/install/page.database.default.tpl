{block name="title" prepend}{$LNG['step1_head']}{/block}
{block name="content"}
<table>
    <tr>
        <th>{$LNG.step1_head}</th>
    </tr>
    <tr>
        <td class="left">
            <p>{$LNG.step1_desc}</p>
            <form action="install/index.php?page=database&amp;mode=test" method="post">
                <table class="req">
                    <tr>
                        <td class="transparent left"><p><label for="mysql_hostname">{$LNG.step1_mysql_server}</label></p></td>
                        <td class="transparent"><input type="text" id="mysql_hostname" name="hostname" value="localhost" size="30"></td>
                    </tr>
                    <tr>
                        <td class="transparent left"><p><label for="mysql_port">{$LNG.step1_mysql_port}</label></p></td>
                        <td class="transparent"><input type="text" id="mysql_port" name="port" value="3306" size="30"></td>
                    </tr>
                    <tr>
                        <td class="transparent left"><p><label for="mysql_user">{$LNG.step1_mysql_dbuser}</label></p></td>
                        <td class="transparent"><input type="text" id="mysql_user" name="user" value="" size="30"></td>
                    </tr>
                    <tr>
                        <td class="transparent left"><p><label for="mysql_password">{$LNG.step1_mysql_dbpass}</label></p></td>
                        <td class="transparent"><input type="password" id="mysql_password" name="password" value="" size="30"></td>
                    </tr>
                    <tr>
                        <td class="transparent left"><p><label for="mysql_database">{$LNG.step1_mysql_dbname}</label></p></td>
                        <td class="transparent"><input type="text" id="mysql_database" name="database" value="" size="30"></td>
                    </tr>
                    <tr>
                        <td class="transparent left"><p><label for="mysql_prefix">{$LNG.step1_mysql_prefix}</label></p></td>
                        <td class="transparent"><input type="text" id="mysql_prefix" name="prefix" value="uni1_" size="30"></td>
                    </tr>
                    <tr class="noborder">
                        <td colspan="2" class="transparent"><input type="submit" name="next" value="{$LNG.continue}"></td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
{/block}