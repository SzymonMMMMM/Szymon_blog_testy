<?php

use Symfony\Component\Dotenv\Dotenv;

// TODO - usunac to i zaadresowac
// error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
passthru('./bin/console --env=test doctrine:schema:drop --full-database --force');
passthru('./bin/console --env=test --no-interaction doctrine:migrations:migrate');
passthru('./bin/console --env=test --no-interaction doctrine:fixtures:load');
