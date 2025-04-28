#!/bin/bash

echo "BOCA Database Troubleshooting Script"
echo "==================================="
echo

echo "1. Checking if container is running..."
if docker-compose ps | grep -q "boca"; then
  echo "✅ Container is running"
else
  echo "❌ Container is not running! Try: docker-compose up -d"
  exit 1
fi

echo
echo "2. Checking PostgreSQL service inside container..."
docker-compose exec boca service postgresql status
if [ $? -ne 0 ]; then
  echo "❌ PostgreSQL service is not running inside the container"
  echo "Trying to start PostgreSQL service..."
  docker-compose exec boca service postgresql start
else
  echo "✅ PostgreSQL service is running"
fi

echo
echo "3. Checking PostgreSQL connection..."
docker-compose exec boca su - postgres -c "psql -c 'SELECT version();'" > /dev/null 2>&1
if [ $? -ne 0 ]; then
  echo "❌ Cannot connect to PostgreSQL"
else
  echo "✅ PostgreSQL connection successful"
fi

echo
echo "4. Checking BOCA database..."
docker-compose exec boca su - postgres -c "psql -lqt | grep -qw bocadb"
if [ $? -ne 0 ]; then
  echo "❌ BOCA database does not exist"
  echo "Trying to create the database..."
  docker-compose exec boca bash -c "cd /var/www/html/boca/src && php private/createdb.php"
else
  echo "✅ BOCA database exists"
fi

echo
echo "5. Checking BOCA configuration file..."
docker-compose exec boca grep -E "dblocal|dbhost|dbname|dbuser|dbpass|dbsuperuser|dbsuperpass" /var/www/html/boca/src/private/conf.php

echo
echo "6. Attempting to restart services..."
docker-compose restart boca
echo "✅ Services restarted"

echo
echo "Troubleshooting complete! Try accessing BOCA at http://localhost:8080/boca/src/"
echo "If you still have issues, try rebuilding the container:"
echo "docker-compose down && docker-compose up -d --build" 
