<?php
/*=======================================================================
 Nuke-Evolution Basic: Enhanced PHP-Nuke Web Portal System
 =======================================================================*/

/************************************************************************
   Nuke-Evolution: Advanced Downloads Module
   ============================================
   Copyright (c) 2005 by The Nuke-Evolution Team

   Filename      : CategoryActivate.php
   Author        : Quake (www.Nuke-Evolution.com)
   Version       : 1.0.0
   Date          : 11.21.2005 (mm.dd.yyyy)

   Notes         : Advanced Downloads module with BBGroups Integration
                   based on NSN GR Downloads.
************************************************************************/

/********************************************************/
/* Based on NSN GR Downloads                            */
/* By: NukeScripts Network (webmaster@nukescripts.net)  */
/* http://www.nukescripts.net                           */
/* Copyright (c) 2000-2005 by NukeScripts Network         */
/********************************************************/

$crawler = array($cid);
CrawlLevelR($cid);
$x=0;
while ($x <= (count($crawler)-1)) {
  $db->sql_query("UPDATE ".$prefix."_downloads_categories SET active='1' WHERE cid='$crawler[$x]'");
  $db->sql_query("UPDATE ".$prefix."_downloads_downloads SET active='1' WHERE cid='$crawler[$x]'");
  $x++;
}
//$db->sql_query("UPDATE ".$prefix."_downloads_downloads SET active='1' WHERE lid='$lid'");
redirect($admin_file.".php?op=Categories&min=$min");

?>