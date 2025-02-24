<?php
global $APPLICATION, $DB;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
if (CModule::IncludeModuleEx("abricos.wbapi") == 3)
    echo GetMessage("DEMO_OFF");
$APPLICATION->SetTitle(GetMessage("WBAPI_LOG"));
?>
<?php if ($_GET['PROFILE_ID'] > 0) {
    $strSql = "SELECT * FROM `abricos_wbapi_update` where `profile_id`=" . $_GET['PROFILE_ID'] . ";";
    $resId = $DB->Query($strSql, false, $err_mess . __LINE__);
    ?>
    <table class="adm-list-table" id="tbl_sale_order">
        <tr class="adm-list-table-header">
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">
                    <?= GetMessage("WBAPI_LOG_ID") ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">
                    <?= GetMessage("WBAPI_LOG_NAME") ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">
                    <?= GetMessage("WBAPI_LOG_STATUS_PRICE") ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">
                    <?= GetMessage("WBAPI_LOG_STATUS_QUANTITY") ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">
                    <?= GetMessage("WBAPI_LOG_TIME") ?></div>
            </td>
        </tr>
        <?php
        while ($ar = $resId->Fetch()) {
            echo '<tr class="adm-list-table-row"><td class="adm-list-table-cell">' . $ar['id_product'] .
                '</td><td class="adm-list-table-cell">' .
                CAbricosWb::getName($ar['id_product']) .
                '</td><td class="adm-list-table-cell">' .
                $ar['status_price'] .
                '</td><td class="adm-list-table-cell">' .
                $ar['status_quantity'] .
                '</td><td class="adm-list-table-cell">' .
                $ar['last_use'] .
                '</td></tr>';
        }
        ?>
    </table>

    <?php
} else {
    ?>
    <table class="adm-list-table" id="tbl_sale_order">
        <thead>
        <tr class="adm-list-table-header">

            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">ID</div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"><?= GetMessage('CES_PROFILE') ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"><?= GetMessage('CES_STATUS') ?></div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner"></div>
            </td>

        </tr>
        </thead>
        <tbody>
        <?php
        global $DB;
        $strSql = "SELECT * FROM `abricos_wbapi_setting`;";
        $res = $DB->Query($strSql, false, $err_mess . __LINE__);
        while ($row = $res->Fetch()) {
            ?>
            <tr class="adm-list-table-row">
                <td class="adm-list-table-cell"><?= $row['id'] ?></td>
                <td class="adm-list-table-cell"><?= $row['name_prof'] ?></td>
                <?php if ($row['action'] == 'STOP' or empty($row['action']))
                    $actMesStatus = GetMessage('CES_STOP_EXP');
                if ($row['action'] == 'START')
                    $actMesStatus = GetMessage('CES_START_EXPORT');
                ?>
                <td class="adm-list-table-cell"><?= $actMesStatus ?></td>
                <td class="adm-list-table-cell"><a
                            href="?PROFILE_ID=<?= $row['id'] ?>"><?= GetMessage('CES_LOOK_PROFILE') ?></a></td>
            </tr>
        <?php } ?>
    </table>

<?php } ?>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>