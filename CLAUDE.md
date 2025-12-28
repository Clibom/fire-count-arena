# Fire Count Arena - Claude Code Instructions

## Project Description

Application for managing the count of people present in a sports hall for emergency situations.
- **Users**: Sports club coaches
- **Features**: Input of children/adults count per session, quick summary display for emergency responders

## Architecture

### Hexagonal Architecture (Ports & Adapters)

```
src/
├── Domain/                     # Business core (no external dependencies)
│   ├── Model/                  # Domain entities and Value Objects
│   ├── Repository/             # Repository interfaces (ports)
│   ├── Service/                # Business service interfaces
│   └── Exception/              # Domain exceptions
│
├── UseCases/                   # Use cases (orchestration)
│   ├── Session/                # Session-related use cases
│   └── Counter/                # Counting-related use cases
│
└── Infrastructure/             # Adapters and implementations
    ├── Controller/             # Symfony controllers
    ├── Entity/                 # Doctrine entities (ORM)
    ├── Repository/             # Repository implementations
    ├── DataFixtures/           # Test data fixtures
    ├── Form/                   # Symfony forms
    └── Twig/                   # Twig extensions and Components
```

### Architecture Rules

1. **Domain**: No dependencies on Infrastructure or Framework
2. **UseCases**: Depends only on Domain (via interfaces)
3. **Infrastructure**: Implements Domain interfaces

## Tech Stack

- **Framework**: Symfony 7.4 LTS
- **PHP**: 8.4+
- **Database**: PostgreSQL 16 via Doctrine ORM
- **Front-end**: Symfony UX (Stimulus 3.2, Turbo 8, Live Components)
- **CSS**: Tailwind CSS 4.1 + DaisyUI 5
- **Tests**: Pest PHP
- **Execution**: Docker (thecodingmachine/php:8.4-v5-apache-node24)
- **Task Runner**: Castor PHP (100% of commands go through Castor)

## Project Management with Castor

**All project initialization and execution management relies 100% on Castor PHP.**

Castor is the single entry point for all project operations. See `castor.php` for the complete list of available tasks.

```bash
# List all available commands
castor list

# Development
castor dev:start              # Start Docker environment
castor dev:stop               # Stop Docker environment

# Deployment
castor deployment:prod:install   # Production installation
castor deployment:prod:update    # Production update
```

**Important**: Never run Docker, Symfony, or npm commands directly. Always use Castor.

**Command Execution**: The user always executes commands themselves. Claude should never run castor or any shell commands directly. Instead, indicate which command should be run and let the user execute it.

## Project Tracking

### Roadmap

Keep the `ROADMAP.md` file up to date with:
- Items in progress
- Completed items
- Backlog

**Note**: The roadmap is written in French.

### Time Tracking

When the user provides a start and end time, record the time spent on the corresponding item in the roadmap.

Format: `Xh Ymin` (e.g., `1h 30min`, `45min`, `2h`)

## Code Conventions

### Language

**All code comments must be written in English.** This includes:
- Inline comments
- Docblocks
- Commit messages
- Code documentation

Exception: The roadmap (`ROADMAP.md`) is in French.

### Naming

- **Controllers**: `NameController` in `Infrastructure/Controller/`
- **Entities**: `Name` in `Infrastructure/Entity/` with Doctrine attributes
- **Repositories**: Interface `NameRepositoryInterface` in `Domain/Repository/`, implementation `DoctrineNameRepository` in `Infrastructure/Repository/`
- **Use Cases**: `VerbNameUseCase` (e.g., `CreateSessionUseCase`)

### Symfony

- Use PHP 8 attributes for routes (`#[Route]`)
- Use constructor dependency injection
- Prefer Live Components for dynamic interactions

### Tests (TDD Required)

**Always apply TDD (Test-Driven Development):**
1. Write or modify the test first
2. Verify that the test fails
3. Implement or modify the code to make the test pass
4. Refactor if necessary

- Organize tests mirroring the `src/` structure
- Use Pest for tests
- Prefer functional tests for controllers

## Business Context

### Main Entities

- **Session**: A training session with date, time, coach
- **Counter**: People count (children, adults) for a session

### User Flow

1. The coach selects or creates their session
2. They input the number of children and adults present
3. In case of emergency, they display the simplified summary for responders