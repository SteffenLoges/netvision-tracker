<?

/*
// +--------------------------------------------------------------------------+
// | Project:    NVTracker - NetVision BitTorrent Tracker                     |
// +--------------------------------------------------------------------------+
// | This file is part of NVTracker. NVTracker is based on BTSource,          |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// |                                                                          |
// | NVTracker is free software; you can redistribute it and/or modify        |
// | it under the terms of the GNU General Public License as published by     |
// | the Free Software Foundation; either version 2 of the License, or        |
// | (at your option) any later version.                                      |
// |                                                                          |
// | NVTracker is distributed in the hope that it will be useful,             |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with NVTracker; if not, write to the Free Software Foundation,     |
// | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            |
// +--------------------------------------------------------------------------+
// | Obige Zeilen d�rfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
*/

require "include/bittorrent.php";
dbconn();
loggedinorreturn();

if (isset($_GET["id"]) && intval($_GET["id"]) != $CURUSER["id"]) {
    $userid = intval($_GET["id"]);

    $res = mysql_query("SELECT username,class FROM users WHERE id=$userid");
    if (mysql_num_rows($res) == 0)
        stderr("Fehler", "Es existiert kein User mit der ID $userid!");
    
    $arr = mysql_fetch_assoc($res);

    if ($CURUSER["class"] < UC_MODERATOR || $CURUSER["class"]<=$arr["class"])
        stderr("Fehler", "Du hast keine Rechte, den BitBucket-Inhalt dieses Benutzers anzusehen oder zu �ndern!");

    $username = $arr["username"];
    $userclass = $arr["class"];  
} else {
    $userid = $CURUSER["id"];
    $username = $CURUSER["username"];
    $userclass = $CURUSER["class"];
}

if ($userclass>=UC_UPLOADER) {
    $maxbucketsize = $GLOBALS["MAX_BITBUCKET_SIZE_UPLOADER"];
} else {
    $maxbucketsize = $GLOBALS["MAX_BITBUCKET_SIZE_USER"];
}

if ($_GET["delete"] != "")
{
    $file_id = intval($_GET["delete"]);
    $numfiles = mysql_result(mysql_query("SELECT COUNT(*) FROM bitbucket WHERE `id`=".$file_id." AND `user`=".$userid),0);
    
    if ($numfiles==1) {
    	$bucketfile = mysql_fetch_array(mysql_query("SELECT * FROM bitbucket WHERE `id`=".$file_id));

        if (!$_GET["sure"]) {
            stderr("Datei wirklich l�schen?", "Bist Du Dir wirklich sicher, dass die Datei '".$bucketfile["originalname"]."' aus dem BitBucket gel�scht werden soll? Wenn ja, dann <a href=\"bitbucket.php?".(isset($_GET["id"])?"id=$userid&amp;":"")."delete=$file_id&amp;sure=1\">klicke hier</a>.");
        } else {    
        	@unlink($GLOBALS["BITBUCKET_DIR"]."/".$bucketfile["filename"]);
        	mysql_query("DELETE FROM bitbucket WHERE `id`=".$file_id);
            
            // Modcomment aktualisieren
            if (isset($_GET["id"])) {
                $arr = mysql_fetch_assoc(mysql_query("SELECT modcomment FROM users WHERE id=$userid"));
                $arr["modcomment"] = date("Y-m-d") . " - Die Datei '".$bucketfile["originalname"]."' wurde von ".$CURUSER["username"]." aus dem BitBucket gel�scht.\n" .$arr["modcomment"];
                mysql_query("UPDATE users SET modcomment=".sqlesc($arr["modcomment"])." WHERE id=$userid");
            }
            
        	stderr("Erfolg", "<p>Die Datei wurde erfolgreich aus Deinem Bitbucket gel&ouml;scht.</p><p><a href=\"bitbucket.php".(isset($_GET["id"])?"?id=$userid":"")."\">Zur&uuml;ck zum BitBucket</p>");
        }
    } else {
    	stderr("Fehler", "<p>Diese Datei geh&ouml;rt nicht Ihnen, oder sie wurde bereits gel&ouml;scht.</p><p><a href=\"bitbucket.php".(isset($_GET["id"])?"?id=$userid":"")."\">Zur&uuml;ck zum BitBucket</p>");
    }
}

stdhead("BitBucket von $username");
begin_frame("BitBucket von $username", FALSE, "650px");
begin_table(TRUE);

