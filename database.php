<?php

// Require Composer's autoloader.
require 'vendor/autoload.php';

// Use the Medoo namespace.
use Medoo\Medoo;

$db = new Medoo([
	'database_type' => 'mysql',
	'database_name' => 'ai-timelapse',
	'server' => 'studioharmhasenaar-do-user-14248305-0.b.db.ondigitalocean.com',
	'username' => 'ai-timelapse',
	'password' => $_ENV['DATABASE_PASSWORD'],
	'port' => 25060
]);