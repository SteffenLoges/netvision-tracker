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

dbconn(false);

loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
  die;

$remove = $HTTP_GET_VARS['remove'];
if (is_valid_id($remove))
{
  mysql_query("DELETE FROM bans WHERE id=$remove") or sqlerr();
  write_log("Ban $remove was removed by $CURUSER[id] ($CURUSER[username])");
}

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_ADMINISTRATOR)
{
	$first = trim($HTTP_POST_VARS["first"]);
	$last = trim($HTTP_POST_VARS["last"]);
	$comment = trim($HTTP_POST_VARS["comment"]);
	if (!$first || !$last || !$comment)
		stderr("Error", "Missing form data.");
	$first = ip2long($first);
	$last = ip2long($last);
	if ($first == -1 || $last == -1)
		stderr("Error", "Bad IP address.");
	$comment = sqlesc($comment);
	$added = sqlesc(get_date_time());
	mysql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES($added, $CURUSER[id], $first, $last, $comment)") or sqlerr(__FILE__, __LINE__);
	header("Location: $BASEURL$HTTP_SERVER_VARS[REQUEST_URI]");
	die;
}

ob_start("ob_gzhandler");

$res = mysql_query("SELECT * FROM bans ORDER BY added DESC") or sqlerr();

stdhead("Bans");

print("<h1>Current Bans</h1>\n");

if (mysql_num_rows($res) == 0)
  print("<p align=center><b>Nothing found</b></p>\n");
else
{
  print("<table border=1 cellspacing=0 cellpadding=5>\n");
  print("<tr><td class=colhead>Added</td><td class=colhead align=left>First IP</td><td class=colhead align=left>Last IP</td>".
    "<td class=colhead align=left>By</td><td class=colhead align=left>Comment</td><td class=colhead>Remove</td></tr>\n");

  while ($arr = mysql_fetch_assoc($res))
  {
  	$r2 = mysql_query("SELECT username FROM users WHERE id=$arr[addedby]") or sqlerr();
  	$a2 = mysql_fetch_assoc($r2);
	$arr["first"] = long2ip($arr["first"]);
	$arr["last"] = long2ip($arr["last"]);
 	  print("<tr><td>$arr[added]</td><td align=left>$arr[first]</td><td align=left>$arr[last]</td><td align=left><a href=userdetails.php?id=$arr[addedby]>$a2[username]".
 	    "</a></td><td align=left>$arr[comment]</td><td><a href=bans.php?remove=$arr[id]>Remove</a></td></tr>\n");
  }
  print("</table>\n");
}

if (get_user_class() >= UC_ADMINISTRATOR)
{
	print("<h2>Add ban</h2>\n");
	print("<table border=1 cellspacing=0 cellpadding=5>\n");
	print("<form method=post action=bans.php>\n");
	print("<tr><td class=rowhead>First IP</td><td><input type=text name=first size=40></td>\n");
	print("<tr><td class=rowhead>Last IP</td><td><input type=text name=last size=40></td>\n");
	print("<tr><td class=rowhead>Comment</td><td><input type=text name=comment size=40></td>\n");
	print("<tr><td colspan=2><input type=submit value='Okay' class=btn></td></tr>\n");
	print("</form>\n</table>\n");
}

stdfoot();

?>