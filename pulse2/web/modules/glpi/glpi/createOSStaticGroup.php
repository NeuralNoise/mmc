<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2012 Mandriva, http://www.mandriva.com
 *
 * $Id$
 *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once("modules/dyngroup/includes/dyngroup.php"); # for Group Class
require_once("modules/glpi/includes/xmlrpc.php");
require_once("modules/dyngroup/includes/xmlrpc.php");
require_once("modules/pulse2/includes/locations_xmlrpc.inc.php");
require_once("modules/base/includes/computers.inc.php");

$requestedOS = $_GET['os'];

// Group name
$groupname = sprintf (_T("Machine with %s installed at %s", "glpi"), $requestedOS, date("Y-m-d H:i:s"));;

// Get user locations
$locations = getUserLocations();
$uuids = array();

$groupmembers = array();

foreach ($locations as $location){
    $result = getRestrictedComputersList(0,-1,array('location'=>$location['uuid']), False);
    foreach ($result as $info){
            $os = str_replace('&nbsp;',' ',htmlentities($info[1]['os']));
            $cn = $info[1]['cn'][0];
            $uuid = $info[1]['objectUUID'][0];
            //
            if (in_array($uuid, $uuids)) continue;
            $uuids[] = $uuid;
            //
            if ($requestedOS == 'Other' && stripos($os,'Microsoft Windows') === False)
            {    
                // Add OS to group members
                $groupmembers["$uuid##$cn"] = array('hostname' => $cn, 'uuid' => $uuid);
            }
            elseif ($requestedOS == 'Otherw' && stripos($os,'Microsoft Windows') !== False)
            {
                // Add OS to group members
                $groupmembers["$uuid##$cn"] = array('hostname' => $cn, 'uuid' => $uuid);
            }
            elseif (stripos($os,$requestedOS) !== False){
                // Add OS to group members
                $groupmembers["$uuid##$cn"] = array('hostname' => $cn, 'uuid' => $uuid);
            }
    }
}

$group = new Group();
$group->create($groupname, False);
$group->addMembers($groupmembers);

$truncate_limit = getMaxElementsForStaticList();
if ($truncate_limit == count($groupmembers)) new NotifyWidgetWarning(sprintf(_T("Computers list has been truncated at %d computers", "dyngroup"), $truncate_limit));

header("Location: " . urlStrRedirect("base/computers/display", array('gid'=>$group->id)));
exit;