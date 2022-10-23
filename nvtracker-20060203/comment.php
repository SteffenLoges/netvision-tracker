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

require_once("include/bittorrent.php");

hit_start();

$action = $_GET["action"];

dbconn(false);

hit_count();

loggedinorreturn();

if ($action == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $torrentid = 0 + $_POST["tid"];
        if (!is_valid_id($torrentid))
            stderr("Fehler", "Ung�ltige ID $torrentid.");

        $res = mysql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_array($res);
        if (!$arr)
            stderr("Fehler", "Kein Torrent mit der ID ID $torrentid vorhanden.");

        $text = trim($_POST["text"]);
        if (!$text)
            stderr("Fehler", "Du musst Text eingeben!");

        mysql_query("INSERT INTO comments (user, torrent, added, text, ori_text) VALUES (" . $CURUSER["id"] . ",$torrentid, '" . get_date_time() . "', " . sqlesc($text) . "," . sqlesc($text) . ")");

        $newid = mysql_insert_id();

        mysql_query("UPDATE torrents SET comments = comments + 1 WHERE id = $torrentid");

        header("Refresh: 0; url=details.php?id=$torrentid&viewcomm=$newid#comm$newid");

        hit_end();
        die;
    } 

    $torrentid = 0 + $_GET["tid"];
    if (!is_valid_id($torrentid))
        stderr("Fehler", "Ung�ltige ID $torrentid.");

    $res = mysql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Fehler", "Kein Torrent mit der ID ID $torrentid vorhanden.");

        
    if (strlen($arr["name"])>50)
        $arr["name"] = substr($arr["name"], 0, 50)."...";
        
    stdhead("Einen Kommentar f�r \"" . $arr["name"] . "\" hinzuf�gen");

    begin_frame("Einen Kommentar f�r \"" . htmlspecialchars($arr["name"]) . "\" hinzuf�gen", FALSE, "500px");
    print("<p><form method=\"post\" action=\"comment.php?action=add\">\n");
    print("<input type=\"hidden\" name=\"tid\" value=\"$torrentid\"/>\n");
    print("<textarea name=\"text\" rows=\"10\" cols=\"80\"></textarea></p>\n");
    print("<p align=\"center\"><input type=\"button\" value=\"Smilie-Legende\" onclick=\"window.open('smilies.php','smilies','')\">&nbsp;<input type=\"submit\" class=btn value=\"Und ab!\" /></p></form>\n");
    end_frame();

    $res = mysql_query("SELECT comments.id, text, comments.added, username, users.id as user, users.avatar FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $torrentid ORDER BY comments.id DESC LIMIT 5");

    $allrows = array();
    while ($row = mysql_fetch_array($res))
    $allrows[] = $row;

    if (count($allrows)) {
        begin_frame("Neueste Kommentare zuerst, in umgekehrter Reihenfolge");
        commenttable($allrows);
        end_frame();
    } 

    stdfoot();
    hit_end();
    die;
} elseif ($action == "edit") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid))
        stderr("Fehler", "Ung�ltige ID $commentid.");

    $res = mysql_query("SELECT c.*, t.name FROM comments AS c JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Fehler", "Ung�lltige ID $commentid.");

    if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
        stderr("Fehler", "Zugriff verweigert.");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $text = $_POST["text"];
        $returnto = $_POST["returnto"];

        if ($text == "")
            stderr("Fehler", "Der Kommentar-Text darf nicht leer sein!");

        $text = sqlesc($text);

        $editedat = sqlesc(get_date_time());

        mysql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
            header("Location: $returnto");
        else
            header("Location: $BASEURL/browse.php?".SID); // change later ----------------------
        
        hit_end();
        die;
    } 

    stdhead("Kommentar f�r \"" . $arr["name"] . "\" bearbeiten");

    begin_frame("Kommentar f�r \"" . htmlspecialchars($arr["name"]) . "\" bearbeiten", FALSE, "500px");
    print("<form method=\"post\" action=\"comment.php?action=edit&amp;cid=$commentid\">\n");
    print("<input type=\"hidden\" name=\"returnto\" value=\"" . $_SERVER["HTTP_REFERER"] . "\" />\n");
    print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
    print("<textarea name=\"text\" rows=\"10\" cols=\"80\">" . htmlspecialchars(stripslashes($arr["text"])) . "</textarea></p>\n");
    print("<p align=\"center\"><input type=\"button\" value=\"Smilie-Legende\" onclick=\"window.open('smilies.php','smilies','')\">&nbsp;<input type=\"submit\" class=btn value=\"Und ab!\" /></p></form>\n");
    end_frame();
    
    stdfoot();
    hit_end();
    die;
} elseif ($action == "delete") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Fehler", "Zugriff verweigert.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Fehler", "Ung�ltige ID $commentid.");

    $sure = $_GET["sure"];

    if (!$sure) {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("Kommentar l�schen", "Du bist im Begriff, einen Kommentar zu L�schen. Klicke \n" . "<a href=?action=delete&cid=$commentid&sure=1" .
            ($referer ? "&returnto=" . urlencode($referer) : "") . ">hier</a> wenn Du Dir sicher bist.");
    } 

    $res = mysql_query("SELECT torrent FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if ($arr)
        $torrentid = $arr["torrent"];

    mysql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    if ($torrentid && mysql_affected_rows() > 0)
        mysql_query("UPDATE torrents SET comments = comments - 1 WHERE id = $torrentid");

    $returnto = $_GET["returnto"];

    if ($returnto)
        header("Location: $returnto");
    else
        header("Location: $BASEURL/browse.php?".SID); // change later ----------------------
    
    hit_end();
    die;
} elseif ($action == "vieworiginal") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Fehler", "Zugriff verweigert.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Fehler", "Ung�ltige ID $commentid.");

    $res = mysql_query("SELECT c.*, t.name FROM comments AS c JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Fehler", "Ung�ltige ID $commentid.");

    stdhead("Originaler Kommentar");
    print("<h1>Urspr�nglicher Inhalt des Kommentars #$commentid</h1><p>\n");
    print("<table width=500 border=1 cellspacing=0 cellpadding=5>");
    print("<tr><td class=comment>\n");
    echo htmlspecialchars(stripslashes($arr["ori_text"]));
    print("</td></tr></table>\n");

    $returnto = $_SERVER["HTTP_REFERER"];
    // $returnto = "details.php?id=$torrentid&amp;viewcomm=$commentid#$commentid";
    if ($returnto)
        print("<p><font size=small>(<a href=$returnto>Zur�ck</a>)</font></p>\n");

    stdfoot();
    hit_end();
    die;
} else
    stderr("Fehler", "Unbekannte Aktion $action");

die;

?>