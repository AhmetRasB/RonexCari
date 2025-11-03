# 🧪 RONEXCARİ - KAPSAMLI TEST SENARYOSU

**Test Tarihi:** _________________  
**Test Eden:** _________________  
**Test Ortamı:** _________________  

Bu doküman, projenin TÜM işlevlerini test etmek için adım adım senaryolar içerir. Her test adımını sırayla uygulayın ve beklenen sonuçları kontrol edin.

## ⚠️ TEST ÖNCESİ HAZIRLIK

1. ✅ Temiz bir veritabanı ile başlayın (veya test verilerini temizleyin)
2. ✅ Admin kullanıcı ile giriş yapın
3. ✅ En az 1 aktif hesap olduğundan emin olun
4. ✅ Test sırasında tarayıcı konsolunu açık tutun (F12) - Hataları görmek için

---

## 📋 İçindekiler
1. [Giriş ve Hesap Yönetimi](#1-giriş-ve-hesap-yönetimi)
2. [Ürün ve Hizmet Yönetimi](#2-ürün-ve-hizmet-yönetimi)
3. [Müşteri Yönetimi](#3-müşteri-yönetimi)
4. [Satış İşlemleri (Faturalar)](#4-satış-işlemleri-faturalar)
5. [Değişim (Exchange) İşlemleri](#5-değişim-exchange-işlemleri)
6. [Tedarikçi Yönetimi](#6-tedarikçi-yönetimi)
7. [Alış Faturaları](#7-alış-faturaları)
8. [Tahsilat İşlemleri](#8-tahsilat-işlemleri)
9. [Tedarikçi Ödemeleri](#9-tedarikçi-ödemeleri)
10. [Gider Yönetimi](#10-gider-yönetimi)
11. [Çalışan Yönetimi](#11-çalışan-yönetimi)
12. [Barkod ve Etiket](#12-barkod-ve-etiket)
13. [Raporlar](#13-raporlar)
14. [Yönetim Paneli](#14-yönetim-paneli)

---

## 1. GİRİŞ VE HESAP YÖNETİMİ

### Test 1.1: Sisteme Giriş
1. Tarayıcıda `http://ronexcari.test` adresine git
2. Login sayfası açılmalı
3. Geçerli bir kullanıcı adı ve şifre ile giriş yap
4. **Beklenen Sonuç:** Dashboard sayfasına yönlendirilmeli

### Test 1.2: Hesap Seçimi
1. Eğer hesap seçimi ekranı açılırsa, bir hesap seç
2. Dashboard'a git
3. **Beklenen Sonuç:** Dashboard yüklenmeli, hesap bilgisi görünür olmalı

### Test 1.3: Hesap Değiştirme
1. Dashboard'da hesap değiştirme butonuna tıkla (varsa)
2. Farklı bir hesap seç
3. **Beklenen Sonuç:** Yeni hesaba geçiş yapılmalı, sayfa yenilenmeli

---

## 2. ÜRÜN VE HİZMET YÖNETİMİ

### Test 2.1: Ürün Kategorisi Oluşturma
1. Sol menüden "Ürünler" > "Kategoriler" sayfasına git
2. "Yeni Kategori" butonuna tıkla
3. Kategori adı: "Gömlek" gir
4. Kaydet
5. **Beklenen Sonuç:** Kategori listesinde "Gömlek" görünmeli, başarı mesajı gösterilmeli

### Test 2.2: Tek Ürün Oluşturma (Renk Varyantı Olmadan)
1. "Ürünler" > "Ürünler" sayfasına git
2. "Yeni Ürün" butonuna tıkla
3. **Sadece Zorunlu Alanları Doldur:**
   - Ad: "Test Gömlek"
   - Kategori: "Gömlek"
4. Diğer alanları boş bırak (fiyat, stok vb.)
5. Kaydet
6. **Beklenen Sonuç:** 
   - Validasyon hatası olmamalı (sadece ad ve kategori zorunlu)
   - Ürün oluşturulmalı
   - Ürün listesinde görünmeli

### Test 2.3: Ürün Oluşturma - Renk Varyantları ile
1. "Yeni Ürün" butonuna tıkla
2. **Ürün Bilgileri:**
   - Ad: "Renkli Gömlek"
   - Kategori: "Gömlek"
   - Beden: "M"
   - Birim Fiyat: 300 ₺
   - Başlangıç Stok: 10
3. **Renk Varyantları Ekle:**
   - Kırmızı: Stok 5, Kritik Stok 2
   - Mavi: Stok 5, Kritik Stok 2
4. Kaydet
5. **Beklenen Sonuç:**
   - Ürün oluşturulmalı
   - 2 renk varyantı oluşturulmalı
   - Ürün detay sayfasında renkler görünmeli

### Test 2.4: Ürün Stok Güncelleme (Quick Stock)
1. Ürünler listesinde "Renkli Gömlek" ürününü bul
2. Hızlı stok güncelleme butonuna tıkla (varsa)
3. Stok miktarını 15 yap
4. Kaydet
5. **Beklenen Sonuç:** Stok güncellenmiş olmalı

### Test 2.5: Ürün Düzenleme
1. "Renkli Gömlek" ürününe tıkla veya düzenle butonuna tıkla
2. Fiyatı 350 ₺ yap
3. Kaydet
4. **Beklenen Sonuç:** Fiyat güncellenmeli

### Test 2.6: Ürün Serisi Oluşturma
1. "Ürünler" > "Seriler" sayfasına git
2. "Yeni Seri" butonuna tıkla
3. **Seri Bilgileri:**
   - Ad: "Seri 2025"
   - Kategori: "Gömlek"
   - Seri Boyutu: 12'li
4. **Bedenler Ekle:**
   - S, M, L, XL
   - Her biri için miktar: 3
5. **Renkler Ekle:**
   - Kırmızı: Stok 10
   - Mavi: Stok 10
6. Kaydet
7. **Beklenen Sonuç:**
   - Seri oluşturulmalı
   - 4 beden oluşturulmalı
   - 2 renk varyantı oluşturulmalı

### Test 2.7: Seriye Beden Ekleme
1. Oluşturduğun seriyi aç
2. "Beden Ekle" butonuna tıkla
3. Beden: "XXL", Miktar: 2 gir
4. Kaydet
5. **Beklenen Sonuç:** Yeni beden eklenmeli, seri detayında görünmeli

### Test 2.8: Seri Stok Güncelleme
1. Seri detay sayfasında hızlı stok güncelleme yap
2. Bir renk varyantının stokunu 20 yap
3. **Beklenen Sonuç:** Stok güncellenmiş olmalı

### Test 2.9: Hizmet Oluşturma
1. "Hizmetler" sayfasına git
2. "Yeni Hizmet" butonuna tıkla
3. **Sadece Zorunlu Alan:**
   - Ad: "Montaj Hizmeti"
4. Diğer alanları boş bırak
5. Kaydet
6. **Beklenen Sonuç:** Hizmet oluşturulmalı (sadece ad zorunlu)

### Test 2.10: Ürün Silme (Single)
1. Ürünler listesinden bir ürün seç
2. Sil butonuna tıkla
3. Onayla
4. **Beklenen Sonuç:** Ürün silinmeli, listeden kaybolmalı

### Test 2.11: Toplu Ürün Silme
1. Ürünler listesinde birden fazla ürün seç (checkbox)
2. "Toplu Sil" butonuna tıkla
3. Onayla
4. **Beklenen Sonuç:** Seçilen ürünler silinmeli

### Test 2.12: Toplu Seri Silme
1. Seriler listesinde birden fazla seri seç
2. Toplu sil işlemini yap
3. **Beklenen Sonuç:** Seçilen seriler silinmeli

---

## 3. MÜŞTERI YÖNETİMİ

### Test 3.1: Yeni Müşteri Oluşturma (Minimal)
1. "Satışlar" > "Müşteriler" sayfasına git
2. "Yeni Müşteri" butonuna tıkla
3. **Sadece Zorunlu Alanları Doldur:**
   - Ad Soyad: "Ahmet Yılmaz"
   - Telefon: "02121234567"
4. Diğer tüm alanları boş bırak (email, şirket, adres vb.)
5. Kaydet
6. **Beklenen Sonuç:**
   - Müşteri oluşturulmalı
   - Validasyon hatası OLMAMALI
   - Müşteri listesinde görünmeli

### Test 3.2: Müşteri Oluşturma (Tüm Alanlarla)
1. Yeni müşteri formunu aç
2. **Tüm Alanları Doldur:**
   - Ad Soyad: "Mehmet Demir"
   - Şirket: "Demir A.Ş."
   - E-posta: "mehmet@demir.com"
   - Telefon: "05321234567"
   - Adres: "İstanbul"
   - Vergi No: "1234567890"
   - İletişim Kişisi: "Ahmet"
   - Notlar: "Önemli müşteri"
3. Kaydet
4. **Beklenen Sonuç:** Tüm bilgilerle müşteri oluşturulmalı

### Test 3.3: Müşteri Düzenleme
1. Oluşturduğun bir müşteriyi düzenle
2. Telefonu değiştir: "05551234567"
3. Kaydet
4. **Beklenen Sonuç:** Telefon güncellenmeli

### Test 3.4: Müşteri Detay Görüntüleme
1. Bir müşteriye tıkla
2. **Beklenen Sonuç:** 
   - Müşteri bilgileri görünmeli
   - Bakiye bilgileri görünmeli (TRY, USD, EUR)
   - Fatura listesi görünmeli

### Test 3.5: Müşteri Silme
1. Bir müşteriyi seç
2. Sil butonuna tıkla
3. Onayla
4. **Beklenen Sonuç:** Müşteri silinmeli

### Test 3.6: Toplu Müşteri Silme
1. Birden fazla müşteri seç
2. Toplu sil işlemi yap
3. **Beklenen Sonuç:** Seçilen müşteriler silinmeli

---

## 4. SATIŞ İŞLEMLERİ (FATURALAR)

### Test 4.1: Yeni Fatura Oluşturma - TRY Para Birimi
1. "Satışlar" > "Faturalar" sayfasına git
2. "Yeni Fatura" butonuna tıkla
3. **Fatura Bilgileri:**
   - Müşteri: "Ahmet Yılmaz" (arama yaparak seç)
   - Fatura Tarihi: Bugünün tarihi
   - Vade Tarihi: 30 gün sonra
   - Para Birimi: TRY
   - KDV Durumu: Dahil
4. **Kalem 1 Ekle:**
   - Ürün/Hizmet: Bir seri ürün seç (arama yaparak)
   - Miktar: 5
   - Birim Fiyat: 300 ₺ (otomatik gelmeli)
   - KDV: %20
   - İndirim: 0
   - Renk seç (varsa)
5. **Kalem 2 Ekle:**
   - Ürün/Hizmet: Bir hizmet seç
   - Miktar: 1
   - Birim Fiyat: 500 ₺
   - KDV: %18
6. Fatura Açıklaması: "Test fatura"
7. Kaydet
8. **Beklenen Sonuç:**
   - Fatura oluşturulmalı
   - Fatura numarası atanmalı (INV-2025-xxxxxx formatında)
   - Müşterinin TRY bakiyesi artmalı (fatura tutarı kadar)
   - Stoklar düşmeli (seri ürün için)
   - Fatura detay sayfasında tüm bilgiler görünmeli

### Test 4.2: Fatura Ödeme İşaretleme (Mark Paid)
1. Oluşturduğun faturayı aç
2. "Ödendi Olarak İşaretle" butonuna tıkla
3. **Beklenen Sonuç:**
   - Fatura durumu "Ödendi" olmalı
   - Müşterinin bakiyesi DEĞİŞMEMELİ (zaten ödenmiş)

### Test 4.3: Fatura Yazdırma
1. Fatura detay sayfasında "Yazdır" butonuna tıkla
2. **Beklenen Sonuç:** PDF formatında fatura yazdırılmalı veya yazdırma önizlemesi açılmalı

### Test 4.4: Fatura Önizleme
1. Fatura detay sayfasında "Önizleme" butonuna tıkla
2. **Beklenen Sonuç:** Fatura önizleme sayfası açılmalı

### Test 4.5: Yeni Fatura - USD Para Birimi
1. Yeni fatura oluştur
2. **Para Birimi:** USD seç
3. **Döviz Kuru:** Sistem otomatik kuru getirmeli (manuel girilebilir)
4. Kalemler ekle (TRY cinsinden ürünler seç)
5. Sistem otomatik USD'ye çevirmeli
6. **Beklenen Sonuç:**
   - Fatura USD cinsinden oluşturulmalı
   - Müşterinin USD bakiyesi artmalı
   - TRY karşılığı doğru hesaplanmalı

### Test 4.6: Yeni Fatura - EUR Para Birimi
1. Aynı testi EUR ile tekrarla
2. **Beklenen Sonuç:** EUR cinsinden fatura, EUR bakiyesi artmalı

### Test 4.7: Fatura Üzerine İade Ekleme
1. Oluşturduğun bir faturayı aç
2. "İade Ekle" veya benzeri butona tıkla
3. **İade Kalemi:**
   - Orijinal kalemden birini seç
   - İade Miktarı: 2 (orijinal miktarın bir kısmı)
4. Kaydet
5. **Beklenen Sonuç:**
   - İade kalemi eklenmeli
   - Fatura toplamı azalmalı (negatif kalem olarak)
   - Müşterinin bakiyesi azalmalı (iade tutarı kadar)
   - Stok geri artmalı (iade edilen ürün için)

### Test 4.8: Fatura Düzenleme
1. Bir faturayı düzenle
2. Bir kalemin miktarını değiştir
3. Yeni bir kalem ekle
4. Kaydet
5. **Beklenen Sonuç:**
   - Değişiklikler kaydedilmeli
   - Toplam yeniden hesaplanmalı
   - Müşteri bakiyesi güncellenmeli

### Test 4.9: Fatura Silme
1. Bir faturayı sil
2. **Beklenen Sonuç:**
   - Fatura silinmeli
   - Müşterinin bakiyesi geri dönmeli (fatura tutarı kadar azalmalı)
   - Stoklar geri artmalı

### Test 4.10: Toplu Fatura Silme
1. Birden fazla fatura seç
2. Toplu sil işlemi yap
3. **Beklenen Sonuç:** Seçilen faturalar silinmeli, bakiyeler düzeltilmeli

---

## 5. DEĞİŞİM (EXCHANGE) İŞLEMLERİ

### Test 5.1: Değişim Oluşturma - Ürün İadesi ve Yeni Ürün
1. Oluşturduğun bir faturayı aç (önceden kalemler eklenmiş olmalı)
2. "Değişim" butonuna tıkla
3. **Değişim Bilgileri:**
   - Orijinal kalemlerden birini seç (checkbox)
   - Değişim Miktarı: 2 (örneğin orijinal 5 ise, 2 tanesi değişilecek)
4. **Yeni Ürün Ekle:**
   - Farklı bir ürün seç
   - Miktar: 3
   - Birim Fiyat: 400 ₺
   - KDV: %20
5. Kaydet
6. **Beklenen Sonuç:**
   - Değişim oluşturulmalı
   - Orijinal faturada iade kalemi görünmeli
   - Orijinal faturada yeni ürün kalemi görünmeli
   - Müşteri bakiyesi DOĞRU hesaplanmalı:
     - Eski tutar: (2 adet x 300 ₺) = 600 ₺
     - Yeni tutar: (3 adet x 400 ₺) = 1200 ₺
     - Fark: 600 ₺ ek borç
     - Müşteri bakiyesi 600 ₺ artmalı

### Test 5.2: Değişim - Sadece İade (Yeni Ürün Yok)
1. Bir faturayı aç
2. Değişim yap
3. Orijinal kalemden 1 adet seç (iade)
4. YENİ ÜRÜN EKLEME
5. Kaydet
6. **Beklenen Sonuç:**
   - Sadece iade kalemi eklenmeli
   - Müşteri bakiyesi azalmalı (iade tutarı kadar)
   - Stok geri artmalı

### Test 5.3: Değişim - Farklı Para Birimi
1. TRY cinsinden bir faturaya değişim ekle
2. Yeni ürünü USD cinsinden ekle
3. **Beklenen Sonuç:**
   - Döviz kuru uygulanmalı
   - Hesaplama doğru yapılmalı
   - Müşteri bakiyesi doğru güncellenmeli

### Test 5.4: Değişim Validasyonu
1. Değişim sayfasında hiçbir kalem seçmeden kaydet
2. **Beklenen Sonuç:** Validasyon hatası: "En az bir kalem seçmelisiniz"

---

## 6. SATIŞ SİPARİŞLERİ

### Test 6.1: Satış Siparişi Oluşturma
1. "Satışlar" > "Siparişler" sayfasına git
2. "Yeni Sipariş" butonuna tıkla
3. **Sipariş Bilgileri:**
   - Müşteri: Bir müşteri seç
   - Sipariş Tarihi: Bugün
   - Teslimat Tarihi: 7 gün sonra
   - Para Birimi: TRY
4. **Kalem Ekle:**
   - Ürün: Bir ürün seç
   - Miktar: 5
   - Birim Fiyat: 200 ₺
5. Kaydet
6. **Beklenen Sonuç:** 
   - Sipariş oluşturulmalı
   - Sipariş listesinde görünmeli
   - **NOT:** Siparişler stok düşmez, sadece kayıt tutar

### Test 6.2: Satış Siparişi Düzenleme
1. Bir siparişi düzenle
2. Miktarı değiştir
3. Kaydet
4. **Beklenen Sonuç:** Sipariş güncellenmeli

### Test 6.3: Satış Siparişi Silme
1. Bir siparişi sil
2. **Beklenen Sonuç:** Sipariş silinmeli (stok etkilenmemeli)

---

## 7. SATIŞ TEKLİFLERİ

### Test 7.1: Teklif Oluşturma
1. "Satışlar" > "Teklifler" sayfasına git
2. "Yeni Teklif" butonuna tıkla
3. **Teklif Bilgileri:**
   - Müşteri: Bir müşteri seç
   - Teklif Tarihi: Bugün
   - Geçerlilik Tarihi: 30 gün sonra
   - Para Birimi: TRY
4. **Kalem Ekle:**
   - Ürün: Bir ürün seç
   - Miktar: 3
   - Birim Fiyat: 250 ₺
5. Kaydet
6. **Beklenen Sonuç:**
   - Teklif oluşturulmalı
   - Teklif listesinde görünmeli
   - **NOT:** Teklifler stok düşmez, sadece kayıt tutar

### Test 7.2: Teklif Düzenleme
1. Bir teklifi düzenle
2. Fiyatı değiştir
3. Kaydet
4. **Beklenen Sonuç:** Teklif güncellenmeli

### Test 7.3: Teklif Silme
1. Bir teklifi sil
2. **Beklenen Sonuç:** Teklif silinmeli

---

## 8. TEDARİKÇİ YÖNETİMİ

### Test 6.1: Yeni Tedarikçi Oluşturma (Minimal)
1. "Alışlar" > "Tedarikçiler" sayfasına git
2. "Yeni Tedarikçi" butonuna tıkla
3. **Sadece Zorunlu Alanları Doldur:**
   - Ad: "ABC Tedarik"
   - Telefon: "02121234568"
4. Diğer tüm alanları boş bırak (email, şirket vb.)
5. Kaydet
6. **Beklenen Sonuç:**
   - Tedarikçi oluşturulmalı
   - Validasyon hatası OLMAMALI
   - Tedarikçi listesinde görünmeli

### Test 6.2: Tedarikçi Oluşturma (Tüm Alanlarla)
1. Yeni tedarikçi formunu aç
2. **Tüm Alanları Doldur:**
   - Ad: "XYZ Tedarik A.Ş."
   - Şirket: "XYZ A.Ş."
   - E-posta: "info@xyz.com"
   - Telefon: "05321234568"
   - Adres: "Ankara"
   - Vergi No: "9876543210"
   - İletişim Kişisi: "Ali"
   - Notlar: "Düzenli tedarikçi"
3. Kaydet
4. **Beklenen Sonuç:** Tüm bilgilerle tedarikçi oluşturulmalı

### Test 6.3: Tedarikçi Düzenleme
1. Bir tedarikçiyi düzenle
2. Telefonu değiştir
3. Kaydet
4. **Beklenen Sonuç:** Telefon güncellenmeli

### Test 6.4: Tedarikçi Silme
1. Bir tedarikçiyi sil
2. **Beklenen Sonuç:** Tedarikçi silinmeli

### Test 6.5: Toplu Tedarikçi Silme
1. Birden fazla tedarikçi seç
2. Toplu sil
3. **Beklenen Sonuç:** Seçilen tedarikçiler silinmeli

---

## 9. ALIŞ FATURALARI

### Test 7.1: Alış Faturası Oluşturma
1. "Alışlar" > "Faturalar" sayfasına git
2. "Yeni Fatura" butonuna tıkla
3. **Fatura Bilgileri:**
   - Tedarikçi: Bir tedarikçi seç
   - Fatura Tarihi: Bugün
   - Vade Tarihi: 30 gün sonra
   - Para Birimi: TRY
   - KDV Durumu: Dahil
4. **Kalem Ekle:**
   - Ürün: Bir ürün seç
   - Miktar: 10
   - Birim Fiyat: 200 ₺
   - KDV: %20
5. Kaydet
6. **Beklenen Sonuç:**
   - Alış faturası oluşturulmalı
   - Stoklar artmalı (alış olduğu için)
   - Tedarikçi bakiyesi artmalı (borç)

### Test 7.2: Alış Faturası Yazdırma
1. Alış faturasını aç
2. Yazdır butonuna tıkla
3. **Beklenen Sonuç:** PDF yazdırılmalı

### Test 7.3: Alış Faturası Silme
1. Bir alış faturasını sil
2. **Beklenen Sonuç:**
   - Fatura silinmeli
   - Stoklar geri düşmeli
   - Tedarikçi bakiyesi azalmalı

---

## 10. ALIŞ SİPARİŞLERİ

### Test 10.1: Alış Siparişi Oluşturma
1. "Alışlar" > "Siparişler" sayfasına git
2. "Yeni Sipariş" butonuna tıkla
3. **Sipariş Bilgileri:**
   - Tedarikçi: Bir tedarikçi seç
   - Sipariş Tarihi: Bugün
   - Teslimat Tarihi: 10 gün sonra
   - Para Birimi: TRY
4. **Kalem Ekle:**
   - Ürün: Bir ürün seç
   - Miktar: 20
   - Birim Fiyat: 150 ₺
5. Kaydet
6. **Beklenen Sonuç:**
   - Alış siparişi oluşturulmalı
   - **NOT:** Siparişler stok artmaz, sadece kayıt tutar

---

## 11. İRSALİYELER

### Test 11.1: İrsaliye Oluşturma
1. "Alışlar" > "İrsaliyeler" sayfasına git
2. "Yeni İrsaliye" butonuna tıkla
3. **İrsaliye Bilgileri:**
   - Tedarikçi: Bir tedarikçi seç
   - İrsaliye Tarihi: Bugün
   - Araç Plakası: "34ABC123" (varsa)
4. **Kalem Ekle:**
   - Ürün: Bir ürün seç
   - Miktar: 15
5. Kaydet
6. **Beklenen Sonuç:**
   - İrsaliye oluşturulmalı
   - İrsaliye listesinde görünmeli

### Test 11.2: İrsaliye Düzenleme
1. Bir irsaliyeyi düzenle
2. Miktarı değiştir
3. Kaydet
4. **Beklenen Sonuç:** İrsaliye güncellenmeli

### Test 11.3: İrsaliye Silme
1. Bir irsaliyeyi sil
2. **Beklenen Sonuç:** İrsaliye silinmeli

---

## 12. TAHŞİLAT İŞLEMLERİ

### Test 8.1: Tahsilat Oluşturma - TRY
1. "Finans" > "Tahsilatlar" sayfasına git
2. "Yeni Tahsilat" butonuna tıkla
3. **Tahsilat Bilgileri:**
   - Müşteri: "Ahmet Yılmaz" (önceden oluşturduğun)
   - Tahsilat Tipi: "Nakit"
   - Tarih: Bugün
   - Tutar: 5000 ₺
   - Para Birimi: TRY
   - İndirim: 500 ₺
   - Açıklama: "Kısmi ödeme"
4. Kaydet
5. **Beklenen Sonuç:**
   - Tahsilat oluşturulmalı
   - Müşterinin TRY bakiyesi AZALMALI: 5000 + 500 = 5500 ₺ azalmalı
   - Tahsilat listesinde görünmeli

### Test 8.2: Tahsilat Oluşturma - USD
1. Yeni tahsilat oluştur
2. Para Birimi: USD
3. Tutar: 100 $
4. **Beklenen Sonuç:**
   - Müşterinin USD bakiyesi azalmalı
   - TRY bakiyesi değişmemeli

### Test 8.3: Tahsilat Düzenleme
1. Bir tahsilatı düzenle
2. Tutarı değiştir: 6000 ₺
3. Kaydet
4. **Beklenen Sonuç:**
   - Tahsilat güncellenmeli
   - Müşteri bakiyesi yeniden hesaplanmalı

### Test 8.4: Tahsilat Silme (Single)
1. Bir tahsilatı sil
2. **Beklenen Sonuç:**
   - Tahsilat silinmeli
   - Müşteri bakiyesi GERİ DÖNMELİ (tutar + indirim kadar artmalı)

### Test 8.5: Toplu Tahsilat Silme
1. Birden fazla tahsilat seç
2. Toplu sil
3. **Beklenen Sonuç:**
   - Seçilen tahsilatlar silinmeli
   - Her müşterinin bakiyesi düzeltilmeli

### Test 8.6: Tahsilat Yazdırma
1. Bir tahsilatı aç
2. Yazdır butonuna tıkla
3. **Beklenen Sonuç:** PDF yazdırılmalı

---

## 13. TEDARİKÇİ ÖDEMELERİ

### Test 9.1: Tedarikçi Ödemesi Oluşturma
1. "Finans" > "Tedarikçi Ödemeleri" sayfasına git
2. "Yeni Ödeme" butonuna tıkla
3. **Ödeme Bilgileri:**
   - Tedarikçi: Bir tedarikçi seç
   - Ödeme Tipi: "Havale"
   - Tarih: Bugün
   - Tutar: 3000 ₺
   - Para Birimi: TRY
   - İndirim: 300 ₺
4. Kaydet
5. **Beklenen Sonuç:**
   - Ödeme oluşturulmalı
   - Tedarikçinin TRY bakiyesi AZALMALI (tutar + indirim kadar)

### Test 9.2: Tedarikçi Ödemesi Silme
1. Bir ödemeyi sil
2. **Beklenen Sonuç:**
   - Ödeme silinmeli
   - Tedarikçi bakiyesi geri dönmeli

### Test 9.3: Toplu Tedarikçi Ödemesi Silme
1. Birden fazla ödeme seç
2. Toplu sil
3. **Beklenen Sonuç:** Seçilen ödemeler silinmeli

---

## 14. GİDER YÖNETİMİ

### Test 10.1: Gider Oluşturma
1. "Giderler" > "Giderler" sayfasına git
2. "Yeni Gider" butonuna tıkla
3. **Gider Bilgileri:**
   - Gider Adı: "Ofis Kirası"
   - Tutar: 5000 ₺
   - Tarih: Bugün
   - Kategori: "Kira"
   - Açıklama: "Aralık ayı kirası"
4. Kaydet
5. **Beklenen Sonuç:** Gider oluşturulmalı, listede görünmeli

### Test 10.2: Gider Düzenleme
1. Bir gideri düzenle
2. Tutarı değiştir
3. Kaydet
4. **Beklenen Sonuç:** Gider güncellenmeli

### Test 10.3: Gider Silme
1. Bir gideri sil
2. **Beklenen Sonuç:** Gider silinmeli

### Test 10.4: Toplu Gider Silme
1. Birden fazla gider seç
2. Toplu sil
3. **Beklenen Sonuç:** Seçilen giderler silinmeli

---

## 15. ÇALIŞAN YÖNETİMİ

### Test 11.1: Çalışan Oluşturma
1. "Giderler" > "Çalışanlar" sayfasına git
2. "Yeni Çalışan" butonuna tıkla
3. **Çalışan Bilgileri:**
   - Ad Soyad: "Fatma Kaya"
   - Telefon: "05331234567"
   - Maaş: 15000 ₺
   - Pozisyon: "Satış Temsilcisi"
   - Başlangıç Tarihi: Bugün
4. Kaydet
5. **Beklenen Sonuç:** Çalışan oluşturulmalı

### Test 11.2: Çalışan Maaş Ödemesi
1. Çalışan detay sayfasına git
2. "Maaş Öde" butonuna tıkla
3. **Ödeme Bilgileri:**
   - Ödeme Tarihi: Bugün
   - Ödeme Tutarı: 15000 ₺
4. Kaydet
5. **Beklenen Sonuç:**
   - Maaş ödemesi kaydedilmeli
   - Kalan maaş bilgisi güncellenmeli

### Test 11.3: Çalışan Düzenleme
1. Bir çalışanı düzenle
2. Maaşı değiştir
3. Kaydet
4. **Beklenen Sonuç:** Çalışan bilgileri güncellenmeli

---

## 16. BARKOD VE ETİKET

### Test 12.1: ZPL Etiket Oluşturma - Ürün
1. "Barkodlar" sayfasına git
2. **Etiket Bilgileri:**
   - Tür: "Ürün"
   - Öğe: Bir ürün seç
   - Adet: 5
3. "ZPL İndir" butonuna tıkla
4. **Beklenen Sonuç:** ZPL dosyası indirilmeli

### Test 12.2: ZPL Etiket - Seri Ürün
1. Tür: "Seri" seç
2. Öğe: Bir seri seç
3. Seri Modu: "FULL" (Dış + Bedenler)
4. Adet: 2
5. "ZPL İndir" butonuna tıkla
6. **Beklenen Sonuç:** Tüm etiketler için ZPL indirilmeli

### Test 12.3: QZ Tray ile Yazdırma
1. Bir ürün seç
2. "Hepsini Makineye Yazdır (ZPL)" butonuna tıkla
3. **Beklenen Sonuç:** QZ Tray açılırsa yazdırma işlemi başlamalı (QZ Tray kurulu olmalı)

### Test 12.4: Barkod Araması
1. Barkod arama sayfasını kullan (varsa)
2. Bir ürün barkodunu ara
3. **Beklenen Sonuç:** Ürün bulunmalı, detay sayfasına yönlendirilmeli

---

## 13. RAPORLAR

### Test 13.1: Rapor Sayfasına Erişim
1. "Raporlar" > "Rapor Al" sayfasına git
2. **Beklenen Sonuç:**
   - Dashboard görünmeli
   - Satış istatistikleri görünmeli
   - Gider istatistikleri görünmeli
   - En çok satan ürünler görünmeli
   - Döviz kurları görünmeli

### Test 13.2: Rapor Verilerini Kontrol
1. Rapor sayfasında şunları kontrol et:
   - Bugünkü satışlar
   - Bu haftaki satışlar
   - Bu ayki satışlar
   - Giderler
   - Kar/Zarar
2. **Beklenen Sonuç:** Tüm veriler doğru hesaplanmış olmalı

### Test 13.3: Admin İçin Şube İstatistikleri
1. Admin kullanıcı ile giriş yap
2. Raporlar sayfasına git
3. **Beklenen Sonuç:** Şube bazlı istatistikler görünmeli (varsa)

---

## 14. YÖNETİM PANELİ

### Test 14.1: Kullanıcı Listesi
1. "Yönetim" > "Kullanıcılar" sayfasına git
2. **Beklenen Sonuç:** Tüm kullanıcılar listelenmeli

### Test 14.2: Yeni Kullanıcı Oluşturma
1. "Yeni Kullanıcı" butonuna tıkla
2. Kullanıcı bilgilerini gir
3. Kaydet
4. **Beklenen Sonuç:** Kullanıcı oluşturulmalı

### Test 14.3: Çalışan Yönetimi (Yönetim)
1. "Yönetim" > "Çalışanlar" sayfasına git
2. Çalışan listesini kontrol et
3. **Beklenen Sonuç:** Çalışanlar listelenmeli

### Test 14.4: Maaş Ödemeleri Görüntüleme
1. Bir çalışanı seç
2. "Maaş Ödemeleri" sayfasına git
3. **Beklenen Sonuç:** Maaş ödemeleri listelenmeli

---

## 🔍 KRİTİK TEST SENARYOLARI (ÖNEMLİ!)

### Test K1: Müşteri Bakiyesi Doğruluğu
1. **Başlangıç:** Bir müşterinin başlangıç bakiyesini not et
2. Fatura kes: 10,000 ₺
3. **Kontrol:** Müşteri bakiyesi 10,000 ₺ artmalı
4. Tahsilat yap: 5,000 ₺ (indirim 500 ₺)
5. **Kontrol:** Müşteri bakiyesi 5,500 ₺ azalmalı (net: +4,500 ₺)
6. Değişim yap: -2,000 ₺ (iade fazla)
7. **Kontrol:** Bakiye doğru hesaplanmalı
8. **Sonuç:** Müşteri detay sayfasında bakiye = Fatura toplamı - Tahsilat toplamı olmalı

### Test K2: Stok Yönetimi Doğruluğu
1. Bir ürünün başlangıç stokunu not et (örnek: 50)
2. Satış faturası kes: 10 adet sat
3. **Kontrol:** Stok 40 olmalı
4. İade ekle: 2 adet iade
5. **Kontrol:** Stok 42 olmalı
6. Değişim yap: 3 adet çıkart, 5 adet ekle
7. **Kontrol:** Stok 44 olmalı (42 - 3 + 5)
8. Alış faturası: 20 adet al
9. **Kontrol:** Stok 64 olmalı

### Test K3: Çok Para Birimi İşlemleri
1. Müşteriye TRY fatura kes: 5,000 ₺
2. Müşteriye USD fatura kes: 100 $
3. Müşteriye EUR fatura kes: 50 €
4. **Kontrol:** 
   - TRY bakiyesi: +5,000 ₺
   - USD bakiyesi: +100 $
   - EUR bakiyesi: +50 €
   - Her biri ayrı ayrı doğru hesaplanmalı

### Test K4: Renk Varyantlı Ürün Stokları
1. Renk varyantlı bir ürün oluştur:
   - Kırmızı: Stok 10
   - Mavi: Stok 5
2. Satış faturası kes: Kırmızıdan 3 adet
3. **Kontrol:**
   - Kırmızı stok: 7 olmalı
   - Mavi stok: 5 (değişmemeli)
4. Değişim yap: Kırmızıdan 1 adet iade, Maviye 2 adet ekle
5. **Kontrol:**
   - Kırmızı stok: 8 olmalı
   - Mavi stok: 3 olmalı

### Test K5: Seri Ürün Stok Yönetimi
1. Seri ürün oluştur: 4 beden (S, M, L, XL), 2 renk (Kırmızı, Mavi)
2. Satış faturası kes: Kırmızı M beden, 5 adet
3. **Kontrol:** Kırmızı renk stok 5 azalmalı (beden stoku yok, seri stoku var)
4. Alış faturası: Seriye 10 adet ekle
5. **Kontrol:** Seri stoku artmalı

### Test K6: Fatura Toplam Hesaplamaları
1. Fatura oluştur:
   - Kalem 1: 10 adet x 100 ₺ = 1,000 ₺, KDV %20 dahil
   - Kalem 2: 5 adet x 200 ₺ = 1,000 ₺, KDV %18 dahil
2. **Kontrol:**
   - Ara Toplam: 2,000 ₺
   - KDV (dahil): Doğru hesaplanmalı
   - Genel Toplam: Doğru hesaplanmalı

### Test K7: Validasyon Testleri
1. **Boş Form Testleri:**
   - Müşteri oluştur: Sadece ad ve telefon ile (diğerleri boş) → ✅ Çalışmalı
   - Fatura oluştur: Müşteri seçmeden kaydet → ❌ Hata vermeli
   - Fatura oluştur: Kalem eklemeden kaydet → ❌ Hata vermeli
   - Ürün oluştur: Ad olmadan kaydet → ❌ Hata vermeli

2. **Geçersiz Veri Testleri:**
   - Negatif miktar gir → ❌ Hata vermeli
   - Negatif fiyat gir → ❌ Hata vermeli
   - Geçersiz email → ❌ Hata vermeli
   - Vade tarihi fatura tarihinden önce → ❌ Hata vermeli

---

## ✅ TEST SONUÇLARI TABLOSU

Her testi tamamladıktan sonra aşağıdaki tabloyu doldurun:

| Test No | Test Adı | Durum | Hata Varsa Açıklama | Çözüldü mü? |
|---------|----------|-------|---------------------|-------------|
| 1.1 | Sisteme Giriş | ☐ Başarılı / ☐ Başarısız | | ☐ |
| 1.2 | Hesap Seçimi | ☐ Başarılı / ☐ Başarısız | | ☐ |
| 2.1 | Kategori Oluşturma | ☐ Başarılı / ☐ Başarısız | | ☐ |
| 2.2 | Ürün Oluşturma (Minimal) | ☐ Başarılı / ☐ Başarısız | | ☐ |
| ... | ... | ... | ... | ... |

---

## 🎯 ÖNEMLİ NOTLAR

1. **Her test sonrasında veritabanı durumunu kontrol edin**
2. **Müşteri/Tedarikçi bakiyelerini her işlem sonrası kontrol edin**
3. **Stok değerlerini her satış/alış sonrası kontrol edin**
4. **Para birimi işlemlerinde döviz kurunu kontrol edin**
5. **Renk varyantlı ürünlerde doğru stokun düştüğünü kontrol edin**
6. **Değişim işlemlerinde müşteri bakiyesi hesaplamasını DİKKATLİ kontrol edin**
7. **Tahsilat/Ödeme silme işlemlerinde bakiyelerin geri döndüğünü kontrol edin**

---

## 📞 TEST SIRASINDA BULUNAN HATALAR

Aşağıya test sırasında bulduğunuz hataları not edin:

1. **Hata:** 
   - **Test:** 
   - **Adımlar:** 
   - **Beklenen:** 
   - **Gerçekleşen:** 

2. **Hata:** 
   - **Test:** 
   - **Adımlar:** 
   - **Beklenen:** 
   - **Gerçekleşen:** 

---

**Test Tarihi:** _________________

**Test Eden:** _________________

**Sonuç:** ☐ Tüm Testler Başarılı / ☐ Bazı Testler Başarısız

