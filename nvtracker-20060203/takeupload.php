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

require_once("include/benc.php");
require_once("include/bittorrent.php");

hit_start();

// ACHTUNG: Nicht immer erlaubt! Bitte notfalls in der VHost-Config via:
//   php_admin_value upload_max_filesize WERT
// setzen. WERT kann z.B. 12M, 1000K oder eine Zahl in Bytes sein.
ini_set("upload_max_filesize", $GLOBALS["MAX_TORRENT_SIZE"] + 2 * $GLOBALS["MAX_UPLOAD_FILESIZE"]);

$GLOBALS["uploaderrors"] = Array();

function tr_msg($msg)
{
    echo "<tr><td class=\"tablea\" style=\"text-align:left;\">$msg</td>";
}

function tr_status($status)
{
    echo "<td class=\"tableb\" style=\"text-align:center;\"><img src=\"".$GLOBALS["PIC_BASE_URL"];
    if ($status == "ok")
        echo "button_online2.gif";
    else
        echo "button_offline2.gif";
    echo "\" width></td></tr>";
    flush();
} 

function abort($msg)
{
    end_table();
    end_frame();
    begin_frame("Torrent-Upload fehlgeschlagen!", FALSE, "650px");
    echo "<p>Beim Upload ist ein schwerwiegender Fehler aufgetreten:</p><p style=\"color:red\">$msg</p><p>Bitte korrigiere den angezeigten Fehler, und versuche es erneut!</p>";
    end_frame();
    stdfoot();
    die();
}

dbconn();

hit_count();

loggedinorreturn();

if (get_user_class() < UC_UPLOADER)
    $activated = "no";
else
    $activated = "yes";

if ($CURUSER["allowupload"] != "yes")
    stderr("Keine Uploadrechte!", "Du hast kein Recht, auf diesem Tracker Torrents hochzuladen, da diese Funktion f�r Deinen Account von einem Moderator deaktiviert wurde.");

foreach(explode(":", "descr:type:name") as $v) {
    if (!isset($_POST[$v]))
        stderr("Fehlende Formulardaten", "Die �bergebenen Daten sind unvollst�ndig. Bitte benutze das Upload-Formular, und f�lle alle n�tigen Felder aus!");
} 

if (!isset($_FILES["file"]))
    stderr("Fehlende Formulardaten", "Die �bergebenen Daten sind unvollst�ndig. Bitte benutze das Upload-Formular, und f�lle alle n�tigen Felder aus!");

stdhead();
begin_frame("�berpr�fe Upload...", FALSE, "650px");
begin_table(TRUE);

tr_msg("Dateiname der Torrent-Metadatei");
$f = $_FILES["file"];
$fname = unesc($f["name"]);
if (empty($fname)) {
    tr_status("err");
    abort("Torrent-Metadatei hat keinen Dateinamen bzw. es wurde kein Torrent hochgeladen!");
}
if (!validfilename($fname)) {
    tr_status("err");
    abort("Der Dateiname der Torrent-Datei enth�lt ung�ltige Zeichen!");
}
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) {
    tr_status("err");
    abort("Der Torrent-Dateiname muss mit \".torrent\" enden.");
}
$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname)) {
    tr_status("err");
    abort("Beim Upload der Torrent-Metadatei ist etwas schiefgegangen...");
}
tr_status("ok");

tr_msg("Max. Gr��e der Torrent-Metadatei");
if ($f["size"] > $GLOBALS["MAX_TORRENT_SIZE"]) {
    tr_status("err");
    abort("Torrent-Metadatei ist zu gro� (max. ".$GLOBALS["MAX_TORRENT_SIZE"]." Bytes)!");
}
if (!filesize($tmpname)) {
    tr_status("err");
    abort("Leere Torrent-Metadatei hochgeladen!");
}
tr_status("ok");

tr_msg("Dateiname der NFO-Datei");
$nfofile = $_FILES['nfo'];
if ($nfofile['name'] == '') {
    tr_status("err");
    abort("Die NFO hat keinen Dateinamen oder es wurde keine NFO-Datei hochgeladen!");
}
tr_status("ok");

tr_msg("Gr��e der NFO-Datei");
if ($nfofile['size'] == 0) {
    tr_status("err");
    abort("0-byte NFO");
}

