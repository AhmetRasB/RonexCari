# Ronex Barkod Yazdırma - Veritabanı Dokümantasyonu

Bu dokümantasyon, harici bir masaüstü uygulaması (ör. Devexpress) ile Ronex veritabanından barkod bilgilerini çekip yazdırmak için gerekli tablo ve kolon bilgilerini içerir.

---

## 📋 Genel Bakış

Ronex sisteminde 4 ana ürün tipi vardır:
1. **Tek Ürünler** (`products` tablosu)
2. **Ürün Renk Varyantları** (`product_color_variants` tablosu)
3. **Seri Ürünler** (`product_series` tablosu)
4. **Seri Renk Varyantları** (`product_series_color_variants` tablosu)

Her ürün tipinin kendine özgü barkod yapısı vardır.

---

## 🗄️ Veritabanı Tabloları ve Kolonlar

### 1. `products` Tablosu (Tek Ürünler)

**Ana Kolonlar:**
```sql
SELECT 
    id,
    account_id,
    name,                    -- Ürün adı
    sku,                     -- SKU kodu
    barcode,                 -- Ana barkod (nullable)
    permanent_barcode,       -- Kalıcı barkod (nullable, alternatif)
    qr_code_value,          -- QR kod değeri (nullable)
    category,               -- Kategori
    brand,                  -- Marka
    size,                   -- Beden
    color,                  -- Renk (eğer renk varyantı yoksa)
    price,                  -- Fiyat
    cost,                   -- Maliyet
    stock_quantity,         -- Stok miktarı (renk varyantı yoksa)
    image,                  -- Görsel yolu
    is_active               -- Aktif mi? (boolean)
FROM products
WHERE is_active = 1
```

**Barkod Formatı:**
- Öncelik sırası: `barcode` → `permanent_barcode` → `sku` → `'P' + id` (fallback)
- Örnek: `GF01`, `PRD-00000123`, `SKU123`

**Kullanım Senaryosu:**
- Eğer ürünün **renk varyantı yoksa**, direkt `products` tablosundan barkod alınır.
- Eğer ürünün **renk varyantı varsa**, `product_color_variants` tablosundan her renk için ayrı barkod alınır.

---

### 2. `product_color_variants` Tablosu (Ürün Renk Varyantları)

**Ana Kolonlar:**
```sql
SELECT 
    id,
    product_id,             -- Hangi ürüne ait (FK -> products.id)
    color,                  -- Renk adı (örn: "Kırmızı", "Mavi")
    color_code,             -- Renk kodu (opsiyonel)
    barcode,                -- Renk varyantı barkodu (UNIQUE, nullable)
    qr_code_value,         -- QR kod değeri (nullable)
    stock_quantity,         -- Bu renk için stok miktarı
    critical_stock,         -- Kritik stok seviyesi
    image,                  -- Renk görseli
    is_active               -- Aktif mi? (boolean)
FROM product_color_variants
WHERE is_active = 1
```

**Barkod Formatı:**
- Format: **Base Code + Incremental Number**
- Örnek: Ürün barkodu `GF01` ise → `GF011`, `GF012`, `GF013`...
- Base code: Ana ürünün `barcode` veya `permanent_barcode` değeri
- Incremental: Her renk varyantı için sırayla artan sayı (1, 2, 3...)

**Önemli Notlar:**
- `barcode` kolonu **UNIQUE** constraint'e sahiptir.
- Eğer `barcode` NULL ise, sistem otomatik olarak base code + incremental formatında üretir.
- QR kod değeri genellikle ürün detay sayfası URL'idir.

**İlişki:**
```sql
-- Ürün ve renk varyantlarını birlikte çekmek için:
SELECT 
    p.id AS product_id,
    p.name AS product_name,
    p.barcode AS product_base_barcode,
    pcv.id AS variant_id,
    pcv.color,
    pcv.barcode AS variant_barcode,
    pcv.qr_code_value,
    pcv.stock_quantity
FROM products p
LEFT JOIN product_color_variants pcv ON p.id = pcv.product_id AND pcv.is_active = 1
WHERE p.is_active = 1
```

---

### 3. `product_series` Tablosu (Seri Ürünler)

**Ana Kolonlar:**
```sql
SELECT 
    id,
    account_id,
    name,                   -- Seri adı
    sku,                    -- SKU kodu
    barcode,                -- Seri base barkodu (nullable)
    category,               -- Kategori
    brand,                  -- Marka
    price,                  -- Fiyat
    cost,                   -- Maliyet
    series_type,            -- Seri tipi
    series_size,            -- Seri boyutu (kaçlı paket)
    stock_quantity,         -- Toplam stok (renk varyantlarından hesaplanır)
    image,                  -- Görsel yolu
    is_active               -- Aktif mi? (boolean)
FROM product_series
WHERE is_active = 1
```

**Barkod Formatı:**
- Base code: `barcode` → `sku` → `'S' + id` (fallback)
- Örnek: `K001`, `SERIES-123`, `S0001`

**Kullanım Senaryosu:**
- Seri ürünler genellikle **dış ambalaj** için base barkod kullanır.
- Her **renk varyantı** için ayrı barkod `product_series_color_variants` tablosunda saklanır.

