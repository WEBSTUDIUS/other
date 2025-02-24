<?php
global $APPLICATION;

use Bitrix\Main\Localization\Loc;
$accessLevel = (string)$APPLICATION->GetGroupRight('vikki.wb');

	Loc::loadMessages(__FILE__);

	$yaMenu = [
		[
			'parent_menu' => 'global_menu_services',
			'section' => 'wb_api',
			'sort' => 1,
			'text' => 'Wb интеграция',
			'title' => 'Wb интеграция',
            'menu_id' => 'wb_api',
            'url' => 'settings.php?lang=ru&mid=vikki.wb&lang=ru',
            'icon' => 'fav_menu_icon_yellow',
            'items_id' => 'global_menu',
            "items"       => [
                    [
                        "text" => 'Настройки',
                        "url" => "wbapi_export.php?lang=ru",
                        "title" => 'Настройки',
                    ],
                    [
                        "text" => 'Логирование',
                        "url" => "wbapi_log.php?lang=ru",
                        "title" => 'Логирование',
                    ]
                ],
			]
		];


	return $yaMenu;
