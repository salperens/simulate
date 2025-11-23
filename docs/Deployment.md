# Deployment Guide

This guide covers deploying the Lig Simulation application to production environments.

## Prerequisites

- Docker and Docker Compose installed on server
- Domain name configured (optional)
- SSL certificate (Let's Encrypt recommended)
- Database backup strategy

## Pre-Deployment Checklist

- [ ] All tests passing (`make test`)
- [ ] Code coverage meets requirements (92.1%)
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] Frontend assets built
- [ ] SSL certificates configured
- [ ] Backup strategy in place
- [ ] Monitoring configured

## Environment Setup

### 1. Clone Repository

```bash
git clone https://github.com/salperens/simulate.git
cd simulate
```

### 2. Configure Environment

Copy and edit `.env` file:

```bash
cp .env.example .env
nano .env
```

**Key Environment Variables:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=lig_simulation
DB_USERNAME=lig_user
DB_PASSWORD=secure_password

# Cache and Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (if needed)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

## Docker Configuration

### Production Dockerfile

Ensure the Dockerfile is optimized for production:

- Multi-stage builds
- Minimal base images
- Proper caching layers
- Security best practices

### Docker Compose

Review `docker-compose.yml` for production:

```yaml
services:
  app:
    build: .
    restart: unless-stopped
    environment:
      - APP_ENV=production
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - app-network

  nginx:
    image: nginx:alpine
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
    networks:
      - app-network

  mysql:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: lig_simulation
      MYSQL_USER: lig_user
      MYSQL_PASSWORD: secure_password
      MYSQL_ROOT_PASSWORD: root_password
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - app-network
```

## Deployment Steps

### 1. Build Images

```bash
make build
```

### 2. Start Containers

```bash
make up
```

### 3. Install Dependencies

```bash
make install
make npm-install
```

### 4. Run Migrations

```bash
make migrate
```

### 5. Build Frontend Assets

```bash
make npm-build
```

### 6. Cache Configuration

```bash
make cache-config
```

### 7. Set Permissions

```bash
docker-compose exec app chown -R www:www storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## SSL Configuration

### Using Let's Encrypt

1. Install Certbot:

```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx
```

2. Obtain certificate:

```bash
sudo certbot --nginx -d your-domain.com
```

3. Configure auto-renewal:

```bash
sudo certbot renew --dry-run
```

### Manual SSL Setup

1. Place certificates in `docker/nginx/ssl/`:
   - `your-domain.crt`
   - `your-domain.key`

2. Update nginx configuration to use SSL

## Nginx Configuration

### Production Nginx Config

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/nginx/ssl/your-domain.crt;
    ssl_certificate_key /etc/nginx/ssl/your-domain.key;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Database Backup

### Automated Backups

Create backup script:

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups"
DB_NAME="lig_simulation"
DB_USER="lig_user"
DB_PASS="secure_password"

docker-compose exec -T mysql mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql" -mtime +7 -delete
```

Schedule with cron:

```bash
0 2 * * * /path/to/backup.sh
```

### Restore Backup

```bash
docker-compose exec -T mysql mysql -u$DB_USER -p$DB_PASS $DB_NAME < backup_20241123.sql
```

## Monitoring

### Health Checks

The application includes health check endpoints:

```bash
# Check application health
curl https://your-domain.com/health

# Check database connection
docker-compose exec app php artisan db:show
```

### Log Monitoring

```bash
# View application logs
make logs-app

# View nginx logs
make logs-nginx

# View MySQL logs
make logs-mysql
```

### Application Monitoring

Consider integrating:
- **Sentry** for error tracking
- **New Relic** or **Datadog** for APM
- **Prometheus** for metrics
- **Grafana** for visualization

## Performance Optimization

### 1. Enable OPcache

Already configured in `docker/php/opcache.ini`

### 2. Use Redis for Caching

Update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 3. Database Optimization

- Add indexes for frequently queried columns
- Use query optimization
- Consider read replicas for high traffic

### 4. Frontend Optimization

- Enable gzip compression in nginx
- Use CDN for static assets
- Minify JavaScript and CSS
- Enable browser caching

## Security Hardening

### 1. Environment Variables

- Never commit `.env` file
- Use strong passwords
- Rotate secrets regularly

### 2. Firewall

```bash
# Allow only necessary ports
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

### 3. Container Security

- Run containers as non-root user
- Keep images updated
- Scan for vulnerabilities

### 4. Application Security

- Keep Laravel updated
- Review and update dependencies
- Enable CSRF protection
- Use HTTPS only

## Scaling

### Horizontal Scaling

1. Use load balancer (nginx, HAProxy)
2. Run multiple app containers
3. Use shared session storage (Redis)
4. Use shared file storage (S3, NFS)

### Vertical Scaling

1. Increase container resources
2. Optimize database queries
3. Add database indexes
4. Use caching aggressively

## Rollback Procedure

### 1. Database Rollback

```bash
# Rollback last migration
make artisan CMD="migrate:rollback"

# Rollback to specific migration
make artisan CMD="migrate:rollback --step=5"
```

### 2. Code Rollback

```bash
# Revert to previous commit
git checkout <previous-commit>
make build
make restart
```

### 3. Restore Backup

```bash
# Stop application
make down

# Restore database
docker-compose exec -T mysql mysql -u$DB_USER -p$DB_PASS $DB_NAME < backup.sql

# Restart application
make up
```

## Maintenance

### Regular Tasks

- **Daily**: Check logs for errors
- **Weekly**: Review performance metrics
- **Monthly**: Update dependencies
- **Quarterly**: Security audit

### Updates

```bash
# Update code
git pull origin main

# Update dependencies
make update
make npm-install

# Rebuild and restart
make build
make restart

# Run migrations if needed
make migrate
```

## Troubleshooting

### Application Won't Start

```bash
# Check logs
make logs

# Check container status
make ps

# Rebuild containers
make build
```

### Database Connection Issues

```bash
# Test connection
make mysql

# Check MySQL logs
make logs-mysql

# Verify credentials in .env
```

### Performance Issues

```bash
# Check resource usage
docker stats

# Review slow queries
docker-compose exec mysql mysql -u root -p -e "SHOW FULL PROCESSLIST;"

# Clear caches
make cache-clear
```

## Support

For deployment issues:
- Check application logs
- Review Docker logs
- Consult Laravel documentation
- Review server resources