---

### 4. `product_series_color_variants` Tablosu (Seri Renk Varyantları)

**Ana Kolonlar:**
```sql
SELECT 
    id,
    product_series_id,      -- Hangi seriye ait (FK -> product_series.id)
    color,                   -- Renk adı
    barcode,                 -- Renk varyantı barkodu (UNIQUE, nullable)
    qr_code_value,          -- QR kod değeri (nullable)
    stock_quantity,          -- Bu renk için stok miktarı
    critical_stock,          -- Kritik stok seviyesi
    is_active                -- Aktif mi? (boolean)
FROM product_series_color_variants
WHERE is_active = 1
```

**Barkod Formatı:**
- Format: **Base Code + Incremental Number**
- Örnek: Seri barkodu `K001` ise → `K0011`, `K0012`, `K0013`...
- Base code: Ana serinin `barcode` değeri
- Incremental: Her renk varyantı için sırayla artan sayı (1, 2, 3...)

**Önemli Notlar:**
- `barcode` kolonu **UNIQUE** constraint'e sahiptir.
- Eğer `barcode` NULL ise veya `SV0000xx` formatında ise, sistem otomatik normalize eder.
- QR kod değeri genellikle renk varyantı detay sayfası URL'idir.

**İlişki:**
```sql
-- Seri ve renk varyantlarını birlikte çekmek için:
SELECT 
    ps.id AS series_id,
    ps.name AS series_name,
    ps.barcode AS series_base_barcode,
    pscv.id AS variant_id,
    pscv.color,
    pscv.barcode AS variant_barcode,
    pscv.qr_code_value,
    pscv.stock_quantity
FROM product_series ps
LEFT JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id AND pscv.is_active = 1
WHERE ps.is_active = 1
```

---

### 5. `product_series_items` Tablosu (Seri Bedenleri - Opsiyonel)

**Ana Kolonlar:**
```sql
SELECT 
    id,
    product_series_id,      -- Hangi seriye ait (FK -> product_series.id)
    size,                    -- Beden (örn: "S", "M", "L", "XL")
    quantity_per_series      -- Seri başına miktar
FROM product_series_items
```

**Kullanım Senaryosu:**
- Bu tablo sadece **beden bilgisi** için kullanılır.
- Barkod yazdırma için **doğrudan kullanılmaz**, ancak etiket üzerinde beden bilgisi gösterilebilir.

---

## 🔍 Barkod Listesi Çekme Sorguları

### ✅ getAllProducts - Tüm Ürünleri Çeken Ana Sorgu (İç İçe Yapılandırılmış)

**Bu sorgu tüm aktif ürünleri, renk varyantlarını, serileri ve seri varyantlarını tek bir sonuç setinde döndürür. Normal tablolarla birleşik, iç içe sorgular kullanılmıştır.**

