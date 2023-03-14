<?php

namespace App\Enum;

class PaymentEnum extends BaseEnum
{
    const TABLE = 'payments';

    const OFFLINE_TYPE = 'OFFLINE';
    const ONLINE_TYPE = 'ONLINE';
    const CREDIT_TYPE = 'CREDIT';

    const TYPE_LIST = [
        self:: OFFLINE_TYPE,
        self:: ONLINE_TYPE,
        self:: CREDIT_TYPE
    ];

    const PENDING = 'PENDING';
    const SUCCESS = 'SUCCESS';
    const EXPIRED = 'EXPIRED';
    const REJECTED = 'REJECTED';
    const CONFIRM_PENDING = 'CONFIRM_PENDING';
    const FAILED = 'FAILED';
    const SUSPENDED = 'SUSPENDED';

    const possibleStatus = [
        self::PENDING,
        self::SUCCESS,
        self::REJECTED,
        self::EXPIRED,
        self::CONFIRM_PENDING,
        self::FAILED,
        self::SUSPENDED,
    ];
}