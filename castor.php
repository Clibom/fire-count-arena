<?php

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\io;
use function Castor\context;
use function Castor\parallel;
use function Castor\run;
use function Castor\load_dot_env;

$state = [];

#[AsTask(name: "build", namespace: 'app', description: 'Build the project', aliases: ['build'])]
function app_build(
    #[AsOption(name: 'disable-up', description: 'Disable up application after build')]
    bool $disableUp = false,
    #[AsOption(name: 'disable-load-fixtures', description: 'Disable load fixtures')]
    bool $disableLoadFixtures = false,
): void
{
    run('docker compose pull');
    app_up(true);
    sleep(5);
    composer_install();
    parallel(
        function () use ($disableLoadFixtures) {
            db_migrate();
            if ($disableLoadFixtures === false) {
                db_fixtures_load();
            }
        },
        function () {
            node_install();
        }
    );
    assets_build();
    io()->table(['Service', 'Status'], [
        ['Docker init', "✅"],
        ['Install php dependencies', "✅"],
        ['Install node dependencies', "✅"],
        ['Migrate DB', "✅"],
        ['Fixtures load', $disableLoadFixtures ? "❌" : "✅"],
        ['Build assets', "✅"],
    ]);
    if ($disableUp === true) {
        app_down();
        io()->success('Your app is now ready to up');
        return;
    }
    display_up_info();
}

#[AsTask(name: "up", namespace: 'app', description: 'Up the project', aliases: ['up'])]
function app_up(
    #[AsOption(description: 'Disable up application informations')]
    bool    $disableInfo = false,
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' up --force-recreate --build -d');

    if ($disableInfo === false && is_dev($env)) {
        display_up_info();
    }
}

#[AsTask(name: "down", namespace: 'app', description: 'Down the project', aliases: ['down', 'stop', 'app:stop'])]
function app_down(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' down');
}

#[AsTask(name: "status", namespace: 'app', description: 'Status the project', aliases: ['status'])]
function app_status(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' ps');
}

#[AsTask(name: "logs", namespace: 'app', description: 'Logs', aliases: ['logs'])]
function app_logs(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' logs -f --tail="100"');
}

#[AsTask(name: "connect", namespace: 'app', description: 'Connect to PHP container', aliases: ['connect'])]
function app_connect(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' exec app bash', context: context()->toInteractive());
}

#[AsTask(name: 'clear', namespace: 'cache', description: 'Clear the application cache', aliases: ['cache-clear', 'c:c'])]
function cache_clear(): void
{
    run('rm -rf var/cache/');
}

#[AsTask(name: 'install', namespace: 'composer', description: 'Installs the project dependencies from the composer.lock file if present, or falls back on the composer.json')]
function composer_install(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    if (is_prod($env)) {
        run(get_docker($env) . ' exec app bash -ci "composer install --no-dev --optimize-autoloader"', context: context()->toInteractive());
        return;
    }

    run(get_docker($env) . ' exec app bash -ci "composer install"', context: context()->toInteractive());
}

#[AsTask(name: 'update', namespace: 'composer', description: 'Updates your dependencies to the latest version according to composer.json, and updates the composer.lock file')]
function composer_update(): void
{
    run('docker compose exec app bash -ci "composer update"', context: context()->toInteractive());
}

#[AsTask(name: 'require', namespace: 'composer', description: 'Updates your dependencies to the latest version according to composer.json, and updates the composer.lock file')]
function composer_require(string $package): void
{
    run("docker compose exec app bash -ci 'composer require $package'", context: context()->toInteractive());
}

#[AsTask(name: 'make')]
function make(string $subject): void
{
    run("docker compose exec app bash -ci 'bin/console make:$subject'", context: context()->toInteractive());
}

#[AsTask(name: 'install', namespace: 'node', description: 'Install a package')]
function node_install(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' exec app bash -ci "npm install"', context: context()->toInteractive());
}