```sql
-- getAllProducts: Tüm aktif ürünler ve varyantları (iç içe sorgular)
SELECT 
    type,
    product_id,
    variant_id,
    name,
    sku,
    category,
    brand,
    size,
    color,
    barcode,
    qr_code_value,
    price,
    cost,
    stock,
    critical_stock,
    image,
    description,
    unit,
    is_active,
    series_size
FROM (
    -- Tek ürünler (renk varyantı olmayanlar)
    SELECT 
        'product' AS type,
        p.id AS product_id,
        NULL AS variant_id,
        p.name AS name,
        p.sku,
        p.category,
        p.brand,
        p.size,
        p.color,
        COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)) AS barcode,
        p.qr_code_value,
        p.price,
        p.cost,
        p.stock_quantity AS stock,
        p.critical_stock,
        p.image,
        p.description,
        p.unit,
        p.is_active,
        NULL AS series_size
    FROM products p
    WHERE p.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM product_color_variants pcv 
          WHERE pcv.product_id = p.id AND pcv.is_active = 1
      )

    UNION ALL

    -- Ürün renk varyantları
    SELECT 
        'product_variant' AS type,
        p.id AS product_id,
        pcv.id AS variant_id,
        CONCAT(p.name, ' - ', pcv.color) AS name,
        p.sku,
        p.category,
        p.brand,
        p.size,
        pcv.color,
        COALESCE(
            pcv.barcode, 
            CONCAT(
                COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)), 
                pcv.id
            )
        ) AS barcode,
        pcv.qr_code_value,
        p.price,
        p.cost,
        pcv.stock_quantity AS stock,
        pcv.critical_stock,
        COALESCE(pcv.image, p.image) AS image,
        p.description,
        p.unit,
        pcv.is_active,
        NULL AS series_size
    FROM products p
    INNER JOIN product_color_variants pcv ON p.id = pcv.product_id
    WHERE p.is_active = 1
      AND pcv.is_active = 1

    UNION ALL

    -- Seri ürünler (dış ambalaj - renk varyantı olmayanlar)
    SELECT 
        'series_outer' AS type,
        ps.id AS product_id,
        NULL AS variant_id,
        ps.name AS name,
        ps.sku,
        ps.category,
        ps.brand,
        NULL AS size,
        NULL AS color,
        COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)) AS barcode,
        NULL AS qr_code_value,
        ps.price,
        ps.cost,
        ps.stock_quantity AS stock,
        ps.critical_stock,
        ps.image,
        ps.description,
        NULL AS unit,
        ps.is_active,
        ps.series_size
    FROM product_series ps
    WHERE ps.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM product_series_color_variants pscv 
          WHERE pscv.product_series_id = ps.id AND pscv.is_active = 1
      )

    UNION ALL

    -- Seri renk varyantları (renk x beden kombinasyonları - her biri ayrı satır)
    SELECT 
        'series_variant' AS type,
        ps.id AS product_id,
        pscv.id AS variant_id,
        CONCAT(ps.name, ' - ', pscv.color, ' - ', psi.size) AS name,
        ps.sku,
        ps.category,
        ps.brand,
        psi.size,
        pscv.color,
        COALESCE(
            pscv.barcode, 
            CONCAT(
                COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), 
                pscv.id
            )
        ) AS barcode,
        pscv.qr_code_value,
        ps.price,
        ps.cost,
        pscv.stock_quantity AS stock,
        pscv.critical_stock,
        ps.image,
        ps.description,
        NULL AS unit,
        pscv.is_active,
        ps.series_size
    FROM product_series ps
    INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
    INNER JOIN product_series_items psi ON ps.id = psi.product_series_id
    WHERE ps.is_active = 1
      AND pscv.is_active = 1

    UNION ALL

    -- Seri renk varyantları (eğer beden yoksa, sadece renk)
    SELECT 
        'series_variant' AS type,
        ps.id AS product_id,
        pscv.id AS variant_id,
        CONCAT(ps.name, ' - ', pscv.color) AS name,
        ps.sku,
        ps.category,
        ps.brand,
        NULL AS size,
        pscv.color,
        COALESCE(
            pscv.barcode, 
            CONCAT(
                COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), 
                pscv.id
            )
        ) AS barcode,
        pscv.qr_code_value,
        ps.price,
        ps.cost,
        pscv.stock_quantity AS stock,
        pscv.critical_stock,
        ps.image,
        ps.description,
        NULL AS unit,
        pscv.is_active,
        ps.series_size
    FROM product_series ps
    INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
    WHERE ps.is_active = 1
      AND pscv.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM product_series_items psi 
          WHERE psi.product_series_id = ps.id
      )
) AS all_products
WHERE barcode IS NOT NULL AND barcode != ''
ORDER BY type, product_id, variant_id, size;
```

**Sorgu Sonuç Kolonları:**
- `type`: `'product'`, `'product_variant'`, `'series_outer'`, `'series_variant'`
- `product_id`: Ana ürün/seri ID'si
- `variant_id`: Renk varyantı ID'si (NULL ise varyant yok)
- `name`: Ürün adı (varyantlarda "Ürün Adı - Renk" formatında)
- `sku`: SKU kodu
- `category`: Kategori
- `brand`: Marka
- `size`: Beden (serilerde beden varsa dolu, yoksa NULL)
- `color`: Renk
- `barcode`: Barkod kodu (her zaman dolu)
- `qr_code_value`: QR kod değeri
- `price`: Satış fiyatı
- `cost`: Maliyet
- `stock`: Stok miktarı
- `critical_stock`: Kritik stok seviyesi
- `image`: Görsel yolu
- `description`: Açıklama
- `unit`: Birim
- `is_active`: Aktif mi?
- `series_size`: Seri boyutu (sadece serilerde)

---

### Senaryo 1: Tüm Aktif Ürünler ve Renk Varyantları (Basit Versiyon)

```sql
-- Tek ürünler (renk varyantı olmayanlar)
SELECT 
    'product' AS type,
    p.id AS product_id,
    NULL AS variant_id,
    p.name AS name,
    p.category,
    p.brand,
    p.size,
    p.color,
    COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)) AS barcode,
    p.qr_code_value,
    p.stock_quantity AS stock,
    p.image
FROM products p
LEFT JOIN product_color_variants pcv ON p.id = pcv.product_id AND pcv.is_active = 1
WHERE p.is_active = 1
  AND pcv.id IS NULL  -- Renk varyantı olmayanlar

UNION ALL

-- Ürün renk varyantları
SELECT 
    'product_variant' AS type,
    p.id AS product_id,
    pcv.id AS variant_id,
    CONCAT(p.name, ' - ', pcv.color) AS name,
    p.category,
    p.brand,
    p.size,
    pcv.color,
    COALESCE(pcv.barcode, CONCAT(COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)), pcv.id)) AS barcode,
    pcv.qr_code_value,
    pcv.stock_quantity AS stock,
    COALESCE(pcv.image, p.image) AS image
FROM products p
INNER JOIN product_color_variants pcv ON p.id = pcv.product_id
WHERE p.is_active = 1
  AND pcv.is_active = 1

UNION ALL

-- Seri ürünler (dış ambalaj - renk varyantı olmayanlar)
SELECT 
    'series_outer' AS type,
    ps.id AS product_id,
    NULL AS variant_id,
    ps.name AS name,
    ps.category,
    ps.brand,
    NULL AS size,
    NULL AS color,
    COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)) AS barcode,
    NULL AS qr_code_value,
    ps.stock_quantity AS stock,
    ps.image
FROM product_series ps
LEFT JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id AND pscv.is_active = 1
WHERE ps.is_active = 1
  AND pscv.id IS NULL  -- Renk varyantı olmayanlar

UNION ALL

-- Seri renk varyantları
SELECT 
    'series_variant' AS type,
    ps.id AS product_id,
    pscv.id AS variant_id,
    CONCAT(ps.name, ' - ', pscv.color) AS name,
    ps.category,
    ps.brand,
    NULL AS size,
    pscv.color,
    COALESCE(pscv.barcode, CONCAT(COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), pscv.id)) AS barcode,
    pscv.qr_code_value,
    pscv.stock_quantity AS stock,
    ps.image
FROM product_series ps
INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
WHERE ps.is_active = 1
  AND pscv.is_active = 1

ORDER BY type, product_id, variant_id;
```

