<?php

return [
    'merchant'        => env('MERCHANT_ZIBAL', 'zibal'),
    'paymentBaseUri'  => 'https://gateway.zibal.ir',
    'paymentTrackId'  => 'v1/request',
    'paymentStart'    => 'start',
    'paymentInquiry'   => 'v1/inquiry',
    'paymentAcceptUrl'   => 'v1/verify',
    'timeout'         => 30,
    'connect_timeout' => 60,
];
