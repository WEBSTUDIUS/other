<?php
/*
 * Файл local/modules/WB/install/index.php
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class vikki_wb extends CModule
{

    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');
            $this->MODULE_ID = 'vikki.wb';
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME = Loc::getMessage('WB_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('WB_DESCRIPTION');
            $this->PARTNER_NAME = 'Andrey Ilin';
            $this->PARTNER_URI = '';
        } else {
            CAdminMessage::ShowMessage(
                Loc::getMessage('WB_FILE_NOT_FOUND') . ' version.php'
            );
        }
    }

    public function DoInstall()
    {

        global $APPLICATION;

        // мы используем функционал нового ядра D7 — поддерживает ли его система?
        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            // копируем файлы, необходимые для работы модуля
            $this->InstallFiles();
            // создаем таблицы БД, необходимые для работы модуля
            $this->InstallDB();
            // регистрируем модуль в системе
            ModuleManager::registerModule($this->MODULE_ID);
            // регистрируем обработчики событий
            $this->InstallEvents();
        } else {
            CAdminMessage::ShowMessage(
                Loc::getMessage('WB_INSTALL_ERROR')
            );
            return;
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('WB_INSTALL_TITLE') . ' «' . Loc::getMessage('WB_NAME') . '»',
            __DIR__ . '/step.php'
        );
    }

    public function InstallFiles()
    {
        // копируем js-файлы, необходимые для работы модуля
        CopyDirFiles(
            __DIR__ . '/assets/scripts',
            Application::getDocumentRoot() . '/bitrix/js/' . $this->MODULE_ID . '/',
            true,
            true
        );
        // копируем css-файлы, необходимые для работы модуля
        CopyDirFiles(
            __DIR__ . '/assets/styles',
            Application::getDocumentRoot() . '/bitrix/css/' . $this->MODULE_ID . '/',
            true,
            true
        );
    }

    public function InstallDB(): bool
    {
        global $DB;
        try {
            $DB->RunSQLBatch(__DIR__ . '/batches/wb_orders_create.sql');
            $DB->RunSQLBatch(__DIR__ . '/batches/wb_logs_create.sql');
        } catch (Exception $ex) {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log/table_error.txt', print_r($ex, true), FILE_APPEND);
        }
        return true;
    }

//    public function InstallEvents() {
//        // перед выводом буферизированного контента добавим свой HTML код,
//        // в котором сохраним настройки для нашей кнопки прокрутки наверх
//        EventManager::getInstance()->registerEventHandler(
//            'main',
//            'OnBeforeEndBufferContent',
//            $this->MODULE_ID,
//            'Vikki\WB\Main',
//            'appendJavaScriptAndCSS'
//        );
//    }

    public function DoUninstall()
    {

        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('WB_UNINSTALL_TITLE') . ' «' . Loc::getMessage('WB_NAME') . '»',
            __DIR__ . '/unstep.php'
        );

    }

    public function UnInstallFiles()
    {
        // удаляем js-файлы
        Directory::deleteDirectory(
            Application::getDocumentRoot() . '/bitrix/js/' . $this->MODULE_ID
        );
        // удаляем css-файлы
        Directory::deleteDirectory(
            Application::getDocumentRoot() . '/bitrix/css/' . $this->MODULE_ID
        );
        // удаляем настройки нашего модуля
        Option::delete($this->MODULE_ID);
    }

    public function UnInstallDB()
    {
        global $DB;
        $DB->RunSQLBatch(__DIR__ . '/batches/wb_orders_drop.sql');
        $DB->RunSQLBatch(__DIR__ . '/batches/wb_logs_drop.sql');
        return true;
    }

    public function UnInstallEvents()
    {
        // удаляем наш обработчик события
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnBeforeEndBufferContent',
            $this->MODULE_ID,
            'Vikki\WB\Main',
            'appendJavaScriptAndCSS'
        );
    }

}