if ($nfofile['size'] > 65535) {
    tr_status("err");
    abort("NFO ist zu gro�! Maximal 65535 Bytes (64 KB) sind erlaubt.");
}
tr_status("ok");

$nfofilename = $nfofile['tmp_name'];

tr_msg("Uploadstatus der NFO-Datei");
if (@!is_uploaded_file($nfofilename)) {
    tr_status("err");
    abort("NFO-Upload fehlgeschlagen");
}
tr_status("ok");

tr_msg("Torrent-Beschreibung");
$descr = unesc($_POST["descr"]);
if (trim($descr) == "") {
    tr_status("err");
    abort("Du musst eine Beschreibung eingeben!");
}
if (strlen($descr) > 20000) {
    tr_status("err");
    abort("Die angebene Beschreibung ist zu gro�. Maximal 20000 Zeichen sind erlaubt!");
}
tr_status("ok");

if ($_POST["stripasciiart"] == "1") {
    $descr = strip_ascii_art($descr);
}

tr_msg("Kategorie-Zuordnung");
$catid = (0 + $_POST["type"]);
if (!is_valid_id($catid)) {
    tr_status("err");
    abort("Du musst eine Kategorie angeben, welcher der Torrent zugeordnet werden soll.");
}
tr_status("ok");


$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"]))
    $torrent = unesc($_POST["name"]);

tr_msg("Torrent-Metadatei dekodieren");
$dict = bdec_file($tmpname, $GLOBALS["MAX_TORRENT_SIZE"]);
if (!isset($dict)) {
    tr_status("err");
    abort("Was zum Teufel hast du da hochgeladen? Das ist jedenfalls keine g�ltige Torrent-Datei!");
}
tr_status("ok");

function dict_check($d, $s, $type = "")
{
    if ($type != "")
        tr_msg("Integrit�tspr�fung der Metadaten ($type)");
    if ($d["type"] != "dictionary") {
        tr_status("err");
        abort("Die Datei ist kein BEnc-Dictionary.");
    }
    $a = explode(":", $s);
    $dd = $d["value"];
    $ret = array();
    foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
            $k = $m[1];
            $t = $m[2];
        } 
        if (!isset($dd[$k])) {
            tr_status("err");
            abort("Es fehlt ein ben�tigter Schl�ssel im Dictionary!");
        }
        if (isset($t)) {
            if ($dd[$k]["type"] != $t) {
                tr_status("err");
                abort("Das Dictionary enth�lt einen ung�ltigen Eintrag (Tats�chlicher Datentyp entspricht nicht dem erwarteten)!");
            }
            $ret[] = $dd[$k]["value"];
        } else
            $ret[] = $dd[$k];
    }
    if ($type != "")
        tr_status("ok");
    return $ret;
} 

function dict_get($d, $k, $t)
{
    if ($d["type"] != "dictionary")
        abort("Unerwarteter Fehler beim Dekodieren der Metadaten: Das ist kein Dictionary (".$d["type"].")!");
    $dd = $d["value"];
    if (!isset($dd[$k]))
        return;
    $v = $dd[$k];
    if ($v["type"] != $t)
        abort("Unerwarteter Fehler beim Dekodieren der Metadaten: Der Datentyp des Eintrags (".$v["type"].") enspricht nicht dem erwarteten Typ ($t)!");
    return $v["value"];
} 

list($ann, $info) = dict_check($dict, "announce(string):info", "Globales Dictionary");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)", "Info-Dictionary");

tr_msg("Announce-URL");
if (!in_array($ann, $GLOBALS["ANNOUNCE_URLS"], 1)) {
    tr_status("err");
    $errstr = "Ung�ltige Announce-URL! Muss eine der Folgenden sein:</p><ul>";
    sort($GLOBALS["ANNOUNCE_URLS"]);
    foreach ($GLOBALS["ANNOUNCE_URLS"] as $aurl)
        $errstr .= "<li>".htmlspecialchars($aurl)."</li>";
    abort($errstr . "</ul><p>");
}
tr_status("ok");


