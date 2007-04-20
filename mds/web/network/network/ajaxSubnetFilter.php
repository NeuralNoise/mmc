<?
require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require("../../../modules/network/includes/network-xmlrpc.inc.php");
require ("../../../includes/PageGenerator.php");


function print_ajax_nav($curstart, $curend, $items, $filter)
{
  $_GET["action"] = "index";
  global $conf;

  $max = $conf["global"]["maxperpage"];

  echo '<form method="post" action="' . $PHP_SELF . '">';
  echo "<ul class=\"navList\">\n";

  if ($curstart == 0)
    {
      echo "<li class=\"previousListInactive\">"._("Previous")."</li>\n";
    }
  else
    {
      $start = $curstart - $max;
      $end = $curstart - 1;
      echo "<li class=\"previousList\"><a href=\"#\" onclick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._("Previous")."</a></li>\n";
    }

  if (($curend + 1) >= count($items))
    {
      echo "<li class=\"nextListInactive\">"._("Next")."</li>\n";
    }
  else
    {
      $start = $curend + 1;
      $end = $curend + $max;
      echo "<li class=\"nextList\"><a href=\"#\" onclick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._("Next")."</a></li>\n";
    }

  echo "</ul>\n";
}

$filter = $_GET["filter"];
$subnets = array();
$count = array();

foreach(getSubnets($filter) as $dn => $entry) {
    $subnet = $entry[1]["cn"][0];
    $subnets[$subnet] = array();
    $subnets[$subnet]["name"] = $entry[1]["dhcpComments"][0];
    $subnets[$subnet]["netmask"] = $entry[1]["dhcpNetMask"][0];
}

ksort($subnets);
$names = array();
$netmasks = array();
$count = array();
$ranges = array();
foreach($subnets as $subnet => $infos) {
    $count[] = '<span style="font-weight: normal;">(' . getSubnetHostsCount($subnet) . ')</span>';
    $names[] = $infos["name"];
    $netmasks[] = $infos["netmask"];
    $pool = getPool($subnet);
    if (count($pool)) {
        $hasSubnetPool = "checked";
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
	$ranges[] = "$ipstart to $ipend";
    } else $ranges[] = _T("No dynamic address pool");

}

if (isset($_GET["start"])) {
    $start = $_GET["start"];
    $end = $_GET["end"];
} else {
    $start = 0;
    if (count($subnets) > 0) {
        $end = $conf["global"]["maxperpage"] - 1;
    } else {
        $end = 0;
    }
}

print_ajax_nav($start, $end, $subnets, $filter);

$n = new ListInfos(array_keys($subnets), _T("DHCP subnets", "network"));
$n->setAdditionalInfo($count);
$n->first_elt_padding = 1;
$n->addExtraInfo($netmasks, _T("Netmask", "network"));
$n->addExtraInfo($names, _T("Description", "network"));
$n->addExtraInfo($ranges, _T("Dynamic pool range", "network"));
$n->setName(_T("DHCP subnets", "network"));

$n->addActionItem(new ActionItem(_T("View DHCP fixed host", "network"),"subnetmembers","afficher","subnet", "network", "network"));
$n->addActionItem(new ActionItem(_T("Edit subnet", "network"),"subnetedit","edit","subnet", "network", "network"));
$n->addActionItem(new ActionItem(_T("Add fixed host to subnet", "network"),"subnetaddhost","addhost","subnet", "network", "network"));
$n->addActionItem(new ActionPopupItem(_T("Delete zone", "network"),"subnetdelete","supprimer","subnet", "network", "network"));

$n->display(0);

print_ajax_nav($start, $end, $subnets, $filter);

?>
