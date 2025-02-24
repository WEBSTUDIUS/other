<?php

namespace Vikki\WB;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class Agent
{

    public static function delete(): void
    {
        \CAgent::RemoveAgent(
            '\Vikki\WB\ExchangeOfOrders::AgentExportWB();',
            'vikki.wb'
        );
    }

    public static function addAgent(): void
    {
        \CAgent::AddAgent(
            '\Vikki\WB\ExchangeOfOrders::AgentExportWB();',
            'vikki.wb',
            'N',
            Option::get('vikki.wb', "WB_TIME_UPDATE") * 60,
            "",
            "Y",
        );
    }

    public static function isExist()
    {

        return \CAllAgent::GetList(
            [],
            [
                'MODULE_ID' => 'vikki.wb',
                'NAME' => '\Vikki\WB\ExchangeOfOrders::AgentExportWB();'
            ]
        )->Fetch()['ID'] ?: false;
    }


}