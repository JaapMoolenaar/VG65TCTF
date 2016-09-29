<?php

return array(

    'dolphinIOS'     => array(
        'environment' => 'development',
        'certificate' => __DIR__ . '/../_keys/apn_pushcert.pem',
        'passPhrase'  => '',
        'service'     => 'apns'
    ),
    'dolphinAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);