tr_msg("Plausibilit�tspr�fung und Einlesen der Dateiliste");
$totallen = dict_get($info, "length", "integer");
$filelist = array();
if ($totallen > 0) {
    $filelist[] = array($dname, $totallen);
    $type = "single";
} else {
    $flist = dict_get($info, "files", "list");
    if (!isset($flist)) {
        tr_status("err");
        abort("Es fehlen sowohl der \"length\"- als auch der \"files\"-Schl�ssel im Info-Dictionary!");
    }
    if (!count($flist)) {
        tr_status("err");
        abort("Der Torrent enth�lt keine Dateien");
    }
    $totallen = 0;
    foreach ($flist as $fn) {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;
        $ffa = array();
        foreach ($ff as $ffe) {
            if ($ffe["type"] != "string") {
                tr_status("err");
                abort("Ein Eintrag in der Dateinamen-Liste hat einen ung�ltigen Datentyp (".$ffe["type"].")");
            }
            if (preg_match('/^[.\\/^~][\/\^]*/', $ffe["value"])) {
                tr_status("err");
                abort("Eine Datei in der Torrent-Metadatei hat einen ung�ltigen Namen (".$ffe["value"].")");
            }
            $ffa[] = $ffe["value"];
        } 
        if (!count($ffa))
            bark("filename error");
        $ffe = implode("/", $ffa);
        $filelist[] = array($ffe, $ll);
    } 
    $type = "multi";
}
tr_status("ok");

tr_msg("Plausibilit�tspr�fung der Piece-Hashes");
if (strlen($pieces) % 20 != 0) {
    tr_status("err");
    abort("Die L�nge der Piece-Hashes ist kein Vielfaches von 20!");
}
$numpieces = strlen($pieces)/20;
if ($numpieces != ceil($totallen/$plen)) {
    tr_status("err");
    abort("Die Anzahl Piecehashes stimmt nicht mit der Torrentl�nge �berein (".$numpieces." ungleich ".ceil($totallen/$plen).")!");
}
tr_status("ok");

// Add private flag to prevent Azureus 2.3.0.0+ from sharing the peers with other clients.
// The array equals to the return value of bdec("7:privatei1e").
$dict["value"]["info"]["value"]["private"] = array("type" => "integer", "value" => "1");
// Adding another random ID will prevent every other peer-sharing client
// (e.g. BitComet) from finding other peers for this torrent, since the infohash
// is unique.
$dict["value"]["info"]["value"]["unique id"] = array("type" => "string", "value" => mksecret());
// Use the new info string to calculate the hash
$infohash = pack("H*", sha1(benc($dict["value"]["info"])));
// Replace punctuation characters with spaces
$torrent = str_replace("_", " ", $torrent);

tr_msg("Torrent-Informationen in die Datenbank schreiben");
$nfo = str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename));
$ret = mysql_query("INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo, activated) VALUES (" .
    implode(",", array_map("sqlesc", array(
	searchfield("$shortfname $dname $torrent"),
	$fname,
	$CURUSER["id"],
	"no",
	$infohash,
	$torrent,
	$totallen,
	count($filelist),
	$type,
	$descr,
	$descr,
	0 + $_POST["type"],
	$dname)
    )) . ", '" . get_date_time() . "', '" . get_date_time() . "', ".sqlesc($nfo).", '$activated')");
    
if (!$ret) {
    tr_status("err");
    if (mysql_errno() == 1062)
        abort("Dieser Torrent wurde bereits hochgeladen!");
    abort("MySQL hat einen Fehler ausgegeben: " . mysql_error() . " (".mysql_errno().")");
} 
$id = mysql_insert_id();

@mysql_query("DELETE FROM files WHERE torrent = $id");
foreach ($filelist as $file) {
    @mysql_query("INSERT INTO files (torrent, filename, size) VALUES ($id, " . sqlesc($file[0]) . "," . $file[1] . ")");
} 
tr_status("ok");

tr_msg("Torrent-Datei auf dem Server speichern");
// We don't move the file anymore, we rather write the changed,
// bencoded version of our dictionary.
$fhandle = fopen($GLOBALS["TORRENT_DIR"]."/$id.torrent", "w");
if ($fhandle) {
    fwrite($fhandle, benc($dict));
    fclose($fhandle);
} else {
    tr_status("err");
    abort("Fehler beim �ffnen der Torrent-Datei auf dem Server (Schreibzugriff verweigert) - bitte SysOp benachrichtigen!");
} 
tr_status("ok");

write_log("torrentupload", "Der Torrent <a href=\"details.php?id=$id\">$id ($torrent)</a> wurde von '<a href=\"userdetails.php?id=$CURUSER[id]\">$CURUSER[username]</a>' hochgeladen.");

