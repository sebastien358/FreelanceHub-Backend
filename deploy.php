<?php
namespace Deployer;

require 'recipe/symfony.php';
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables from .env files
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env', __DIR__ . '/.env.dev', __DIR__ . '/.env.local');

// Common configuration
set('repository', $_ENV['DEPLOYER_REPOSITORY']);
set('writable_mode', 'chmod');

// Production configuration
host('prod')
    ->set('hostname', $_ENV['DEPLOYER_HOST'])
    ->set('remote_user', $_ENV['DEPLOYER_USER'])
    ->set('http_user', $_ENV['DEPLOYER_USER'])
    ->set('identity_file', $_ENV['DEPLOYER_SSHKEY'])
    ->set('deploy_path', $_ENV['DEPLOYER_PROD_PATH']);

after('deploy:cache:clear', 'database:migrate');
after('deploy:cache:clear', 'deploy:dump-env');
