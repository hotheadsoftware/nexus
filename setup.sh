#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

error_exit() {
    echo -e "${RED}$1${NC}" 1>&2
    exit 1
}

echo -e "${GREEN}Starting the setup for CDI Nexus...${NC}"

if [ ! -f "./composer.json" ]; then
    error_exit "Error: composer.json not found. Is this a Laravel project?"
fi

echo -e "\n${YELLOW}Setting up the environment...${NC}"
docker run -it --rm -v "$(pwd):/app" composer:latest composer install --ignore-platform-reqs || error_exit "Error during Composer install."

if [ ! -f "./vendor/bin/sail" ]; then
    error_exit "Error: vendor/bin/sail not found. Please make sure you've run 'composer install'."
fi

if [ ! -f "./docker-compose.yml" ]; then
    selected_services="pgsql,redis,mailpit"
    docker run -it --rm -v "$(pwd):/app" composer:latest php artisan sail:install --with="$selected_services" || error_exit "Error installing Sail."
fi

if [ ! -f "./.env" ] && [ -f "./.env.example" ]; then
    echo -e "\n${YELLOW}Copying .env.example to .env...${NC}"
    cp .env.example .env || error_exit "Error copying .env.example to .env."
fi

echo -e "\n${YELLOW}Starting application services...${NC}"
./vendor/bin/sail up -d || error_exit "Error starting application services."

container_id=$(docker ps -a --filter "name=pgsql" --format "{{.ID}}")

if [ -n "$container_id" ]; then
    echo -e "${YELLOW}Checking Database Container Age...${NC}"

    creation_time=$(docker inspect -f '{{.Created}}' "$container_id" 2>/dev/null)

    if [ -n "$creation_time" ]; then
        creation_time_sec=$(date -d "$creation_time" +%s)
        current_time_sec=$(date +%s)

        age_sec=$((current_time_sec - creation_time_sec))

        if [ "$age_sec" -le 60 ]; then
            echo "Container was created less than 1 minute ago. Sleeping for 30 seconds."
            sleep 30
        fi
    fi
fi

echo -e "\n${YELLOW}Running migrations and seeding the database...${NC}"
./vendor/bin/sail artisan migrate --seed || error_exit "Error running migrations and seeding database."

echo -e "\n${YELLOW}Installing NPM packages...${NC}"
./vendor/bin/sail npm install || error_exit "Error installing NPM packages."

if git rev-parse --is-inside-work-tree > /dev/null 2>&1; then
    echo -e "\n${YELLOW}Setting up Pint on a pre-commit hook...${NC}"


    cat > .git/hooks/pre-commit <<EOF
#!/bin/sh

# Run Pint to fix code style issues
./vendor/bin/pint || { echo -e "${RED}Pint execution failed.${NC}"; exit 1; }

# Check if Pint has made any changes
if git status --porcelain | grep -qE '^[AM]+\s+.*\.(php)$'; then
    echo -e "${RED}Pint has made changes to some PHP files. Please review and add them before committing.${NC}"
    exit 1
fi

# Continue with the commit if no changes were made
exit 0
EOF

    chmod +x .git/hooks/pre-commit || error_exit "Error setting pre-commit hook as executable."
else
    echo "${GREEN}Skipping Pint Pre-Hook: We're Not In A Repository${NC}"
fi

echo -e "\n${YELLOW}If you'd like a better developer experience, you can install the following Bash aliases:${NC}\n"

echo -e "${GREEN}|======================================================================================|${NC}"
echo -e "${GREEN}| alias sail='.vendor/bin/sail'                                                        |${NC}"
echo -e "${GREEN}| alias artisan='sail artisan'                                                         |${NC}"
echo -e "${GREEN}| alias tinker='sail artisan tinker'                                                   |${NC}"
echo -e "${GREEN}| alias composer='sail composer'                                                       |${NC}"
echo -e "${GREEN}|======================================================================================|${NC}"

read -p -r "\nWould you like to install these aliases? (y/n): " user_input

# Case-insensitive comparison of first character & search for negative intent.
# -- True: y, yes, yeah, yup, yo, yippie-kay-yay, etc.
# -- False: "Yeah, no", "Yeah, that's a no from me, dawg.", "You realize I said no, right?"

if [[ "${user_input,,}" == y* && ! "${user_input,,}" == *no* ]]; then
    cat <<EOF >> ~/.bashrc
alias sail='.vendor/bin/sail'
alias artisan='sail artisan'
alias tinker='sail artisan tinker'
alias composer='sail composer'
EOF
    echo -e "\nAliases have been added to your ~/.bashrc. Please restart your terminal or use the following command: source ~/.bashrc"
else
    echo -e "\n${GREEN}Setup completed successfully! Try .vendor/bin/sail npm run dev to get started!${NC}"
    exit 0
fi
