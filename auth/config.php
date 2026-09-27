<?php
return [
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '360314848870-gn4ebpcuutkinvon6j2np3g0r7dbp3rg.apps.googleusercontent.com',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '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',
        'redirect_uri' => 'https://bukid2bayan.vercel.app/auth/google.php',
    ],
    'facebook' => [
        'app_id' => $_ENV['FACEBOOK_APP_ID'] ?? 'YOUR_FACEBOOK_APP_ID',
        'app_secret' => $_ENV['FACEBOOK_APP_SECRET'] ?? 'YOUR_FACEBOOK_APP_SECRET',
        'redirect_uri' => 'https://bukid2bayan.vercel.app/auth/facebook.php',
    ],
    'recaptcha' => [
        'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '6LethNEtAAAAAL5PWr5m0eVNyr81u6b-EpAs3jwp',
        'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '6LethNEtAAAAALpakvndTVHdialFIBgv5JO8RMsE',
    ]
];
