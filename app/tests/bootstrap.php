<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
$consolePath = 'D:/epi_2_st/xampp/htdocs/blog-tdd/app/bin/console';
// Drop the existing schema
passthru('php ' . $consolePath . ' --env=test doctrine:schema:drop --full-database --force');

// Run the migrations to set up the schema
passthru('php ' . $consolePath . ' --env=test --no-interaction doctrine:migrations:migrate');
