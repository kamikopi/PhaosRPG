<?php
/*
i correct "if" errors in this file
*/
include "config.php";
include_once 'include_lang.php';
echo "<html><head>
	<link href='styles/phaos.css' rel='stylesheet' type='text/css'>
	</head><body class='bodym'>
	<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#111111' width='100%' id='AutoNumber1' height='103'>
	<tr>
	<td width='100%' height='100%' align='center'>
	<img src='lang/".$lang."_images/clan_home.png'><br>
	</td>
	</tr>";

$clanmemberid = 0;
$clanrank = 0;
$clanname = '';
$result_b = mysql_query("SELECT id, name FROM phaos_characters WHERE username = '$PHP_PHAOS_USER'");
if ($row = mysql_fetch_array($result_b)) {
        $clanmemberid = $row['id'];
	$clanmember = $row["name"];
}

$result = mysql_query ("SELECT clanname, clanrank FROM phaos_clan_in WHERE clanmemberid = '$clanmemberid'");
if (($row = mysql_fetch_array($result))) {
    $clanname = $row["clanname"];
    $clanrank = intval($row["clanrank"]);
}

if($clanname === '' || $clanrank !== 99) {
  echo "<p align='center'><font color='#FF0000'><b>
          <a href=\"town_hall.php\">".$lang_guild3["not_in"]."</a></b></font></td>
          </tr>
          </table><br><br>";
}
else if($delclan == "yes") {
          echo "<br><br>
                  <table class='utktable' border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#111111' width='98%'>
                  <tr>
                  <td width='100%'>
                  <center><b><font color='#FF0000'>".$lang_guild2["del_gu"]."...</font></b><br>";

          $query_3 = "DELETE FROM phaos_clan_admin WHERE clanname = '$clanname'";
          $result = mysql_query($query_3) or die ("Error in query: $query_3. " . mysql_error());

          echo "<a target='content' href='town_hall.php'>".$lang_clan["town_ret"]."</a></b></font></center>
                  </td>
                  </tr>
                  </table><br><br>";
} else {
	echo "<tr>
		<td align='center' valign='top' height='63'>
		<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#111111' width='100%' id='AutoNumber2'>
		<tr>
		<td width='100%' bgcolor='#003300' align='center'>&nbsp;</td>
		</tr>
		<tr>
		<td width='100%' align='center' valign='top'>
		<form method='post' action='clan_delete.php'>
		".$lang_guild2["del_msg"]." $clanname<br>
		<br><hr color='#FFFFFF' width='98%'><br>
		<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' width='95%' id='AutoNumber3'>
		<tr>
		<td width='100%' align='center' bgcolor='#003300'><b>".$lang_guild2["onc"].".</b>
		</td>
		</tr>
		<tr>
		<td width='100%' align='center'>
		".$lang_guild2["sure"]."$clanname<br><br>
		".$lang_o_yes." <input type='radio' value='yes' name='delclan'><br>
		".$lang_o_no." <input type='radio' value='no' name='delclan' checked><br>
		</td>
		</tr>
		</table>
		<br>
		<input class='buttont' type='submit' value='".$lang_guild2["confirm"]."' name='B1'><br><br>
		</form>
		</td>
		</tr>
		<tr>
		<td width='100%' bgcolor='#003300'>
		<a href='clan_home.php' target='content'>".$lang_clan["back"]."</a>
		<br>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		<tr>
		<td width='100%' colspan='3' height='19'>&nbsp;</td>
		</tr>";
}
echo "</table></body></html>";
