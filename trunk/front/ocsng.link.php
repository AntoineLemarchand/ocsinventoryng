<?php
/*
 * @version $Id: ocsng.link.php 14685 2011-06-11 06:40:30Z remi $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
}
include (GLPI_ROOT . "/inc/includes.php");

plugin_ocsinventoryng_checkRight("ocsng","w");

Html::header(__('OCS Inventory NG'), "", "plugins", "ocsinventoryng");

$CFG_GLPI["use_ajax"] = 1;

//First time this screen is displayed : set the import mode to 'basic'
if (!isset($_SESSION["change_import_mode"])) {
   $_SESSION["change_import_mode"] = false;
}

//Changing the import mode
if (isset($_GET["change_import_mode"])) {

   if ($_GET["change_import_mode"] == "false") {
      $_SESSION["change_import_mode"] = false;
   } else {
      $_SESSION["change_import_mode"] = true;
   }
}

if (isset($_SESSION["ocs_link"])) {
   if ($count = count($_SESSION["ocs_link"])) {
      $percent = min(100,
                     round(100*($_SESSION["ocs_link_count"]-$count)/$_SESSION["ocs_link_count"], 0));

      displayProgressBar(400,$percent);

      $key = array_pop($_SESSION["ocs_link"]);
      PluginOcsinventoryngOcsServer::linkComputer($key["ocsid"],
                                                  $_SESSION["plugin_ocsinventoryng_ocsservers_id"],
                                                  $key["computers_id"]);
      Html::back();

   } else {
      displayProgressBar(400,100);

      unset($_SESSION["ocs_link"]);
      echo "<div class='center b'>".__('Successful importation')."<br>";
      echo "<a href='".$_SERVER['PHP_SELF']."'>".__('Back')."</a></div>";
   }
}

if (!isset($_POST["import_ok"])) {
   if (!isset($_GET['check'])) {
      $_GET['check'] = 'all';
   }
   if (!isset($_GET['start'])) {
      $_GET['start'] = 0;
   }
   PluginOcsinventoryngOcsServer::manageDeleted($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);
   PluginOcsinventoryngOcsServer::showComputersToAdd($_SESSION["plugin_ocsinventoryng_ocsservers_id"],
                                                     $_SESSION["change_import_mode"], $_GET['check'],
                                                     $_GET['start'], $_SESSION['glpiactiveentities'],
                                                     1);

} else {
   if (isset($_POST['tolink']) && count($_POST['tolink']) >0) {
      $_SESSION["ocs_link_count"] = 0;

      foreach ($_POST['tolink'] as $ocsid => $computers_id) {
         if ($computers_id >0) {
            $_SESSION["ocs_link"][] = array('ocsid'        => $ocsid,
                                            'computers_id' => $computers_id);
            $_SESSION["ocs_link_count"]++;
         }
      }
   }
   Html::back();
}

Html::footer();
?>