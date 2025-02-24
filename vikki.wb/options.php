<?php
/*
 * Файл local/modules/WB/options.php
 */

global $APPLICATION;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Vikki\WB\Agent;
use Vikki\WB\ExchangeOfOrders;
use Vikki\WB\OrdersTable;

Loc::loadMessages(__FILE__);
Bitrix\Main\Loader::includeModule('catalog');
// Получить список складов
$arStores = StoreTable::getList([
    'select' => ['*']
])->fetchAll();
$warehouseOptions = [];
foreach ($arStores as $arStore) {
    $warehouseOptions[$arStore['ID']] = $arStore['TITLE'];
}

// GET STATUSES
try {
    $statusesOptions = \Vikki\WB\Bitrix\OrdersTableManager::getOrderStatuses();
} catch (\Throwable $e) {
    $statusesOptions = ['error' => $e->getMessage()];
}

// получаем идентификатор модуля
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);
// подключаем наш модуль
Loader::includeModule($module_id);

//$odj = new \Vikki\Wb\Api\WbConnector();
//Debug::dump($odj->getAllOrders());


//\Vikki\WB\ExchangeOfOrders::AgentExportWB();
//\Vikki\WB\ExchangeOfOrders::AgentExportWB();

//$result = OrdersTable::add(array(
//    'ORDER_WB_ID ' => '9780321127426',
//    'STATUS' => 'Suc',
//));
//if ($result->isSuccess())
//{
//    $id = $result->getId();
//    print_r($id);
//}
$array = [
    'select' => ["ORDER_WB_ID"],
    'filter' => ["ID" => 1],
];
//$result = OrdersTable::getList($array)->fetch();
//print_r($result);


/*
 * Параметры модуля со значениями по умолчанию
 */
$aTabs = array(
    array(
        /*
         * Первая вкладка «Основные настройки»
         */
        'DIV' => 'main',
        'TAB' => 'Основные настройки',
        'TITLE' => 'Основные настройки',
        'OPTIONS' => array(
            array(
                'WB_ACTIVE_MODULE',
                'Активность модуля',
                'Y',
                array('checkbox')
            ),
            array(
                'WB_API_KEY',
                'api-key',
                '',
                array('textarea', 10)
            ),
            array(
                'WB_TIME_UPDATE',
                'Частота обмена (мин)',
                '5',
                array('text', 5)
            ),
            array(
                'WB_DAYS_FROM',
                'Период обмена в днях (дней в минус от текущей даты)',
                '4',
                array('text', 5)
            ),
            array(
                'WB_RESPONSIBLE_PERSON',
                'Ответственный',
                '',
                array('text', 5)
            ),
            array(
                'WB_CATALOG_ID',
                'Каталог id',
                '',
                array('text', 3)
            ),
        )
    ),
    array(
        /*
         * Вторая вкладка «Сопоставление полей»
         */
        'DIV' => 'comparison',
        'TAB' => 'Сопоставление полей',
        'TITLE' => 'Сопоставление полей',
        'OPTIONS' => array(
            array(
                'WB_WAREHOUSE_ID',
                'Склад отгрузки',
                '',
                array('selectbox', $warehouseOptions)
            ),
        )
    ),

    [
        /*
     * Третья вкладка «Сопоставление статусов»
     */
        'DIV' => 'statuses',
        'TAB' => 'Сопоставление статусов',
        'TITLE' => 'Сопоставление статусов',
        'OPTIONS' => [

            [
                'WB_NEW_ORDER_STATUS',
                'Статусы нового заказа из ВБ (оплачен в сделке)',
                '',
                ['multiselectbox', $statusesOptions]
            ],
            [
                'WB_SENT_ORDER_STATUS',
                'Статусы отгруженного заказа из ВБ (отгружен в сделке)',
                '',
                ['multiselectbox', $statusesOptions]
            ],
            [
                'WB_WON_ORDER_STATUS',
                'Статусы доставленного заказа из ВБ (завершен успешно в сделке)',
                '',
                ['multiselectbox', $statusesOptions]
            ],
            [
                'WB_LOSE_ORDER_STATUS',
                'Статусы отмененного заказа из ВБ (сделка проиграна)',
                '',
                ['multiselectbox', $statusesOptions]
            ],

        ]
    ],

);

/*
 * Создаем форму для редактирвания параметров модуля
 */
$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);

$tabControl->Begin();
?>

    <form action="<?= $APPLICATION->GetCurPage(); ?>?mid=<?= $module_id; ?>&lang=<?= LANGUAGE_ID; ?>" method="post">
        <?= bitrix_sessid_post(); ?>
        <?php
        foreach ($aTabs as $aTab) { // цикл по вкладкам
            if ($aTab['OPTIONS']) {
                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
            }
        }
        $tabControl->Buttons();
        ?>

        <?php if (!Agent::isExist()) { ?>
            <input type="submit" name="apply"
                   value="<?= Loc::GetMessage('WB_OPTIONS_INPUT_APPLY'); ?>" class="adm-btn-save"/>
            <input type="submit" name="start"
                   value="<?= Loc::GetMessage('WB_OPTIONS_INPUT_START'); ?>"/>
            <div style="color: red; margin-top: 10px;">Перед запуском все поля должны быть заполнены!</div>
        <?php } else { ?>
            <input type="submit" name="delete"
                   value="<?= Loc::GetMessage('WB_OPTIONS_INPUT_DELETE'); ?>" class="adm-btn-save"/>
        <?php } ?>
    </form>

<?php
$tabControl->End();

/*
 * Обрабатываем данные после отправки формы
 */
if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) { // цикл по вкладкам
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption)) { // если это название секции
                continue;
            }
            if ($arOption['note']) { // если это примечание
                continue;
            }
            if ($request['apply']) { // сохраняем введенные настройки
                $optionValue = $request->getPost($arOption[0]);
                if ($arOption[0] == 'switch_on') {
                    if ($optionValue == '') {
                        $optionValue = 'N';
                    }
                }
                if ($arOption[0] == 'jquery_on') {
                    if ($optionValue == '') {
                        $optionValue = 'N';
                    }
                }
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
            } elseif ($request['default']) { // устанавливаем по умолчанию
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }
    if ($request['start'] && ExchangeOfOrders::checkModuleSettings()) { // если это старт агента
        Agent::addAgent();
    }
    if ($request['delete']) { // если это удаление агента
        Agent::delete();
    }

    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . $module_id . '&lang=' . LANGUAGE_ID);

}