---

### Senaryo 2: Belirli Bir Ürün/Seri için Tüm Barkodlar

```sql
-- Ürün ID'si ile (örnek: product_id = 123)
SELECT 
    'product' AS type,
    p.id AS product_id,
    NULL AS variant_id,
    p.name,
    p.category,
    p.brand,
    p.size,
    p.color,
    COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)) AS barcode,
    p.qr_code_value,
    p.stock_quantity AS stock
FROM products p
WHERE p.id = 123
  AND p.is_active = 1
  AND NOT EXISTS (
      SELECT 1 FROM product_color_variants pcv 
      WHERE pcv.product_id = p.id AND pcv.is_active = 1
  )

UNION ALL

SELECT 
    'product_variant' AS type,
    p.id AS product_id,
    pcv.id AS variant_id,
    CONCAT(p.name, ' - ', pcv.color) AS name,
    p.category,
    p.brand,
    p.size,
    pcv.color,
    COALESCE(pcv.barcode, CONCAT(COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)), pcv.id)) AS barcode,
    pcv.qr_code_value,
    pcv.stock_quantity AS stock
FROM products p
INNER JOIN product_color_variants pcv ON p.id = pcv.product_id
WHERE p.id = 123
  AND p.is_active = 1
  AND pcv.is_active = 1;
```

---

### Senaryo 3: Barkod Kodu ile Arama

```sql
-- Barkod kodu ile ürün/varyant bulma
SELECT 
    'product' AS type,
    p.id AS product_id,
    NULL AS variant_id,
    p.name,
    p.barcode,
    NULL AS variant_barcode
FROM products p
WHERE p.barcode = 'GF01' OR p.permanent_barcode = 'GF01'

UNION ALL

SELECT 
    'product_variant' AS type,
    p.id AS product_id,
    pcv.id AS variant_id,
    CONCAT(p.name, ' - ', pcv.color) AS name,
    p.barcode AS base_barcode,
    pcv.barcode AS variant_barcode
FROM products p
INNER JOIN product_color_variants pcv ON p.id = pcv.product_id
WHERE pcv.barcode = 'GF011'

UNION ALL

SELECT 
    'series' AS type,
    ps.id AS product_id,
    NULL AS variant_id,
    ps.name,
    ps.barcode,
    NULL AS variant_barcode
FROM product_series ps
WHERE ps.barcode = 'K001'

UNION ALL

SELECT 
    'series_variant' AS type,
    ps.id AS product_id,
    pscv.id AS variant_id,
    CONCAT(ps.name, ' - ', pscv.color) AS name,
    ps.barcode AS base_barcode,
    pscv.barcode AS variant_barcode
FROM product_series ps
INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
WHERE pscv.barcode = 'K0011';
```

---

## 📊 Örnek Veri Yapısı

### Örnek 1: Tek Ürün (Renk Varyantı Yok)

```
products tablosu:
- id: 1
- name: "Klasik Gömlek"
- barcode: "GF01"
- stock_quantity: 50
- color: NULL (renk varyantı yok)

→ Yazdırılacak Barkod: "GF01"
```

### Örnek 2: Ürün + Renk Varyantları

```
products tablosu:
- id: 2
- name: "Polo Yaka T-Shirt"
- barcode: "PT01"
- stock_quantity: 0 (renk varyantlarından hesaplanır)

product_color_variants tablosu:
- id: 10, product_id: 2, color: "Kırmızı", barcode: "PT011", stock_quantity: 20
- id: 11, product_id: 2, color: "Mavi", barcode: "PT012", stock_quantity: 15
- id: 12, product_id: 2, color: "Siyah", barcode: "PT013", stock_quantity: 25

→ Yazdırılacak Barkodlar: "PT011", "PT012", "PT013"
```

### Örnek 3: Seri Ürün (Dış Ambalaj)

```
product_series tablosu:
- id: 5
- name: "Çorap Serisi"
- barcode: "K001"
- series_size: 5
- stock_quantity: 100

→ Yazdırılacak Barkod: "K001" (dış ambalaj için)
```

