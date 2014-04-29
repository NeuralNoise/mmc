<?php
/**
 * (c) 2014 Zentyal, http://www.zentyal.com
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
 *
 * Author(s):
 *   Miguel Julián <mjulian@zentyal.com>
 */

require_once("modules/samba4/includes/common-xmlrpc.inc.php");
require("modules/samba4/includes/domaincontroller-xmlrpc.inc.php");

/* Provision has been ordered, just handle it or show the form */
if (isset($_POST["bprovision"]) and ! isSamba4Provisioned()) {
    if (_doProvision($_POST)) {
        header("Location: " . urlStrRedirect("base/main/default"));
        exit;
    } else {
        new NotifyWidgetFailure(_T("Provision has failed, please try again or ask for support."));
    }
}

/* If the user is entering the submodule or if the provision has failed, we show the form */
_showProvisionForm();

function _showProvisionForm() {
    _redirectIfAlreadyProvisioned();

    $page = new PageGenerator();
    $page->display();

    $form = new ValidatingForm(array('method' => 'POST','enctype' => 'multipart/form-data'));
    $form->push(new Table());

    $tr = new TrFormElement(_T("Realm", "samba4"), new InputTpl("realm"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("Domain controller FQDN", "samba4"), new InputTpl("dcFQDN"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("Domain DNS server IP", "samba4"), new InputTpl("dnsServerIP"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("Administrator account", "samba4"), new InputTpl("adminAccount"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("Administrator password", "samba4"), new InputTpl("adminPassword"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("NetBIOS domain name", "samba4"), new InputTpl("domainName"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("NetBIOS server name", "samba4"), new InputTpl("serverName"));
    $form->add($tr, array("value" => "", "required" => True));

    $tr = new TrFormElement(_T("Server description"), new InputTpl("description"));
    $form->add($tr, array("value" => "", "required" => True));

    $form->pop();

    $form->addButton("bprovision", _("Do provision"));

    $form->pop();
    $form->display();
}

/* Private functions */
function _doProvision($_POST) {
    $inputIds = array("realm", "dcFQDN", "dnsServerIP", "adminAccount", "adminPassword", "domainName", "serverName", "description");

    $parameters = array();
    foreach ($inputIds as $id) {
        if (isset($_POST[$id])) {
            $parameters[] = $_POST[$id];
        }
    }

    return provisionSamba4AditionalDC($parameters);
}

function _redirectIfAlreadyProvisioned() {
    if (isSamba4Provisioned()) {
        header("Location: " . urlStrRedirect("samba4/shares/index"));
        exit;
    }
}

?>
