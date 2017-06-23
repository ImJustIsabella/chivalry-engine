<?php
/*
	File:		halloffame.php
	Created: 	6/23/2017 at 12:18AM Eastern Time
	Info: 		Lists the top 20 users based on input.
	Author:		TheMasterGeneral
	Website: 	https://github.com/MasterGeneral156/chivalry-engine
*/
require('globals.php');
echo "<h3>{$lang['HOF_TITLE']}</h3><hr />";
//Add stats to this array.
$StatArray=array('total','level','strength','agility','guard','labor','iq',
                    'primary_currency','mining_level', 'secondary_currency');
//Stat is not chosen, set to level.
if (!isset($_GET['stat']))
{
    $_GET['stat']='level';
}
//Stat chosen is not a valid stat.
if (!in_array($_GET['stat'],$StatArray))
{
    $_GET['stat']='level';
}
$_GET['stat']=$db->escape(strip_tags(stripslashes($_GET['stat'])));
if ($_GET['stat'] == 'total')
{
    
}
elseif ($_GET['stat'] == 'mining_level')
{
    $q=$db->query("SELECT `u`.*, `m`.* 
                    FROM `users` `u` 
                    INNER JOIN `mining` AS `m`
                    ON `u`.`userid` = `m`.`userid`
                    WHERE `user_level` != 'Admin' AND `user_level` != 'NPC'
                    ORDER BY `mining_level` DESC
                    LIMIT 20");
}
else
{
    $q=$db->query("SELECT `u`.*, `us`.* 
                    FROM `users` `u` 
                    INNER JOIN `userstats` AS `us`
                    ON `u`.`userid` = `us`.`userid`
                    WHERE `user_level` != 'Admin' AND `user_level` != 'NPC'
                    ORDER BY `{$_GET['stat']}` DESC
                    LIMIT 20");
}
echo "<a href='?stat=level'>{$lang['INDEX_LEVEL']}</a> 
        || <a href='?stat=primary_currency'>{$lang['INDEX_PRIMCURR']}</a>
        || <a href='?stat=secondary_currency'>{$lang['INDEX_SECCURR']}</a> 
        || <a href='?stat=strength'>{$lang['GEN_STR']}</a>";
echo "<br />Listing the 20 players with the highest {$_GET['stat']}.";
echo "<table class='table table-bordered'>
<tr>
    <th width='10%'>
        {$lang['HOF_RANK']}
    </th>
    <th width='45%'>
        {$lang['HOF_USER']}
    </th>";
    if ($_GET['stat'] == 'level' || $_GET['stat'] == 'primary_currency' || $_GET['stat'] == 'secondary_currency'
            || $_GET['stat'] == 'mining')
    {
        echo "<th width='45%'>
                {$lang['HOF_VALUE']}
               </th>";
    }
    echo "
</tr>";
$rank=1;
while ($r=$db->fetch_row($q))
{
    echo "
    <tr>
        <td>
            {$rank})
        </td>
        <td>
            <a href='profile.php?user={$r['userid']}'>{$r['username']}</a> [{$r['userid']}]
        </td>";
        if ($_GET['stat'] == 'level' || $_GET['stat'] == 'primary_currency' || $_GET['stat'] == 'secondary_currency'
            || $_GET['stat'] == 'mining')
        {
            echo "<td>
                    " . number_format($r[$_GET['stat']]) . "
                   </td>";
        }
        echo"
    </tr>";
    $rank++;
}
echo"</table>";
$h->endpage();