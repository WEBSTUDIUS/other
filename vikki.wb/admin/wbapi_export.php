<?php
global $APPLICATION, $ACTION, $DB;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
if (CModule::IncludeModuleEx("abricos.wbapi") == 3)
    echo GetMessage("DEMO_OFF");
$APPLICATION->SetTitle(GetMessage("WBAPI_EXPORT"));
$ACTION == "EXPORT_SETUP";
$profileID = intval($_POST['PROFILE_ID']);
?>
<?php
if ($strErrorMessage <> '' and !empty($strErrorMessage))
    ShowError($strErrorMessage);
?>
<?php
if ($_GET['ACTION'] == "DELETE") {
    if ($_GET['PROFILE_ID']) {
        CAgent::RemoveAgent("AgentWBExport(" . $_GET['PROFILE_ID'] . ");", "abricos.wbapi");
        $insSql = "DELETE FROM `abricos_wbapi_setting` WHERE `id`=" . $DB->ForSql($_GET['PROFILE_ID']) . ";";
        $resIns = $DB->Query($insSql, false, $err_mess . __LINE__);
    }

}
if ($_GET['EXPORT'] == 'START') {
    CAgent::RemoveAgent("AgentWBExport(" . $_GET['PROFILE_ID'] . ");", "abricos.wbapi");
    CAgent::AddAgent("AgentWBExport(" . $_GET['PROFILE_ID'] . ");", "abricos.wbapi", "N", $_GET['PERIOD'] * 3600, "", "Y");
    $insSql = "UPDATE `abricos_wbapi_setting` SET `action`='START' where `id`=" . $_GET['PROFILE_ID'] . ";";
    $res = $DB->Query($insSql, false, $err_mess . __LINE__);
} elseif ($_GET['EXPORT'] == 'STOP') {
    CAgent::RemoveAgent("AgentWBExport(" . $_GET['PROFILE_ID'] . ");", "abricos.wbapi");
    $insSql = "UPDATE `abricos_wbapi_setting` SET `action`='STOP' where `id`=" . $_GET['PROFILE_ID'] . ";";
    $res = $DB->Query($insSql, false, $err_mess . __LINE__);
}
if ($_POST['ACTION'] == 'SAVE') {
    $periodAgent = $_POST['WBAPI_PERIOD'] * 3600;
    $strErrorMessage = '';
    if (isset($_POST['SETUP_FIELDS_LIST']) and $_GET['EXPORT'] != 'STOP') {
        $massVar = explode(',', $_POST['SETUP_FIELDS_LIST']);
        $SETUP_VARS = '';

        foreach ($massVar as $val) {
            if ($val == 'V') {
                $i = 0;
                foreach ($_POST[$val] as $valCat) {
                    $SETUP_VARS .= $val . '[' . $i . ']=' . $valCat . '&';
                    $i++;
                }
            } else
                $SETUP_VARS .= $val . '=' . $_POST[$val] . '&';
        }
        $SETUP_VARS = substr($SETUP_VARS, 0, -1);
        if ($profileID) {
            $insSql = "UPDATE `abricos_wbapi_setting` SET `name_prof`='" . $DB->ForSql($_POST['SETUP_PROFILE_NAME']) . "',`period`=" . $DB->ForSql($_POST['WBAPI_PERIOD']) . ",`SETUP_VARS`='" . $DB->ForSql($SETUP_VARS) . "' where `id`=" . $profileID;
            $res = $DB->Query($insSql, false, $err_mess . __LINE__);
        } else {
            $arFields = array(
                "name_prof" => "'" . $_POST['SETUP_PROFILE_NAME'] . "'",
                "period" => $DB->ForSql($_POST['WBAPI_PERIOD']),
                "SETUP_VARS" => "'" . $DB->ForSql($SETUP_VARS) . "'",
            );

            $ID = $DB->Insert("abricos_wbapi_setting", $arFields, $err_mess . __LINE__);
        }
        if ($ID)
            $strErrorMessage = GetMessage('EXPORT_OK') . '<br>';
    }
} elseif ($_GET['ACTION'] == 'EXPORT_EDIT' or $_GET['ACTION'] == 'NEW')
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/abricos.wbapi/load/wbapi_setup.php");


if ($_GET['ACTION'] != 'EXPORT_EDIT' and $_GET['ACTION'] != 'NEW') { ?>
    <div class="ui-btn-double ui-btn-primary" style="float:right;padding-right:0;padding-bottom:5px;">
        <a href="?lang=ru&STEP=1&ACT_FILE=wbapi&ACTION=NEW" class="ui-btn-main"><?= GetMessage('CES_ADD_PROFILE') ?></a>
    </div>
    <table class="adm-list-table" id="tbl_sale_order">
        <thead>
        <tr class="adm-list-table-header">
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">ID</div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"><?= GetMessage('CES_PROFILE') ?></div>
            </td>
            <td class="adm-list-table-cell" colspan=2>
                <div class="adm-list-table-cell-inner"><?= GetMessage('CES_STATUS') ?></div>
            </td>

            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"><?= GetMessage('CES_RUN_INTERVAL') ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"><?= GetMessage('CES_EDIT_PROFILE') ?></div>
            </td>

        </tr>
        </thead>
        <tbody>
        <?php
        global $DB;
        \Bitrix\Main\UI\Extension::load("ui.buttons");
        $strSql = "SELECT * FROM `abricos_wbapi_setting`;";
        $res = $DB->Query($strSql, false, $err_mess . __LINE__);
        while ($row = $res->Fetch()) {
            ?>
            <tr class="adm-list-table-row">
                <td class="adm-list-table-cell"><a
                            href="?lang=ru&ACT_FILE=wbapi&ACTION=DELETE&PROFILE_ID=<?= $row['id'] ?>">&#10006;</a></td>
                <td class="adm-list-table-cell"><?= $row['id'] ?></td>
                <td class="adm-list-table-cell"><?= $row['name_prof'] ?></td>
                <?php
                if ($row['action'] == 'STOP' or empty($row['action'])) {
                    $actMes = GetMessage('CES_RUN_EXPORT');
                    $actMesStatus = GetMessage('CES_STOP_EXP');
                    $act = 'START&PERIOD=' . $row['period'];
                }
                if ($row['action'] == 'START') {
                    $act = 'STOP';
                    $actMesStatus = GetMessage('CES_START_EXPORT');
                    $actMes = GetMessage('CES_STOP_EXPORT');
                }
                ?>
                <td class="adm-list-table-cell"><?= $actMesStatus ?></td>
                <td class="adm-list-table-cell"><a
                            href="?lang=ru&ACT_FILE=wbapi&EXPORT=<?= $act ?>&PROFILE_ID=<?= $row['id'] ?>"><?= $actMes ?></a>
                </td>
                <td class="adm-list-table-cell" style="text-align: center;"><?= $row['period'] ?></td>
                <td class="adm-list-table-cell"><a
                            href="?lang=ru&STEP=1&ACT_FILE=wbapi&ACTION=EXPORT_EDIT&PROFILE_ID=<?= $row['id'] ?>"><?= GetMessage('CES_EDIT_PROFILE') ?></a>
                </td>
            </tr>
        <?php
        } ?>
    </table>

<?php
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>