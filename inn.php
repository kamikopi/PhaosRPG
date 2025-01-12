<?php
include "aup.php"; 
include_once 'class_character.php';
include_once 'class_quest.php';
include_once 'class_quest_generator.php';

$character=new character($PHP_PHAOS_CHARID);

// generate quests
$quest_generator = new quest_generator();
$quest_generator->delete_finished_quests();
$quest_generator->generate();

// make sure the requested shop is where the player is
if (!($shop_id = shop_valid($character->location, 'inn.php'))) {
        include "header.php";
	echo $lang_markt["no_sell"].'</body></html>' ;
	exit;
}

$reload= false;

if($character->gold >= 30 && @$_REQUEST['spend_night']) {
	$character->stamina_points += $character->max_stamina;
	$character->hit_points += (int)( $character->max_hp*0.75 );
	$character->gold -= 30;
	$reload= true;
}

if($character->gold >= 3 && @$_REQUEST['have_drink']) {
	$character->hit_points += $character->race=='Dwarf'?5:3;
	$character->stamina_points += ($character->race=='Human'?21:13);
	$character->gold -= 3;
	$reload= true;
}

$npc_id = intval(@$_POST['npc_id']);
$rumors_yn = @$_POST['rumors'];
$quests_yn = @$_POST['quests'];
$questid = null;
$rumors = '';

if ($npc_id) {
    // NPC CONVERSATION OPTIONS
    $result = mysql_query ("SELECT * FROM phaos_npcs WHERE id = '$npc_id' AND location = '".$character->location."'");
    if (($row = mysql_fetch_array($result))) {
            $npc_name = $row["name"];
            $npc_image = $row["image_path"];
            $id_npc = $row["id"];
            $rumors = $row["rumors"];
            $questid = intval($row["quest"]);
            $quest = new quest($questid);
            $quest_status = $quest->get_status($character);
            $can_do = $quest->can_do_quest($character);
            $quest_accepted = false;
            $quest_completed = false;

            if (isset($_POST['acceptq']) && $quest_status === 0 && $can_do === 1) {
              $quest_accepted = true;
              $quest->accept($character);
            }

            if (isset($_POST['completeq']) && $quest_status === 2 && $can_do === 1) {
              $quest_completed = true;
              $quest->complete($character);
            }

    } else {
      $npc_id = 0; // npc not exists
    }
}


