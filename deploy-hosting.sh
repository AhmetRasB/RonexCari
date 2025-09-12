#!/bin/bash

# Ronex Cari System - Hosting Deployment Script
# This script helps deploy the application to shared hosting

echo "🚀 Ronex Cari System - Hosting Deployment"
echo "=========================================="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

echo "📋 Pre-deployment checks..."

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP Version: $PHP_VERSION"

# Check required extensions
echo "📋 Checking required extensions..."
REQUIRED_EXTENSIONS=("curl" "json" "openssl" "mbstring" "fileinfo" "gd")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        echo "✅ $ext extension found"
    else
        echo "❌ $ext extension missing"
    fi
done

echo ""
echo "🔧 Laravel optimization..."

# Install dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Clear and cache configurations
echo "🗑️ Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔐 Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public

# Create .htaccess for public directory if not exists
if [ ! -f "public/.htaccess" ]; then
    echo "📝 Creating .htaccess file..."
    cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Cache Static Assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
EOF
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Run database seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Test currency API
echo "🌐 Testing currency API..."
php artisan hosting:diagnose --test-api

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Update your .env file with correct database credentials"
echo "2. Point your domain to the 'public' directory"
echo "3. Test the application by visiting your domain"
echo "4. Check logs in storage/logs/ if you encounter any issues"
echo ""
echo "🔧 Useful commands:"
echo "- Test hosting environment: php artisan hosting:diagnose"
echo "- Clear all caches: php artisan optimize:clear"
echo "- View logs: tail -f storage/logs/laravel.log"
echo ""
echo "🎉 Happy hosting!"