### Örnek 4: Seri + Renk Varyantları

```
product_series tablosu:
- id: 6
- name: "Çorap Serisi Premium"
- barcode: "K002"
- series_size: 5

product_series_color_variants tablosu:
- id: 20, product_series_id: 6, color: "Beyaz", barcode: "K0021", stock_quantity: 30
- id: 21, product_series_id: 6, color: "Siyah", barcode: "K0022", stock_quantity: 25
- id: 22, product_series_id: 6, color: "Gri", barcode: "K0023", stock_quantity: 20

→ Yazdırılacak Barkodlar: "K0021", "K0022", "K0023"
```

---

## 🖨️ Yazdırma Önerileri

### 1. Barkod Formatı
- **Barkod Tipi:** Code128 (alphanumeric destekler)
- **QR Kod:** Eğer `qr_code_value` dolu ise, QR kod olarak yazdırılabilir

### 2. Etiket İçeriği Önerileri

**Tek Ürün Etiketi:**
```
[Kategori]
[Ürün Adı]
BEDEN: [Size]
BARKOD: [Barcode]
[Code128 Barcode]
[QR Code (opsiyonel)]
```

**Renk Varyantı Etiketi:**
```
[Kategori]
[Ürün Adı]
RENK: [Color]
BEDEN: [Size]
BARKOD: [Variant Barcode]
[Code128 Barcode]
[QR Code (opsiyonel)]
```

**Seri Dış Ambalaj Etiketi:**
```
[Kategori]
[Seri Adı]
SERİ: [Series Size]'li
BARKOD: [Series Barcode]
[Code128 Barcode]
```

**Seri Renk Varyantı Etiketi:**
```
[Kategori]
[Seri Adı]
RENK: [Color]
BARKOD: [Variant Barcode]
[Code128 Barcode]
[QR Code (opsiyonel)]
```

---

## 🔐 Veritabanı Bağlantı Bilgileri

**Not:** Bu bilgiler `.env` dosyasından alınmalıdır. Örnek:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ronex_cari
DB_USERNAME=root
DB_PASSWORD=
```

---

## ⚠️ Önemli Notlar

1. **Barkod Benzersizliği:**
   - `product_color_variants.barcode` ve `product_series_color_variants.barcode` kolonları **UNIQUE** constraint'e sahiptir.
   - Aynı barkod iki farklı varyantta olamaz.

2. **NULL Barkodlar:**
   - Eğer bir varyantın `barcode` değeri NULL ise, sistem otomatik olarak base code + incremental formatında üretir.
   - Ancak harici uygulamada bu durumu kontrol edip, NULL olanları atlamak veya fallback kullanmak gerekebilir.

3. **Stok Kontrolü:**
   - `stock_quantity` değerleri gerçek zamanlı stok bilgisini gösterir.
   - Yazdırma öncesi stok kontrolü yapılması önerilir.

4. **Aktif/Pasif Kontrolü:**
   - Sadece `is_active = 1` olan kayıtları çekmek önerilir.
   - Pasif ürünler genellikle yazdırılmaz.

5. **Account ID:**
   - Çoklu hesap (multi-tenant) yapısı varsa, `account_id` filtresi eklenmelidir.

---

## 📝 Örnek Devexpress C# Kodu (SQL Sorgusu)

```csharp
// DevExpress GridControl için örnek sorgu
string sql = @"
    SELECT 
        'product' AS type,
        p.id AS product_id,
        NULL AS variant_id,
        p.name AS name,
        p.category,
        p.brand,
        p.size,
        p.color,
        COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)) AS barcode,
        p.qr_code_value,
        p.stock_quantity AS stock,
        p.image
    FROM products p
    WHERE p.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM product_color_variants pcv 
          WHERE pcv.product_id = p.id AND pcv.is_active = 1
      )
    
    UNION ALL
    
    SELECT 
        'product_variant' AS type,
        p.id AS product_id,
        pcv.id AS variant_id,
        CONCAT(p.name, ' - ', pcv.color) AS name,
        p.category,
        p.brand,
        p.size,
        pcv.color,
        COALESCE(pcv.barcode, CONCAT(COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)), pcv.id)) AS barcode,
        pcv.qr_code_value,
        pcv.stock_quantity AS stock,
        COALESCE(pcv.image, p.image) AS image
    FROM products p
    INNER JOIN product_color_variants pcv ON p.id = pcv.product_id
    WHERE p.is_active = 1
      AND pcv.is_active = 1
    
    UNION ALL
    
    SELECT 
        'series_outer' AS type,
        ps.id AS product_id,
        NULL AS variant_id,
        ps.name AS name,
        ps.category,
        ps.brand,
        NULL AS size,
        NULL AS color,
        COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)) AS barcode,
        NULL AS qr_code_value,
        ps.stock_quantity AS stock,
        ps.image
    FROM product_series ps
    WHERE ps.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM product_series_color_variants pscv 
          WHERE pscv.product_series_id = ps.id AND pscv.is_active = 1
      )
    
    UNION ALL
    
    SELECT 
        'series_variant' AS type,
        ps.id AS product_id,
        pscv.id AS variant_id,
        CONCAT(ps.name, ' - ', pscv.color) AS name,
        ps.category,
        ps.brand,
        NULL AS size,
        pscv.color,
        COALESCE(pscv.barcode, CONCAT(COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), pscv.id)) AS barcode,
        pscv.qr_code_value,
        pscv.stock_quantity AS stock,
        ps.image
    FROM product_series ps
    INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
    WHERE ps.is_active = 1
      AND pscv.is_active = 1
    
    ORDER BY type, product_id, variant_id;