// Handle picture uploads
$picnum = 0;
if ($_FILES["pic1"]["name"] != "") {
    tr_msg("Vorschaubild ".($picnum+1)." verkleinern und ablegen");
    if (torrent_image_upload($_FILES["pic1"], $id, $picnum+1))
        $picnum++;
} 

if ($_FILES["pic2"]["name"] != "") {    
    tr_msg("Vorschaubild ".($picnum+1)." verkleinern und ablegen");
    if (torrent_image_upload($_FILES["pic2"], $id, $picnum+1))
        $picnum++;
} 

if ($picnum)
    @mysql_query("UPDATE torrents SET numpics=$picnum WHERE id=$id");

// Create NFO image
tr_msg("NFO-Bild erzeugen");
if (gen_nfo_pic($nfo, $GLOBALS["BITBUCKET_DIR"]."/nfo-$id.png") == 0)
    tr_status("err");
else
    tr_status("ok");

/* RSS feeds */
// Now dynamically handled by rss.php!

if ($activated == "no") {
    tr_msg("Gastuploader-Team und Moderatoren benachrichtigen");
    // Send team message
    $mod_msg = "[b]Der Benutzer [url=".$DEFAULTBASEURL."/userdetails.php?id=".$CURUSER["id"]."]".$CURUSER["username"]."[/url] hat einen Torrent hochgeladen:[/b]\n\n[url=".$DEFAULTBASEURL."/details.php?id=".$id."]".$torrent."[/url] (".$id.")\n\nBitte �berpr�fen und freischalten/l�schen.";
    //sendPersonalMessage(0, 0, "Der Benutzer ".$CURUSER["username"]." hat einen Torrent hochgeladen.", $mod_msg, PM_FOLDERID_MOD, 0, "open");
    
    // Send a system message to each member of the guest upload team
    $res = mysql_query("SELECT `id` FROM `users` WHERE `class` = ".UC_GUTEAM);
    while ($uid = mysql_fetch_assoc($res))
        sendPersonalMessage(0, $uid["id"], "Der Benutzer ".$CURUSER["username"]." hat einen Torrent hochgeladen.", $mod_msg);
    tr_status("ok");
}

end_table();
end_frame();
begin_frame("Torrent-Upload war erfolgreich!", FALSE, "650px");
?>
<p>Dein Torrent wurde erfolgreich hochgeladen. <b>Beachte</b> dass Dein Torrent erst
sichtbar wird, wenn der erste Seeder verf�gbar ist!</p>
<?php

if (count($GLOBALS["uploaderrors"])) {
?>
<p>Beim Upload des Torrents ist mindestens ein unkritischer Fehler aufgetreten:</p>
<ul>
<?php
foreach($GLOBALS["uploaderrors"] as $error)
    echo "<li>$error</li>";
?>
</ul>
<?php
}

if ($activated == "no") {
?>
<p><b>Da Du kein Uploader bist, wurde Dein Torrent als Gastupload gewertet, und muss
zuerst von einem Gastupload-Betreuer &uuml;berpr&uuml;ft und freigeschaltet werden.
Erst dann kannst Du den Torrent zum Seeden herunterladen.</b> Bitte sende uns keine
Nachrichten mit der Bitte um Freischaltung. Das Team wurde bereits per PN &uuml;ber
Deinen Upload benachrichtigt, und wird sich baldm&ouml;glichst darum k&uuml;mmern.</p>
<?php } ?>
<p><b>Wichtiger Hinweis:</b><br>Bevor Du den Torrent seeden kannst, musst Du den Torrent
erneut vom Tracker herunterladen, da beim Upload einige �nderungen an der Torrent-Datei
vorgenommen wurden. Dadurch hat der Torrent einen neuen Info-Hash erhalten, und beim
Download wird ebenfalls Dein PassKey in die Announce-URL eingef�gt. <b>Das
&Auml;ndern der Announce-URL in Deiner soeben hochgeladenen Torrent-Metadatei gen&uuml;gt
nicht!</b></p>
<p style="text-align:center"><a href="details.php?id=<?=$id?>">Weiter zu den Details Deines Torrents</a></p>
<?php

end_frame();
stdfoot();

hit_end();

?>
