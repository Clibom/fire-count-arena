# Arena Tournament

## Production Deployment

### Initial Setup
1. Install Castor CLI
   ```bash
   # Follow the installation guide at:
   # https://castor.jolicode.com/installation/#as-a-static-binary
   ```

2. Clone and prepare the project
   ```bash
   # Clone the repository
   git clone ...

   # Set required permissions for database backups
   sudo chown -R 999:999 ./var/backup/db
   ```

3. Configure Apache reverse proxy
    - Set up to redirect HTTP traffic to port 7071

4. Add correct Application url in `.env.local`
    - `APP_URL="{{YOUR_ARENA_TOURNAMENT_URL}}"`

5. Initialize the production environment
   ```bash
   castor deployment:prod:install
   ```

### Subsequent Deployments
To update an existing installation:
```bash
castor deployment:prod:update
```