#[AsTask(name: "build", namespace: 'assets', description: 'Build assets', aliases: ['a:b'])]
function assets_build(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    if (is_prod($env)) {
        run(get_docker($env) . ' exec app bash -ci "npm run build"', context: context()->toInteractive());
        return;
    }
    run('docker compose exec app bash -ci "npm run dev"', context: context()->toInteractive());
}

#[AsTask(name: "watch", namespace: 'assets', description: 'Build assets and automatically re-compile when files change')]
function assets_watch(): void
{
    run('docker compose exec app bash -ci "npm run watch"', context: context()->toInteractive());
}

#[AsTask(name: "icons:import", namespace: 'assets', description: 'Scan project and import icon(s) from iconify.design', aliases: ['i:i'])]
function assets_icons_import(): void
{
    run('docker compose exec app bash -ci "php bin/console ux:icons:lock"', context: context()->toInteractive());
}

#[AsTask(name: 'drop', namespace: 'db', description: 'Drop database schema', aliases: ['drop'])]
function db_drop(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    messenger_stop();
    run(get_docker($env) . ' exec app bash -ci "php bin/console doctrine:database:drop --force"');
    messenger_start();
}

#[AsTask(name: 'create', namespace: 'db', description: 'Create database schema', aliases: ['create'])]
function db_create(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    messenger_stop();
    run(get_docker($env) . ' exec app bash -ci "php bin/console doctrine:database:create"');
    messenger_start();
}

#[AsTask(name: 'migrate', namespace: 'db', description: 'Migrates database schema', aliases: ['migrate'])]
function db_migrate(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' exec app bash -ci "php bin/console doctrine:migration:migrate --no-interaction"');
}

#[AsTask(name: 'diff', namespace: 'db', description: 'Migrates database schema', aliases: ['db:d'])]
function db_diff(): void
{
    run('docker compose exec app bash -ci "php bin/console doctrine:migrations:diff --no-interaction"');
}

#[AsTask(name: 'generate', namespace: 'db', description: 'Generate database migration', aliases: ['db:g'])]
function db_generate(): void
{
    run('docker compose exec app bash -ci "php bin/console doctrine:migrations:generate --no-interaction"');
}

#[AsTask(name: 'fixtures', namespace: 'db', description: 'Load fixtures', aliases: ['fixtures'])]
function db_fixtures_load(): void
{
    run('docker compose exec app bash -ci "php bin/console doctrine:fixtures:load --no-interaction"');
}

#[AsTask(name: 'run', namespace: 'tests', description: 'Run tests', aliases: ['tests', 't:r'])]
function tests_run(): void
{
    run('docker compose exec app bash -ci "php vendor/bin/pest"', context: context()->toInteractive());
}

#[AsTask(name: 'stop', namespace: 'messenger', description: 'Arrête le worker Symfony Messenger')]
function messenger_stop(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' stop messenger_worker');
}

#[AsTask(name: 'start', namespace: 'messenger', description: 'Démarre le worker Symfony Messenger')]
function messenger_start(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);
    run(get_docker($env) . ' start messenger_worker');
}

#[AsTask(name: 'prod:update', namespace: 'deployment', description: 'Run prod deployment')]
function deployment_prod_update(
    #[AsOption(name: 'env')]
    ?string $env = null,
    #[AsOption(name: 'disable-db-backup')]
    ?bool   $disableDbBackup = false,
    #[AsOption(name: 'disable-git-pull')]
    ?bool   $disableGitPull = false,
    #[AsOption(name: 'disable-env-update')]
    ?bool   $disableEnvUpdate = false,
): void
{
    $env = get_env($env);
    if ($disableDbBackup === false) {
        messenger_stop(env: $env);
        run(get_docker($env) . ' exec pgbackups "/backup.sh"');
        messenger_start(env: $env);
        io()->info('Database backup done.');
    }

    if ($disableEnvUpdate === false) {
        do {
            $key = io()->ask('Enter a configuration key for .env.local (e.g. APP_ENV), or press Enter to finish: ');
            if ($key !== null) {
                $value = io()->ask("Enter the value for '$key': ");
                run("echo '$key=$value' >> .env.local");
            }

        } while ($key !== null);
    }

    if ($disableGitPull === false) {
        run('git pull', context: context()->toInteractive());
        io()->info('Git pull done.');
    }

    run(get_docker($env) . ' pull');
    app_up(env: $env);
    sleep(5);
    composer_install(env: $env);
    db_migrate(env: $env);
    node_install(env: $env);
    assets_build(env: $env);
    cache_clear();
}

