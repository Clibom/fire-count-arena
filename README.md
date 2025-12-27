# Fire Count Arena

Application for counting people present in a sports hall for emergency situation management.

## Purpose

Enable sports club coaches to:
- Record the number of children and adults present during their sessions
- Quickly display a clear summary for emergency responders in case of an incident

## Prerequisites

- [Castor CLI](https://castor.jolicode.com/installation/)
- Docker & Docker Compose

## Installation

**All project management relies 100% on Castor PHP and Docker.**

### Local Development

1. Clone the project
   ```bash
   git clone <repository-url>
   cd fire-count-arena
   ```

2. List available commands
   ```bash
   castor list
   ```

3. Build and start the development environment
   ```bash
   castor build
   ```

## Production Deployment

### Initial Setup

1. Install Castor CLI
   ```bash
   # Follow the installation guide:
   # https://castor.jolicode.com/installation/#as-a-static-binary
   ```

2. Prepare the project
   ```bash
   git clone <repository-url>
   sudo chown -R 999:999 ./var/backup/db
   ```

3. Configure Apache reverse proxy
   - Redirect HTTP traffic to port 7071

4. Configure `.env.local`
   ```
   APP_URL="https://your-domain.com"
   ```

5. Initialize the environment
   ```bash
   castor deployment:prod:install
   ```

### Updates

```bash
castor deployment:prod:update
```

## Tech Stack

- **Backend**: Symfony 7.4 LTS
- **Frontend**: Symfony UX (Stimulus 3.2, Turbo 8, Live Components)
- **CSS**: Tailwind CSS 4.1 + DaisyUI 5
- **Database**: PostgreSQL 16
- **Tests**: Pest PHP
- **Architecture**: Hexagonal (Ports & Adapters)
- **Execution**: Docker (thecodingmachine/php:8.4-v5-apache-node24)
- **Task Runner**: Castor PHP

## Project Structure

```
src/
├── Domain/           # Business core, interfaces
├── UseCases/         # Use cases
└── Infrastructure/   # Controllers, Entities, Repositories
```

See [CLAUDE.md](./CLAUDE.md) for more details on the architecture.

See [docs/COMMANDS.md](./docs/COMMANDS.md) for the full Castor commands reference.

## License

Proprietary