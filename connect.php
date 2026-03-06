<?php
$dsn = 'mysql:host=localhost;dbname=evershine_leb';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Default fetch as associative array
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$db = new PDO($dsn, 'root', '', $options);
