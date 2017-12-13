<?php

//the file is storing the credentials for third party services.
return array(

    //Can be used to redis data encryption
    'ses' => array(
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ),
    //...
);