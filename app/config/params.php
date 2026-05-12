<?php

return [
    'adminEmail' => 'admin@example.com',
    'allowedDomain' => getenv('ALLOWED_DOMAIN'),
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'smsPilotApiKey' => getenv('SMS_PILOT_API_KEY'),
];
