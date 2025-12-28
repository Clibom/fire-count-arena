<?php

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\io;
use function Castor\context;
use function Castor\run;
use function Castor\load_dot_env;

$state = [];

// =============================================================================
// APP COMMANDS
// =============================================================================

#[AsTask(name: 'build', namespace: 'app', description: 'Full project build from scratch (reset everything)', aliases: ['build'])]
function app_build(
    #[AsOption(name: 'env')]
    ?string $env = null,
    #[AsOption(name: 'no-fixtures', description: 'Skip loading fixtures')]
    bool $noFixtures = false,
): void {
    $env = get_env($env);

    io()->title('Building project from scratch...');

    // Pull latest images
    io()->section('Pulling Docker images');
    run(get_docker($env) . 'pull');

    // Start containers
    io()->section('Starting containers');
    run(get_docker($env) . 'up -d --force-recreate --build');
    sleep(5);

    // Clean dependencies
    io()->section('Cleaning dependencies');
    run('rm -rf vendor node_modules var/cache var/log');

    // Install PHP dependencies (required for Symfony console commands)
    io()->section('Installing PHP dependencies');
    run(get_docker($env) . 'exec app bash -ci "composer install"', context: context()->toInteractive());

    // Install Node dependencies
    io()->section('Installing Node dependencies');
    run(get_docker($env) . 'exec app bash -ci "npm install"', context: context()->toInteractive());

    // Reset database
    io()->section('Resetting database');
    db_reset(env: $env, noFixtures: $noFixtures);

    // Build assets
    io()->section('Building assets');
    assets_build(env: $env);

    // Clear cache
    cache_clear();

    io()->success('Build completed successfully!');
    display_up_info($env);
}

#[AsTask(name: 'up', namespace: 'app', description: 'Start the project containers', aliases: ['up', 'start'])]
function app_up(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'up -d');

    if (is_dev($env)) {
        display_up_info($env);
    }
}

#[AsTask(name: 'down', namespace: 'app', description: 'Stop the project containers', aliases: ['down', 'stop'])]
function app_down(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'down');
    io()->success('Containers stopped.');
}

#[AsTask(name: 'restart', namespace: 'app', description: 'Restart the project containers', aliases: ['restart'])]
function app_restart(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    app_down(env: $env);
    app_up(env: $env);
}

#[AsTask(name: 'status', namespace: 'app', description: 'Show container status', aliases: ['status', 'ps'])]
function app_status(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'ps');
}

#[AsTask(name: 'logs', namespace: 'app', description: 'Show container logs', aliases: ['logs'])]
function app_logs(
    #[AsOption(name: 'env')]
    ?string $env = null,
    #[AsOption(name: 'follow', shortcut: 'f', description: 'Follow log output')]
    bool $follow = false,
): void {
    $env = get_env($env);
    $cmd = get_docker($env) . 'logs --tail="100"';
    if ($follow) {
        $cmd .= ' -f';
    }
    run($cmd, context: context()->toInteractive());
}

#[AsTask(name: 'shell', namespace: 'app', description: 'Open a shell in the PHP container', aliases: ['shell', 'ssh', 'connect'])]
function app_shell(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'exec app bash', context: context()->toInteractive());
}

// =============================================================================
// CACHE COMMANDS
// =============================================================================

#[AsTask(name: 'clear', namespace: 'cache', description: 'Clear the application cache', aliases: ['cc'])]
function cache_clear(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    run('rm -rf var/cache/*');
    io()->success('Cache cleared.');
}

// =============================================================================
// COMPOSER COMMANDS
// =============================================================================

#[AsTask(name: 'install', namespace: 'composer', description: 'Install PHP dependencies')]
function composer_install(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    $cmd = 'composer install';
    if (is_prod($env)) {
        $cmd .= ' --no-dev --optimize-autoloader';
    }
    run(get_docker($env) . "exec app bash -ci \"$cmd\"", context: context()->toInteractive());
}

