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

ob_start("ob_gzhandler");
require "include/bittorrent.php";
// 0 - No debug; 1 - Show and run SQL query; 2 - Show SQL query only
$DEBUG_MODE = 0;

dbconn();
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
    stderr("Fehler", "Zugriff verweigert.");

stdhead("Administrative Benutzersuche");
begin_frame("Administrative Benutzersuche", false, "650px");

if ($_GET['h']) {
    begin_table(true);

    echo "<tr><td class=tablea><div align=left>\n
    Frei gelassene Felder werden ignoriert;\n
        Die Platzhalter * and ? k�nnen in den Feldern Name, Email and Mod-Kommentar benutzt werden,\n
        ebenso mehrere Werte, die mit Leerzeichen getrennt wurden (z.B. \"wyz Max*\"\n
        wird sowohl Benutzer anzeigen, die \"wyz\" hei�en, als auch diejenigen, die mit \"Max\"\n
        beginnen). Ebenso kann \"~\" benutzt werden, um einen Begriff auszuschlie�en.\n
        \"~alfiest\" wird also alle Benutzer finden, die den Begriff \"alfiest\" nicht in ihrem\n
        Mod-Kommentar haben.<br><br>\n
    Das Ratio-Feld akzeptiert \"Inf\" und \"---\" neben den �blichen numerischen Werten.<br><br>\n
        Die Subnet-Mask kann entweder in der Punkt- oer CIDR-Notation eingegeben werden\n
        (z.B. ist 255.255.255.0 das selbe wie /24).<br><br>\n
    Upload und Download sollten in GB eingegeben werden.<br><br>\n
        Falls es f�r einen Suchparameter mehrere Eingabefelder gibt, wird das\n
        zweite Textfeld ignoriert, au�er es ist ein entsprechender Operator ausgew�hlt.<br><br>\n
    \"Nur Aktive\" schr�nkt die Suche auf Benutzer ein, die aktuell Seeden oder Leechen,\n
        \"Deaktivierte IPs\" auf diejenigen, deren IPs auch in deaktivierten Accounts auftauchen.<br><br>\n
    Die \"p\"-Spalten in den Suchergebnissen zeigen partielle Statistiken an, sprich\n
        Stats der gerade aktiven Torrents.<br><br>\n
    Die \"Verlauf\"-Spalte zeigt die Anzahl der Forum-Beitr�ge und Torrent-Kommentare an,\n
        und verlinkt auf die Verlauf-Seite.\n
        </div></td></tr>\n";
    end_table();
} else {
    echo "<p align=center><a href='" . $_SERVER["PHP_SELF"] . "?h=1'>Anleitung</a>";
    echo "&nbsp;-&nbsp;<a href='" . $_SERVER["PHP_SELF"] . "'>Zur�cksetzen</a></p>\n";
} 

$highlight = " class=tablecat";
$nohighlight = " class=tablea";

?>

<form method=get action=<?=$_SERVER["PHP_SELF"]?>>
<?php begin_table(true);
?>
<tr>

  <td valign="middle" class="tableb">Name:</td>
  <td<?=$_GET['n']?$highlight:$nohighlight?>><input name="n" type="text" value="<?=$_GET['n']?>" size=35></td>

  <td valign="middle" class="tableb">Ratio:</td>
  <td<?=$_GET['r']?$highlight:$nohighlight?>><select name="rt">
    <?php