";

// DevExpress GridControl'a yükle
gridControl1.DataSource = ExecuteQuery(sql);
```

---

## 📞 Destek

Sorularınız için: [Ronex Destek Ekibi]

---

---

## 🎨 Devexpress Report Designer için Minimum Gerekli Alanlar

**Kullanıcı DataGrid'den seçip "Yazdır" butonuna bastığında, Report Designer'a şu alanlar yeterli:**

### ✅ ZORUNLU ALANLAR (Etiket için mutlaka gerekli)

```sql
SELECT 
    type,              -- 'product', 'product_variant', 'series_outer', 'series_variant'
    name,              -- Ürün adı (varyantlarda "Ürün Adı - Renk" formatında)
    barcode,           -- Barkod kodu (Code128 için ZORUNLU)
    category,          -- Kategori (etiket üstünde gösterilir)
    color,             -- Renk (NULL olabilir, varsa gösterilir)
    size,              -- Beden (NULL olabilir, varsa gösterilir)
    qr_code_value      -- QR kod değeri (NULL olabilir, varsa QR kod gösterilir)
FROM (getAllProducts sorgusu)
```

### 📋 OPSİYONEL ALANLAR (İsterseniz ekleyebilirsiniz)

```sql
SELECT 
    -- Yukarıdaki zorunlu alanlar +
    brand,             -- Marka (opsiyonel)
    sku,               -- SKU kodu (opsiyonel)
    price,             -- Fiyat (opsiyonel)
    stock,             -- Stok miktarı (opsiyonel)
    series_size,       -- Seri boyutu (sadece serilerde, opsiyonel)
    product_id,        -- Ürün ID'si (opsiyonel, log için)
    variant_id         -- Varyant ID'si (opsiyonel, log için)
```

---

## 🖨️ Devexpress Report Designer Kullanımı

### 1. DataSource Bağlantısı

**Minimum Sorgu (Sadece Etiket için Yeterli):**
```sql
SELECT 
    type,
    name,
    barcode,
    category,
    color,
    size,
    qr_code_value
FROM (
    -- getAllProducts sorgusunun tamamı buraya
    -- ... (yukarıdaki UNION ALL sorgusu)
) AS all_products
WHERE barcode IS NOT NULL  -- Barkod boş olanları filtrele
ORDER BY type, product_id, variant_id;
```

### 2. Report Designer'da Kullanılacak Alanlar

**Etiket Tasarımı için:**

| Alan Adı | Devexpress Field Name | Kullanım Amacı | Tip |
|----------|----------------------|----------------|-----|
| `type` | `[type]` | Hangi tip ürün olduğunu anlamak için | String |
| `name` | `[name]` | Etiket üzerinde ürün adı | String |
| `barcode` | `[barcode]` | **Code128 barkod oluşturma** (ZORUNLU) | String |
| `category` | `[category]` | Etiket üstünde kategori bilgisi | String |
| `color` | `[color]` | Renk bilgisi (NULL olabilir) | String (nullable) |
| `size` | `[size]` | Beden bilgisi (NULL olabilir) | String (nullable) |
| `qr_code_value` | `[qr_code_value]` | QR kod oluşturma (NULL olabilir) | String (nullable) |

### 3. Etiket Tasarım Örneği

**Devexpress Report Designer'da şu şekilde kullanın:**

```
┌─────────────────────────────┐
│ [category]                  │  ← XrLabel: [category]
│ [name]                      │  ← XrLabel: [name]
│                             │
│ RENK: [color]               │  ← XrLabel: "RENK: " + [color] (color NULL değilse)
│ BEDEN: [size]               │  ← XrLabel: "BEDEN: " + [size] (size NULL değilse)
│                             │
│ [barcode]                    │  ← XrLabel: [barcode] (küçük font)
│ ████████████████            │  ← XrBarCode: [barcode] (Code128)
│                             │
│ [QR Code]                   │  ← XrBarCode: [qr_code_value] (QR Code, NULL değilse)
└─────────────────────────────┘
```

### 4. Devexpress XrBarCode Kontrolü Ayarları

**Code128 Barkod için:**
- **Control Type:** `XrBarCode`
- **Symbology:** `Code128`
- **Data Field:** `[barcode]`
- **Show Text:** `true` (barkod altında kod gösterilsin)
- **AutoModule:** `true`

**QR Kod için:**
- **Control Type:** `XrBarCode`
- **Symbology:** `QRCode`
- **Data Field:** `[qr_code_value]`
- **Show Text:** `false`
- **Error Correction Level:** `M` veya `L`

### 5. Koşullu Gösterim (Conditional Formatting)

**Renk ve Beden alanları NULL olabilir, o zaman gizle:**

```csharp
// C# kodunda (Report Designer'da Expression Editor)
// Renk gösterimi:
[color] != null && [color] != "" ? "RENK: " + [color] : ""