#[AsTask(name: 'update', namespace: 'composer', description: 'Update PHP dependencies')]
function composer_update(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'exec app bash -ci "composer update"', context: context()->toInteractive());
}

#[AsTask(name: 'require', namespace: 'composer', description: 'Require a new PHP package')]
function composer_require(
    string $package,
    #[AsOption(name: 'dev', description: 'Add as dev dependency')]
    bool $dev = false,
): void {
    $cmd = "composer require $package";
    if ($dev) {
        $cmd .= ' --dev';
    }
    run("docker compose exec app bash -ci '$cmd'", context: context()->toInteractive());
}

// =============================================================================
// NODE COMMANDS
// =============================================================================

#[AsTask(name: 'install', namespace: 'node', description: 'Install Node dependencies')]
function node_install(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'exec app bash -ci "npm install"', context: context()->toInteractive());
}

#[AsTask(name: 'update', namespace: 'node', description: 'Update Node dependencies')]
function node_update(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'exec app bash -ci "npm update"', context: context()->toInteractive());
}

// =============================================================================
// ASSETS COMMANDS
// =============================================================================

#[AsTask(name: 'build', namespace: 'assets', description: 'Build assets for development or production', aliases: ['assets'])]
function assets_build(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    $cmd = is_prod($env) ? 'npm run build' : 'npm run dev';
    run(get_docker($env) . "exec app bash -ci \"$cmd\"", context: context()->toInteractive());
}

#[AsTask(name: 'watch', namespace: 'assets', description: 'Watch and rebuild assets on changes')]
function assets_watch(): void {
    run('docker compose exec app bash -ci "npm run watch"', context: context()->toInteractive());
}

#[AsTask(name: 'icons', namespace: 'assets', description: 'Import icons from iconify.design')]
function assets_icons(): void {
    run('docker compose exec app bash -ci "php bin/console ux:icons:lock"', context: context()->toInteractive());
}

// =============================================================================
// DATABASE COMMANDS
// =============================================================================

#[AsTask(name: 'reset', namespace: 'db', description: 'Reset database (drop, create, migrate, fixtures)', aliases: ['db:reset'])]
function db_reset(
    #[AsOption(name: 'env')]
    ?string $env = null,
    #[AsOption(name: 'no-fixtures', description: 'Skip loading fixtures')]
    bool $noFixtures = false,
): void {
    $env = get_env($env);

    io()->warning('This will destroy all data in the database!');

    // Drop and create database
    run(get_docker($env) . 'exec app bash -ci "php bin/console doctrine:database:drop --force --if-exists"');
    run(get_docker($env) . 'exec app bash -ci "php bin/console doctrine:database:create"');

    // Run migrations
    db_migrate(env: $env);

    // Load fixtures (only in dev/demo)
    if (!$noFixtures && !is_prod($env)) {
        db_fixtures(env: $env);
    }

    io()->success('Database reset completed.');
}

#[AsTask(name: 'migrate', namespace: 'db', description: 'Run database migrations', aliases: ['migrate'])]
function db_migrate(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);
    run(get_docker($env) . 'exec app bash -ci "php bin/console doctrine:migrations:migrate --no-interaction"');
}

#[AsTask(name: 'diff', namespace: 'db', description: 'Generate a migration by comparing database to entities', aliases: ['db:diff'])]
function db_diff(): void {
    run('docker compose exec app bash -ci "php bin/console doctrine:migrations:diff --no-interaction"');
    io()->success('Migration generated. Review it in migrations/ folder.');
}

#[AsTask(name: 'generate', namespace: 'db', description: 'Generate a blank migration file', aliases: ['db:generate'])]
function db_generate(): void {
    run('docker compose exec app bash -ci "php bin/console doctrine:migrations:generate"');
}

