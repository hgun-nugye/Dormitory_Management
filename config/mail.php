<?php

return [
    'host' => getenv('MAIL_HOST') ?: 'smtp.mailtrap.io',
    'port' => (int)(getenv('MAIL_PORT') ?: 2525),
    'username' => (getenv('MAIL_USERNAME') !== false && getenv('MAIL_USERNAME') !== 'null' && getenv('MAIL_USERNAME') !== '') ? getenv('MAIL_USERNAME') : null,
    'password' => (getenv('MAIL_PASSWORD') !== false && getenv('MAIL_PASSWORD') !== 'null' && getenv('MAIL_PASSWORD') !== '') ? getenv('MAIL_PASSWORD') : null,
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@ktx.vnuhcm.edu.vn',
        'name' => getenv('MAIL_FROM_NAME') ?: 'KTX ĐHQG-HCM'
    ]
];

