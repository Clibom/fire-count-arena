# Castor Commands Reference

All project operations are managed through Castor. Never run Docker, Composer, or npm commands directly.

## Quick Reference

```bash
castor list              # Show all available commands
castor <command> --help  # Show help for a specific command
```

---

## Application Commands

### `castor build`

Full project build from scratch. Resets everything.

```bash
castor build                    # Full build with fixtures
castor build --no-fixtures      # Full build without fixtures
castor build --env=demo         # Build for demo environment
```

**What it does:**
1. Pulls latest Docker images
2. Starts containers with rebuild
3. Deletes `vendor/`, `node_modules/`, `var/cache/`, `var/log/`
4. Runs `composer install`
5. Runs `npm install`
6. Resets database (drop, create, migrate, fixtures)
7. Builds assets
8. Clears cache

### `castor up`

Start the project containers (quick start, no rebuild).

```bash
castor up                # Start dev containers
castor up --env=prod     # Start prod containers
castor up --env=demo     # Start demo containers
```

### `castor down`

Stop the project containers.

```bash
castor down              # Stop dev containers
castor down --env=prod   # Stop prod containers
```

### `castor restart`

Restart the project containers.

```bash
castor restart
```

### `castor status`

Show container status.

```bash
castor status
castor ps                # Alias
```

### `castor logs`

Show container logs.

```bash
castor logs              # Last 100 lines
castor logs -f           # Follow logs in real-time
castor logs --follow     # Same as -f
```

### `castor shell`

Open a bash shell in the PHP container.

```bash
castor shell
castor ssh               # Alias
castor connect           # Alias
```

---

## Database Commands

### `castor db:reset`

Reset database completely (drop, create, migrate, fixtures).

```bash
castor db:reset                 # Full reset with fixtures
castor db:reset --no-fixtures   # Reset without fixtures
```

> ⚠️ **Warning**: This destroys all data in the database!

### `castor db:migrate`

Run pending database migrations.

```bash
castor migrate           # Alias
castor db:migrate
```

### `castor db:diff`

Generate a migration by comparing database schema to entities.

```bash
castor db:diff
```

### `castor db:generate`

Generate a blank migration file.

```bash
castor db:generate
```

### `castor db:fixtures`

Load fixtures into database.

```bash
castor fixtures          # Alias
castor db:fixtures
```

> ⚠️ Blocked in production environment.

### `castor db:status`

Show migration status.

```bash
castor db:status
```

---

## Test Commands

### `castor tests`

Run all tests.

```bash
castor tests                        # Run all tests
castor test                         # Alias
castor tests -f "FireCount"         # Filter by name
castor tests --filter="creates"     # Filter by test description
```

### `castor tests:coverage`

Run tests with code coverage report.

```bash
castor coverage          # Alias
castor tests:coverage
```

### `castor tests:unit`

Run only unit tests (`tests/Unit/`).

```bash
castor tests:unit
```

### `castor tests:functional`

Run only functional tests (`tests/Functional/`).

```bash
castor tests:functional
```

### `castor tests:integration`

Run only integration tests (`tests/Integration/`).

```bash
castor tests:integration
```

---

## Dependency Commands

### Composer

```bash
castor composer:install              # Install PHP dependencies
castor composer:update               # Update PHP dependencies
castor composer:require <package>    # Add a new package
castor composer:require <pkg> --dev  # Add as dev dependency
```

### Node

```bash
castor node:install      # Install Node dependencies
castor node:update       # Update Node dependencies
```

---

## Asset Commands

### `castor assets:build`

Build assets for current environment.

```bash
castor assets            # Alias
castor assets:build      # npm run dev (dev) or npm run build (prod)
```

### `castor assets:watch`

Watch and rebuild assets on changes.

```bash
castor assets:watch
```

### `castor assets:icons`

Import icons from iconify.design.

```bash
castor assets:icons
```

---

## Symfony Commands

### `castor console`

Run any Symfony console command.

```bash
castor console "cache:clear"
castor console "debug:router"
castor c "doctrine:schema:validate"   # Alias
```

### `castor make`

Run Symfony maker commands.

```bash
castor make controller
castor make entity
castor make form
```

---

## Cache Commands

### `castor cache:clear`

Clear the application cache.

```bash
castor cc                # Alias
castor cache:clear
```

---

## Deployment Commands

### Production

#### `castor deployment:prod:install`

Initialize production environment. Creates `.env.local` with secure credentials.

```bash
castor deployment:prod:install
```

**Interactive prompts:**
- Application URL
- Mailer DSN

#### `castor deployment:prod:update`

Deploy updates to production.

```bash
castor deployment:prod:update                    # Full deployment
castor deployment:prod:update --no-backup        # Skip database backup
castor deployment:prod:update --no-pull          # Skip git pull
```

**What it does:**
1. Backup database (optional)
2. Git pull (optional)
3. Pull and start containers
4. Install dependencies
5. Run migrations
6. Build assets
7. Clear cache

### Demo

#### `castor deployment:demo:install`

Initialize demo environment.

```bash
castor deployment:demo:install
```

#### `castor deployment:demo:update`

Deploy updates to demo (includes fixtures reload).

```bash
castor deployment:demo:update
castor deployment:demo:update --no-pull
```

---

## Environment Options

Most commands support the `--env` option:

```bash
castor up --env=dev      # Development (default)
castor up --env=demo     # Demo
castor up --env=prod     # Production
```

The environment is automatically detected from `.env.local` if not specified.

---

## Common Workflows

### First-time setup (development)

```bash
castor build
```

### Daily development

```bash
castor up                # Start containers
castor assets:watch      # Watch assets in another terminal
# ... develop ...
castor tests             # Run tests
castor down              # Stop when done
```

### After pulling changes

```bash
castor up
castor composer:install
castor node:install
castor db:migrate
castor assets:build
castor cc
```

### Reset everything

```bash
castor build
```

### Production deployment

```bash
castor deployment:prod:update
```