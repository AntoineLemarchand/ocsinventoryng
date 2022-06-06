<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  ocsinventoryng plugin for GLPI
  Copyright (C) 2015-2016 by the ocsinventoryng Development Team.

  https://github.com/pluginsGLPI/ocsinventoryng
  -------------------------------------------------------------------------

  LICENSE

  This file is part of ocsinventoryng.

  ocsinventoryng is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  ocsinventoryng is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with ocsinventoryng. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */


include('../../../inc/includes.php');

Session::checkRight("plugin_ocsinventoryng", UPDATE);

Html::header('OCS Inventory NG', '', "tools", "pluginocsinventoryngmenu", "importipdiscover");

$display_list = true;
//First time this screen is displayed : set the import mode to 'basic'
if (!isset($_SESSION["change_import_mode"])) {
   $_SESSION["change_import_mode"] = false;
}

//Changing the import mode
if (isset($_POST["change_import_mode"])) {
   if ($_POST['id'] == "false") {
      $_SESSION["change_import_mode"] = false;
   } else {
      $_SESSION["change_import_mode"] = true;
   }
}

$actions = array("noninventoried", "identified", "full");

if(isset($_GET["action"]) && isset($_GET["serverid"]) && in_array($_GET["action"], $actions)) {
    if(!PluginOcsinventoryngOcsServer::checkOCSconnection($_SESSION["plugin_ocsinventoryng_ocsservers_id"])){
        PluginOcsinventoryngOcsProcess::showStatistics($_SESSION["ocs_importipdiscover"]['statistics']);
        $_SESSION["ocs_importipdiscover"]["datas"] = [];
        Html::redirect($_SERVER['PHP_SELF']);
    } else {
        $_SESSION["ocs_importipdiscover"]["connection"] = true;
    }

    $ocsClient = PluginOcsinventoryngOcsServer::getDBocs($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);
    $ocsConf = PluginOcsinventoryngOcsServer::getConfig($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);

    $ocsResult = $ocsClient->getIpDiscover($_SESSION["plugin_ocsinventoryng_ocsservers_id"], $_GET["action"]);

    $_SESSION["ocs_importipdiscover"]['statistics']["imported_ipdiscover_number"] = 0;
    $_SESSION["ocs_importipdiscover"]['statistics']["notupdated_ipdiscover_number"] = 0;
    $_SESSION["ocs_importipdiscover"]['statistics']["failed_imported_ipdiscover_number"] = 0;
    $_SESSION["ocs_importipdiscover"]['statistics']["synchronized_ipdiscover_number"] = 0;
    $_SESSION["ocs_importipdiscover"]['statistics']["removed_ipdiscover_number"] = 0;
    $_SESSION["ocs_importipdiscover"]['statistics']["failed_removed_ipdiscover_number"] = 0;
    $_SESSION["ocs_importipdiscover"]['statistics']["total_number_machines"] = 0;

    //Import Ipd objects
    if($ocsResult['TOTAL_COUNT'] != 0) {
        foreach ($ocsResult['IPDISCOVER'] as $mac => $ipdDatas) {
            if($_GET["action"] != 'full') {
                $action = PluginOcsinventoryngIpdiscoverOcslinkrework::importIpDiscover($ipdDatas, $_SESSION["plugin_ocsinventoryng_ocsservers_id"], ["inventory_type" => $_GET["action"]]);
                PluginOcsinventoryngOcsProcess::manageImportStatistics($_SESSION["ocs_importipdiscover"]['statistics'], $action['status']);
            } else {
                foreach($ipdDatas as $addmac => $ipdDatasBis) {
                    $action = PluginOcsinventoryngIpdiscoverOcslinkrework::importIpDiscover($ipdDatasBis, $_SESSION["plugin_ocsinventoryng_ocsservers_id"], ["inventory_type" => $mac]);
                    PluginOcsinventoryngOcsProcess::manageImportStatistics($_SESSION["ocs_importipdiscover"]['statistics'], $action['status']);
                }
            }  
        }

        PluginOcsinventoryngOcsProcess::showStatistics($_SESSION["ocs_importipdiscover"]['statistics'], false, false, true);
        echo "<div class='center b'><br>";
        echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/ocsinventoryng/front/ocsng.php'>" . __('Back') . "</a></div>";
    } else {
        if (isset($_SESSION["ocs_importipdiscover"]['statistics'])) {
           PluginOcsinventoryngOcsProcess::showStatistics($_SESSION["ocs_importipdiscover"]['statistics'], false, false, true);
        } else {
           echo "<div class='center b red'>";
           echo __('No import: the plugin will not import these elements', 'ocsinventoryng');
           echo "</div>";
        }
        unset($_SESSION["ocs_importipdiscover"]);
  
        echo "<div class='center b'><br>";
        echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/ocsinventoryng/front/ocsng.php'>" . __('Back') . "</a></div>";
        $display_list = false;
    }
}

Html::footer();


