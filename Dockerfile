FROM ubuntu:20.04

# Avoid interactive prompts during installation
ENV DEBIAN_FRONTEND=noninteractive

# Set encoding
ENV LANG C.UTF-8

# Install required packages
RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-php \
    php \
    php-cli \
    php-pgsql \
    php-gd \
    php-zip \
    php-xml \
    postgresql \
    postgresql-contrib \
    openssl \
    wget \
    unzip \
    sharutils \
    makepasswd \
    debootstrap \
    schroot \
    quotatool \
    coreutils \
    libany-uri-escape-perl \
    sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable required Apache modules
RUN a2enmod php7.4
RUN a2enmod rewrite

# Set the document root to /var/www/html/boca
RUN sed -i 's|/var/www/html|/var/www/html|g' /etc/apache2/sites-available/000-default.conf

# Create Apache configuration for BOCA
RUN echo '<Directory /var/www/html/boca>\n\
    AllowOverride Options AuthConfig Limit\n\
    Require all granted\n\
    AddDefaultCharset utf-8\n\
</Directory>\n\
<Directory /var/www/html/boca/private>\n\
    AllowOverride None\n\
    Deny from all\n\
</Directory>\n\
<Directory /var/www/html/boca/doc>\n\
    AllowOverride None\n\
    Deny from all\n\
</Directory>\n\
<Directory /var/www/html/boca/tools>\n\
    AllowOverride None\n\
    Deny from all\n\
</Directory>' > /etc/apache2/conf-available/boca.conf

RUN a2enconf boca

# Configure PostgreSQL to listen on all interfaces
RUN sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/12/main/postgresql.conf && \
    echo "host    all   all 0.0.0.0/0 md5" >> /etc/postgresql/12/main/pg_hba.conf

# Copy BOCA files
WORKDIR /var/www/html
COPY . /var/www/html/boca

# Update permissions
RUN chown -R www-data:www-data /var/www/html/boca && \
    chmod 750 /var/www/html/boca/src/private

# Create initialization script
RUN echo "#!/bin/bash\n\
# Start PostgreSQL service\n\
service postgresql start\n\
\n\
# Wait for PostgreSQL to be ready\n\
echo 'Waiting for PostgreSQL to start...'\n\
timeout=30\n\
while ! pg_isready -h localhost -U postgres -t 1 && [ \$timeout -gt 0 ]; do\n\
    echo \"Waiting for PostgreSQL... \$timeout seconds remaining...\"\n\
    timeout=\$((timeout-1))\n\
    sleep 1\n\
done\n\
\n\
if [ \$timeout -eq 0 ]; then\n\
    echo 'Failed to connect to PostgreSQL within the timeout period'\n\
    exit 1\n\
fi\n\
\n\
# Create the bocauser if it doesn't exist\n\
echo 'Creating PostgreSQL user bocauser if it does not exist...'\n\
su - postgres -c \"psql -tAc \\\"SELECT 1 FROM pg_roles WHERE rolname='bocauser'\\\"\" | grep -q 1 ||\n\
    su - postgres -c \"psql -c \\\"CREATE USER bocauser WITH PASSWORD 'boca' CREATEDB;\\\"\"\n\
\n\
# Start Apache\n\
service apache2 start\n\
\n\
# Check if the database is already created\n\
su - postgres -c \"psql -lqt | cut -d \\| -f 1 | grep -qw bocadb\"\n\
if [ \$? -ne 0 ]; then\n\
    echo 'Creating BOCA database...'\n\
    cd /var/www/html/boca/src\n\
    # Update configuration\n\
    sed -i 's/\\$conf\\[\"dblocal\"\\]=\"true\"/\\$conf\\[\"dblocal\"\\]=\"false\"/' private/conf.php\n\
    sed -i 's/\\$conf\\[\"dbhost\"\\]=\"localhost\"/\\$conf\\[\"dbhost\"\\]=\"localhost\"/' private/conf.php\n\
    sed -i 's/\\$conf\\[\"dbname\"\\]=\"bocadb\"/\\$conf\\[\"dbname\"\\]=\"bocadb\"/' private/conf.php\n\
    sed -i 's/\\$conf\\[\"dbuser\"\\]=\"bocauser\"/\\$conf\\[\"dbuser\"\\]=\"bocauser\"/' private/conf.php\n\
    sed -i 's/\\$conf\\[\"dbpass\"\\]=\".*\"/\\$conf\\[\"dbpass\"\\]=\"boca\"/' private/conf.php\n\
    sed -i 's/\\$conf\\[\"dbsuperuser\"\\]=\".*\"/\\$conf\\[\"dbsuperuser\"\\]=\"bocauser\"/' private/conf.php\n\
    sed -i 's/\\$conf\\[\"dbsuperpass\"\\]=\".*\"/\\$conf\\[\"dbsuperpass\"\\]=\"boca\"/' private/conf.php\n\
    # Generate a random key\n\
    RANDOM_KEY=\"secretKey:\$(openssl rand -hex 16)\"\n\
    sed -i \"s/\\$conf\\[\\\"key\\\"\\]\\=\\\".*\\\"/\\$conf\\[\\\"key\\\"\\]\\=\\\"$RANDOM_KEY\\\"/\" private/conf.php\n\
    \n\
    # Print the configuration for debugging\n\
    echo 'BOCA database configuration:'\n\
    grep -E \"dblocal|dbhost|dbname|dbuser|dbpass|dbsuperuser|dbsuperpass\" private/conf.php\n\
    \n\
    # Create database\n\
    php private/createdb.php\n\
    echo 'Database created successfully!'\n\
else\n\
    echo 'BOCA database already exists'\n\
fi\n\
\n\
# Verify database connection\n\
echo 'Verifying database connection...'\n\
su - postgres -c \"psql -c 'SELECT version();'\"\n\
su - postgres -c \"psql -l\"\n\
\n\
# Fix file permissions\n\
chown -R www-data:www-data /var/www/html/boca\n\
\n\
# Debug information\n\
echo '==============================================='\n\
echo 'BOCA Online Contest Administrator is now running'\n\
echo 'Access at http://localhost:8080/boca/src/'\n\
echo 'Default access: '\n\
echo '- Username: system (empty password or \"boca\")'\n\
echo '- After first login, change passwords immediately!'\n\
echo '==============================================='\n\
\n\
# List files to debug\n\
echo 'Directory listing for debugging:'\n\
ls -la /var/www/html/boca\n\
ls -la /var/www/html/boca/src\n\
\n\
# Check PostgreSQL status\n\
echo 'PostgreSQL status:'\n\
service postgresql status\n\
\n\
# Keep container running\n\
tail -f /var/log/postgresql/postgresql-12-main.log /var/log/apache2/error.log" > /usr/local/bin/start-boca.sh

RUN chmod +x /usr/local/bin/start-boca.sh

# Expose ports
EXPOSE 80 5432

# Start services
CMD ["/usr/local/bin/start-boca.sh"] 
