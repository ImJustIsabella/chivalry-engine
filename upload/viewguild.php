<?php
/*
	File:		viewguild.php
	Created: 	4/5/2016 at 12:32AM Eastern Time
	Info: 		Allows users to view their guild and do various actions.
	Author:		TheMasterGeneral
	Website: 	https://github.com/MasterGeneral156/chivalry-engine
*/
require('globals.php');
if (!$ir['guild']) {
    alert('danger', "Uh Oh!", "You are not in a guild.", true, 'index.php');
} else {
    $gq = $db->query("SELECT * FROM `guild` WHERE `guild_id` = {$ir['guild']}");
    if ($db->num_rows($gq) == 0) {
        alert('danger', "Uh Oh!", "Your guild's data could not be selected. Please contact an admin immediately.");
        die($h->endpage());
    }
    $gd = $db->fetch_row($gq);
    $db->free_result($gq);
    $wq = $db->query("SELECT COUNT(`gw_id`) FROM `guild_wars` WHERE (`gw_declarer` = {$ir['guild']} OR `gw_declaree` = {$ir['guild']}) AND `gw_winner` = 0");
    if ($db->fetch_single($wq) > 0) {
        alert('warning', "Guild Wars in Progress", "Your guild is in {$db->fetch_single($wq)} wars. View active wars <a href='?action=warview'>here</a>.", false);
    }
    echo "
	<h3><u>Your Guild, {$gd['guild_name']}</u></h3>
   	";
    if (!isset($_GET['action'])) {
        $_GET['action'] = '';
    }
    switch ($_GET['action']) {
        case 'summary':
            summary();
            break;
        case 'donate':
            donate();
            break;
        case "members":
            members();
            break;
        case "kick":
            staff_kick();
            break;
        case "leave":
            leave();
            break;
        case "atklogs":
            atklogs();
            break;
        case "staff":
            staff();
            break;
        case "warview":
            warview();
            break;
        case "armory":
            armory();
            break;
        case "adonate":
            adonate();
            break;
        case "crimes":
            crimes();
            break;
        default:
            home();
            break;
    }
}
function home()
{
    global $db, $userid, $ir, $gd;
    //The main guild index.
    echo "
    <table class='table table-bordered'>
    		<tr>
    			<td>
    			    <a href='?action=summary'>Summary</a>
                </td>
    			<td>
    			    <a href='?action=donate'>Donate</a>
                </td>
    		</tr>
    		<tr>
    			<td>
    			    <a href='?action=members'>Members</a>
                </td>
    			<td>
    			    <a href='?action=crimes'>Crimes</a>
                </td>
    		</tr>
    		<tr>
    			<td>
    			    <a href='?action=leave'>Leave Guild</a>
                </td>
				<td>
				    <a href='?action=atklogs'>Attack Logs</a>
                </td>
    		</tr>
    		<tr>
    			<td>
    			    <a href='?action=armory'>Armory</a>
                </td>
    			<td>";
    if ($gd['guild_owner'] == $userid || $gd['guild_coowner'] == $userid) {
        echo "<a href='?action=staff&act2=idx'>Staff Room</a>";
    }
    echo "</td></tr>
	</table>
	<br />
	<table class='table table-bordered'>
		<tr class='table-secondary'>
			<th>
			    Guild Announcement
			</th>
		</tr>
		<tr>
			<td>
			    {$gd['guild_announcement']}
			</td>
		</tr>
	</table>
	<br />
	<b>Last 10 Guild Notifications</b>
	<br />
   	";
    $q = $db->query("SELECT * FROM `guild_notifications` WHERE `gn_guild` = {$ir['guild']} ORDER BY `gn_time` DESC  LIMIT 10");
    echo "
	<table class='table table-bordered'>
		<tr>
			<th>
			    Notification Info
            </th>
			<th>
			    Notification Content
            </th>
		</tr>
   	";
    while ($r = $db->fetch_row($q)) {
        echo "
		<tr>
			<td>
			    " . date('F j Y, g:i:s a', $r['gn_time']) . "
            </td>
			<td>
			    {$r['gn_text']}
            </td>
		</tr>
   		";
    }
    $db->free_result($q);
    echo "</table>";
}

function summary()
{
    global $db, $gd, $set;

    //List all the guild's information
    echo "
	<table class='table table-bordered'>
	<tr>
		<th colspan='2'>
			Guild Information
		</th>
	</tr>
	<tr>
		<th>
			Leader
		</th>
		<td>
       ";
    $pq = $db->query("SELECT `username` FROM `users` WHERE `userid` = {$gd['guild_owner']}");
    if ($db->num_rows($pq) > 0) {
        $ldrnm = $db->fetch_single($pq);
        echo "<a href='profile.php?user={$gd['guild_owner']}'> {$ldrnm} </a>";
    } else {
        echo "N/A";
    }
    echo "</td>
	</tr>
	<tr>
		<th>
			Co-Leader
		</th>
		<td>";
    $db->free_result($pq);
    $vpq = $db->query("SELECT `username` FROM `users` WHERE `userid` = {$gd['guild_coowner']}");
    if ($db->num_rows($vpq) > 0) {
        $vldrnm = $db->fetch_single($vpq);
        echo "<a href='profile.php?user={$gd['guild_coowner']}'> {$vldrnm} </a>";
    } else {
        echo "N/A";
    }
    echo "</td>
	</tr>";
    $db->free_result($vpq);
    $cnt = $db->query("SELECT COUNT(`userid`) FROM `users` WHERE `guild` = {$gd['guild_id']}");
    echo "
	<tr>
		<th>
			Members
		</th>
		<td>
			" . $db->fetch_single($cnt) . " / " . $gd['guild_level'] * 5 . "
		</td>
	</tr>
	<tr>
		<th>
			Level
		</th>
		<td>
			{$gd['guild_level']}
		</td>
	</tr>
	<tr>
		<th>
			Experience
		</th>
		<td>
			{$gd['guild_xp']}
		</td>
	</tr>
	<tr>
		<th>
			Primary Currency
		</th>
		<td>
			" . number_format($gd['guild_primcurr']) . " / " . number_format($gd['guild_level'] * $set['GUILD_PRICE']) . "
		</td>
	</tr>
	<tr>
		<th>
			Secondary Currency
		</th>
		<td>
			" . number_format($gd['guild_seccurr']) . "
		</td>
	</tr>
      </table>
	  <a href='viewguild.php'>Go Back</a>";
}

