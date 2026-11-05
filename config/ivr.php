<?php

return [
    // Must match the URL configured on the Twilio number exactly: request signatures depend on it.
    'public_url' => env('TWILIO_IVR_PUBLIC_URL', 'https://www.madeleinecafebistro.com'),
    'business_name' => env('TWILIO_IVR_BUSINESS_NAME', 'Madeline Cafe Bistro'),
    'transfer_to' => env('TWILIO_IVR_TRANSFER_TO', '+17877631285'),
    'location_url' => env('TWILIO_IVR_LOCATION_URL', 'https://maps.app.goo.gl/jP2xL3wia2sXekcD6'),

    // Used until the restaurant saves its own text in the admin panel.
    'hours' => [
        'es' => "Madeline Cafe Bistro\nHorarios:\n\nLunes 7:00AM A 3:00PM (Brunch All Day)\nMartes 7:00AM A 5:00PM\nMiércoles 7:00AM A 5:00PM\nJueves 7:00AM A 5:00PM\nViernes 7:00AM A 5:00PM\n\nBrunch All Day\nSábado 12:00PM A 10:00PM\nDomingo 12:00PM A 9:00PM",
        'en' => "Madeline Cafe Bistro\nHours:\n\nMonday 7:00 AM to 3:00 PM (All Day Brunch)\nTuesday 7:00 AM to 5:00 PM\nWednesday 7:00 AM to 5:00 PM\nThursday 7:00 AM to 5:00 PM\nFriday 7:00 AM to 5:00 PM\n\nBrunch All Day\nSaturday 12:00 PM to 10:00 PM\nSunday 12:00 PM to 9:00 PM",
    ],
];
