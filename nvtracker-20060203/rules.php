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

ob_start("ob_gzhandler");

require "include/bittorrent.php";

dbconn();

stdhead("Regeln");


if (isset($_GET["accept_rules"])) {
?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr><td width="100%" class="tablea" style="font-weight:bold;color:red;">
WICHTIG! Regel�nderung - Du musst die neuen Regeln akzeptieren, bevor Du den
Tracker weiter nutzen kannst!</td></tr></table><br>
<? } ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Grundlegende Regeln</b> - Missachtung kann zu sofortigem Ban f�hren!
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Den Anordnungen des Teams ist grunds�tzlich Folge zu leisten!</li>
<li><p>Bevor man eine Frage stellt in der FAQ lesen, ob diese schon beantwortet wurde.</li>
<li><p>Poste unsere Torrents nicht auf anderen Trackern.</li>
<li><p><a name="warning"></a>Unsachgem�sses Verhalten und Missachtung der Regeln resultiert in einer Verwarnung (<img src="<?=$GLOBALS["PIC_BASE_URL"]?>warned.gif"> ).<br>
�ndert sich nach dieser Verwarnung nichts, wirst du "h�flich" gebeten zu gehen ;)</li>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>IRC Regeln</b> - Missachtung kann zu Kick/Ban aus dem Channel oder aus dem IRC-Netzwerk f�hren!
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Keine Announcer-Scripts (also solche, die irgendetwas ank�ndigen wie MP3s,
BT-Infos, Sysinfos, ...).</li>
<li><p>Keine "Danke"-Scripts (solche, die z.B. automatisch schreiben "THX f�r
Voice" usw.).</li>
<li><p>Kein Betteln um Voice, HOP, OP usw. - wer bettelt, fliegt.</li>
<li><p>Seid nett zueinander. Kindergartenverhalten, Beleidigungen usw. werden nicht
toleriert und resultieren in Kick und/oder Ban.</li>
<li><p>Kein Spam jeglichen Typs (Wiederholte Texte, mehrzeiliges ASCII-Art,
h�ufige Nickchanges, Join/Part-Spam...).</li>
<li><p>Keine unn�tigen CTCP-Requests an andere User.</li>
<li><p>Keine �berm��ige Nutzung von Farben. Wenn Dein Script so etwas
automatisch tut, schalte es bitte ab!</li>
<li><p>Keine Warscripts. Dieser Punkt gilt besonders f�r HOPs und OPs.</li>
<li><p>Das Verbreiten von Viren und sch�dlichen Scripten ist untersagt.
Wenn wir mitbekommen, dass ein User solcherlei Dinge verbreitet,
resultiert dies in einer KLine f�r diesen User (aka Ban aus dem
IceVelvet-IRC-Netzwerk)!</li>
<li><p>Den Anweisungen der HOPs und OPs ist Folge zu leisten.</li>
</ul>
<p><a href="http://tracker-netvision.ath.cx/ircrules.txt">Des weiteren gilt das, was Dante uns zu sagen hat</a> :)</p>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Download Regeln</b> - Bei Missachtung wird die M�glichkeit etwas zu downloaden entzogen!
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Der Zugriff auf die neusten Torrents ist abh�ngig von deiner Ratio (Info hierzu:
<a href=faq.php#dl8 class=altlink><b>FAQ</b></a>.)</li>
<li><p>Eine tiefe Ratio resultiert in mehreren Konsequenzen. Im Extremfall Ban.</li>
<li><p>Doppelaccounts gibts nur auf Anfrage und mit Begr�ndung (bei <a
href="messages.php?action=send&amp;receiver=352">CrazyCat</a> melden). Werden unangemeldete Doppelaccs
gefunden, werden sie umgehend gel�scht.</li>
<li><p>Wenn Du einen Torrent fertiggestellt hast, lasse diese noch einige Zeit im Seed.
BitTorrent lebt davon, dass Torrents weiter geseedet werden. Ausreden wie "Plattencrash"
sind INAKZEPTABEL (jaja, pro Woche schmieren hier 20 Usern die Platten exakt dann ab, wenn
die gerade einen Torrent fertig hatten... sicherlich).</p></li>
<li><p>F�r die Beantragung der Wartezeitaufhebung sind <a href="faq.php#dlf">die im FAQ
genannten Bedingungen</a> bindend.</li>
</ul>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Regeln f�r <a href="faq.php#site4" title="Was sind Webseeder?"><u>Webseeder</u></a></b> - Bei Missachtung wird die IP des Rootservers dauerhaft gesperrt!
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Sei Dir im Klaren dar�ber, dass jedes zuviel hochgeladene GB auf Kosten der Ratio eines Benutzers geht, der
nicht so einen schnellen Upload hat.</li>
<li><p>Es gen�gt oft, wenn Du etwa 400% der Torrent-Datenmenge seedest, um eine Grundverteilung zu erreichen.
Wenn Du der Masterseeder bist, benutze bitte den "Superseeder"-Modus (Parameter "--super_seeder 1" in BitTornado).
Haben nach den 400% noch nicht gen�gend Benutzer ihren Download durch, oder ist der Torrent sehr stark
Nachgefragt, kann auch ein wenig weitergeseedet werden, bis gen�gend Seeder vorhanden sind. Die n�tige
Upload-Menge schwankt stark von Torrent zu Torrent, und soll hier auch nicht festgelegt werden.
Wahre die Verh�ltnism��igkeit!</li>
<li><p>Die Uploadgeschwindigkeit soll maximal 512 KB/sek betragen. Bei einer Seeder/Leecher-Ratio von 1:4 oder
schlechter, kann die Geschwindigkeit auch auf 768 KB/sek erh�ht werden, aber auf keinen Fall mehr.
Die Upload-Geschwindigkeit kann man in BitTornado mit dem Parameter --max_upload_rate begrenzen. Mehr dazu im
<a href="faq.php" title="Hilfe zu BitTornado"><b>FAQ</b></a>.</li>
<li><p>Seeden, nur um in die Top10 zu gelangen, ist untersagt. Es gibt keine Krone daf�r.</li>
<li><p>Webseeder k�nnen nachtr�glich in einen Torrent einsteigen, um den Seed ein wenig zu unterst�tzen.
Wie f�r Masterseeder, gilt auch hier die Einhaltung der Verh�ltnism��igkeit.</li>
<li><p>�berm��iges Seeden wird mit Abzug von Upload, der Sperrung der Server-IP, und in schweren oder wiederholten
F�llen mit Verwarnungen oder Accountl�schung geahndet. Ausreden wie "Ich hab vergessen den Seed zu Stoppen"
werden nicht akzeptiert - achtet selber darauf, was ihr tut!</li>
<li><p>Im �brigen gilt wie immer: Anweisungen des Staffs ist umgehend Folge zu leisten.</li>
</ul>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Richtlinien f�r Avatare und BitBucket-Inhalte</b> - Bitte beachten
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Die erlaubten Formate sind .gif, .jpg und .png.</li>
<li><p>Die maximal erlaubten Dimensionen eines Avatars betragen 150x150 und 150kByte. Avatare in
anderen Gr�ssen werden automatisch angepasst, deswegen kann es vorkommen, dass kleinere Avatare
verzogen aussehen. Gr�ssere hingegen verbrauchen nur unn�tig Bandbreite, da auch sie automatisch
angepasst werden.</li>
<li><p>Benutze kein potentiell angreifendes Material wie pornografische, Anti-Religi�se , Gewaltverherrlichende 
oder rassistische Bilder. Die Moderatoren wissen genau, was wie einzustufen ist. 
Bei Zweifeln einfach einen Mod per PN fragen.</li>
<li><p>Der BitBucket dient nicht nur dazu, einen Avatar abzulegen. Dort kannst Du auch Screenshots f�r
Torrents, Banner f�r Signaturen u.�. ablegen. Der Speicherplatz ist auf 1 MB begrenzt. Dein BitBucket-Inhalt
kann von allen Moderatoren eingesehen werden, und diese k�nnen auch Bilder daraus l�schen, falls
sie gegen die Richtlinien versto�en.</p></li>
</ul>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Upload-Regeln</b> - Torrents, welche gegen diese Bestimmungen verstossen, werden kommentarlos gel�scht!
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Ein Gastuploader darf maximal 1 Eigenseed gleichzeitig haben</p></li>
<li><p>Der Torrent muss mindestens 30 MB gro� sein. Falls Du etwas hast, was unbedingt auf den Tracker soll,
aber kleiner als 30 MB ist, wende Dich an das Team.</p></li>
<li><p>Alle Uploads m�ssen eine NFO beinhalten.</p></li>
<li><p>Archive (ZIP, RAR) in Torrents d�rfen kein Passwort enthalten.</p></li>
<li><p>Szenereleases m�ssen im Originalzustand geseedet werden.</p></li>
<li><p>Non-Final-Releases m�ssen mit *ALPHA*, *BETA* oder *PRERELEASE* Tag gekennzeichnet werden.</p></li>
<li><p>In der Torrentbeschreibung brauchen CD-Keys/Serials nicht entfernt werden, da jeder User
NFOs lesen darf.</p></li>
<li><p>Folgende Torrents sind nicht erlaubt:</p>
<ul>
<li>S�mtliche Produkte von Microsoft, egal ob MS als Hersteller oder
Publisher fungiert.</li>
<li>Spiele von Valve</li>
<li>Jegliche XBox-Titel (Da Microsoft-Konsole)</li>
<li>Alle Torrents mit den weiter unten genannten Inhalten</li>
</ul>
</li>
<li><p><a href="http://tracker-netvision.ath.cx/wbb/thread.php?threadid=8900&sid="><b>Gastupload-Verhaltensregeln</b></a></p></li>
<li><p>Der Torrent-Name muss sich an der �blichen Szene-Schreibweise orientieren, und alle
wichtigen Informationen in knapper Form enthalten. Beispiele:</p>
<ul>
<li><b>DVD:</b> Koenigreich.der.Himmel.German.AC3.Dubbed.DVDR-GTR</li>
<li><b>Screener:</b> Die.weisse.Massai.TS.Line.Dubbed.German.SVCD-MRM</li>
<li><b>MP3-Album:</b> Knorkator-Hasenchartbreaker.MP3.192kBit</li>
<li><b>Spiel:</b> Fifa 2006-PAL-PS2DVD-DAGGER</li></ul>
<p>Eine gewisse Freiheit bei der Benennung ist jedem gestattet, dennoch sollten die Torrent-Titel
einheitlich benannt werden. Folgendes Format sollte man generell als Ausgangspunkt nehmen,
sowie Leerzeichen grunds�tzlich durch Punkte ersetzen (Gruppe ist die Release-Group. Kann weggelassen
werden, sofern unbekannt):</p>

<p><b>Die neu zur Verf�gung gestellt NFO ist Pflicht, ausser es ist ein Szenerelease oder ihr baut euch eine vom Informationsgehalt her gleichwertige NFO.</b></p>

<ul>
<li><b>Anime-Movie:</b> Titel.Sprache.Videoquelle.Audioquelle.Format-Gruppe <br>
<a href="NFO/anime.nfo" target="_blank">Muster-NFO</a></li>
<li><b>Anime-Serie:</b> Titel.S??E??-S??E??.Episodenname.Sprache.Videoquelle.Audioquelle.Format-Gruppe <br>
<a href="NFO/anime.nfo" target="_blank">Muster-NFO</a></li>
<li><b>Audio:</b> Band.-.Albumname-Releasejahr-Format.Qualit�t-Gruppe <br>
<a href="NFO/audio.nfo" target="_blank">Muster-NFO</a></li>
<li><b>Doku/Magazin:</b> Titel.[E??].Thema.Sprache.[Doku/Mag].Quelle.Format-Gruppe <br>
<a href="NFO/xviddivx.nfo" target="_blank">Muster-NFO (DivX/XviD)</a> | <a href="NFO/xvcd.nfo" target="_blank">Muster-NFO (xVCD)</a></li>
<li><b>Games:</b> Titel.Sprache.Format-Gruppe <br>
<a href="NFO/pcgames.nfo" target="_blank">Muster-NFO (PC)</a> | <a href="NFO/konsolenspiele.nfo" target="_blank">Muster-NFO (Konsole)</a></li>
<li><b>H�rspiel/H�rbuch:</b> Autor.-.Titel.Qualitaet.Format.AnzahlCDs-Gruppe <br>
<a href="NFO/hoerbuch.nfo" target="_blank">Muster-NFO</a></li>
<li><b>Movie(CD)s:</b> Titel.Sprache.Videoquelle.Audioquelle.Format-Gruppe <br>
<a href="NFO/xviddivx.nfo" target="_blank">Muster-NFO (DivX/XviD)</a> | <a href="NFO/xvcd.nfo" target="_blank">Muster-NFO (xVCD)</a></li>
<li><b>Movie(DVD)s:</b> Titel.Sprache.Videoquelle.Audioquelle.Format-Gruppe <br>
<a href="NFO/dvd.nfo" target="_blank">Muster-NFO</a></li>
<li><b>Serien:</b> Titel.S??E??-S??E??.Episodenname.Sprache.Quelle.Format-Gruppe <br>
<a href="NFO/tvrips.nfo" target="_blank">Muster-NFO></a></li>
<li><b>Software:</b> Name.Sprache-Gruppe <br>
<a href="NFO/software.nfo" target="_blank">Muster-NFO</a></li>
<li><b>Sonstiges:</b> Abh�ngig vom Typ, an oben genannte Beispiele anpassen. F�r E-Books --> <a href="NFO/ebooks.nfo" target="_blank">Muster-NFO</a></li>
</li>
<br>
<p><b>Beschreibungsinhalt</b></p><br>
<li>Anime: Plot/Inhaltsangabe
</li><li>Audio: Tracklist
</li><li>Doku/Magazin: Plot/Inhaltsangabe
</li><li>Games/*: Spielbeschreibung<br>
</li><li>H�rspiel/H�rbuch: Plot/Inhaltsangabe
</li><li>Movies/*: Plot/Inhaltsangabe
</li><li>Serien: Plot/Inhaltsangabe
</li><li>Software: Programmbeschreibung<br>
</li><li>Sonstiges: Beschreibung<br></li><br>
<p>Alle weiteren Informationen k�nnen der NFO entnommen werden, doppelte Infos sind nicht n�tig.</p><br>

<li><p>Stelle sicher, dass dein Torrent lange genug geseedet wird.</p></li>
<li><p>Das Releasedatum sowie der Nick des Seeders geh�ren nicht in den Torrentnamen.</p></li>
<li><p>Folgende Inhalte sind auf diesem Tracker untersagt, und werden umgehend gel�scht:</p>
<ol>
<li>Pornographie</li>
<li>Gesetzlich verbotene Werke (Kinderpornos, Nazi-Musik, Spiele wie "Manhunt", ...)</li>
</ol>
<p>Bei indizierten Medien oder Unsicherheiten beispielsweise bei Splattern wendet ihr euch an <a href="http://tracker-netvision.ath.cx/userdetails.php?id=27">Dante</a></p>
<p>Bei schweren Verst��en und in Wiederholungsf�llen kann der Uploader mit Verwarnung,
Degradierung oder Kick bestraft werden!</p></li>
<li><p>Direkte Links auf Seiten wie offizielle Filmeseiten (z.B. XYZ-der-film.de) und
auch das direkte Einbinden von Bildern auf diesen Seiten ist strengstens untersagt.
F�r Screenshots und Filmplakate/DVD-Covers gibt es den BitBucket, sowie die M�glichkeit beim
Upload zwei Bilder mit hochzuladen. IMDB darf jedoch verlinkt werden.</p></li>
<li><p>Inaktive Uploader verlieren ihren Status. Als inaktiv wird ein Uploader gewertet, der in den letzten 28
Tagen weniger als 4 Torrents hochgeladen, und keine besondere Begr�ndung daf�r angegeben hat.</p></li>
<li><p>Torrents, die von Benutzern hochgeladen werden, die einen niedrigeren Rang als "Uploader"
besitzen, m�ssen erst vom Team freigeschaltet werden. Das Team wird bei einem erfolgreichen
Upload automatisch benachrichtigt, und schaltet den Torrent frei, sofern er den Regeln entspricht.
Benutzer, die wiederholt gegen die Upload-Regeln versto�en, verlieren diese Berechtigung.</p></li>
</ul>
</td></tr></table><br>

<? if (get_user_class() >= UC_MODERATOR) { ?>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Moderator Regeln</b> - Wer wird bef�rdert und warum
</center></span></td></tr><tr><td width="100%" class="tablea">
<table border="0" cellspacing="1" cellpadding="2">
<tr>
	<td class="inposttable" valign=top width=140>&nbsp; <b>Power User</b></td>
	<td width=5>&nbsp;</td>
	<td>Wird automatisch vergeben, wenn ein User mindestens 4 Wochen dabei ist, mindestens 25GB geuploadet hat und
 	eine Ratio von �ber 1.05 vorweisen kann. Von Mods gemachte �nderungen werden bei jeder Ausf�hrung dieses Scriptes �berschrieben.</td>
</tr>
<tr>
	<td class="inposttable" valign=top>&nbsp; <b><img src="<?=$GLOBALS["PIC_BASE_URL"]?>star.gif"></b></td>
	<td width=5>&nbsp;</td>
	<td>Dieser Status wird <b>nur</b> von Administratoren oder SysOps vergeben.</td>
</tr>
<tr>
	<td class="inposttable" valign=top>&nbsp; <b>VIP</b></td>
	<td width=5>&nbsp;</td>
	<td>F�r User, die deiner Meinung etwas besonderes in Bezug auf den Tracker geleistet haben. (Wer sich einen h�heren Status erbetteln will, ist automatisch ausgeschlossen)</td>
</tr>
<tr>
	<td class="inposttable" valign=top>&nbsp; <b>Sonstige</b></td>
	<td width=5>&nbsp;</td>
	<td>Benutzerdefinierte Titel f�r spezielle User. (Nicht verf�gbar f�r User und Power User).</td>
</tr>
<tr>
	<td class="inposttable" valign=top>&nbsp; <b><font color="#4040c0">Uploader</font></b></td>
	<td width=5>&nbsp;</td>
	<td>Wird von Administratoren/SysOps vergeben. Sende eine PM an
            <a class=altlink href="messages.php?action=send&amp;receiver=27">Dante<a>
            oder einen anderen Admin/SysOp, wenn du Dich als Uploader bewerben willst.</td>
</tr>
<tr>
	<td class="inposttable" valign=top>&nbsp; <b><font color="#348534">Moderator</font></b></td>
	<td width=5>&nbsp;</td>
	<td>Wird von Administratoren/SysOps vergeben, sofern welche gesucht sind. Trifft dies zu
	sende eine Bewerbung an <a class=altlink href="messages.php?action=send&amp;receiver=27">Dante</a>.</td>
</tr>
</table>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Moderator Regeln</b> - Grundlegendes
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li><p>Zier dich nicht, <b>NEIN!</b> zu sagen.
<li><p>Keine �ffentlichen Streitigkeiten mit anderen Mods. L�st das per PM/IM.</li>
<li><p>Sei tolerant. Gib dem User eine Chance, sich zu bessern, au�er es ist offensichtlich, dass er daran
absolut kein Interesse zeigt.</li>
<li><p>Handle nicht voreilig. Lass einen User einen Fehler machen und korrigiere ihn danach.</li>
<li><p>Bevor du einen Account deaktivierst, sende dem User eine Message. Wenn er sich meldet, gib ihm eine Woche Zeit, sich zu bessern.</li>
<li><p>Schreibe <b>immer</b> eine Begr�ndung, warum der betreffende User deaktiviert/verwarnt wurde.</li>
</ul>
</td></tr></table><br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Moderator Regeln</b> - Freischalteregeln
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li>Exakt gleicher Torrent nicht bereits mit aktiven Usern vorhanden.</li>
<li>Torrentname stimmt mit dem Muster aus den Regeln �berein.</li>
<li>Muster- oder gleichewetige NFO korrekt ausgef�llt oder Szene-NFO vorhanden</li>
<li>Beschreibung korrekt.</li>
<li>Typ korrekt.</li>
<li>Movies: Screenshot vorhanden</li>
<li>Gastuploader hat nicht bereits einen Eigenseed aktiv</li>
</ul>
</td></tr></table><br>


<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Moderator Privilegien</b> - Was kann ich alles tun?
</center></span></td></tr><tr><td width="100%" class="tablea">
<ul>
<li>Du kannst Forumposts l�schen und editieren (wenns denn mal kommt *fg*).</li>
<li>Du kannst Torrents l�schen und editieren.</li>
<li>Du kannst Useravatare �ndern und l�schen.</li>
<li>Du kannst Accounts deaktivieren.</li>
<li>Du kannst den Titel von VIP's bearbeiten.</li>
<li>Du siehst die kompletten Infos von Usern.</li>
<li>Du kannst einem User Comments hinzuf�gen, die andere Mods/Admins lesen k�nnen.</li>
</ul>
</td></tr></table><br>

<? }

if (isset($_GET["accept_rules"])) { ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Regel�nderung best�tigen</b>
</center></span></td></tr><tr><td width="100%" class="tablea">
<p>Durch eine k�rzlich vorgenommene Regel�nderung musst Du erneut best�tigen, diese gelesen zu haben.
Bitte denke daran, Dich auch daran zu halten!</p>
<p>Wenn Du die Regeln nicht akzepierst, kannst Deinen Account <a href="delacct.php">hier umgehend
l�schen</a>.</p>
<form action="takeprofedit.php" method="post">
<center>
<table>
<tr><td><input type="checkbox" id="accept" name="acceptrules" value="yes"></td><td><label for="accept">Ich habe die Regeln gelesen, und bin damit einverstanden.</label></td></tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="rulessubmit" value="Best�tigen"></td></tr>
</table>
</center>
</form>
</td></tr></table><br>
<? } ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="smallfont"><center>
  Regeln zuletzt editiert am 18.10.2005 von ][Dante][ (14:08 CET)</center></span></td> 
 </tr></table>
 
<? stdfoot(); ?>