// Beden gösterimi:
[size] != null && [size] != "" ? "BEDEN: " + [size] : ""

// QR kod gösterimi (sadece qr_code_value doluysa):
[qr_code_value] != null && [qr_code_value] != "" ? [qr_code_value] : null
```

---

## 📝 Özet: Devexpress'e Verilecek Minimum Sorgu

**Bu sorgu yeterli:**

```sql
SELECT 
    type,
    name,
    barcode,        -- ZORUNLU: Code128 için
    category,
    color,          -- NULL olabilir
    size,           -- NULL olabilir
    qr_code_value   -- NULL olabilir (QR kod için)
FROM (
    -- getAllProducts sorgusunun tamamı
    SELECT 
        'product' AS type,
        p.name AS name,
        COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)) AS barcode,
        p.category,
        p.color,
        p.size,
        p.qr_code_value
    FROM products p
    LEFT JOIN product_color_variants pcv ON p.id = pcv.product_id AND pcv.is_active = 1
    WHERE p.is_active = 1 AND pcv.id IS NULL
    
    UNION ALL
    
    SELECT 
        'product_variant' AS type,
        CONCAT(p.name, ' - ', pcv.color) AS name,
        COALESCE(pcv.barcode, CONCAT(COALESCE(p.barcode, p.permanent_barcode, p.sku, CONCAT('P', p.id)), pcv.id)) AS barcode,
        p.category,
        pcv.color,
        p.size,
        pcv.qr_code_value
    FROM products p
    INNER JOIN product_color_variants pcv ON p.id = pcv.product_id
    WHERE p.is_active = 1 AND pcv.is_active = 1
    
    UNION ALL
    
    SELECT 
        'series_outer' AS type,
        ps.name AS name,
        COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)) AS barcode,
        ps.category,
        NULL AS color,
        NULL AS size,
        NULL AS qr_code_value
    FROM product_series ps
    LEFT JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id AND pscv.is_active = 1
    WHERE ps.is_active = 1 AND pscv.id IS NULL
    
    UNION ALL
    
    -- Seri renk varyantları (renk x beden kombinasyonları - her biri ayrı satır)
    SELECT 
        'series_variant' AS type,
        CONCAT(ps.name, ' - ', pscv.color, ' - ', psi.size) AS name,
        COALESCE(pscv.barcode, CONCAT(COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), pscv.id)) AS barcode,
        ps.category,
        pscv.color,
        psi.size,
        pscv.qr_code_value
    FROM product_series ps
    INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
    INNER JOIN product_series_items psi ON ps.id = psi.product_series_id
    WHERE ps.is_active = 1 AND pscv.is_active = 1
    
    UNION ALL
    
    -- Seri renk varyantları (eğer beden yoksa, sadece renk)
    SELECT 
        'series_variant' AS type,
        CONCAT(ps.name, ' - ', pscv.color) AS name,
        COALESCE(pscv.barcode, CONCAT(COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), pscv.id)) AS barcode,
        ps.category,
        pscv.color,
        NULL AS size,
        pscv.qr_code_value
    FROM product_series ps
    INNER JOIN product_series_color_variants pscv ON ps.id = pscv.product_series_id
    WHERE ps.is_active = 1 
      AND pscv.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM product_series_items psi 
          WHERE psi.product_series_id = ps.id
      )
) AS all_products
WHERE barcode IS NOT NULL AND barcode != ''
ORDER BY type, name;
```

**Bu 7 alan ile etiket tasarımı yapabilirsiniz:**
1. ✅ `type` - Ürün tipi
2. ✅ `name` - Ürün adı
3. ✅ `barcode` - **Barkod kodu (ZORUNLU)**
4. ✅ `category` - Kategori
5. ✅ `color` - Renk (NULL olabilir)
6. ✅ `size` - Beden (NULL olabilir)
7. ✅ `qr_code_value` - QR kod (NULL olabilir)

---

---

### 🎁 getOuterLabels - Sadece Dış Ambalaj Etiketleri (JSON Formatında)

**Bu sorgu sadece dış ambalaj etiketleri için kullanılır. Tüm bilgiler JSON formatında tek satırda döndürülür.**

```sql
-- getOuterLabels: Sadece dış ambalaj etiketleri (JSON formatında)
SELECT 
    ps.id AS series_id,
    ps.name,
    ps.sku,
    ps.category,
    ps.brand,
    COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)) AS barcode,
    ps.price,
    ps.cost,
    ps.stock_quantity AS stock,
    ps.critical_stock,
    ps.image,
    ps.description,
    ps.series_size,
    ps.is_active,
    -- Tüm renkleri JSON array olarak
    (
        SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'id', pscv.id,
                'color', pscv.color,
                'barcode', COALESCE(pscv.barcode, CONCAT(COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), pscv.id)),
                'qr_code_value', pscv.qr_code_value,
                'stock_quantity', pscv.stock_quantity,
                'critical_stock', pscv.critical_stock,
                'is_active', pscv.is_active
            )
        )
        FROM product_series_color_variants pscv
        WHERE pscv.product_series_id = ps.id AND pscv.is_active = 1
    ) AS color_variants_json,
    -- Tüm bedenleri JSON array olarak
    (
        SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'id', psi.id,
                'size', psi.size,
                'quantity_per_series', psi.quantity_per_series
            )
        )
        FROM product_series_items psi
        WHERE psi.product_series_id = ps.id
    ) AS sizes_json,
    -- Tüm bilgileri tek bir JSON objesi olarak
    JSON_OBJECT(
        'series_id', ps.id,
        'name', ps.name,
        'sku', ps.sku,
        'category', ps.category,
        'brand', ps.brand,
        'barcode', COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)),
        'price', ps.price,
        'cost', ps.cost,
        'stock_quantity', ps.stock_quantity,
        'critical_stock', ps.critical_stock,
        'image', ps.image,
        'description', ps.description,
        'series_size', ps.series_size,
        'is_active', ps.is_active,
        'color_variants', (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'id', pscv.id,
                    'color', pscv.color,
                    'barcode', COALESCE(pscv.barcode, CONCAT(COALESCE(ps.barcode, ps.sku, CONCAT('S', ps.id)), pscv.id)),
                    'qr_code_value', pscv.qr_code_value,
                    'stock_quantity', pscv.stock_quantity,
                    'critical_stock', pscv.critical_stock,
                    'is_active', pscv.is_active
                )
            )
            FROM product_series_color_variants pscv
            WHERE pscv.product_series_id = ps.id AND pscv.is_active = 1
        ),
        'sizes', (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'id', psi.id,
                    'size', psi.size,
                    'quantity_per_series', psi.quantity_per_series
                )
            )
            FROM product_series_items psi
            WHERE psi.product_series_id = ps.id
        )
    ) AS all_data_json
