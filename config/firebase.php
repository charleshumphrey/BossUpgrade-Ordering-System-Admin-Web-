<?php


return [
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('C:\xampp\htdocs\dashboard\BossUpgrade\storage\serviceaccountkey\bossupgrade-101-firebase-adminsdk-ejl42-ad77ad6c97.json')),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://bossupgrade-101-default-rtdb.firebaseio.com'),
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID'),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
];