if($reload) {
	if($character->hit_points > $character->max_hp) {$character->hit_points = $character->max_hp;}
	if($character->stamina_points > $character->max_stamina) {$character->stamina_points = $character->max_stamina;}

	//do updates for all actions
	$query = ("UPDATE phaos_characters
				SET hit_points = $character->hit_points, stamina = $character->stamina_points, gold = $character->gold
				WHERE id = '$character->id'");
	$req = mysql_query($query);
	if (!$req) {
		showError(__FILE__,__LINE__,__FUNCTION__,$query);
		exit;
	}
}

include "header.php";
?>

<table border=0 cellspacing=0 cellpadding=0 width="100%" height="100%">
<tr>
<td align=center valign=top>

<table border=0 cellspacing=5 cellpadding=0 width="100%">
<tr>
<td align=center colspan=2>
<img src="lang/<?php echo $lang ?>_images/inn.png">
</td>
</tr>
<tr>
<td align=center colspan=2>
<table border=0 cellspacing=0 cellpadding=5 width="100%">
<tr>
<td align=left>
<b>Available Actions:</b>
</td>
</tr>
<tr>
<form action='inn.php' method='post'> 
<td align=left>
<input type='hidden' name='spend_night' value='yes'> 
<button type='submit' style="width:250px;"><?php echo $lang_inn["spnd_night"]; ?></button>
</form>
</td>
</tr>
<tr>
<form action='inn.php' method='post'> 
<td align=left>
<input type='hidden' name='have_drink' value='yes'> 
<button type='submit' style="width:250px;"><?php echo $lang_inn["hav_drnk"]; ?></button> 
</form>
</td>
</tr>
<tr>
<form action='game_1.php' method='post'>
<td align=left>
<button type='submit' style="width:250px;"><?php echo $lang_inn["ply_dic"]; ?></button>
</td>
</form>
</tr>
<tr>
<form action='game_2.php' method='post'>
<td align=left>
<button type='submit' style="width:250px;"><?php echo $lang_inn["ply_rps"]; ?></button>
</td>
</form>
</tr>
<tr>
<td align=left>
<br><br>
<b>Others in the inn:</b>
<p>
<?php

if(!$npc_id) {
	// SELECT NPC TO TALK TO
	$result = mysql_query ("SELECT * FROM phaos_npcs WHERE location = '".$character->location."'");
	if ($row = mysql_fetch_array($result)) {
		do {
			$npc_name = $row["name"];
			$npc_image = $row["image_path"];
			$id_npc = $row["id"];

			print ("<div style=\"display: inline-block\"><form action=\"inn.php\" method=\"post\">");
			print ("<input type=\"hidden\" name=\"npc_id\" value=\"$id_npc\">");
			print ("<button type=\"submit\"><div align=\"center\">");
			if($npc_image != "") {print ("<img src=\"$npc_image\"><br>");}
			print ("$npc_name</div>");
			print ("</button><br>");
			print ("</form></div>");
		} while($row = mysql_fetch_array($result));
	} else {print ($lang_inn['inn_empty']);}
} else {
	// NPC CONVERSATION OPTIONS

        print ("<div align=center><button type=\"button\"><div align=\"center\">");
        if($npc_image != "") {print ("<img src=\"$npc_image\"><br>");}
        print ("$npc_name</div>");
        print ("</button></div>");

        print ("<form action=\"inn.php\" method=\"post\">");
        print ("<button type=\"submit\" style=\"border:none;text-align:left;\">".$lang_inn["heard_rumor"]);
        print ("</button>");
        print ("<input type=\"hidden\" name=\"rumors\" value=\"yes\">");
        print ("<input type=\"hidden\" name=\"npc_id\" value=\"$id_npc\">");
        print ("</form>");

        print ("<form action=\"inn.php\" method=\"post\">");
        print ("<button type=\"submit\" style=\"border:none;text-align:left;\">".$lang_inn["look_stg"]);
        print ("</button>");
        print ("<input type=\"hidden\" name=\"quests\" value=\"yes\">");
        print ("<input type=\"hidden\" name=\"npc_id\" value=\"$id_npc\">");
        print ("</form>");

        print ("<form action=\"inn.php\" method=\"post\">");
        print ("<button type=\"submit\" style=\"border:none;text-align:left;\">".$lang_inn["gdbye"]);
        print ("</button>");
        print ("<input type=\"hidden\" name=\"npc_id\" value=\"\">");
        print ("</form>");
}

print ("<p><hr>");

if($rumors_yn) {
	if($rumors == "") {
          print ("<big><b>".$lang_inn["sorry_no"]."</b></big>");
        } else {
          print ("<big><b>$rumors</b></big>");
        }
}

if($quest_accepted) {
        print ("<big><b>".$lang_quest['good_luck']."</b></big>");
}

if($quest_completed) {
        print ("<h4 class=\"b\">".$lang_quest['completed']."</h4>");
}

if ($quests_yn && $questid === null) {
        print ("<big><b>".$lang_inn["sorry_no"]."</b></big>");
}

if ($quests_yn && $questid === 0) {
        print ("<big><b>".$lang_inn["sorry_no"]."</b></big>");
}

if($quests_yn && $questid > 0 && !$acceptq && !$completeq) {
        
        // not started
        if ($quest_status === 0) {

            // not enough time left to clear the quest
            if ($can_do == -1 || $can_do == -3) {
                    print ("<big><b>".$lang_inn["sorry_no_bus"]."</b></big>");
            }

            // not enough experience
            else if ($can_do == -2) {
                    print ("<big><b>".$lang_inn["u2weak"]."</b></big>");
            }

            // all requirements met, character can take the quest
            else {
?>
              <h4 class="b"><?php echo $quest->narrate; ?></h4>
              <?php $quest->print_reward($character); ?>
              <form class="center" action="" method="post">
                      <input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
                      <input type="hidden" name="acceptq" value="<?php echo $lang_inn["acceptq"]; ?>">
                      <button class="button" type="submit">
                              <?php echo $lang_inn["acceptq"]; ?>
                      </button>
              </form>
<?php
            }
        }

        // started, but not finished
        else if ($quest_status === 1) {
              print ("<big><b>".$quest->get_trace_message($character)."</b></big>");
        }

        // finished, but reward not received
        else if ($quest_status === 2) {
?>
              <h4 class="b"><?php echo $quest->completemsg; ?></h4>
              <?php $quest->print_reward($character); ?>
              <form class="center" action="" method="post">
                      <button class="button" type="submit">
                              <?php echo $lang_inn["completeq"]; ?>
                              <input type="hidden" name="npc_id" value="<?php echo $id_npc;?>">
                              <input type="hidden" name="completeq" value="<?php echo $lang_inn["completeq"]; ?>">
                      </button>
              </form>
<?php
        }

        // already completed
        else {
          print ("<big><b>".$lang_inn["sorry_no_bus"]."</b></big>");
        }
}
?>
</td>
</tr>
</table>

</td>
</tr>
</table>

<br>
<br>
<a href="town.php"><?php print $lang_inn["return"]; ?></a>
</td>
</tr>
</table>

<?php include "footer.php"; ?>
