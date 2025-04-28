# BOCA Docker Setup

This repository contains Docker configuration files to run BOCA (BOCA Online Contest Administrator) in a Docker container.

## Requirements

- Docker
- Docker Compose

## Getting Started

1. Clone this repository:
   ```
   git clone https://github.com/eavelasquez/boca.git
   cd boca
   ```

2. Build and start the container:
   ```
   docker-compose up -d
   ```

3. Access BOCA in your browser:
   ```
   http://localhost:8080/boca/src/
   ```

4. Login credentials:
   - Username: `system`
   - Password: empty or `boca` (as configured in src/private/conf.php)

5. After your first login, immediately change the passwords for both `system` and `admin` users.

## Troubleshooting Database Issues

If you encounter database connection issues, use the troubleshooting script:

```
./db-troubleshoot.sh
```

This script will:
- Check if the container is running
- Verify PostgreSQL service status
- Test database connections
- Create the database if needed
- Display configuration details
- Restart services

Alternatively, you can manually fix database issues:

1. Rebuild the container:
   ```
   docker-compose down
   docker-compose up -d --build
   ```

2. Check container logs for errors:
   ```
   docker-compose logs boca
   ```

3. Access the container shell for manual inspection:
   ```
   docker-compose exec boca bash
   ```

4. Within the container, check PostgreSQL:
   ```
   service postgresql status
   su - postgres -c "psql -c 'SELECT version();'"
   ```

## Running a Contest with BOCA

### Initial Setup

1. Access BOCA at http://localhost:8080/boca/src/
2. Log in as "system" or "admin" (password is empty or "boca")

### Creating the Contest

1. Click on "Contest" and select "new" from the dropdown menu
2. Fill in the contest details:
   - **Name**: Name of your contest
   - **Start date**: When the contest will begin
   - **Duration**: How many minutes the contest will last
   - **Stop answering**: When to stop sending feedback to teams (usually 15 minutes before end)
   - **Stop scoreboard**: When to freeze the scoreboard (e.g., 240 minutes)
   - **Penalty**: Time penalty for incorrect submissions (typically 20 minutes)
   - **Max file size**: Limit for submitted files

3. Click "Send" and then "Activate" to make the contest active (you'll be logged out)

### Admin Configuration

1. Log in as "admin" (password is empty or "boca")
2. Change the admin password via "Options"

### Setting Up Contest Components

#### Languages
1. Click "Languages" and add programming languages:
   - Number: Unique identifier (1, 2, 3...)
   - Name: Language name (C, C++, Java...)
   - Extension: File extension (.c, .cpp, .java)

#### Problems
1. Click "Problems" to add contest problems:
   - Number: Unique identifier
   - Name: Problem nickname (typically A, B, C...)
   - Problem package: Upload ZIP file with problem definition
   - Color: For balloon tracking (optional)

#### Users
1. Click "Users" to create accounts:
   - Teams: Create accounts for competing teams
   - Judges: Create accounts for people who will judge submissions
   - Staff: For balloon runners and other support staff

### Site Configuration

1. Go to "Site" and configure:
   - Site number (usually 1 for single-site contests)
   - Set contest timing
   - Enable/disable auto-ending

### Starting the Contest

1. The contest will start automatically at the specified time
2. Judges log in to evaluate submissions
3. Teams log in to submit solutions

### Cleaning Up After a Warm-up (if needed)

If you ran a warm-up contest, you can reset by:
1. Go to the "Site" tab
2. Use the buttons to delete all runs, clarifications, tasks, and backups
3. Then start the real contest

### During the Contest

- Judges evaluate solutions and answer clarification requests
- Staff deliver balloons for correct solutions (if using physical balloons)
- Admin monitors the contest progress via the dashboard

**Important Note**: Any deletion of problems, languages, answers, or users will cascade and delete all related records, so avoid removing these during an active contest.

## Configuration

- The PostgreSQL database user is set to `bocauser` with password `boca`
- You can modify the configuration by editing the `src/private/conf.php` file before building the Docker image
- To access the container: `docker-compose exec boca bash`

## Advanced Configuration

### Customizing the Docker Environment

You can customize the environment by editing:
- `Dockerfile` - For changing the base image or installed packages
- `docker-compose.yml` - For modifying ports, volumes, or environment variables

### Preparing Problem Packages

Problems in BOCA are defined by ZIP files with specific structure:
- `description/` - Contains problem statement and metadata
- `input/` - Test case input files
- `output/` - Expected output for test cases
- `compare/`, `compile/`, `run/` - Scripts for each language to handle the problem

See `doc/problemexamples/` for examples.

## License

This software is distributed under the terms of the GNU General Public License v3.0. 
