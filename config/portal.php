<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public Contact Details
    |--------------------------------------------------------------------------
    |
    | Shown on the landing page and the sign-in help note. These are
    | placeholders — set the PORTAL_CONTACT_* variables in .env to the
    | Registrar's / ICT office's real details.
    |
    */
    'contact' => [
        'email' => env('PORTAL_CONTACT_EMAIL', 'registrar@ncbii.edu.ph'),
        'office' => env('PORTAL_CONTACT_OFFICE', "Registrar's Office"),
        'address' => env('PORTAL_CONTACT_ADDRESS', 'North Coast Bohol Institute Incorporated, Bohol'),
    ],
];
