<?php

use Tapp\FilamentActivity\Models\Activity;

return [
    'model' => Activity::class,

    'tenant' => [
        'enabled' => false,
        'model' => null,
        'foreign_key' => 'tenant_id',
    ],

    'user' => [
        'model' => config('auth.providers.users.model'),
    ],
];