function donate()
{
    global $db, $userid, $ir, $gd, $api, $h, $set;
    if (isset($_POST['primary'])) {

        //Make sure the POST is safe to work with.
        $_POST['primary'] = (isset($_POST['primary']) && is_numeric($_POST['primary'])) ? abs(intval($_POST['primary'])) : 0;
        $_POST['secondary'] = (isset($_POST['secondary']) && is_numeric($_POST['secondary'])) ? abs(intval($_POST['secondary'])) : 0;

        //Verify we passed the CSRF check.
        if (!isset($_POST['verf']) || !verify_csrf_code('guild_donate', stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Make sure the form is filled out.
        if (empty($_POST['primary']) && empty($_POST['secondary'])) {
            alert('danger', "Uh Oh!", "Please fill out the previous form before submitting.");
            die($h->endpage());
        }

        //Trying to donate more primary than user has.
        if ($_POST['primary'] > $ir['primary_currency']) {
            alert('danger', "Uh Oh!", "You are trying to donate more Primary Currency than you currently have.");
            die($h->endpage());
            //Trying to donate more secondary than user has.
        } else if ($_POST['secondary'] > $ir['secondary_currency']) {
            alert('danger', "Uh Oh!", "You are trying to donate more Secondary Currency than you currently have.");
            die($h->endpage());
            //Donation amount would fill up the guild's vault.
        } else if ($_POST['primary'] + $gd['guild_primcurr'] > $gd['guild_level'] * $set['GUILD_PRICE']) {
            alert('danger', "Uh Oh!", "Your guild's vault can only hold " . $gd['guild_level'] * $set['GUILD_PRICE'] . " Primary Currency.");
            die($h->endpage());
        } else {
            //Donate the currencies!
            $api->UserTakeCurrency($userid, 'primary', $_POST['primary']);
            $api->UserTakeCurrency($userid, 'secondary', $_POST['secondary']);
            $db->query("UPDATE `guild`
                        SET `guild_primcurr` = `guild_primcurr` + {$_POST['primary']},
					    `guild_seccurr` = `guild_seccurr` + {$_POST['secondary']}
					    WHERE `guild_id` = {$gd['guild_id']}");
            $my_name = htmlentities($ir['username'], ENT_QUOTES, 'ISO-8859-1');
            $event = $db->escape("<a href='profile.php?user={$userid}'>{$my_name}</a> donated
									" . number_format($_POST['primary']) . " Primary Currency and/or
									" . number_format($_POST['secondary']) . " Secondary Currency to the guild.");
            $api->GuildAddNotification($gd['guild_id'], $event);
            $api->SystemLogsAdd($userid, 'guild_vault', "Donated " . number_format($_POST['primary']) . " Primary
                Currency and/or " . number_format($_POST['secondary']) . " Secondary Currency to their guild.");
            alert('success', "Success!", "You have successfully donated " . number_format($_POST['primary']) . " Primary
			Currency and/or " . number_format($_POST['secondary']) . " Secondary Currency to your guild.", true, 'viewguild.php');
        }
    } else {
        $csrf = request_csrf_html('guild_donate');
        echo "
		<form action='?action=donate' method='post'>
			<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					Enter the amount of currency you wish to donate to your guild " . number_format($ir['primary_currency']) . "
					Primary Currency and " . number_format($ir['secondary_currency']) . " Secondary Currency
				</th>
			</tr>
    		<tr>
    			<td>
    				<b>Primary Currency</b><br />
    				<input type='number' name='primary' value='0' required='1' max='{$ir['primary_currency']}' class='form-control' min='0' />
    			</td>
    			<td>
    				<b>Secondary Currency</b><br />
    				<input type='number' name='secondary' required='1' max='{$ir['secondary_currency']}' class='form-control' value='0' min='0' />
    			</td>
    		</tr>
    		<tr>
    			<td colspan='2' align='center'>
    			    {$csrf}
    				<input type='submit' class='btn btn-primary' value='Donate' />
    			</td>
    		</tr>
    	</table>
		</form>
		<a href='viewguild.php'>Go Back</a>";
    }
}

function members()
{
    global $db, $userid, $gd;
    //List all the guild members. ^_^
    echo "
    <table class='table table-bordered table-striped'>
		<tr>
    		<th>
				User
			</th>
    		<th>
				Level
			</th>
    		<th>
				&nbsp;
			</th>
    	</tr>";
    $q = $db->query("SELECT `userid`, `username`, `level`, `display_pic` FROM `users` WHERE `guild` = {$gd['guild_id']} ORDER BY `level` DESC");
    $csrf = request_csrf_html('guild_kickuser');
    while ($r = $db->fetch_row($q)) {
        echo "
		<tr>
        	<td>
				<img src='{$r['display_pic']}' width='64' height='64'><br />
				<a href='profile.php?user={$r['userid']}'>{$r['username']}</a>
			</td>
        	<td>
				{$r['level']}
			</td>
        	<td>
           ";
        if ($gd['guild_owner'] == $userid || $gd['guild_coowner'] == $userid) {
            echo "
					<form action='?action=kick' method='post'>
						<input type='hidden' name='ID' value='{$r['userid']}' />
						{$csrf}
						<input type='submit' class='btn btn-primary' value='Kick {$r['username']}' />
					</form>";
        } else {
            echo "&nbsp;";
        }
        echo "
			</td>
		</tr>
   		";
    }
    $db->free_result($q);
    echo "
	</table>
	<br />
	<a href='viewguild.php'>Go Back</a>
   	";
}

function staff_kick()
{
    global $db, $userid, $ir, $gd, $api, $h, $wq;
    //Current user is either owner or co-owner
    if ($gd['guild_owner'] == $userid || $gd['guild_coowner'] == $userid) {

        //Verify CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_kickuser", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.", true, '?action=members');
            die($h->endpage());
        }

        //Make sure POST is safe to work with.
        $_POST['ID'] = (isset($_POST['ID']) && is_numeric($_POST['ID'])) ? abs(intval($_POST['ID'])) : 0;
        $who = $_POST['ID'];

        //Trying to kick the owner.
        if ($who == $gd['guild_owner']) {
            alert('danger', "Uh Oh!", "You cannot kick the guild leader.", true, '?action=members');
            //Trying to kick the co-owner
        } else if ($who == $gd['guild_coowner']) {
            alert('danger', "Uh Oh!", "You cannot kick the guild co-leader.", true, '?action=members');
            //Trying to kick themselves.
        } else if ($who == $userid) {
            alert('danger', "Uh Oh!", "You cannot kick yourself from the guild.", true, '?action=members');
            //Trying to kick while at war.
        } else if ($db->fetch_single($wq) > 0) {
            alert('danger', "Uh Oh!", "You cannot kick members from your guild while you are at war.", true, '?action=members');
        } else {
            //User to be kicked exists and is in the guild.
            $q = $db->query("SELECT `username` FROM `users` WHERE `userid` = $who AND `guild` = {$gd['guild_id']}");
            if ($db->num_rows($q) > 0) {
                //Kick the user and add the notification.
                $kdata = $db->fetch_row($q);
                $db->query("UPDATE `users` SET `guild` = 0 WHERE `userid` = {$who}");
                $d_username = htmlentities($kdata['username'], ENT_QUOTES, 'ISO-8859-1');
                $d_oname = htmlentities($ir['username'], ENT_QUOTES, 'ISO-8859-1');
                alert('success', "Success!", "You have kicked {$kdata['username']} from the guild.", true, '?action=members');
                $their_event = "You were kicked out of the {$gd['guild_name']} guild by <a href='profile.php?user={$userid}'>{$d_oname}</a>.";
                $api->GameAddNotification($who, $their_event);
                $event = $db->escape("<a href='profile.php?user={$who}'>{$d_username}</a> was kicked out of the guild by <a href='profile.php?user={$userid}'>{$d_oname}</a>.");
                $api->GuildAddNotification($gd['guild_id'], $event);
            } else {
                alert('danger', "Uh Oh!", "User does not exist, or is not in the guild.", true, '?action=members');
            }
            $db->free_result($q);
        }
    } else {
        alert('danger', "Uh Oh!", "You do not have permission to kick people from the guild.", true, 'viewguild.php');
    }
}

function leave()
{
    global $db, $userid, $ir, $gd, $api, $h, $wq;
    //Make sure person leaving is not a guild owner/co-owner.
    if ($gd['guild_owner'] == $userid || $gd['guild_coowner'] == $userid) {
        alert('danger', "Uh Oh!", "You cannot leave the guild as the leader or co-leader.", true, 'viewguild.php');
        die($h->endpage());
    }
    //Player *does* want to leave.
    if (isset($_POST['submit']) && $_POST['submit'] == 'yes') {

        //Verify CSRF Check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_leave", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Make sure no deserters during war times.
        if ($db->fetch_single($wq) > 0) {
            alert('danger', "Uh Oh!", "You cannot leave your guild while at war.", true, 'viewguild.php');
            die($h->endpage());
        }

        //Allow player to leave.
        $db->query("UPDATE `users` SET `guild` = 0  WHERE `userid` = {$userid}");
        $api->GuildAddNotification($ir['guild'], "<a href='profile.php?user={$userid}'>{$ir['username']}</a> has left the guild.");
        alert('success', "Success!", "You have successfully left your guild.", true, 'index.php');

        //Player *does not* want to leave
    } elseif (isset($_POST['submit']) && $_POST['submit'] == 'no') {
        alert('success', "Success!", "You have chosen to stay in your guild.", true, 'viewguild.php');
    } else {
        $csrf = request_csrf_html('guild_leave');
        echo "Do you really want to leave your guild?
        <form action='?action=leave' method='post'>
            {$csrf}
			<input type='hidden' name='submit' value='yes'>
        	<input type='submit' class='btn btn-primary' value='Yes, leave.' />
		</form><br />
		<form action='?action=leave' method='post'>
			{$csrf}
			<input type='hidden' name='submit' value='no'>
        	<input type='submit' class='btn btn-primary' value='No, stay.' />
        </form>
		<a href='viewguild.php'>Go Back</a>";
    }
}

function atklogs()
{
    global $db, $ir, $api;
    //Select the last 50 attacks involving someone in the guild.
    $atks = $db->query("SELECT `l`.*, `u`.`guild`, `u`.`userid`
                        FROM `logs` as `l`
                        INNER JOIN `users` as `u`
                        ON `l`.`log_user` = `u`.`userid`
                        WHERE (`u`.`guild` = {$ir['guild']}) AND log_type = 'attacking'
                        ORDER BY `log_time` DESC
                        LIMIT 50");
    echo "<b>Last 50 attacks involving anyone in your guild</b><br />
	<table class='table table-bordered'>
		<tr>
			<th>Time</th>
			<th>Attack Info</th>
		</tr>";
    while ($r = $db->fetch_row($atks)) {
        $d = DateTime_Parse($r['log_time']);
        echo "<tr>
        		<td>$d</td>
        		<td>
					" . $api->SystemUserIDtoName($r['log_user']) . " {$r['log_text']}
        		</td>
        	  </tr>";
    }
    $db->free_result($atks);
    echo "</table>
	<a href='viewguild.php'>Go Back</a>";
}

function warview()
{
    global $db, $ir, $api;
    //Select all active wars.
    $wq = $db->query("SELECT * FROM `guild_wars` WHERE
					(`gw_declarer` = {$ir['guild']} OR `gw_declaree` = {$ir['guild']}) 
					AND `gw_winner` = 0");
    echo "<b>These are the current wars your guild is participating in.</b><hr />
	<table class='table table-bordered'>
		<tr>
			<th>
				Declarer
			</th>
			<th>
				Declared Upon
			</th>
			<th>
				War Concludes
			</th>
		</tr>";
    while ($r = $db->fetch_row($wq)) {
        echo "<tr>
				<td>
					<a href='guilds.php?action=view&id={$r['gw_declarer']}'>{$api->GuildFetchInfo($r['gw_declarer'],'guild_name')}</a><br />
						(Points: " . number_format($r['gw_drpoints']) . ")
				</td>
				<td>
					<a href='guilds.php?action=view&id={$r['gw_declaree']}'>{$api->GuildFetchInfo($r['gw_declaree'],'guild_name')}</a><br />
						(Points: " . number_format($r['gw_depoints']) . ")
				</td>
				<td>
					" . TimeUntil_Parse($r['gw_end']) . "
				</td>
			</tr>";
    }
    echo "</table>";
}

function armory()
{
    global $db, $gd, $h, $api, $ir;
    //Guild has not purchased the armory
    if ($gd['guild_hasarmory'] == 'false') {
        alert('danger', "Uh Oh!", "Your guild has yet to purchase an armory. Come back after your guild has purchased an armory.", true, 'viewguild.php');
        die($h->endpage());
    } else {
        //List all the armory items.
        echo "Here are the items your guild currently has stockpiled in its armory. You may donate items <a href='?action=adonate'>here</a>.<br />";
        $inv = $db->query("SELECT `gaQTY`, `itmsellprice`, `itmid`, `gaID`,
                             `weapon`, `armor`, `itmtypename`, `itmdesc`
                             FROM `guild_armory` AS `iv`
                             INNER JOIN `items` AS `i`
                             ON `iv`.`gaITEM` = `i`.`itmid`
                             INNER JOIN `itemtypes` AS `it`
                             ON `i`.`itmtype` = `it`.`itmtypeid`
                             WHERE `iv`.`gaGUILD` = {$ir['guild']}
                             ORDER BY `i`.`itmtype` ASC, `i`.`itmname` ASC");
        echo "<table class='table table-bordered table-striped'>
	    <thead>
		<tr>
			<th>
			    Item (Qty)
            </th>
			<th class='hidden-xs-down'>
			    Item Cost (Total)
            </th>
		</tr></thead>";
        $lt = "";
        while ($i = $db->fetch_row($inv)) {
            if ($lt != $i['itmtypename']) {
                $lt = $i['itmtypename'];
                echo "\n<thead><tr>
            			<th colspan='4'>
            				<b>{$lt}</b>
            			</th>
            		</tr></thead>";
            }
            $i['itmdesc'] = htmlentities($i['itmdesc'], ENT_QUOTES);
            echo "
            <tr>
        		<td>
					<a href='iteminfo.php?ID={$i['itmid']}' data-toggle='tooltip' data-placement='right' title='{$i['itmdesc']}'>
						{$api->SystemItemIDtoName($i['itmid'])}
					</a>";
            if ($i['gaQTY'] > 1) {
                echo " (" . number_format($i['gaQTY']) . ")";
            }
            echo "</td>
        	  <td class='hidden-xs-down'>" . number_format($i['itmsellprice']);
            echo "  (" . number_format($i['itmsellprice'] * $i['gaQTY']) . ")</td></tr>";
        }
        echo "</table>";
    }
}

function adonate()
{
    global $api, $userid, $h, $ir, $gd;
    if ($gd['guild_hasarmory'] == 'false') {
        alert('danger', "Uh Oh!", "Your guild has yet to purchase an armory. Come back after your guild has purchased an armory.", true, 'viewguild.php');
        die($h->endpage());
    } else {
        if (isset($_POST['item'])) {
            //Secure the POST
            $_POST['item'] = (isset($_POST['item']) && is_numeric($_POST['item'])) ? abs($_POST['item']) : 0;
            $_POST['qty'] = (isset($_POST['qty']) && is_numeric($_POST['qty'])) ? abs($_POST['qty']) : 0;

            //Verify CSRF Check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_armory_donate", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }

            //Verify item exists
            if (!$api->SystemItemIDtoName($_POST['item'])) {
                alert('danger', "Uh Oh!", "You are trying to donate a non-existent item.");
                die($h->endpage());
            }

            //Verify user has the item/quantity
            if (!$api->UserHasItem($userid, $_POST['item'], $_POST['qty'])) {
                alert('danger', "Uh Oh!", "You are trying to donate an item you don't have, or an amount you don't have.");
                die($h->endpage());
            }

            //Donation successful!, log everything.
            $item = $api->SystemItemIDtoName($_POST['item']);
            $api->UserTakeItem($userid, $_POST['item'], $_POST['qty']);
            $api->GuildAddItem($userid, $_POST['item'], $_POST['qty']);
            $api->SystemLogsAdd($userid, 'guilds', "Donated {$_POST['qty']} {$item}(s) to their guild's armory.");
            $api->GuildAddNotification($ir['guild'], "{$ir['username']} has donated {$_POST['qty']} {$item}(s) to the guild's armory.");
            alert("success", "Success!", "You have successfully donated {$_POST['qty']} $item}(s) to your guild's armory.", true, "?action=armory");
        } else {
            $csrf = request_csrf_html('guild_armory_donate');
            echo "<form method='post'>
            Fill out the form completely to donate an item to your guild.<br />
            " . inventory_dropdown() . "<br />
            <input type='number' name='qty' placeholder='Quantity' class='form-control'>
            <br />
            {$csrf}
            <input type='submit' value='Donate Item' class='btn btn-primary'>
            </form>";
        }
    }
}

function crimes()
{
    global $gd, $db;
    if ($gd['guild_crime'] > 0) {
        $ttc = TimeUntil_Parse($gd['guild_crime_done']);
        $gcname = $db->fetch_single($db->query("SELECT `gcNAME` from `guild_crimes` WHERE `gcID` = {$gd['guild_crime']}"));
        echo "Your guild will be attempting to commit the {$gcname} crime. It will begin in {$ttc}.";
    } else {
        echo "Your guild is not currently planning on committing a crime. Contact your guild's leadership to stage one.";
    }
}

function staff()
{
    global $userid, $gd, $h;
    if ($gd['guild_owner'] == $userid || $gd['guild_coowner'] == $userid) {
        if (!isset($_GET['act2'])) {
            $_GET['act2'] = 'idx';
        }
        switch ($_GET['act2']) {
            case "idx":
                staff_idx();
                break;
            case "apps":
                staff_apps();
                break;
            case "vault":
                staff_vault();
                break;
            case "coowner":
                staff_coowner();
                break;
            case "ament":
                staff_announcement();
                break;
            case "massmail":
                staff_massmail();
                break;
            case "masspay":
                staff_masspayment();
                break;
            case "desc":
                staff_desc();
                break;
            case "leader":
                staff_leader();
                break;
            case "name":
                staff_name();
                break;
            case "town":
                staff_town();
                break;
            case "untown":
                staff_untown();
                break;
            case "declarewar":
                staff_declare();
                break;
            case "levelup":
                staff_levelup();
                break;
            case "tax":
                staff_tax();
                break;
            case "dissolve":
                staff_dissolve();
                break;
            case "armory":
                staff_armory();
                break;
            case "crimes":
                staff_crimes();
                break;
            default:
                staff_idx();
                break;
        }
    } else {
        alert('danger', "Uh Oh!", "You have no permission to be here.", true, 'viewguild.php');
        die($h->endpage());
    }
}

function staff_idx()
{
    global $db, $userid, $gd;
    echo "<table class='table table-bordered'>
	<tr>
		<td>
			<b>Guild Co-Leader</b><br />
			<a href='?action=staff&act2=apps'>Application Management</a><br />
			<a href='?action=staff&act2=vault'>Vault Management</a><br />
			<a href='?action=staff&act2=armory'>Armory Management</a><br />
			<a href='?action=staff&act2=coowner'>Transfer Co-Leader</a><br />
			<a href='?action=staff&act2=ament'>Change Guild Announcement</a><br />
			<a href='?action=staff&act2=massmail'>Mass Mail Guild</a><br />
			<a href='?action=staff&act2=masspay'>Mass Pay Guild</a><br />
			<a href='?action=staff&act2=levelup'>Level Up Guild</a><br />
			<a href='?action=staff&act2=crimes'>Guild Crimes</a><br />
		</td>";
    if ($gd['guild_owner'] == $userid) {
        echo "
		<td>
			<b>Guild Leader</b><br />
			<a href='?action=staff&act2=leader'>Transfer Leader</a><br />
			<a href='?action=staff&act2=name'>Change Guild Name</a><br />
			<a href='?action=staff&act2=desc'>Change Guild Description</a><br />
			<a href='?action=staff&act2=town'>Change Guild Town</a><br />";
        if ($db->fetch_single($db->query("SELECT `town_id` FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}")) > 0) {
            echo "<a href='?action=staff&act2=untown'>Surrender Guild Town</a><br />
				<a href='?action=staff&act2=tax'>Change Town Tax</a><br />";
        }
        echo "<a href='?action=staff&act2=declarewar'>Declare War</a><br />
<a href='?action=staff&act2=dissolve'>Dissovle Guild</a><br />
		</td>";
    }
    echo "</tr></table>
	<a href='viewguild.php'>Go Back</a>";
}

function staff_apps()
{
    global $db, $userid, $ir, $gd, $api, $h;
    $_POST['app'] = (isset($_POST['app']) && is_numeric($_POST['app'])) ? abs(intval($_POST['app'])) : '';
    $what = (isset($_POST['what']) && in_array($_POST['what'], array('accept', 'decline'), true)) ? $_POST['what'] : '';
    if (!empty($_POST['app']) && !empty($what)) {

        //Verify that the CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_apps", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Verify the application exists and belongs to this guild.
        $aq = $db->query("SELECT `ga_user`
                         FROM `guild_applications`
                         WHERE `ga_id` = {$_POST['app']}
                         AND `ga_guild` = {$gd['guild_id']}");

        //Application does exist and belong to this guild.
        if ($db->num_rows($aq) > 0) {
            $appdata = $db->fetch_row($aq);

            //User declines the application. Delete the application, and alert the applicant they were declined.
            if ($what == 'decline') {
                $db->query("DELETE FROM `guild_applications` WHERE `ga_id` = {$_POST['app']}");
                $api->GameAddNotification($appdata['ga_user'], "We regret to inform you that your application to join the {$gd['guild_name']} guild was declined.");
                $event = $db->escape("<a href='profile.php?user={$userid}'>{$ir['username']}</a> has declined
                                        <a href='profile.php?user={$appdata['ga_user']}'>
                                        " . $api->SystemUserIDtoName($appdata['ga_user']) . "</a>'s  application to join
                                         the guild.");
                //Add to guild notifications.
                $api->GuildAddNotification($gd['guild_id'], $event);
                alert('success', "Success!", "You have denied " . $api->SystemUserIDtoName($appdata['ga_user']) . "'s application to join the guild.'");
            } else {
                //User is accepted, yay!

                //Make sure the guild has enough capacity to accept this member.
                $cnt = $db->query("SELECT COUNT(`userid`) FROM `users` WHERE `guild` = {$gd['guild_id']}");

                //Guild does not have enough capacity to accept another member.s
                if ($gd['guild_capacity'] <= $db->fetch_single($cnt)) {
                    $db->free_result($cnt);
                    alert('danger', "Uh Oh!", "Your guild does not have the capacity for another member. Please level up your guild.");
                    die($h->endpage());

                    //Applicant has joined another guild. =/
                } else if ($api->UserInfoGet($appdata['ga_user'], 'guild') != 0) {
                    $db->free_result($cnt);
                    alert('danger', "Uh Oh!", "The applicant has already joined another guild.");
                    die($h->endpage());
                }

                //Select the town level if the guild's got one.
                $townlevel = $db->fetch_single($db->query("SELECT `town_min_level` FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}"));

                //Applicant cannot reach the town the guild owns.
                if ($townlevel > $api->UserInfoGet($appdata['ga_user'], 'level') && $townlevel > 0) {
                    alert('danger', "Uh Oh!", "The applicant cannot reach your guild's town because their level is too low.");
                    die($h->endpage());
                }
                $db->free_result($cnt);

                //Delete the application and put the applicant inside the guild! Woo!
                $db->query("DELETE FROM `guild_applications` WHERE `ga_id` = {$_POST['app']}");
                $api->GameAddNotification($appdata['ga_user'], "Your application to join the {$gd['guild_name']} guild was accepted.");
                $event = "<a href='profile.php?user={$userid}'>{$ir['username']}</a>
									has accepted <a href='profile.php?user={$appdata['ga_user']}'>
									" . $api->SystemUserIDtoName($appdata['ga_user']) . "</a>'s 
									application to join the guild.";
                $api->GuildAddNotification($gd['guild_id'], $event);
                $db->query("UPDATE `users` SET `guild` = {$gd['guild_id']} WHERE `userid` = {$appdata['ga_user']}");
                alert('success', "Success!", "You have accepted " . $api->SystemUserIDtoName($appdata['ga_user']) . "'s applicantion to join the guild.");
            }
        } else {
            alert('danger', "Uh Oh!", "You are trying to accept a non-existent application.");
        }
        $db->free_result($aq);
    } else {
        echo "
        <b>Application Management</b>
        <br />
        <table class='table table-bordered table-striped'>
        		<tr>
        			<th>Filing Time</th>
        			<th>Applicant</th>
					<th>Level</th>
        			<th>Application</th>
        			<th>Actions</th>
        		</tr>
   		";
        $q =
            $db->query(
                "SELECT *
                         FROM `guild_applications`
                         WHERE `ga_guild` = {$gd['guild_id']}
						 ORDER BY `ga_time` DESC");
        $csrf = request_csrf_html('guild_staff_apps');
        while ($r = $db->fetch_row($q)) {
            $r['ga_text'] = htmlentities($r['ga_text'], ENT_QUOTES, 'ISO-8859-1', false);
            echo "
            <tr>
            	<td>
					" . DateTime_Parse($r['ga_time']) . "
            	</td>
            	<td>
					<a href='profile.php?user={$r['ga_user']}'>" . $api->SystemUserIDtoName($r['ga_user']) . "</a>
            		[{$r['ga_user']}]
				</td>
            	<td>
					" . $api->UserInfoGet($r['ga_user'], 'level') . "
				</td>
				<td>
					{$r['ga_text']}
				</td>
            	<td>
            		<form action='?action=staff&act2=apps' method='post'>
            			<input type='hidden' name='app' value='{$r['ga_id']}' />
            			<input type='hidden' name='what' value='accept' />
            			{$csrf}
            			<input class='btn btn-success' type='submit' value='Accept' />
            		</form>
					<br />
            		<form action='?action=staff&act2=apps' method='post'>
            			<input type='hidden' name='app' value='{$r['ga_id']}' />
            			<input type='hidden' name='what' value='decline' />
            			{$csrf}
            			<input class='btn btn-danger' type='submit' value='Decline' />
            		</form>
            	</td>
            </tr>
               ";
        }
        echo "</table>";
    }
}

function staff_vault()
{
    global $db, $userid, $gd, $api, $h, $wq;
    if (isset($_POST['primary']) || isset($_POST['secondary'])) {

        //Verify CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_vault", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }
        //Make sure the POST is safe to work with.
        $_POST['primary'] = (isset($_POST['primary']) && is_numeric($_POST['primary'])) ? abs($_POST['primary']) : 0;
        $_POST['secondary'] = (isset($_POST['secondary']) && is_numeric($_POST['secondary'])) ? abs($_POST['secondary']) : 0;
        $_POST['user'] = (isset($_POST['user']) && is_numeric($_POST['user'])) ? abs($_POST['user']) : 0;

        //Attempting to give more primary currency than the guild currently has.
        if ($_POST['primary'] > $gd['guild_primcurr']) {
            alert('danger', "Uh Oh!", "You are trying to give out more Primary Currency than your guild has in its vault.");
            die($h->endpage());
        }

        //Attempting to give more secondary currency than the guild currently has.
        if ($_POST['secondary'] > $gd['guild_seccurr']) {
            alert('danger', "Uh Oh!", "You are trying to give out more Secondary Currency than your guild has in its vault.");
            die($h->endpage());
        }

        //Didn't fill out how much currency they wanted to give out.
        if ($_POST['primary'] == 0 && $_POST['secondary'] == 0) {
            alert('danger', "Uh Oh!", "Please fill out the form completely before submitting.");
            die($h->endpage());
        }

        //Recipient is on the same IP Address as the sender... stop.
        if ($api->SystemCheckUsersIPs($userid, $_POST['user'])) {
            alert('danger', "Uh Oh!", "You cannot give from the guild's vault if you share the same IP address as the recipient.");
            die($h->endpage());
        }

        //Check that the user to receive the cash is in the guild and/or exists.
        $q = $db->query("SELECT `username` FROM `users` WHERE `userid` = {$_POST['user']} AND `guild` = {$gd['guild_id']}");
        if ($db->num_rows($q) == 0) {
            alert('danger', "Uh Oh!", "You are trying to give to a user that does not exist, or is not in the guild.");
            die($h->endpage());
        }

        //Do not allow the transaction to continue if at war.
        if ($db->fetch_single($wq) > 0) {
            alert('danger', "Uh Oh!", "You cannot withdraw from your guild's vault while at war.");
            die($h->endpage());
        }
        $db->free_result($q);
        //Give the currency and log everything.
        $api->UserGiveCurrency($_POST['user'], 'primary', $_POST['primary']);
        $api->UserGiveCurrency($_POST['user'], 'secondary', $_POST['secondary']);
        $db->query("UPDATE `guild` SET `guild_primcurr` = `guild_primcurr` - {$_POST['primary']},
                      `guild_seccurr` = `guild_seccurr` - {$_POST['secondary']} WHERE `guild_id` = {$gd['guild_id']}");
        $api->GameAddNotification($_POST['user'], "You were given " . number_format($_POST['primary']) . " Primary
            Currency and/or " . number_format($_POST['secondary']) . " Secondary Currency from your guild's vault.");
        $api->GuildAddNotification($gd['guild_id'], "<a href='profile.php?user={$userid}'>
            {$api->SystemUserIDtoName($userid)}</a> has given <a href='profile.php?user={$_POST['user']}'>
            {$api->SystemUserIDtoName($_POST['user'])}</a> " . number_format($_POST['primary']) . "
            Primary Currency and/or " . number_format($_POST['secondary']) . " Secondary Currency from the guild's
            vault.");
        alert('success', "Success!", "You have given {$api->SystemUserIDtoName($_POST['user'])} ", true, '?action=staff&act2=idx');
        $api->SystemLogsAdd($userid, "guild_vault", "Gave <a href='profile.php?user={$_POST['user']}'>{$api->SystemUserIDtoName($_POST['user'])}</a> " . number_format($_POST['primary']) . " Primary Currency and/or " . number_format($_POST['secondary']) . " Secondary Currency from their guild's vault.");
    } else {
        $csrf = request_csrf_html('guild_staff_vault');
        echo "<form method='post'>
        <table class='table table-bordered'>
            <tr>
                <th colspan='2'>
                    You may give out currency from your guild's vault. Your vault currently has " . number_format($gd['guild_primcurr']) . " Primary Currency and
                    " . number_format($gd['guild_seccurr']) . " Secondary Currency.
                </th>
            </tr>
            <tr>
                <th>
                    User
                </th>
                <td>
                    " . guild_user_dropdown('user', $gd['guild_id']) . "
                </td>
            </tr>
            <tr>
                <th>
                    Primary Currency
                </th>
                <td>
                    <input type='number' class='form-control' min='0' max='{$gd['guild_primcurr']}' name='primary'>
                </td>
            </tr>
            <tr>
                <th>
                    Secondary Currency
                </th>
                <td>
                    <input type='number' class='form-control' min='0' max='{$gd['guild_seccurr']}' name='secondary'>
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <input type='submit' class='btn btn-primary' value='Give'>
                </td>
            </tr>
            {$csrf}
        </table>
        </form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
    }

}

function staff_coowner()
{
    global $db, $userid, $api, $h, $gd;
    if (isset($_POST['user'])) {

        //Verify CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_coleader", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Make sure POST is safe to work with.
        $_POST['user'] = (isset($_POST['user']) && is_numeric($_POST['user'])) ? abs($_POST['user']) : 0;

        //Verify the user chosen is existent and is in the guild.
        $q = $db->query("SELECT `userid`, `username` FROM `users` WHERE `userid` = {$_POST['user']} AND `guild` = {$gd['guild_id']}");
        if ($db->num_rows($q) == 0) {
            $db->free_result($q);
            alert('danger', "Uh Oh!", "You cannot give co-leadership abilities to someone that does not exist, or is not
			    in the guild.");
            die($h->endpage());
        }
        $db->free_result($q);

        //Update the guild's leader.
        $db->query("UPDATE `guild` SET `guild_coowner` = {$_POST['user']} WHERE `guild_id` = {$gd['guild_id']}");
        $api->GameAddNotification($_POST['user'], "<a href='profile.php?user={$userid}'>{$api->SystemUserIDtoName($userid)}</a> has transferred you co-leader privileges for the {$gd['guild_name']} guild.");
        $api->GuildAddNotification($gd['guild_id'], "<a href='profile.php?user={$userid}'>{$api->SystemUserIDtoName($userid)}</a> has transferred co-leader privileges to <a href='profile.php?user={$_POST['user']}'>{$api->SystemUserIDtoName($_POST['user'])}</a>.");
        alert('success', "Success!", "You have successfully transferred co-leadership privileges to {$api->SystemUserIDtoName($_POST['user'])}.", true, '?action=staff&act2=idx');
    } else {
        $csrf = request_csrf_html('guild_staff_coleader');
        echo "<form method='post'>
		<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					Select the user you wish to give your co-leadership privileges to.
				</th>
			</tr>
			<tr>
				<th>
					User
				</th>
				<td>
					" . guild_user_dropdown('user', $gd['guild_id'], $gd['guild_coowner']) . "
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' class='btn btn-primary' value='Transfer Co-Leader'>
				</td>
			</tr>
			{$csrf}
		</table>
		</form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
    }
}

function staff_announcement()
{
    global $gd, $db, $h;
    if (isset($_POST['ament'])) {

        //Verify CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_ament", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Make sure the POST is safe to work with.
        $ament = $db->escape(nl2br(htmlentities(stripslashes($_POST['ament']), ENT_QUOTES, 'ISO-8859-1')));

        //Update the guild's announcement.
        $db->query("UPDATE `guild` SET `guild_announcement` = '{$ament}' WHERE `guild_id` = {$gd['guild_id']}");
        alert('success', "Success!", "You have updated your guild's announcement.", true, '?action=staff&act2=idx');
    } else {
        //Escape the announcement for safety reasons.
        $am_for_area = strip_tags($gd['guild_announcement']);
        $csrf = request_csrf_html('guild_staff_ament');
        echo "<form method='post'>
		<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					You may change your guild's announcement here.
				</th>
			</tr>
			<tr>
				<th>
					Announcement
				</th>
				<td>
					<textarea class='form-control' name='ament'>{$am_for_area}</textarea>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' value='Change Announcement' class='btn btn-primary'>
				</td>
			</tr>
			{$csrf}
		</table>
		</form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
    }
}

function staff_massmail()
{
    global $db, $api, $userid, $h, $gd;
    if (isset($_POST['text'])) {

        //Verify the CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_massmail", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Escape the message.
        $_POST['text'] = (isset($_POST['text'])) ? $db->escape(htmlentities(stripslashes($_POST['text']), ENT_QUOTES, 'ISO-8859-1')) : '';
        $subj = 'Guild Mass Mail';
        $q = $db->query("SELECT `userid` FROM `users` WHERE `guild` = {$gd['guild_id']}");
        //Send the mail out to everyone in the guild.
        while ($r = $db->fetch_row($q)) {
            $api->GameAddMail($r['userid'], $subj, $_POST['text'], $userid);
        }
        alert('success', "Success!", "Mass mail has been sent successfully.", true, '?action=staff&act2=idx');
    } else {
        $csrf = request_csrf_html('guild_staff_massmail');
        echo "<form method='post'>
		<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					Use this form to send a mass mail to each member of your guild.
				</th>
			</tr>
			<tr>
				<th>
					Message
				</th>
				<td>
					<textarea class='form-control' name='text' rows='7'></textarea>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' value='Mass Mail' class='btn btn-primary'>
				</td>
			</tr>
			{$csrf}
		</table>
		</form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
    }
}

function staff_masspayment()
{
    global $db, $api, $userid, $gd, $h, $wq;
    if (isset($_POST['payment'])) {

        //Verify the CSRF check has passed.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_masspay", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Make sure the POST is safe to work with.
        $_POST['payment'] = (isset($_POST['payment']) && is_numeric($_POST['payment'])) ? abs($_POST['payment']) : 0;
        $cnt = $db->fetch_single($db->query("SELECT COUNT(`userid`) FROM `users` WHERE `guild` = {$gd['guild_id']}"));

        //Make sure there's enough primary currency to pay each member of the guild the amount specified.
        if (($_POST['payment'] * $cnt) > $gd['guild_primcurr']) {
            alert('danger', "Uh Oh!", "You do not have enough currency in your vault to give out that much to each member.");
            die($h->endpage());

            //Check that the guild is not at war.
        } else if ($db->fetch_single($wq) > 0) {
            alert('danger', "Uh Oh!", "You cannot mass pay your guild while at war.");
            die($h->endpage());
        } else {
            $q = $db->query("SELECT `userid`, `username` FROM `users` WHERE `guild` = {$gd['guild_id']}");
            //Pay each member.
            while ($r = $db->fetch_row($q)) {
                //User shares an IP with the user being paid... stop this.
                if ($api->SystemCheckUsersIPs($userid, $r['userid'])) {
                    alert('danger', "Uh Oh!", "{$r['username']} could not receive their Mass Payment because they share an IP Address with you.");
                } else {
                    //Pay everyone.
                    $gd['guild_primcurr'] -= $_POST['payment'];
                    $api->GameAddNotification($r['userid'], "You were given a mass-payment of {$_POST['payment']} Primary Currency from your guild.");
                    $api->UserGiveCurrency($r['userid'], 'primary', $_POST['payment']);
                    alert('success', "Success!", "{$r['username']} was paid {$_POST['payment']} Primary Currency.");
                }
            }
            //Notify the user of the success and log everything.
            $db->query("UPDATE `guild` SET `guild_primcurr` = {$gd['guild_primcurr']} WHERE `guild_id` = {$gd['guild_id']}");
            $notif = $db->escape("A mass payment of " . number_format($_POST['payment']) . " Primary Currency was sent out to the members of the guild.");
            $api->GuildAddNotification($gd['guild_id'], $notif);
            $api->SystemLogsAdd($userid, 'guilds', "Sent a mass payment of " . number_format($_POST['payment']) . "to their guild.");
            alert('success', "Success!", "Mass payment complete.", true, '?action=staff&act2=idx');
        }
    } else {
        $csrf = request_csrf_html('guild_staff_masspay');
        echo "<form method='post'>
		<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					You can pay each and every member of your guild using this form
				</th>
			</tr>
			<tr>
				<th>
					Payment
				</th>
				<td>
					<input type='number' min='1' max='{$gd['guild_primcurr']}' class='form-control' name='payment'>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' value='Mass Pay' class='btn btn-primary'>
				</td>
			</tr>
			{$csrf}
		</table>
		</form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
    }
}

function staff_desc()
{
    global $gd, $db, $userid, $h;
    //Verify the current user is the guild owner.
    if ($userid == $gd['guild_owner']) {
        if (isset($_POST['desc'])) {

            //Verify CSRF check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_desc", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }

            //Make sure the POST is safe to work with.
            $desc = $db->escape(nl2br(htmlentities(stripslashes($_POST['desc']), ENT_QUOTES, 'ISO-8859-1')));

            //Update guild's description.
            $db->query("UPDATE `guild` SET `guild_desc` = '{$desc}' WHERE `guild_id` = {$gd['guild_id']}");
            alert('success', "Success!", "You have updated your guild's description", true, '?action=staff&act2=idx');
        } else {
            //Escape the description for safety reasons.
            $am_for_area = strip_tags($gd['guild_desc']);
            $csrf = request_csrf_html('guild_staff_desc');
            echo "<form method='post'>
			<table class='table table-bordered'>
				<tr>
					<th colspan='2'>
						You can use this form to change your guild's description.
					</th>
				</tr>
				<tr>
					<th>
						Description
					</th>
					<td>
						<textarea class='form-control' name='desc' rows='7'>{$am_for_area}</textarea>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' value='Update Description' class='btn btn-primary'>
					</td>
				</tr>
				{$csrf}
			</table>
			</form>
			<a href='?action=staff&act2=idx'>Go Back</a>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.");
    }
}

function staff_leader()
{
    global $gd, $db, $userid, $api, $h;
    //Verify the current user is the guild owner.
    if ($userid == $gd['guild_owner']) {
        if (isset($_POST['user'])) {

            //Verify CSRF check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_leader", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }

            //Make the POST safe to work with it.
            $_POST['user'] = (isset($_POST['user']) && is_numeric($_POST['user'])) ? abs($_POST['user']) : 0;

            //Select the user from database.
            $q = $db->query("SELECT `userid`, `username` FROM `users` WHERE `userid` = {$_POST['user']} AND `guild` = {$gd['guild_id']}");

            //User does not exist, or is not in the guild.
            if ($db->num_rows($q) == 0) {
                $db->free_result($q);
                alert('danger', "Uh Oh!", "You cannot give leadership privileges to a user not in your guild, or that doesn't exist.");
                die($h->endpage());
            }
            $db->free_result($q);

            //Update the guild's leader and log everything.
            $db->query("UPDATE `guild` SET `guild_coowner` = {$_POST['user']} WHERE `guild_id` = {$gd['guild_id']}");
            $api->GameAddNotification($_POST['user'], "<a href='profile.php?user={$userid}'>{$api->SystemUserIDtoName($userid)}</a> has transferred you leader privileges for the {$gd['guild_name']} guild.");
            $api->GuildAddNotification($gd['guild_id'], "<a href='profile.php?user={$userid}'>{$api->SystemUserIDtoName($userid)}</a> has transferred leader privileges to <a href='profile.php?user={$_POST['user']}'>{$api->SystemUserIDtoName($_POST['user'])}</a>.");
            alert('success', "Success!", "You have transferred your leadership privileges over to {$api->SystemUserIDtoName($_POST['user'])}.", true, '?action=staff&act2=idx');
        } else {
            $csrf = request_csrf_html('guild_staff_leader');
            echo "<form method='post'>
		<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					Select a user to give them your leadership privileges.
				</th>
			</tr>
			<tr>
				<th>
					User
				</th>
				<td>
					" . guild_user_dropdown('user', $gd['guild_id'], $gd['guild_owner']) . "
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' class='btn btn-primary' value='Transfer Leader'>
				</td>
			</tr>
			{$csrf}
		</table>
		</form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }
}

function staff_name()
{
    global $gd, $db, $userid, $h;

    //Verify the current user is the guild owner.
    if ($userid == $gd['guild_owner']) {
        if (isset($_POST['name'])) {

            //Check that the CSRF check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_name", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }
            //Make sure the POST is safe to work with.
            $name = $db->escape(nl2br(htmlentities(stripslashes($_POST['name']), ENT_QUOTES, 'ISO-8859-1')));

            //Select guilds with the same name.
            $cnt = $db->query("SELECT `guild_id` FROM `guild` WHERE `guild_name` = '{$name}' AND `guild_id` != {$gd['guild_id']}");

            //If there's a guild with the same name, disallow the name change.
            if ($db->num_rows($cnt) > 0) {
                alert('danger', "Uh Oh!", "The name you have chosen is already in use by another guild.");
                die($h->endpage());
            }

            //Update the guild's name.
            $db->query("UPDATE `guild` SET `guild_name` = '{$name}' WHERE `guild_id` = {$gd['guild_id']}");
            alert('success', "Success!", "You have changed your guild's name to {$name}.", true, '?action=staff&act2=idx');
        } else {
            $am_for_area = strip_tags($gd['guild_name']);
            $csrf = request_csrf_html('guild_staff_name');
            echo "<form method='post'>
			<table class='table table-bordered'>
				<tr>
					<th colspan='2'>
						You can change your guild's name here.
					</th>
				</tr>
				<tr>
					<th>
						Name
					</th>
					<td>
						<input type='text' required='1' value='{$am_for_area}' class='form-control' name='name'>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' value='Change Guild Name' class='btn btn-primary'>
					</td>
				</tr>
				{$csrf}
			</table>
			</form>
			<a href='?action=staff&act2=idx'>Go Back</a>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }
}

function staff_town()
{
    global $db, $gd, $api, $h, $userid, $wq;

    //Verify current user is the guild owner.
    if ($userid == $gd['guild_owner']) {
        if (isset($_POST['town'])) {

            //Verify CSRF
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_town", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }
            //Make sure POST is safe to work with.
            $town = (isset($_POST['town']) && is_numeric($_POST['town'])) ? abs($_POST['town']) : 0;

            //Make sure current guild doesn't already have a town.
            $cnt = $db->fetch_single($db->query("SELECT COUNT(`town_id`) FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}"));
            if ($cnt > 0) {
                alert('danger', "Uh Oh!", "Your guild already owns a town. Surrender your current town to own a new one.");
                die($h->endpage());
            }

            //Make sure town claimed exists.
            if ($db->num_rows($db->query("SELECT `town_id` FROM `town` WHERE `town_id` = {$town}")) == 0) {
                alert('danger', "Uh Oh!", "The town you wish to own does not exist.");
                die($h->endpage());
            }

            //Check to see if the town is unowned.
            if ($db->fetch_single($db->query("SELECT `town_guild_owner` FROM `town` WHERE `town_id` = {$town}")) > 0) {
                alert('danger', "Uh Oh!", "The town you wish to own is already owned by another guild. If you want this town, declare war on them!");
                die($h->endpage());
            }

            //Check to see if current guild is at war, if so, stop them.
            if ($db->fetch_single($wq) > 0) {
                alert('danger', "Uh Oh!", "You may not change your guild's town while at war.");
                die($h->endpage());
            }
            $lowestlevel = $db->fetch_single($db->query("SELECT `level` FROM `users` WHERE `guild` = {$gd['guild_id']} ORDER BY `level` ASC LIMIT 1"));
            $townlevel = $db->fetch_single($db->query("SELECT `town_min_level` FROM `town` WHERE `town_id` = {$town}"));

            //Verify that everyone in the guild can reach the city.
            if ($townlevel > $lowestlevel) {
                alert('danger', "Uh Oh!", "You cannot own this town as there are members in your guild who cannot access it.");
                die($h->endpage());
            }

            //Update everything. City is now the guild's.
            $db->query("UPDATE `town` SET `town_guild_owner` = {$gd['guild_id']} WHERE `town_id` = {$town}");
            $api->GuildAddNotification($gd['guild_id'], "Your guild has successfully claimed {$api->SystemTownIDtoName($town)}.");
            alert('success', "Success!", "You have successfully claimed {$api->SystemTownIDtoName($town)} for your guild.", true, '?action=staff&act2=idx');
        } else {
            $csrf = request_csrf_html('guild_staff_town');
            echo "
			<form method='post'>
				<table class='table table-bordered'>
					<tr>
						<th colspan='2'>
							You can claim a town for your guild here. This town must be unowned, and must be accessible
							to all your guild members. If it is currently owned, you must declare war on the owning
							guild to get a chance to claim the town as yours.
						</th>
					</tr>
					<tr>
						<th>
							Town
						</th>
						<td>
							" . location_dropdown('town') . "
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							<input type='submit' value='Claim Town' class='btn btn-primary'>
						</td>
					</tr>
					{$csrf}
				</table>
			</form>
			<a href='?action=staff&act2=idx'>Go Back</a>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }
}

function staff_untown()
{
    global $db, $gd, $api, $h, $userid, $wq;
    //Verify current user is the guild's owner.
    if ($userid == $gd['guild_owner']) {

        //Check to be sure the guild has a town under their control
        $townowned = $db->query("SELECT `town_id` FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}");
        if ($db->num_rows($townowned) == 0) {
            alert('danger', "Uh Oh!", "Your guild doesn't have a town to surrender.", true, '?action=staff&act2=idx');
            die($h->endpage());

            //Check that the guild is not at war.
        } else if ($db->fetch_single($wq) > 0) {
            alert('danger', "Uh Oh!", "You cannot surrender your town while at war.", true, '?action=staff&act2=idx');
            die($h->endpage());
        } elseif (isset($_POST['confirm'])) {

            //Surrender the town.
            $r = $db->fetch_single($townowned);
            alert('success', "Success!", "You have surrendered your guild's town.", true, '?action=staff&act2=idx');
            $db->query("UPDATE `town`
                        SET `town_guild_owner` = 0
                        WHERE `town_id` = {$r}");
            $api->GuildAddNotification($gd['guild_id'], "Your guild was willingly given up their town.");
            $api->SystemLogsAdd($userid, 'guilds', "Willingly surrendered {$gd['guild_name']}'s town, {$api->SystemTownIDtoName($r)}.");
        } else {
            echo "Are you sure you wish to surrender your guild's town? This is not reversible.<br />
			<form method='post'>
				<input type='hidden' name='confirm' value='yes'>
				<input type='submit' class='btn btn-success' value='Yes'>
			</form>
			<a href='?action=staff&act2=idx'>Go Back</a>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }
}

function staff_declare()
{
    global $db, $gd, $api, $h, $userid, $ir;
    //Verify current user is the guild owner.
    if ($userid == $gd['guild_owner']) {
        if (isset($_POST['guild'])) {

            //Verify POST is safe to work with.
            $_POST['guild'] = (isset($_POST['guild']) && is_numeric($_POST['guild'])) ? abs($_POST['guild']) : 0;

            //Verify CSRF check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_declarewar", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }

            //Check if the declared guild is the current guild, and stop them if that's the case.
            if ($_POST['guild'] == $gd['guild_id']) {
                alert('danger', "Uh Oh!", "You cannot declare war on your own guild.");
                die($h->endpage());
            }

            //Verify that the declared guild exists.
            $data_q = $db->query("SELECT `guild_name`,`guild_owner`
                                  FROM `guild`
                                  WHERE `guild_id` = {$_POST['guild']}");
            if ($db->num_rows($data_q) == 0) {
                $db->free_result($data_q);
                alert('danger', "Uh Oh!", "You cannot declare war on a non-existent guild.");
                die($h->endpage());
            }

            //Make sure the two guilds are not at war already.
            $time = time();
            $iswarredon = $db->query("SELECT `gw_id`
                                      FROM `guild_wars`
                                      WHERE `gw_declarer` = {$gd['guild_id']}
                                      AND `gw_declaree` = {$_POST['guild']}
                                      AND `gw_end` > {$time}");
            if ($db->num_rows($iswarredon) > 0) {
                alert('danger', "Uh Oh!", "You cannot declare war on this guild as you are already at war!");
                die($h->endpage());
            }

            //Make sure the two guilds are not at war already.
            $iswarredon1 = $db->query("SELECT `gw_id`
                                        FROM `guild_wars`
                                        WHERE `gw_declaree` = {$gd['guild_id']}
                                        AND `gw_declarer` = {$_POST['guild']}
                                        AND `gw_end` > {$time}");
            if ($db->num_rows($iswarredon1) > 0) {
                alert('danger', "Uh Oh!", "You cannot declare war on this guild as you are already at war!");
                die($h->endpage());
            }

            //Check to see if its been a week since the last war.
            $lastweek = $time - 604800;
            $istoosoon = $db->fetch_single($db->query("SELECT `gw_end`
                                                        FROM `guild_wars`
                                                        WHERE `gw_declarer` = {$gd['guild_id']}
                                                        AND `gw_declaree` = {$_POST['guild']}
                                                        ORDER BY `gw_id`DESC
                                                        LIMIT 1"));
            if ($istoosoon > $lastweek) {
                alert('danger', "Uh Oh!", "You cannot declare war on this guild as its been less than a week since the last war concluded.");
                die($h->endpage());
            }

            //Check to see if its been a week since the last war.
            $istoosoon1 = $db->fetch_single($db->query("SELECT `gw_end`
                                                        FROM `guild_wars`
                                                        WHERE `gw_declaree` = {$gd['guild_id']}
                                                        AND `gw_declarer` = {$_POST['guild']}
                                                        ORDER BY `gw_id` DESC
                                                        LIMIT 1"));
            if ($istoosoon1 > $lastweek) {
                alert('danger', "Uh Oh!", "You cannot declare war on this guild as its been less than a week since the last war concluded.");
                die($h->endpage());
            }
            $yourcount = $db->query("SELECT `userid` FROM `users` WHERE `guild` = {$ir['guild']}");
            $theircount = $db->query("SELECT `userid` FROM `users` WHERE `guild` = {$_POST['guild']}");

            //Current guild does not have 5 members.
            if ($db->num_rows($yourcount) < 5) {
                alert('danger', "Uh Oh!", "You cannot declare war on another guild if you've got less than 5 members in your own guild.");
                die($h->endpage());
            }

            //Current guild does not have 5 members.
            if ($db->num_rows($theircount) < 5) {
                alert('danger', "Uh Oh!", "You cannot declare war on this guild, as they do not have 5 members currently in their guild.");
                die($h->endpage());
            }
            $r = $db->fetch_row($data_q);
            $endtime = time() + 259200;

            //Start the war, and notify all parties involved.
            $db->query("INSERT INTO `guild_wars` VALUES (NULL, {$gd['guild_id']}, {$_POST['guild']}, 0, 0, {$endtime}, 0)");
            $api->GameAddNotification($r['guild_owner'], "The {$gd['guild_name']} guild has declared war on your guild.");
            $api->GuildAddNotification($_POST['guild'], "The {$gd['guild_name']} guild has declared war on your guild.");
            $api->GuildAddNotification($gd['guild_id'], "Your guild has declared war on {$r['guild_name']}");
            $api->SystemLogsAdd($userid, 'guilds', "Declared war on {$r['guild_name']} [{$_POST['guild']}]");
            alert('success', "Success!", "You have declared war on {$r['guild_name']}", true, '?action=staff&act2=idx');
        } else {
            $csrf = request_csrf_html('guild_staff_declarewar');
            echo "
			<table class='table table-bordered'>
			<form method='post'>
				<tr>
					<th colspan='2'>
						You can declare war on another guild. Be ready to reap what you sow.
					</th>
				</tr>
				<tr>
					<th>
						Guild
					</th>
					<td>
						" . guilds_dropdown() . "
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' class='btn btn-primary' value='Declare War'>
					</td>
				</tr>
			{$csrf}
			</form>
			</table>
			<a href='?action=staff&act2=idx'>Go Back</a>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }
}

function staff_levelup()
{
    global $db, $gd, $api, $userid;
    //Experience required set to variable
    $xprequired = $gd['guild_level'] * 36;
    if (isset($_POST['do'])) {

        //Guild does not have enough experience to level up.
        if ($gd['guild_xp'] < $xprequired) {
            alert('danger', "Uh Oh!", "Your guild does not have enough experience to level up. You can get more experience by going to war.");
        } else {
            //Level the guild up.
            $db->query("UPDATE `guild` SET `guild_level` = `guild_level` + 1,
			`guild_xp` = `guild_xp` - {$xprequired} WHERE `guild_id` = {$gd['guild_id']}");
            alert('success', "Success!", "You have successfully leveled up your guild.", true, '?action=staff&act2=idx');
            $api->SystemLogsAdd($userid, 'guilds', "Leveled up the {$gd['guild_name']} guild.");
            $api->GuildAddNotification($gd['guild_id'], "Your guild has leveled up!");
        }
    } else {
        echo "You may level up your guild. Your guild will need the minimum required Experience to do this. You may gain
        guild Experience by going to war with another guild and gaining points in war. At your guild's level, your
        guild will need " . number_format($xprequired) . " Guild Experience to level up. Your guild currently has
        {$gd['guild_xp']} Experience. Do you wish to attempt to level up?<br />
		<form method='post'>
			<input type='hidden' name='do' value='yes'>
			<input type='submit' value='Level Up' class='btn btn-success'>
		</form>
		<a href='?action=staff&act2=idx'>Go Back</a>";
    }
}

function staff_tax()
{
    global $db, $gd, $api, $h, $userid;
    //Check if the user is the owner of the guild.
    if ($userid == $gd['guild_owner']) {
        //Guild does not own a town, so tell them so.
        if (!$db->fetch_single($db->query("SELECT `town_id` FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}")) > 0) {
            alert('danger', "Uh Oh!", "Your guild does not own a town to set a tax rate on.", true, '?action=staff&act2=idx');
            die($h->endpage());
        }
        if (isset($_POST['tax'])) {
            //Verify the variables are safe to work with.
            $_POST['tax'] = (isset($_POST['tax']) && is_numeric($_POST['tax'])) ? abs($_POST['tax']) : 0;

            //Verify CSRF check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_tax", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }

            //Make sure tax rate is between 0-10%
            if ($_POST['tax'] < 0 || $_POST['tax'] > 10) {
                alert('danger', "Uh Oh!", "You can only set a tax rate between 0% and 10%");
                die($h->endpage());
            }
            //Update town's tax rate.
            $town_id = $db->fetch_single($db->query("SELECT `town_id` FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}"));
            $db->query("UPDATE `town` SET `town_tax` = {$_POST['tax']} WHERE `town_guild_owner` = {$gd['guild_owner']}");
            $api->SystemLogsAdd($userid, 'tax', "Set tax rate to {$_POST['tax']}% in {$api->SystemTownIDtoName($town_id)}.");
            alert('success', "Success!", "You have set the tax rate of {$api->SystemTownIDtoName($town_id)} to {$_POST['tax']}%.", true, '?action=staff&act2=idx');

        } else {
            $csrf = request_csrf_html('guild_staff_tax');
            $current_tax = $db->fetch_single($db->query("SELECT `town_tax` FROM `town` WHERE `town_guild_owner` = {$gd['guild_id']}"));
            echo "
			<table class='table table-bordered'>
			<form method='post'>
				<tr>
					<th colspan='2'>
						You may change the tax rate for the town your guild owns here.
					</th>
				</tr>
				<tr>
					<th>
						Tax Rate (Percent)
					</th>
					<td>
						<input type='number' name='tax' class='form-control' value='{$current_tax}' min='0' max='20' required='1'>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' class='btn btn-primary' value='Change Tax'>
					</td>
				</tr>
			{$csrf}
			</form>
			</table>
			<a href='?action=staff&act2=idx'>Go Back</a>";
        }

    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }
}

function staff_dissolve()
{
    global $db, $gd, $api, $h, $userid, $ir, $wq;
    //Check if user is the owner of the guild.
    if ($userid == $gd['guild_owner']) {
        if (isset($_POST['do'])) {
            //Verify CSRF check has passed.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_dissolve", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }
            //Make sure guild is not at war.
            if ($db->fetch_single($wq) > 0) {
                alert('danger', "Uh Oh!", "You cannot dissolve your guild when you are at war.");
                die($h->endpage());
            }

            //Select all guild members, and tell them what happened to their guild via notification.
            $q = $db->query("SELECT `userid`,`username` FROM `users` WHERE `guild` = {$ir['guild']}");
            while ($r = $db->fetch_row($q)) {
                $api->GameAddNotification($r['userid'], "Your guild, {$gd['guild_name']}, has been dissolved by <a href='profile.php?user={$userid}'>{$ir['username']}</a> [{$userid}].");
            }
            //Log the guild being deleted.
            $api->SystemLogsAdd($userid, 'guilds', "Dissolved Guild ID {$ir['guild']}");

            //Delete everything.
            $db->query("DELETE FROM `guild_applications` WHERE `ga_guild` = {$ir['guild']}");
            $db->query("DELETE FROM `guild_armory` WHERE `gaGUILD` = {$ir['guild']}");
            $db->query("DELETE FROM `guild_notifications` WHERE `gn_guild` = {$ir['guild']}");
            $db->query("DELETE FROM `guild_wars` WHERE `gw_declarer` = {$ir['guild']}");
            $db->query("DELETE FROM `guild_wars` WHERE `gw_declaree` = {$ir['guild']}");
            $db->query("DELETE FROM `guild` WHERE `guild_id` = {$ir['guild']}");
            $db->query("UPDATE `users` SET `guild` = 0 WHERE `guild` = {$ir['guild']}");
            alert("success", "Success!", "You have successfully dissolved your guild.", true, 'index.php');
        } else {
            $csrf = request_csrf_html('guild_staff_dissolve');
            echo "Are you sure you wish to dissolve your guild? This action cannot be undone. Everything in the guild's
            armory and vault will be removed from the game entirely.<br />
            <form method='post'>
            {$csrf}
            <input type='hidden' name='do' value='do'>
            <input type='submit' class='btn btn-primary' value='Dissolve Guild'>
            </form>";
        }
    } else {
        alert('danger', "Uh Oh!", "You can only be here if you're the guild's leader.", true, '?action=staff&act2=idx');
    }

}

function staff_armory()
{
    global $db, $gd, $api, $h, $set, $userid, $ir;
    //Check to see if the guild has bought the armory.
    if ($gd['guild_hasarmory'] == 'false') {

        //Set the cost to varaible for ease of use.
        $cost = $set['GUILD_PRICE'] * 4;
        if (isset($_GET['buy'])) {

            //Guild does not have enough Primary Currency to buy the armory.
            if ($gd['guild_primcurr'] < $cost) {
                alert('danger', "Uh Oh!", "Your guild does not have enough Primary Currency to buy an armory.", true, '?action=staff&act2=idx');
                die($h->endpage());
            }
            //Buy the armory and remove the currency.
            $db->query("UPDATE `guild`
                        SET `guild_hasarmory` = 'true',
                        `guild_primcurr` = `guild_primcurr` - {$cost}
                        WHERE `guild_id` = {$gd['guild_id']}");
            //Log
            alert('success', 'Success!', "You have successfully purchased an armory for your guild.");
            $api->SystemLogsAdd($userid, 'guilds', "Purchased guild armory.");
        } else {
            echo "Your guild does not have an armory. It will cost your guild " . number_format($cost) . " Primary
            Currency to purchase an armory. Do you wish to purchase an armory for your guild?<br />
            <a href='?action=staff&act2=armory&buy=yes' class='btn btn-success'>Yes</a>
            <a href='?action=staff&act2=idx' class='btn btn-danger'>No</a>";
        }
    } else {
        if (isset($_POST['user'])) {
            //Make sure every variable is safe to work with.
            $_POST['user'] = (isset($_POST['user']) && is_numeric($_POST['user'])) ? abs($_POST['user']) : 0;
            $_POST['item'] = (isset($_POST['item']) && is_numeric($_POST['item'])) ? abs($_POST['item']) : 0;
            $_POST['qty'] = (isset($_POST['qty']) && is_numeric($_POST['qty'])) ? abs($_POST['qty']) : 0;

            //Verify CSRF check is successful.
            if (!isset($_POST['verf']) || !verify_csrf_code("guild_give_item", stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
                die($h->endpage());
            }

            //Verify user chosen is in the guild.
            $q = $db->query("SELECT `userid`, `username` FROM `users` WHERE `userid` = {$_POST['user']} AND `guild` = {$gd['guild_id']}");
            if ($db->num_rows($q) == 0) {
                $db->free_result($q);
                alert('danger', "Uh Oh!", "You cannot give items to someone not in your guild.");
                die($h->endpage());
            }
            $db->free_result($q);

            //Verify the item chosen exists.
            $q = $db->query("SELECT `itmname` FROM `items` WHERE `itmid` = {$_POST['item']}");
            if ($db->num_rows($q) == 0) {
                $db->free_result($q);
                alert('danger', "Uh Oh!", "You cannot give out non-existent items.");
                die($h->endpage());
            }
            $db->free_result($q);

            //Verify the user is giving at least one item.
            if ($_POST['qty'] <= 0) {
                alert('danger', "Uh Oh!", "You must give out at least one item.");
                die($h->endpage());
            }

            //Check users' IP Address. Returns false if not and/or same user
            if ($api->SystemCheckUsersIPs($userid, $_POST['user'])) {
                alert('danger', "Uh Oh!", "You cannot give items to players who share the same IP Address as you.");
                die($h->endpage());
            }

            //Give items and whatnot.
            $api->GuildRemoveItem($ir['guild'], $_POST['item'], $_POST['qty']);
            $api->UserGiveItem($_POST['user'], $_POST['item'], $_POST['qty']);

            //Resolve item to variable
            $item = $api->SystemItemIDtoName($_POST['item']);
            $user = $api->SystemUserIDtoName($_POST['user']);

            //Notification
            $api->GameAddNotification($_POST['user'], "You have been given {$_POST['qty']} {$item}(s) from your guild's armory.");
            $api->GuildAddNotification($ir['guild'], "{$ir['username']} has given {$_POST['qty']} {$item}(s) from your guild's armory to {$user}.");
            alert('success', "Success!", "You have successfully given {$_POST['qty']} {$item}(s) from your guild's armory to {$user}.", true, "?action=staff&act2=idx");
            $api->SystemLogsAdd($userid, 'guilds', "Gave {$user} {$_POST['qty']} {$item}(s) from their armory.");
        } else {
            //Giving item form.
            $csrf = request_csrf_html('guild_give_item');
            echo "Fill out the form below completely to give out items from your armory.<br />
            <form method='post'>
            " . guild_user_dropdown('user', $ir['guild']) . "<br />
            " . armory_dropdown() . "<br />
            <input type='number' required='1' min='1' name='qty' placeholder='Quantity' class='form-control'><br />
            <input type='submit' value='Give Item' class='btn btn-primary'>
            {$csrf}
            </form>";
        }
    }
}

function staff_crimes()
{
    global $db, $userid, $api, $h, $ir, $gd;
    //Select the guild's member count.
    $cnt = $db->query("SELECT COUNT(`userid`) FROM `users` WHERE `guild` = {$ir['guild']}");
    $membs = $db->fetch_single($cnt);
    $db->free_result($cnt);
    if (isset($_POST['crime'])) {
        //Make the POST safe to work with.
        $_POST['crime'] = (isset($_POST['crime']) && is_numeric($_POST['crime'])) ? abs(intval($_POST['crime'])) : 0;

        //Verify CSRF check is successful.
        if (!isset($_POST['verf']) || !verify_csrf_code("guild_staff_crimes", stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "Forms expire fairly quickly. Be quicker next time.");
            die($h->endpage());
        }

        //Check that the guild isn't already planning a crime.
        if ($gd['guild_crime'] != 0) {
            alert('danger', "Uh Oh!", "You guild is already planning a crime.");
            die($h->endpage());
        }
        //Verify crime exists.
        $cq = $db->query("SELECT `gcUSERS` from `guild_crimes` WHERE `gcID` = {$_POST['crime']}");
        if ($db->num_rows($cq) == 0) {
            alert('danger', "Uh Oh!", "You cannot commit a non-existent crime.");
            die($h->endpage());
        }
        //Verify guild has enough members to commit this crime.
        $cr = $db->fetch_single($cq);
        if ($cr > $membs) {
            alert('danger', "Uh Oh!", "You cannot commit this crime as you need {$cr} guild members. You only have {$membs}.");
            die($h->endpage());
        }
        //Select time 24 hours from now.
        $ttc = time() + 86400;

        //Set guild's crime.
        $db->query("UPDATE `guild`
                    SET `guild_crime` = {$_POST['crime']},
                    `guild_crime_done` = {$ttc}
                    WHERE `guild_id` = {$ir['guild']}");
        alert('success', "Success!", "You have started to plan this crime. It will take 24 hours to commit.", true, '?action=staff&act2=idx');
    } else {
        //Select the crimes from database, based on how many members the guild has.
        $q = $db->query("SELECT *
                         FROM `guild_crimes`
                         WHERE `gcUSERS` <= $membs");

        //If there's crimes the guild can commit.
        if ($db->num_rows($q) > 0) {
            $csrf = request_csrf_html('guild_staff_crimes');
            echo "Select the crime you wish your guild to commit.<br />
            <form method='post'>
                <select name='crime' type='dropdown' class='form-control'>";
            while ($r = $db->fetch_row($q)) {
                echo "<option value='{$r['gcID']}'>{$r['gcNAME']}
                		({$r['gcUSERS']} members needed)</option>\n";
            }

            echo "</select><br />
                <input type='submit' value='Plan Crime' class='btn btn-primary'>
                {$csrf}
            </form>";
        } //Guild has no crimes they can commit.
        else {
            alert('danger', "Uh Oh!", "You guild cannot commit any crimes at this time.", true, '?action=staff&act2=idx');
        }
    }
}

$h->endpage();