<?php

namespace Vikki\WB\Api\Orders\Model;

class Field
{
    protected array $order;

    protected function getField($keyPart){
        return $this->order[$keyPart] ?? null;
    }
}