#[AsTask(name: 'fixtures', namespace: 'db', description: 'Load fixtures into database', aliases: ['fixtures'])]
function db_fixtures(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void {
    $env = get_env($env);

    if (is_prod($env)) {
        io()->error('Cannot load fixtures in production environment!');
        return;
    }

    run(get_docker($env) . 'exec app bash -ci "php bin/console doctrine:fixtures:load --no-interaction"');
}

#[AsTask(name: 'status', namespace: 'db', description: 'Show migration status')]
function db_status(): void {
    run('docker compose exec app bash -ci "php bin/console doctrine:migrations:status"');
}

// =============================================================================
// TEST COMMANDS
// =============================================================================

#[AsTask(name: 'run', namespace: 'tests', description: 'Run tests', aliases: ['tests', 't:r'])]
function tests_run(): void
{
    $process = run('docker compose exec app php vendor/bin/pest --colors=always', context: context()->withAllowFailure());
    exit($process->getExitCode());
}

#[AsTask(name: 'coverage', namespace: 'tests', description: 'Run tests with coverage report', aliases: ['coverage'])]
function tests_coverage(): void {
    run('docker compose exec app bash -ci "php vendor/bin/pest --coverage"', context: context()->toInteractive());
}

#[AsTask(name: 'unit', namespace: 'tests', description: 'Run only unit tests')]
function tests_unit(): void {
    run('docker compose exec app bash -ci "php vendor/bin/pest tests/Unit"', context: context()->toInteractive());
}

#[AsTask(name: 'functional', namespace: 'tests', description: 'Run only functional tests')]
function tests_functional(): void {
    run('docker compose exec app bash -ci "php vendor/bin/pest tests/Functional"', context: context()->toInteractive());
}

#[AsTask(name: 'integration', namespace: 'tests', description: 'Run only integration tests')]
function tests_integration(): void {
    run('docker compose exec app bash -ci "php vendor/bin/pest tests/Integration"', context: context()->toInteractive());
}

// =============================================================================
// SYMFONY COMMANDS
// =============================================================================

#[AsTask(name: 'console', namespace: 'sf', description: 'Run a Symfony console command', aliases: ['console', 'c'])]
function sf_console(string $command): void {
    run("docker compose exec app bash -ci \"php bin/console $command\"", context: context()->toInteractive());
}

#[AsTask(name: 'make', namespace: 'sf', description: 'Run a Symfony maker command', aliases: ['make'])]
function sf_make(string $subject): void {
    run("docker compose exec app bash -ci \"php bin/console make:$subject\"", context: context()->toInteractive());
}

// =============================================================================
// DEPLOYMENT COMMANDS - PRODUCTION
// =============================================================================

#[AsTask(name: 'prod:install', namespace: 'deployment', description: 'Initialize production environment')]
function deployment_prod_install(): void {
    if (file_exists('.env.local')) {
        io()->error('The .env.local file already exists. Delete it first if you want to reinitialize.');
        return;
    }

    io()->title('Production Environment Setup');

    $appSecret = bin2hex(random_bytes(32));
    $dbPassword = bin2hex(random_bytes(20));

    // Collect configuration
    $appUrl = io()->ask('Enter the application URL (e.g., https://firecount.example.com)');
    $mailerDsn = io()->ask('Enter the mailer DSN (e.g., smtp://user:pass@smtp.example.com:587)', 'null://null');

    // Create .env.local
    $envContent = <<<ENV
APP_ENV=prod
APP_SECRET={$appSecret}
APP_URL={$appUrl}
POSTGRES_PASSWORD={$dbPassword}
MAILER_DSN={$mailerDsn}
ENV;

    file_put_contents('.env.local', $envContent);

    io()->success('Configuration (.env.local) created successfully.');
    io()->info('Starting build process...');

    deployment_prod_update(disableBackup: true, disableGitPull: true);
}

#[AsTask(name: 'prod:update', namespace: 'deployment', description: 'Deploy updates to production')]
function deployment_prod_update(
    #[AsOption(name: 'no-backup', description: 'Skip database backup')]
    bool $disableBackup = false,
    #[AsOption(name: 'no-pull', description: 'Skip git pull')]
    bool $disableGitPull = false,
): void {
    $env = 'prod';

    io()->title('Production Deployment');

    // Backup database
    if (!$disableBackup) {
        io()->section('Backing up database');
        run(get_docker($env) . 'exec pgbackups "/backup.sh"');
        io()->success('Database backup completed.');
    }

    // Git pull
    if (!$disableGitPull) {
        io()->section('Pulling latest changes');
        run('git pull', context: context()->toInteractive());
    }

    // Pull and start containers
    io()->section('Updating containers');
    run(get_docker($env) . 'pull');
    run(get_docker($env) . 'up -d');
    sleep(5);

    // Install dependencies
    io()->section('Installing dependencies');
    composer_install(env: $env);
    node_install(env: $env);

    // Run migrations
    io()->section('Running migrations');
    db_migrate(env: $env);

    // Build assets
    io()->section('Building assets');
    assets_build(env: $env);

    // Clear cache
    cache_clear();

    io()->success('Production deployment completed!');
}

// =============================================================================
// DEPLOYMENT COMMANDS - DEMO
// =============================================================================

#[AsTask(name: 'demo:install', namespace: 'deployment', description: 'Initialize demo environment')]
function deployment_demo_install(): void {
    if (file_exists('.env.local')) {
        io()->error('The .env.local file already exists. Delete it first if you want to reinitialize.');
        return;
    }

    io()->title('Demo Environment Setup');

    $appSecret = bin2hex(random_bytes(32));
    $dbPassword = bin2hex(random_bytes(20));

    // Create .env.local
    $envContent = <<<ENV
APP_ENV=demo
APP_SECRET={$appSecret}
POSTGRES_PASSWORD={$dbPassword}
ENV;

    file_put_contents('.env.local', $envContent);

    io()->success('Configuration (.env.local) created successfully.');
    io()->info('Starting build process...');

    deployment_demo_update(disableGitPull: true);
}

#[AsTask(name: 'demo:update', namespace: 'deployment', description: 'Deploy updates to demo')]
function deployment_demo_update(
    #[AsOption(name: 'no-pull', description: 'Skip git pull')]
    bool $disableGitPull = false,
): void {
    $env = 'demo';

    io()->title('Demo Deployment');

    // Git pull
    if (!$disableGitPull) {
        io()->section('Pulling latest changes');
        run('git pull', context: context()->toInteractive());
    }

    // Pull and start containers
    io()->section('Updating containers');
    run(get_docker($env) . 'pull');
    run(get_docker($env) . 'up -d');
    sleep(5);

    // Install dependencies
    io()->section('Installing dependencies');
    composer_install(env: $env);
    node_install(env: $env);

    // Reset database with fixtures
    io()->section('Resetting database');
    db_reset(env: $env, noFixtures: false);

    // Build assets
    io()->section('Building assets');
    assets_build(env: $env);

    // Clear cache
    cache_clear();

    io()->success('Demo deployment completed!');
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

function display_up_info(string $env = 'dev'): void {
    io()->success('Application is running');

    $rows = [
        ['Web', '✅', 'http://localhost:8080'],
    ];

    if (is_dev($env)) {
        $rows[] = ['Mailer', '✅', 'http://localhost:8025'];
    }

    io()->table(['Service', 'Status', 'URL'], $rows);
}

function get_docker(string $env): string {
    return match ($env) {
        'dev' => 'docker compose ',
        'prod' => 'docker compose -f compose.yaml -f compose.prod.yaml ',
        'demo' => 'docker compose -f compose.yaml -f compose.demo.yaml ',
        default => throw new RuntimeException("Unknown environment: $env"),
    };
}

function get_env(?string $env): string {
    global $state;

    if ($env !== null) {
        return $env;
    }

    $loadEnv = load_dot_env();
    $e = $loadEnv['APP_ENV'] ?? 'dev';

    if (!isset($state['env_displayed'])) {
        io()->note("Environment: $e");
        $state['env_displayed'] = true;
    }

    return $e;
}

function is_prod(string $env): bool {
    return $env === 'prod';
}

function is_dev(string $env): bool {
    return $env === 'dev';
}

function is_demo(string $env): bool {
    return $env === 'demo';
}