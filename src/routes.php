<?php
$routes = [
    'metadata',
    'authorise',
    'authorise3D',
    'capture',
    'cancel',
    'refund',
    'cancelOrRefund',
    'adjustAuthorisation',
    'getRecurringDetailsList',
    'disablesStoredPaymentDetails'
];
foreach($routes as $file) {
    require __DIR__ . '/../src/routes/'.$file.'.php';
}

