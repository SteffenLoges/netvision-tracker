<?php

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

dbconn(false);

loggedinorreturn();

function puke($text = "w00t")
{
    stderr("w00t", $text);
} 

if (get_user_class() < UC_MODERATOR)
    puke();

$action = $_POST["action"];

if ($action == "edituser") {
    $userid = $_POST["userid"];
    $title = $_POST["title"];
    $avatar = $_POST["avatar"];
    $enabled = $_POST["enabled"];
    $warned = $_POST["warned"];
    $warnlength = 0 + $_POST["warnlength"];
    $warnpm = $_POST["warnpm"];
    $donor = $_POST["donor"];
    $modcomment = $_POST["modcomment"];
    $waittime = $_POST["wait"];
    $acceptrules = $_POST["acceptrules"];
    $baduser = $_POST["baduser"];
    if ($baduser == "yes")
        $allowupload = "no";
    else
        $allowupload = ($_POST["denyupload"] == "yes"?"no":"yes");

    $class = 0 + $_POST["class"];

    if (!is_valid_id($userid) || !is_valid_user_class($class))
        stderr("Fehler", "Falsche User ID oder Klassen ID."); 
    // check target user class
    $res = mysql_query("SELECT email, title, warned, enabled, username, class, tlimitall, tlimitseeds, tlimitleeches, allowupload, uploaded, downloaded FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or puke();
    $curenabled = $arr["enabled"];
    $curclass = $arr["class"];
    $curwarned = $arr["warned"];
    $curallowupload = $arr["allowupload"];
    $curtitle = $arr["title"];
    $curdownloaded = $arr["downloaded"];
    $curuploaded = $arr["uploaded"];
    $username = $arr["username"];
    $email = $arr["email"]; 
    // User may not edit someone with same or higher class than himself!
    if (get_user_class() != UC_SYSOP && $curclass >= get_user_class())
        puke();

    if ($warnlength && $warnpm == "")
        stderr("Fehler", "Du musst einen Verwarnungsgrund angeben (z.B. \"Zu niedrige Ratio\" oder \"Ich mag Dich einfach nicht!\").");

    if ($enabled != $curenabled && $enabled == true && $_POST["disablereason"] == "")
        stderr("Fehler", "Du musst einen Grund f�r die Deaktivierung angeben (z.B. \"Verwarnungsbedingungen nicht erf�llt\" oder \"Cheating\"). Der Benutzer erh�lt den Grund als E-Mail zugesandt.");

    switch ($_POST["limitmode"]) {
        case "auto":
        default:
            $maxtotal = 0;
            $maxseeds = 0;
            $maxleeches = 0;
            break;

        case "unlimited":
            $maxtotal = -1;
            $maxseeds = 0;
            $maxleeches = 0;
            break;

        case "manual":
            $maxtotal = intval($_POST["maxtotal"]);
            $maxseeds = intval($_POST["maxseeds"]);
            $maxleeches = intval($_POST["maxleeches"]);

            if ($maxseeds > $maxtotal) $maxseeds = $maxtotal;
            if ($maxleeches > $maxtotal) $maxleeches = $maxtotal; 
            // Allow leeches to be set to 0, but not total and seeds.
            if ($maxtotal <= 0 || $maxleeches < 0 || $maxseeds <= 0)
                stderr("Fehler", "Die Torrentbegrenzung muss bei Seeds und Gesamt min. 1 sein, bei Leeches 0 oder h�her.");

            break;
    } 

    if ($modcomment != "") {
        write_modcomment($userid, $CURUSER["id"], $modcomment);
    } 

    if ($maxtotal <> intval($arr["tlimitall"]) || $maxseeds <> intval($arr["tlimitseeds"]) || $maxleeches <> intval($arr["tlimitleeches"])) {
        $updateset[] = "tlimitall = " . $maxtotal;
        $updateset[] = "tlimitseeds = " . $maxseeds;
        $updateset[] = "tlimitleeches = " . $maxleeches;
        write_modcomment($userid, $CURUSER["id"], "Torrentbegrenzung ge�ndert: $maxleeches Leeches, $maxseeds Seeds, $maxtotal Gesamt");
    } 

    if ($curtitle != $title) {
        write_modcomment($userid, $CURUSER["id"], "Titel ge�ndert auf '" . $title . "'.");
    } 

    if ($curclass != $class) {
        // Notify user
        $what = ($class > $curclass ? "bef�rdert" : "degradiert");
        $type = ($class > $curclass ? "promotion" : "demotion");
        $msg = sqlesc(($class > $curclass?"[b]Herzlichen Gl�ckwunsch![/b]\n\n":"") . "Du wurdest soeben von [b]" . $CURUSER["username"] . "[/b] zum  '" . get_user_class_name($class) . "' $what.\n\nWenn etwas an dieser Aktion nicht in Ordnung sein sollte, melde Dich bitte bei dem angegebenen Teammitglied!");
        $added = sqlesc(get_date_time());
        sendPersonalMessage(0, $userid, "Du wurdest zum '" . get_user_class_name($class) . "' $what", $msg, PM_FOLDERID_SYSTEM, 0);
        write_log($type, "Der Benutzer '<a href=\"userdetails.php?id=$userid\">$username</a>' wurde von $CURUSER[username] zum " . get_user_class_name($class) . " $what.");
        $updateset[] = "class = $class";
        $what = ($class > $curclass ? "Bef�rderung" : "Degradierung");
        write_modcomment($userid, $CURUSER["id"], "$what zum '" . get_user_class_name($class) . "'."); 
        // User has to re-accept rules if promoted to anything higher than UC_VIP
        if ($class > UC_VIP && $curclass < $class)
            $updateset[] = "accept_rules = 'no'";
    } 

    if ($warned && $curwarned != $warned) {
        $updateset[] = "warned = " . sqlesc($warned);
        $updateset[] = "warneduntil = '0000-00-00 00:00:00'";
        if ($warned == 'no') {
            $msg = "Deine Verwarnung wurde von " . $CURUSER['username'] . " zur�ckgenommen.\n\nFalls die Verwarnung nicht wegen Unrechtm��igkeit zur�ckgenommen wurde, achte bitte in Zukunft darauf, die Tracker-Regeln ernstzunehmen.";
            write_log("remwarn", "Die Verwarnung f�r Benutzer '<a href=\"userdetails.php?id=$userid\">$username</a>' wurde von $CURUSER[username] zur�ckgenommen.");
            write_modcomment($userid, $CURUSER["id"], "Die Verwarnung wurde zur�ckgenommen.");
            sendPersonalMessage(0, $userid, "Deine Verwarnung wurde zur�ckgenommen", $msg, PM_FOLDERID_SYSTEM, 0);
        } 
    } elseif ($warnlength) {
        if ($_POST["addwarnratio"] == "yes") {
            $warnratio = "\nRuntergeladen: " . mksize($curdownloaded) . "\nHochgeladen: " . mksize($curuploaded) . "\nRatio: " . number_format($curuploaded / $curdownloaded, 3);
        } else {
            $warnratio = "";
        } 
        if ($warnlength == 255) {
            $msg = "Du wurdest von " . $CURUSER['username'] . " [url=rules.php#warning]verwarnt[/url]." . ($warnpm ? "\n\nGrund: $warnpm" : "");
            write_modcomment($userid, $CURUSER["id"], "Verwarnung erteilt.\nGrund: $warnpm" . $warnratio);
            $updateset[] = "warneduntil = '0000-00-00 00:00:00'";
        } else {
            $warneduntil = get_date_time(time() + $warnlength * 604800);
            $dur = $warnlength . " Woche" . ($warnlength > 1 ? "n" : "");
            $msg = "Du wurdest f�r $dur von " . $CURUSER['username'] . " [url=rules.php#warning]verwarnt[/url]." . ($warnpm ? "\n\nGrund: $warnpm" : "");
            write_modcomment($userid, $CURUSER["id"], "Verwarnt f�r $dur.\nGrund: $warnpm" . $warnratio);
            $updateset[] = "warneduntil = '$warneduntil'";
        } 
        $added = sqlesc(get_date_time());
        write_log("addwarn", "Der Benutzer '<a href=\"userdetails.php?id=$userid\">$username</a>' wurde von $CURUSER[username] verwarnt ($warnpm).");
        sendPersonalMessage(0, $userid, "Du wurdest verwarnt", $msg . "\n\nFalls Du diese Verwarnung ungerechtfertigt findest, melde Dich bitte bei einem Teammitglied!", PM_FOLDERID_SYSTEM, 0);
        $updateset[] = "warned = 'yes'";
    } 

    if (is_array($waittime)) {
        foreach($waittime as $torrent => $ack) {
            $torrent = intval($torrent);
            $res = mysql_query("SELECT name FROM torrents WHERE id=$torrent");
            if (mysql_num_rows($res)) {
                $arr = mysql_fetch_assoc($res);
                $torrent_name = $arr["name"];
                $new_status = "";
                if ($ack == "yes") {
                    $msg = "Dein Antrag auf Aufhebung der Wartezeit f�r den Torrent '" . $torrent_name . "' wurde von " . $CURUSER['username'] . " angenommen. Du kannst nun beginnen, diesen Torrent zu nutzen.";
                    $new_status = "granted";
                    $log_type = "waitgrant";
                    $log_msg = "akzeptiert";
                } elseif ($ack == "no") {
                    $msg = "Dein Antrag auf Aufhebung der Wartezeit f�r den Torrent '" . $torrent_name . "' wurde von " . $CURUSER['username'] . " abgelehnt. Bitte beachte, dass eine erneute Antragstellung f�r diesen Torrent nicht m�glich ist!\n\nEbenso solltest Du Dir noch einmal die Regeln bzw. das FAQ zum Thema Wartezeitaufhebung durchlesen, bevor Du eine weitere Aufhebung beantragst. Beachte, dass h�ufige, nicht regelkonforme Antr�ge zu Verwarnungen f�hren k�nnen!";
                    $new_status = "rejected";
                    $log_type = "waitreject";
                    $log_msg = "abgelehnt";
                } 
                if ($new_status) {
                    mysql_query("UPDATE nowait SET `status`='$new_status',grantor=$CURUSER[id] WHERE user_id=$userid AND torrent_id=$torrent AND `status`='pending'");
                    if (mysql_affected_rows()) {
                        write_log($log_type, "Antrag auf Wartezeitaufhebung von '<a href=\"userdetails.php?id=$userid\">$username</a>' f�r Torrent '<a href=\"details.php?id=$torrent\">$torrent_name</a>' wurde von $CURUSER[username] $log_msg.");
                        sendPersonalMessage(0, $userid, "Dein Antrag auf Wartezeitaufhebung wurde $log_msg", $msg, PM_FOLDERID_SYSTEM, 0);
                        write_modcomment($userid, $CURUSER["id"], "Wartezeitaufhebung f�r Torrent '" . $torrent_name . " '$log_msg.");
                    } 
                } 
            } 
        } 
    } 

    if ($allowupload != $curallowupload) {
        $updateset[] = "allowupload = '$allowupload'";
        write_modcomment($userid, $CURUSER["id"], "Torrentupload " . ($allowupload == "yes"?"erlaubt":"gesperrt"));
    } 

    if ($enabled != $curenabled) {
        if ($enabled == 'yes') {
            write_modcomment($userid, $CURUSER["id"], "Account aktiviert. Grund:\n" . ($_POST["disablereason"] != ""?$_POST["disablereason"]:""));
            write_log("accenabled", "Benutzeraccount '<a href=\"userdetails.php?id=$userid\">$username</a>' wurde von $CURUSER[username] aktiviert.");
        } else {
            write_modcomment($userid, $CURUSER["id"], "Account deaktiviert. Grund:\n" . $_POST["disablereason"]);
            write_log("accdisabled", "Benutzeraccount '<a href=\"userdetails.php?id=$userid\">$username</a>' wurde von $CURUSER[username] deaktiviert (Grund: " . $_POST["disablereason"] . ").");
            $mailbody = "Dein Account auf " . $GLOBALS["SITENAME"] . " wurde von einem Moderator deaktiviert.
Du kannst dich ab sofort nicht mehr einloggen. Grund f�r diesen Schritt:

" . $_POST["disablereason"] . "

Bitte sehe in Zukunft davon ab, Dir einen neuen Account zu erstellen. Dieser
wird umgehend und ohne weitere Warnung deaktiviert werden.

Bei Fragen besuche uns im IRC.";
            mail($email, "Account $username auf " . $GLOBALS["SITENAME"] . " wurde deaktiviert", $mailbody);
        } 
    } 

    $acctdata = mysql_fetch_assoc(mysql_query("SELECT baduser,chash FROM accounts WHERE userid=$userid"));
    if (!is_array($acctdata)) {
        $hash = md5(mksecret());
        mysql_query("INSERT INTO `accounts` (`userid`,`chash`,`lastaccess`,`username`,`email`,`baduser`) VALUES (" . $row["id"] . "," . sqlesc($hash) . ", NOW(), " . sqlesc($username) . ", " . sqlesc($email) . ", " . ($baduser == "yes"?1:0) . ")");
    } else {
        $oldbaduser = ($acctdata["baduser"] == 1 ? "yes" : "no");
        if ($oldbaduser != $baduser) {
            mysql_query("UPDATE accounts SET baduser=" . ($baduser == "yes"?1:0) . " WHERE userid=$userid OR chash=" . sqlesc($acctdata["chash"]));
            write_modcomment($userid, $CURUSER["id"], "BAD-Flag " . ($baduser == "yes"?"gesetzt":"entfernt"));
        } 
    } 
    $updateset[] = "enabled = " . sqlesc($enabled);
    $updateset[] = "donor = " . sqlesc($donor);
    $updateset[] = "avatar = " . sqlesc($avatar);
    $updateset[] = "title = " . sqlesc($title);
    $updateset[] = "accept_rules = " . sqlesc($acceptrules);
    mysql_query("UPDATE users SET  " . implode(", ", $updateset) . " WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
    $returnto = $_POST["returnto"];

    header("Location: " . $GLOBALS["BASEURL"] . "/$returnto");
    die;
} 

puke();

?>