$options = array("equal", "above", "below", "between");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['rt'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
    </select>
    <input name="r" type="text" value="<?=$_GET['r']?>" size="5" maxlength="4">
    <input name="r2" type="text" value="<?=$_GET['r2']?>" size="5" maxlength="4"></td>

  <td valign="middle" class="tableb">Mitglieds-Status:</td>
  <td<?=$_GET['st']?$highlight:$nohighlight?>><select name="st">
    <?php
$options = array("(Beliebig)", "Best�tigt", "Unbest�tigt");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['st'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
    </select></td></tr>
<tr><td valign="middle" class="tableb">E-Mail:</td>
  <td<?=$_GET['em']?$highlight:$nohighlight?>><input name="em" type="text" value="<?=$_GET['em']?>" size="35"></td>
  <td valign="middle" class="tableb">IP:</td>
  <td<?=$_GET['ip']?$highlight:$nohighlight?>><input name="ip" type="text" value="<?=$_GET['ip']?>" maxlength="17"></td>

  <td valign="middle" class="tableb">Account-Status:</td>
  <td<?=$_GET['as']?$highlight:$nohighlight?>><select name="as">
    <?php
$options = array("(Beliebig)", "Aktiviert", "Deaktiviert");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['as'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
    </select></td></tr>
<tr>
  <td valign="middle" class="tableb">Mod-Kommentar:</td>
  <td<?=$_GET['co']?$highlight:$nohighlight?>><input name="co" type="text" value="<?=$_GET['co']?>" size="35"></td>
  <td valign="middle" class="tableb">Subnetz-Maske:</td>
  <td<?=$_GET['ma']?$highlight:$nohighlight?>><input name="ma" type="text" value="<?=$_GET['ma']?>" maxlength="17"></td>
  <td valign="middle" class="tableb">Klasse:</td>
  <td<?=($_GET['c'] && $_GET['c'] != 1)?$highlight:$nohighlight?>><select name="c"><option value='1'>(Beliebig)</option>
  <?php
$class = $_GET['c'];
if (!is_valid_id($class))
    $class = '';
for ($i = 2;$i<=UC_SYSOP+2;++$i) {
    if ($c = get_user_class_name($i-2))
        print("<option value=" . $i . ($class && $class == $i? " selected" : "") . ">$c</option>\n");
    else
        continue;
} 

?>
    </select></td></tr>
<tr>

    <td valign="middle" class="tableb">Registiert:</td>

  <td<?=$_GET['d']?$highlight:$nohighlight?>><select name="dt">
    <?php
$options = array("Am", "Vor", "Nach", "Zwischen");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['dt'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
    </select>

    <input name="d" type="text" value="<?=$_GET['d']?>" size="12" maxlength="10">

    <input name="d2" type="text" value="<?=$_GET['d2']?>" size="12" maxlength="10"></td>


  <td valign="middle" class="tableb">Upload:</td>

  <td<?=$_GET['ul']?$highlight:$nohighlight?>><select name="ult" id="ult">
    <?php
$options = array("Exakt", "Mindestens", "H�chstens", "Zwischen");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['ult'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
    </select>

    <input name="ul" type="text" id="ul" size="8" maxlength="7" value="<?=$_GET['ul']?>">

    <input name="ul2" type="text" id="ul2" size="8" maxlength="7" value="<?=$_GET['ul2']?>"></td>
  <td valign="middle" class="tableb">Gespendet:</td>

  <td<?=$_GET['do']?$highlight:$nohighlight?>><select name="do">
    <?php
$options = array("(Beliebig)", "Ja", "Nein");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['do'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
        </select></td></tr>
<tr>

<td valign="middle" class="tableb">Zuletzt gesehen:</td>

  <td <?=$_GET['ls']?$highlight:$nohighlight?>><select name="lst">
  <?php
$options = array("Am", "Vor", "Nach", "Zwischen");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['lst'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
  </select>

  <input name="ls" type="text" value="<?=$_GET['ls']?>" size="12" maxlength="10">

  <input name="ls2" type="text" value="<?=$_GET['ls2']?>" size="12" maxlength="10"></td>
          <td valign="middle" class="tableb">Download:</td>

  <td<?=$_GET['dl']?$highlight:$nohighlight?>><select name="dlt" id="dlt">
  <?php
$options = array("Exakt", "Mindestens", "H�chstens", "Zwischen");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['dlt'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
    </select>

    <input name="dl" type="text" id="dl" size="8" maxlength="7" value="<?=$_GET['dl']?>">

    <input name="dl2" type="text" id="dl2" size="8" maxlength="7" value="<?=$_GET['dl2']?>"></td>

        <td valign="middle" class="tableb">Verwarnt:</td>

        <td<?=$_GET['w']?$highlight:$nohighlight?>><select name="w">
  <?php
$options = array("(Beliebig)", "Ja", "Nein");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['w'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
        </select></td></tr>

<tr>
  <td class="tableb">Sortieren nach</td>
  <td class="tableb"><select name="s" size="1">
  <?php
$options = array("Benutzername", "Zuletzt gesehen", "Registriert", "Ratio");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['s'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
  </select> <select name="so" size="1">
  <?php
$options = array("Aufsteigend", "Absteigend");
for ($i = 0; $i < count($options); $i++) {
    echo "<option value=$i " . (($_GET['so'] == "$i")?"selected":"") . ">" . $options[$i] . "</option>\n";
} 

?>
  </select></td>
  <td valign="middle" class="tableb">Nur Aktive:</td>
  <td<?=$_GET['ac']?$highlight:$nohighlight?>><input name="ac" type="checkbox" value="1" <?=($_GET['ac'])?"checked":"" ?>></td>
  <td valign="middle" class="tableb">Deaktivierte IPs: </td>
  <td<?=$_GET['dip']?$highlight:$nohighlight?>><input name="dip" type="checkbox" value="1" <?=($_GET['dip'])?"checked":"" ?>></td>

  </tr>
<tr><td colspan="6" class="tablea" style="text-align:center"><input name="submit" type=submit class=btn></td></tr>
<?php end_table();
?>
</form>

<?php
end_frame();

begin_frame("Suchergebnis", false, "650px");
// Validates date in the form [yy]yy-mm-dd;
// Returns date if valid, 0 otherwise.
function mkdate($date)
{
    if (strpos($date, '-'))
        $a = explode('-', $date);
    elseif (strpos($date, '/'))
        $a = explode('/', $date);
    else
        return 0;
    for ($i = 0;$i < 3;$i++)
    if (!is_numeric($a[$i]))
        return 0;
    if (checkdate($a[1], $a[2], $a[0]))
        return date ("Y-m-d", mktime (0, 0, 0, $a[1], $a[2], $a[0]));
    else
        return 0;
} 
// ratio as a string
function ratios($up, $down, $color = true)
{
    if ($down > 0) {
        $r = number_format($up / $down, 2);
        if ($color)
            $r = "<font color=" . get_ratio_color($r) . ">$r</font>";
    } else
    if ($up > 0)
        $r = "Inf.";
    else
        $r = "---";
    return $r;
} 
// checks for the usual wildcards *, ? plus mySQL ones
function haswildcard($text)
{
    if (strpos($text, '*') === false && strpos($text, '?') === false && strpos($text, '%') === false && strpos($text, '_') === false)
        return false;
    else
        return true;
} 
// /////////////////////////////////////////////////////////////////////////////
if (count($_GET) > 0 && !$_GET['h']) {
    // name
    $names = explode(' ', trim($_GET['n']));
    if ($names[0] !== "") {
        foreach($names as $name) {
            if (substr($name, 0, 1) == '~') {
                if ($name == '~') continue;
                $names_exc[] = substr($name, 1);
            } else
                $names_inc[] = $name;
        } 

        if (is_array($names_inc)) {
            $where_is .= isset($where_is)?" AND (":"(";
            foreach($names_inc as $name) {
                if (!haswildcard($name))
                    $name_is .= (isset($name_is)?" OR ":"") . "u.username = " . sqlesc($name);
                else {
                    $name = str_replace(array('?', '*'), array('_', '%'), $name);
                    $name_is .= (isset($name_is)?" OR ":"") . "u.username LIKE " . sqlesc($name);
                } 
            } 
            $where_is .= $name_is . ")";
            unset($name_is);
        } 

        if (is_array($names_exc)) {
            $where_is .= isset($where_is)?" AND NOT (":" NOT (";
            foreach($names_exc as $name) {
                if (!haswildcard($name))
                    $name_is .= (isset($name_is)?" OR ":"") . "u.username = " . sqlesc($name);
                else {
                    $name = str_replace(array('?', '*'), array('_', '%'), $name);
                    $name_is .= (isset($name_is)?" OR ":"") . "u.username LIKE " . sqlesc($name);
                } 
            } 
            $where_is .= $name_is . ")";
        } 
        $q .= ($q ? "&amp;" : "") . "n=" . urlencode(trim($_GET['n']));
    } 
    // email
    $emaila = explode(' ', trim($_GET['em']));
    if ($emaila[0] !== "") {
        $where_is .= isset($where_is)?" AND (":"(";
        foreach($emaila as $email) {
            if (strpos($email, '*') === false && strpos($email, '?') === false && strpos($email, '%') === false) {
                if (validemail($email) !== 1) {
                    stdmsg("Fehler", "Ung�ltige E-Mail-Adresse.");
                    stdfoot();
                    die();
                } 
                $email_is .= (isset($email_is)?" OR ":"") . "u.email =" . sqlesc($email);
            } else {
                $sql_email = str_replace(array('?', '*'), array('_', '%'), $email);
                $email_is .= (isset($email_is)?" OR ":"") . "u.email LIKE " . sqlesc($sql_email);
            } 
        } 
        $where_is .= $email_is . ")";
        $q .= ($q ? "&amp;" : "") . "em=" . urlencode(trim($_GET['em']));
    } 
    // class
    // NB: the c parameter is passed as two units above the real one
    $class = $_GET['c'] - 2;
    if (is_valid_id($class + 1)) {
        $where_is .= (isset($where_is)?" AND ":"") . "u.class=$class";
        $q .= ($q ? "&amp;" : "") . "c=" . ($class + 2);
    } 
    // IP
    $ip = trim($_GET['ip']);
    if ($ip) {
        $regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
        if (!preg_match($regex, $ip)) {
            stdmsg("Fehler", "Ung�ltige IP.");
            stdfoot();
            die();
        } 

        $mask = trim($_GET['ma']);
        if ($mask == "" || $mask == "255.255.255.255")
            $where_is .= (isset($where_is)?" AND ":"") . "u.ip = '$ip'";
        else {
            if (substr($mask, 0, 1) == "/") {
                $n = substr($mask, 1, strlen($mask) - 1);
                if (!is_numeric($n) or $n < 0 or $n > 32) {
                    stdmsg("Fehler", "Ung�ltige Subnetz-Maske.");
                    stdfoot();
                    die();
                } else
                    $mask = long2ip(pow(2, 32) - pow(2, 32 - $n));
            } elseif (!preg_match($regex, $mask)) {
                stdmsg("Fehler", "Ung�ltige Subnetz-Maske.");
                stdfoot();
                die();
            } 
            $where_is .= (isset($where_is)?" AND ":"") . "INET_ATON(u.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
            $q .= ($q ? "&amp;" : "") . "ma=$mask";
        } 
        $q .= ($q ? "&amp;" : "") . "ip=$ip";
    } 
    // ratio
    $ratio = trim($_GET['r']);
    if ($ratio) {
        if ($ratio == '---') {
            $ratio2 = "";
            $where_is .= isset($where_is)?" AND ":"";
            $where_is .= " u.uploaded = 0 and u.downloaded = 0";
        } elseif (strtolower(substr($ratio, 0, 3)) == 'inf') {
            $ratio2 = "";
            $where_is .= isset($where_is)?" AND ":"";
            $where_is .= " u.uploaded > 0 and u.downloaded = 0";
        } else {
            if (!is_numeric($ratio) || $ratio < 0) {
                stdmsg("Fehler", "Ung�ltige Ratio.");
                stdfoot();
                die();
            } 
            $where_is .= isset($where_is)?" AND ":"";
            $where_is .= " (u.uploaded/u.downloaded)";
            $ratiotype = $_GET['rt'];
            $q .= ($q ? "&amp;" : "") . "rt=$ratiotype";
            if ($ratiotype == "3") {
                $ratio2 = trim($_GET['r2']);
                if (!$ratio2) {
                    stdmsg("Fehler", "F�r diesen Operator m�ssen zwei Ratios angegeben werden.");
                    stdfoot();
                    die();
                } 
                if (!is_numeric($ratio2) or $ratio2 < $ratio) {
                    stdmsg("Fehler", "Ung�ltige zweite Ratio.");
                    stdfoot();
                    die();
                } 
                $where_is .= " BETWEEN $ratio and $ratio2";
                $q .= ($q ? "&amp;" : "") . "r2=$ratio2";
            } elseif ($ratiotype == "2")
                $where_is .= " < $ratio";
            elseif ($ratiotype == "1")
                $where_is .= " > $ratio";
            else
                $where_is .= " BETWEEN ($ratio - 0.004) and ($ratio + 0.004)";
        } 
        $q .= ($q ? "&amp;" : "") . "r=$ratio";
    } 
    // comment
    $comments = explode(' ', trim($_GET['co']));
    if ($comments[0] !== "") {
        $distinct = "DISTINCT ";
        foreach($comments as $comment) {
            if (substr($comment, 0, 1) == '~') {
                if ($comment == '~') continue;
                $comments_exc[] = substr($comment, 1);
            } else
                $comments_inc[] = $comment;
        } 

        if (is_array($comments_inc)) {
            $where_is .= isset($where_is)?" AND (":"(";
            foreach($comments_inc as $comment) {
                if (!haswildcard($comment))
                    $comment_is .= (isset($comment_is)?" OR ":"") . "mc.txt LIKE " . sqlesc("%" . $comment . "%");
                else {
                    $comment = str_replace(array('?', '*'), array('_', '%'), $comment);
                    $comment_is .= (isset($comment_is)?" OR ":"") . "mc.txt LIKE " . sqlesc($comment);
                } 
            } 
            $where_is .= $comment_is . ")";
            unset($comment_is);
        } 

        if (is_array($comments_exc)) {
            $where_is .= isset($where_is)?" AND NOT (":" NOT (";
            foreach($comments_exc as $comment) {
                if (!haswildcard($comment))
                    $comment_is .= (isset($comment_is)?" OR ":"") . "mc.txt LIKE " . sqlesc("%" . $comment . "%");
                else {
                    $comment = str_replace(array('?', '*'), array('_', '%'), $comment);
                    $comment_is .= (isset($comment_is)?" OR ":"") . "mc.txt LIKE " . sqlesc($comment);
                } 
            } 
            $where_is .= $comment_is . ")";
        } 
        $q .= ($q ? "&amp;" : "") . "co=" . urlencode(trim($_GET['co']));
        $join_is .= " JOIN modcomments AS mc ON mc.userid=u.id";
    } 

    $unit = 1073741824; // 1GB
     
    // uploaded
    $ul = trim($_GET['ul']);
    if ($ul) {
        if (!is_numeric($ul) || $ul < 0) {
            stdmsg("Fehler", "Ung�ltige Upload-Menge. Bitte eine Zahl gleich oder gr��er Null eingeben, Wert in GB.");
            stdfoot();
            die();
        } 
        $where_is .= isset($where_is)?" AND ":"";
        $where_is .= " u.uploaded ";
        $ultype = $_GET['ult'];
        $q .= ($q ? "&amp;" : "") . "ult=$ultype";
        if ($ultype == "3") {
            $ul2 = trim($_GET['ul2']);
            if (!$ul2) {
                stdmsg("Fehler", "F�r diesen Operator werden zwei Upload-Mengen ben�tigt.");
                stdfoot();
                die();
            } 
            if (!is_numeric($ul2) or $ul2 < $ul) {
                stdmsg("Fehler", "Ung�ltige zweite Upload-Menge. Bitte eine Zahl gleich oder gr��er Null eingeben, Wert in GB.");
                stdfoot();
                die();
            } 
            $where_is .= " BETWEEN " . $ul * $unit . " and " . $ul2 * $unit;
            $q .= ($q ? "&amp;" : "") . "ul2=$ul2";
        } elseif ($ultype == "2")
            $where_is .= " < " . $ul * $unit;
        elseif ($ultype == "1")
            $where_is .= " >" . $ul * $unit;
        else
            $where_is .= " BETWEEN " . ($ul - 0.004) * $unit . " and " . ($ul + 0.004) * $unit;
        $q .= ($q ? "&amp;" : "") . "ul=$ul";
    } 
    // downloaded
    $dl = trim($_GET['dl']);
    if ($dl) {
        if (!is_numeric($dl) || $dl < 0) {
            stdmsg("Fehler", "Ung�ltige Download-Menge. Bitte eine Zahl gleich oder gr��er Null eingeben, Wert in GB.");
            stdfoot();
            die();
        } 
        $where_is .= isset($where_is)?" AND ":"";
        $where_is .= " u.downloaded ";
        $dltype = $_GET['dlt'];
        $q .= ($q ? "&amp;" : "") . "dlt=$dltype";
        if ($dltype == "3") {
            $dl2 = trim($_GET['dl2']);
            if (!$dl2) {
                stdmsg("Fehler", "F�r diesen Operator werden zwei Download-Mengen ben�tigt.");
                stdfoot();
                die();
            } 
            if (!is_numeric($dl2) or $dl2 < $dl) {
                stdmsg("Fehler", "Ung�ltige zweite Download-Menge. Bitte eine Zahl gleich oder gr��er Null eingeben, Wert in GB.");
                stdfoot();
                die();
            } 
            $where_is .= " BETWEEN " . $dl * $unit . " and " . $dl2 * $unit;
            $q .= ($q ? "&amp;" : "") . "dl2=$dl2";
        } elseif ($dltype == "2")
            $where_is .= " < " . $dl * $unit;
        elseif ($dltype == "1")
            $where_is .= " > " . $dl * $unit;
        else
            $where_is .= " BETWEEN " . ($dl - 0.004) * $unit . " and " . ($dl + 0.004) * $unit;
        $q .= ($q ? "&amp;" : "") . "dl=$dl";
    } 
    // date joined
    $date = trim($_GET['d']);
    if ($date) {
        if (!$date = mkdate($date)) {
            stdmsg("Fehler", "Ung�ltiges Datum.");
            stdfoot();
            die();
        } 
        $q .= ($q ? "&amp;" : "") . "d=$date";
        $datetype = $_GET['dt'];
        $q .= ($q ? "&amp;" : "") . "dt=$datetype";
        if ($datetype == "0") 
            // For mySQL 4.1.1 or above use instead
            // $where_is .= (isset($where_is)?" AND ":"")."DATE(added) = DATE('$date')";
            $where_is .= (isset($where_is)?" AND ":"") . "(UNIX_TIMESTAMP(added) - UNIX_TIMESTAMP('$date')) BETWEEN 0 and 86400";
        else {
            $where_is .= (isset($where_is)?" AND ":"") . "u.added ";
            if ($datetype == "3") {
                $date2 = mkdate(trim($_GET['d2']));
                if ($date2) {
                    if (!$date = mkdate($date)) {
                        stdmsg("Fehler", "Ung�ltiges Datum.");
                        stdfoot();
                        die();
                    } 
                    $q .= ($q ? "&amp;" : "") . "d2=$date2";
                    $where_is .= " BETWEEN '$date' and '$date2'";
                } else {
                    stdmsg("Fehler", "F�r diesen Operator werden zwei Daten ben�tigt.");
                    stdfoot();
                    die();
                } 
            } elseif ($datetype == "1")
                $where_is .= "< '$date'";
            elseif ($datetype == "2")
                $where_is .= "> '$date'";
        } 
    } 
    // date last seen
    $last = trim($_GET['ls']);
    if ($last) {
        if (!$last = mkdate($last)) {
            stdmsg("Fehler", "Ung�ltiges Datum.");
            stdfoot();
            die();
        } 
        $q .= ($q ? "&amp;" : "") . "ls=$last";
        $lasttype = $_GET['lst'];
        $q .= ($q ? "&amp;" : "") . "lst=$lasttype";
        if ($lasttype == "0") 
            // For mySQL 4.1.1 or above use instead
            // $where_is .= (isset($where_is)?" AND ":"")."DATE(added) = DATE('$date')";
            $where_is .= (isset($where_is)?" AND ":"") . "(UNIX_TIMESTAMP(last_access) - UNIX_TIMESTAMP('$last')) BETWEEN 0 and 86400";
        else {
            $where_is .= (isset($where_is)?" AND ":"") . "u.last_access ";
            if ($lasttype == "3") {
                $last2 = mkdate(trim($_GET['ls2']));
                if ($last2) {
                    $where_is .= " BETWEEN '$last' and '$last2'";
                    $q .= ($q ? "&amp;" : "") . "ls2=$last2";
                } else {
                    stdmsg("Fehler", "Das zweite Datum ist ung�ltig.");
                    stdfoot();
                    die();
                } 
            } elseif ($lasttype == "1")
                $where_is .= "< '$last'";
            elseif ($lasttype == "2")
                $where_is .= "> '$last'";
        } 
    } 
    // status
    $status = $_GET['st'];
    if ($status) {
        $where_is .= ((isset($where_is))?" AND ":"");
        if ($status == "1")
            $where_is .= "u.status = 'confirmed'";
        else
            $where_is .= "u.status = 'pending'";
        $q .= ($q ? "&amp;" : "") . "st=$status";
    } 
    // account status
    $accountstatus = $_GET['as'];
    if ($accountstatus) {
        $where_is .= (isset($where_is))?" AND ":"";
        if ($accountstatus == "1")
            $where_is .= " u.enabled = 'yes'";
        else
            $where_is .= " u.enabled = 'no'";
        $q .= ($q ? "&amp;" : "") . "as=$accountstatus";
    } 
    // donor
    $donor = $_GET['do'];
    if ($donor) {
        $where_is .= (isset($where_is))?" AND ":"";
        if ($donor == 1)
            $where_is .= " u.donor = 'yes'";
        else
            $where_is .= " u.donor = 'no'";
        $q .= ($q ? "&amp;" : "") . "do=$donor";
    } 
    // warned
    $warned = $_GET['w'];
    if ($warned) {
        $where_is .= (isset($where_is))?" AND ":"";
        if ($warned == 1)
            $where_is .= " u.warned = 'yes'";
        else
            $where_is .= " u.warned = 'no'";
        $q .= ($q ? "&amp;" : "") . "w=$warned";
    }
    // disabled IP
    $disabled = $_GET['dip'];
    if ($disabled) {
        $distinct = "DISTINCT ";
        $join_is .= " JOIN users AS u2 ON u.ip = u2.ip";
        $where_is .= ((isset($where_is))?" AND ":"") . "u2.enabled = 'no'";
        $q .= ($q ? "&amp;" : "") . "dip=$disabled";
    }
    // active
    $active = $_GET['ac'];
    if ($active == "1") {
        $distinct = "DISTINCT ";
        $join_is .= " JOIN peers AS p ON u.id = p.userid";
        $q .= ($q ? "&amp;" : "") . "ac=$active";
    } 

    if (isset($_GET["s"])) {
        $orderby = "ORDER BY ";

        $s = $_GET["s"];
        switch ($s) {
            default:
            case 0:
                $orderby .= "username ";
                break;
            case 1:
                $orderby .= "last_access ";
                break;
            case 2:
                $orderby .= "added ";
                break;
            case 3:
                $orderby .= "(uploaded/downloaded) ";
                break;
        } 
        $so = $_GET["so"];
        if ($so == 1)
            $orderby .= "DESC";
        else
            $orderby .= "ASC";
    } 

    $from_is = "users AS u" . $join_is;
    $distinct = isset($distinct)?$distinct:"";

    $queryc = "SELECT COUNT(" . $distinct . "u.id) FROM " . $from_is .
    (($where_is == "")?"":" WHERE $where_is ");

    $querypm = "FROM " . $from_is . (($where_is == "")?" ":" WHERE $where_is ");

    $select_is = "u.id, u.username, u.email, u.status, u.added, u.last_access, u.ip,
          u.class, u.uploaded, u.downloaded, u.donor, u.enabled, u.warned";

    $query = "SELECT " . $distinct . " " . $select_is . " " . $querypm . " " . $orderby . " ";
    // <temporary>    /////////////////////////////////////////////////////
    if ($DEBUG_MODE > 0) {
        stdmsg("Count Query", $queryc);
        echo "<BR><BR>";
        stdmsg("Search Query", $query);
        echo "<BR><BR>";
        stdmsg("URL ", $q);
        if ($DEBUG_MODE == 2)
            die();
        echo "<BR><BR>";
    } 
    // </temporary>   /////////////////////////////////////////////////////
    $res = mysql_query($queryc) or sqlerr();
    $arr = mysql_fetch_row($res);
    $count = $arr[0];

    $q = isset($q)?($q . "&amp;"):"";

    $perpage = 30;

    list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?" . $q);

    $query .= $limit;

    $res = mysql_query($query) or sqlerr();

    if (mysql_num_rows($res) == 0)
        stdmsg("Hinweis", "Es wurden keine �bereinstimmenden Benutzer gefunden.");
    else {
        if ($count > $perpage)
            echo $pagertop;
        begin_table(true);
        echo "<tr><td class=\"tablecat\" align=left>Name</td>
                    <td class=\"tablecat\" align=left>Ratio</td>
        <td class=\"tablecat\" align=left>IP</td>
        <td class=\"tablecat\" align=left>E-Mail</td>" . 
        "<td class=\"tablecat\" align=left>Registriert</td>" . 
        "<td class=\"tablecat\" align=left>Zuletzt&nbsp;gesehen</td>" . 
        "<td class=\"tablecat\" align=left>Status</td>" . 
        "<td class=\"tablecat\" align=left>Aktiviert</td>" . 
        "<td class=\"tablecat\" title=\"Ratio der aktiven Peers\">pR</td>" . 
        "<td class=\"tablecat\" title=\"Upload der aktiven Peers\">pUL</td>" . 
        "<td class=\"tablecat\" title=\"Download der aktiven Peers\">pDL</td>" . 
        "<td class=\"tablecat\">Verlauf</td></tr>";
        while ($user = mysql_fetch_array($res)) {
            if ($user['added'] == '0000-00-00 00:00:00')
                $user['added'] = '---';
            if ($user['last_access'] == '0000-00-00 00:00:00')
                $user['last_access'] = '---';

            if ($user['ip']) {
                $nip = ip2long($user['ip']);
                $auxres = mysql_query("SELECT COUNT(*) FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
                $array = mysql_fetch_row($auxres);
                if ($array[0] == 0)
                    $ipstr = $user['ip'];
                else
                    $ipstr = "<a href='/testip.php?ip=" . $user['ip'] . "'><font color='#FF0000'><b>" . $user['ip'] . "</b></font></a>";
            } else
                $ipstr = "---";

            $auxres = mysql_query("SELECT SUM(uploaded) AS pul, SUM(downloaded) AS pdl FROM peers WHERE userid = " . $user['id']) or sqlerr(__FILE__, __LINE__);
            $array = mysql_fetch_array($auxres);

            $pul = $array['pul'];
            $pdl = $array['pdl'];

            $auxres = mysql_query("SELECT COUNT(DISTINCT p.id) FROM posts AS p JOIN topics as t ON p.topicid = t.id
              JOIN forums AS f ON t.forumid = f.id WHERE p.userid = " . $user['id'] . " AND f.minclassread <= " . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);

            $n = mysql_fetch_row($auxres);
            $n_posts = $n[0];

            $auxres = mysql_query("SELECT COUNT(id) FROM comments WHERE user = " . $user['id']) or sqlerr(__FILE__, __LINE__); 
            // Use JOIN to exclude orphan comments
            // $auxres = mysql_query("SELECT COUNT(c.id) FROM comments AS c JOIN torrents as t ON c.torrent = t.id WHERE c.user = '".$user['id']."'") or sqlerr(__FILE__, __LINE__);
            $n = mysql_fetch_row($auxres);
            $n_comments = $n[0];

            echo "<tr> <td class=\"tablea\" nowrap><b><a href='userdetails.php?id=" . $user['id'] . "'><font class=\"".get_class_color($user['class'])."\">" . $user['username'] . "</font></a></b> " . get_user_icons($user) . "</td>" . " <td class=\"tableb\">" . ratios($user['uploaded'], $user['downloaded']) . "</td>
           <td class=\"tablea\">" . $ipstr . "</td> <td class=\"tableb\">" . $user['email'] . "</td>
           <td class=\"tablea\"><div align=center>" . $user['added'] . "</div></td>
           <td class=\"tableb\"><div align=center>" . $user['last_access'] . "</div></td>
           <td class=\"tablea\"><div align=center>" . ($user['status'] == "confirmed"?"Best�tigt":"Unbest�tigt") . "</div></td>
           <td class=\"tableb\"><div align=center>" . ($user['enabled'] == "no"?"Nein":"Ja") . "</div></td>
           <td class=\"tablea\"><div align=center>" . ratios($pul, $pdl) . "</div></td>" . " <td class=\"tableb\"><div align=right>" . mksize($pul) . "</div></td>
           <td class=\"tablea\"><div align=right>" . mksize($pdl) . "</div></td>
           <td class=\"tableb\"><div align=center>" . ($n_posts?"<a href=/userhistory.php?action=viewposts&id=" . $user['id'] . ">$n_posts</a>":$n_posts) . "|" . ($n_comments?"<a href=/userhistory.php?action=viewcomments&id=" . $user['id'] . ">$n_comments</a>":$n_comments) . "</div></td></tr>\n";
        } 
        end_table();
        if ($count > $perpage)
            echo "$pagerbottom";
    } 
} else {
    stdmsg("Suche starten", "Bitte gib die gew�nschten Suchparameter an, und klicke auf 'Daten abschicken'.");
} 

print("<p>$pagemenu<br>$browsemenu</p>");
end_frame();
stdfoot();
die;

?>