if ($userid == $CURUSER["id"]) {
?>
<form method=post action="bitbucket-upload.php<?=(isset($_GET["id"])?"?id=$userid":"")?>" enctype="multipart/form-data">
<? begin_table(TRUE); ?>
<tr><td colspan="2" class="tablecat" align="left"><b>Neue Datei hochladen</b> - Maximale Dateigr&ouml;&szlig;e: <?=mksize($GLOBALS["MAX_UPLOAD_FILESIZE"]); ?></td></tr>
<tr><td class="tableb">Datei</td><td class="tablea"><input type=file name=file size=60></td></tr>
<tr><td class="tableb">Avatar</td><td class="tablea"><input type="checkbox" id="avatar" name="is_avatar" value="1"><label for="avatar"> Dieses Bild ist ein Avatar, und soll automatisch auf die richtige Gr&ouml;&szlig;e gebracht werden.</label><br><i>(Nur JPEG und PNG)</i></td></tr>
<tr><td class="tablea" colspan="2" align="center"><input type=submit value="Hochladen" class=btn></td></tr>
</table><br>
</form>
<table class="main" width="640" border="0" cellspacing="0" cellpadding="0"><tr><td>
<font class="small"><b>Hinweis:</b> Die hochgeladenen Dateien m&uuml;ssen mit den Avatar-Regeln konform sein,
und d&uuml;rfen keine illegalen, gewaltverherrlichenden oder pornographischen Inhalte enthalten. Lade bitte
auch keine Dateien hoch, von denen du nicht m&ouml;chtest, dass diese ein Fremder zu sehen bekommt.</font>
</td></tr></table><br>
<?
} else {
begin_table(TRUE);
echo "<tr><td class=tablea align=center><a href=\"userdetails.php?id=$userid\">Zur�ck zum Profil</a></td></tr>";
end_table();
} ?>

<p>Der BitBucket enth&auml;lt momentan folgende Bilddateien:</p>
<?php

begin_table(TRUE);
?>
<colgroup>
  <col width="1*">
  <col width="1*">
  <col width="1*">
</colgroup>  
<?

$numfiles = mysql_result(mysql_query("SELECT COUNT(*) FROM bitbucket WHERE user=".$userid),0);
$bucketfiles = mysql_query("SELECT * FROM bitbucket WHERE user=".$userid);
$bucketsize = mysql_result(mysql_query("SELECT SUM(size) FROM bitbucket WHERE user=".$userid),0);

if ($numfiles==0) {
    echo "<tr><td class=tablea colspan=4>Es sind zurzeit keine Dateien im BitBucket vorhanden.</td></tr>";
} else {
    $imgline = "<tr>\n";
    $descline = "<tr>\n";
    $cnt = 0;
    while ($fileinfo = mysql_fetch_array($bucketfiles)) {
	if ($cnt>0 && $cnt%3==0) {
	    echo $imgline."</tr>\n";
	    echo $descline."</tr>\n";
	    $imgline = "<tr>\n";
	    $descline = "<tr>\n";	    
	}
	$imgline .= "<td class=tablea align=center valign=middle><img src=\"".$GLOBALS["BITBUCKET_DIR"]."/".$fileinfo["filename"]."\" width=\"100\" alt=\"".htmlspecialchars($fileinfo["originalname"])."\" title=\"".htmlspecialchars($fileinfo["originalname"])."\"></td>\n";
	$descline .= "<td class=tableb align=center valign=top><a href=\"".$GLOBALS["BITBUCKET_DIR"]."/".$fileinfo["filename"]."\">".htmlspecialchars($fileinfo["originalname"])."</a><br>\n";
	$descline .="(". mksize($fileinfo["size"]).") ";
	$descline .= "<a href=\"bitbucket.php?".(isset($_GET["id"])?"id=$userid&amp;":"")."delete=".$fileinfo["id"]."\"><img src=\"".$GLOBALS["PIC_BASE_URL"]."/editdelete.png\" width=\"16\" height=\"16\" alt=\"L&ouml;schen\" style=\"border:none;vertical-align:middle;\"></a></td>";
	
	$cnt++;
    }
    if ($cnt%3!=0) {
	for ($I=0; $I<3-$cnt%3; $I++) {
	    $imgline .= "<td class=tablea align=center valign=middle>&nbsp;</td>\n";
	    $descline .= "<td class=tableb align=center valign=top>&nbsp;</td>\n";
	}
    }
    
    echo $imgline."</tr>\n";
    echo $descline."</tr>\n";				 
}
end_table();

if ($userid == $CURUSER["id"]) {
?>
<p><b>Hinweis:</b> Um den Link auf die Datei zu erhalten, klicke mit der rechten Maustaste auf den Dateinamen und w&auml;hle den Eintrag "Link-Adresse kopieren" aus dem Men&uuml;. Diesen Link kannst Du dann auf dem Tracker frei benutzen.</p>
<p>Bei Fragen lies bitte die <a class="altlink" href="faq.php#usere">FAQ</a>!

<?
}
end_frame();

begin_frame("BitBucket Speicherplatznutzung", FALSE, "650px");
echo "<br><center>";
begin_table();
?>
<tr><td style='padding: 0px; width: 400px; background-image: url(<?=$GLOBALS["PIC_BASE_URL"]?>loadbarbg.gif); background-repeat: repeat-x'>
<?
    $size = mysql_result(mysql_query("SELECT SUM(size) FROM bitbucket WHERE `user`=".$userid),0);
	$percent = min(100,round($size/$maxbucketsize*100));
        if ($percent <= 70) $pic = "loadbargreen.gif";
        elseif ($percent <= 90) $pic = "loadbaryellow.gif";
        else $pic = "loadbarred.gif";
        $width = $percent * 4;
        print("<img src=\"".$GLOBALS["PIC_BASE_URL"].$pic."\" height=\"15\" width=\"$width\" alt=\"$percent%\">");
        
echo "</td></tr>";

end_table();
echo mksize($size), " von ".mksize($maxbucketsize)." belegt ($percent%)</center>";
end_frame();
?>


<?
stdfoot();

?>