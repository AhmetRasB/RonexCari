# 🚀 Ronex Cari System - Hosting Deployment Guide

Bu rehber, Ronex Cari sistemini paylaşımlı Linux hosting'e yüklemek için hazırlanmıştır.

## 📋 Hosting Gereksinimleri

### Minimum Gereksinimler
- **PHP**: 8.1 veya üzeri
- **MySQL**: 5.7 veya üzeri (veya MariaDB 10.2+)
- **Disk Alanı**: En az 500MB
- **RAM**: En az 256MB
- **Bandwidth**: Sınırsız (önerilen)

### Gerekli PHP Eklentileri
- `curl` - HTTP istekleri için
- `json` - JSON veri işleme için
- `openssl` - HTTPS bağlantıları için
- `mbstring` - Çok baytlı string desteği
- `fileinfo` - Dosya bilgisi desteği
- `gd` - Resim işleme için

## 🔧 Kurulum Adımları

### 1. Dosyaları Yükleme
```bash
# Tüm dosyaları public_html dizinine yükleyin
# Dosya yapısı:
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          # Bu dizin web root olmalı
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
└── composer.json
```

### 2. Veritabanı Oluşturma
1. Hosting panelinden MySQL veritabanı oluşturun
2. Veritabanı kullanıcısı oluşturun ve yetkileri verin
3. `.env` dosyasını düzenleyin:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 3. Environment Ayarları
`hosting.env.example` dosyasını `.env` olarak kopyalayın ve düzenleyin:

```bash
cp hosting.env.example .env
```

### 4. Composer Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 5. Laravel Optimizasyonu
```bash
# Uygulama anahtarı oluştur
php artisan key:generate

# Cache'leri temizle
php artisan optimize:clear

# Production için optimize et
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Veritabanı Kurulumu
```bash
# Migration'ları çalıştır
php artisan migrate --force

# Seed'leri çalıştır
php artisan db:seed --force
```

### 7. Dosya İzinleri
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public
```

## 🌐 Domain Ayarları

### Web Root Ayarlama
Domain'inizi `public` dizinine yönlendirin:

**cPanel File Manager ile:**
1. `public` dizinindeki tüm dosyaları bir üst dizine taşıyın
2. `public` dizinini silin
3. Domain'i ana dizine yönlendirin

**Alternatif (Subdomain ile):**
1. `public` dizinini `public_html` olarak yeniden adlandırın
2. Diğer dosyaları bir üst dizine taşıyın

## 🔍 Sorun Giderme

### Hosting Ortamını Test Etme
```bash
php artisan hosting:diagnose
```

### API Test Etme
```bash
php artisan hosting:diagnose --test-api
```

### Log Kontrolü
```bash
tail -f storage/logs/laravel.log
```

## ⚠️ Yaygın Sorunlar ve Çözümleri

### 1. "Class not found" Hatası
```bash
composer dump-autoload
php artisan config:clear
```

### 2. "Permission denied" Hatası
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 3. "Database connection failed" Hatası
- `.env` dosyasındaki veritabanı bilgilerini kontrol edin
- Hosting sağlayıcınızın veritabanı sunucusu adresini kullanın

### 4. "Currency API not working" Hatası
- `php artisan hosting:diagnose --test-api` komutunu çalıştırın
- Manuel döviz kuru girişi kullanın

### 5. "Memory limit exceeded" Hatası
`.env` dosyasına ekleyin:
```env
MEMORY_LIMIT=256M
```

### 6. "SSL certificate verify failed" Hatası
`.env` dosyasına ekleyin:
```env
HTTP_VERIFY_SSL=false
```

## 🚀 Performans Optimizasyonu

### 1. Cache Ayarları
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Composer Optimizasyonu
```bash
composer install --optimize-autoloader --no-dev
```

### 3. .htaccess Optimizasyonu
`public/.htaccess` dosyası otomatik oluşturulur ve optimize edilir.

## 📊 Monitoring ve Bakım

### 1. Log Monitoring
```bash
# Hata loglarını takip et
tail -f storage/logs/laravel.log

# Currency API loglarını takip et
grep "Currency" storage/logs/laravel.log
```

### 2. Cache Temizleme
```bash
# Tüm cache'leri temizle
php artisan optimize:clear

# Sadece config cache'i temizle
php artisan config:clear
```

### 3. Database Backup
```bash
# Veritabanı yedeği al
mysqldump -u username -p database_name > backup.sql
```

## 🔒 Güvenlik

### 1. .env Dosyası Güvenliği
- `.env` dosyasının web'den erişilemediğinden emin olun
- Production'da `APP_DEBUG=false` olarak ayarlayın

### 2. Dosya İzinleri
```bash
chmod 644 .env
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 3. SSL Sertifikası
- HTTPS kullanımını zorunlu hale getirin
- SSL sertifikasını aktifleştirin

## 📞 Destek

### Hosting Sağlayıcısı Desteği
- PHP versiyonu güncellemesi
- Eklenti kurulumu
- Veritabanı oluşturma
- Domain yönlendirme

### Uygulama Desteği
- Log dosyalarını kontrol edin
- `php artisan hosting:diagnose` komutunu çalıştırın
- Hata mesajlarını kaydedin

## 🎯 Başarılı Kurulum Kontrolü

Kurulum başarılı olduğunda şunları görebilmelisiniz:

1. ✅ Ana sayfa yüklenir
2. ✅ Giriş yapabilirsiniz
3. ✅ Dashboard görünür
4. ✅ Döviz kurları çalışır (veya manuel giriş)
5. ✅ Fatura oluşturabilirsiniz
6. ✅ Raporlar çalışır

## 🚀 Otomatik Deployment

Deployment script'ini kullanmak için:

```bash
chmod +x deploy-hosting.sh
./deploy-hosting.sh
```

Bu script tüm kurulum adımlarını otomatik olarak gerçekleştirir.

---

**🎉 Başarılı kurulum! Ronex Cari sisteminiz artık canlıda!**