FROM product_series ps
WHERE ps.is_active = 1
  AND (
      -- Sadece renk varyantı olmayanlar (dış ambalaj için)
      NOT EXISTS (
          SELECT 1 FROM product_series_color_variants pscv 
          WHERE pscv.product_series_id = ps.id AND pscv.is_active = 1
      )
      OR
      -- VEYA tüm serileri getir (opsiyonel - yorum satırını kaldırarak aktif edebilirsiniz)
      -- 1 = 1
  )
ORDER BY ps.id;
```

**Sorgu Sonuç Kolonları:**
- `series_id`: Seri ID'si
- `name`: Seri adı
- `sku`: SKU kodu
- `category`: Kategori
- `brand`: Marka
- `barcode`: Barkod kodu
- `price`: Satış fiyatı
- `cost`: Maliyet
- `stock`: Stok miktarı
- `critical_stock`: Kritik stok seviyesi
- `image`: Görsel yolu
- `description`: Açıklama
- `series_size`: Seri boyutu
- `is_active`: Aktif mi?
- `color_variants_json`: Tüm renk varyantları JSON array formatında
- `sizes_json`: Tüm bedenler JSON array formatında
- `all_data_json`: **Tüm bilgiler tek bir JSON objesi formatında (en önemli kolon)**

**Örnek JSON Çıktısı (`all_data_json` kolonu):**
```json
{
  "series_id": 5,
  "name": "Çorap Serisi Premium",
  "sku": "CS-PREM-001",
  "category": "Çorap",
  "brand": "Gucci",
  "barcode": "K001",
  "price": 150.00,
  "cost": 75.00,
  "stock_quantity": 100,
  "critical_stock": 10,
  "image": "uploads/series/5.jpg",
  "description": "Premium çorap serisi",
  "series_size": 5,
  "is_active": true,
  "color_variants": [
    {
      "id": 20,
      "color": "Beyaz",
      "barcode": "K0011",
      "qr_code_value": "https://ronex.com.tr/products/series/5/color/20",
      "stock_quantity": 30,
      "critical_stock": 5,
      "is_active": true
    },
    {
      "id": 21,
      "color": "Siyah",
      "barcode": "K0012",
      "qr_code_value": "https://ronex.com.tr/products/series/5/color/21",
      "stock_quantity": 25,
      "critical_stock": 5,
      "is_active": true
    }
  ],
  "sizes": [
    {
      "id": 10,
      "size": "X",
      "quantity_per_series": 1
    },
    {
      "id": 11,
      "size": "XL",
      "quantity_per_series": 1
    }
  ]
}
```

**Kullanım Senaryosu:**
- Devexpress'te dış ambalaj etiketi yazdırırken, `all_data_json` kolonunu parse edip tüm bilgilere erişebilirsiniz.
- Renk ve beden listelerini JSON array'lerden alıp etiket üzerinde gösterebilirsiniz.
- Tek satırda tüm seri bilgileri mevcut olduğu için performanslıdır.

---

**Son Güncelleme:** 2025-01-XX
**Versiyon:** 1.0