#[AsTask(name: 'prod:install', namespace: 'deployment', description: 'Run prod deployment settings')]
function deployment_prod_init(): void
{
    if (file_exists('.env.local')) {
        throw new RuntimeException('The .env.local file already exists.');
    }

    $appSecret = bin2hex(random_bytes(random_int(60, 100)));
    $password = bin2hex(random_bytes(random_int(30, 40)));
    $mailer="smtp://valentin.metz@clibom.com:N%S%aVfTe533@ssl0.ovh.net:587";
    run("echo 'APP_ENV=prod' >> .env.local");
    run("echo 'APP_SECRET={$appSecret}' >> .env.local");
    run("echo 'POSTGRES_PASSWORD={$password}' >> .env.local");
    run("echo 'MAILER_DSN={$mailer}' >> .env.local");

    io()->success('Configuration (.env.local) initialized successfully.');
    io()->info('Now starting build and launch processes...');
    deployment_prod_update(disableDbBackup: true, disableGitPull: true, disableEnvUpdate: true);
}

#[AsTask(name: 'demo:install', namespace: 'deployment', description: 'Run demo deployment settings')]
function deployment_demo_init()
{
    if (file_exists('.env.local')) {
        throw new RuntimeException('The .env.local file already exists.');
    }

    $appSecret = bin2hex(random_bytes(random_int(60, 100)));
    $password = bin2hex(random_bytes(random_int(30, 40)));
    run("echo 'APP_ENV=demo' >> .env.local");
    run("echo 'APP_SECRET={$appSecret}' >> .env.local");
    run("echo 'POSTGRES_PASSWORD={$password}' >> .env.local");

    io()->success('Configuration (.env.local) initialized successfully.');
    io()->info('Now starting build and launch processes...');
    deployment_demo_update();
}

#[AsTask(name: 'demo:update', namespace: 'deployment', description: 'Run prod deployment')]
function deployment_demo_update(
    #[AsOption(name: 'env')]
    ?string $env = null,
): void
{
    $env = get_env($env);

    run(get_docker($env) . ' pull');
    app_up(env: $env);
    composer_install(env: $env);
    db_migrate(env: $env);
    db_fixtures_load();
    node_install(env: $env);
    assets_build(env: $env);
    cache_clear();
}

function display_up_info(): void
{
    io()->success('Your app is up');
    io()->table(['App', 'Status', 'Available at'], [
        ['Web', "✅", "http://localhost:8002"],
        ['Mailer', "✅", "http://localhost:8027"],
    ]);
}

function get_docker(string $env): string
{
    return match ($env) {
        'dev' => 'docker compose ',
        'prod' => 'docker compose -f compose.yaml -f compose.prod.yaml ',
        'demo' => 'docker compose -f compose.yaml -f compose.demo.yaml ',
        default => throw new Exception('Environment not found'),
    };
}

function get_env(?string $env): string
{
    global $state;

    $loadEnv = load_dot_env();
    $e = $env ?: $loadEnv['APP_ENV'];

    if (!isset($state['has_already_displaying_env_info'])) {
        io()->info('Environment is : ' . $e);
        $state['has_already_displaying_env_info'] = true;
    }

    return $e;
}

function is_prod(string $env): bool
{
    return $env === 'prod' || $env === '--prod';
}

function is_dev(string $env): bool
{
    return $env === 'dev' || $env === '--dev';
}