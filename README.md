# RonexCari - Business Management System

A comprehensive business management system built with Laravel 12, featuring complete CRUD operations for sales, purchases, expenses, products & services, and finance modules.

## 🚀 Features

### ✅ **Laravel 12 Upgrade Complete**
- **Laravel Framework:** 12.28.1 (Latest)
- **PHP Version:** 8.2+
- **Database:** SQLite (Production Ready)

### 📊 **5 Main Business Modules**

#### 1. **SATIŞLAR (Sales)**
- **Müşteriler (Customers)** - Customer management with full CRUD
- **Faturalar (Invoices)** - Sales invoice management
- **Siparişler (Orders)** - Sales order processing
- **Teklifler (Quotes)** - Sales quotation system

#### 2. **ALIŞLAR (Purchases)**
- **Tedarikçiler (Suppliers)** - Supplier management
- **Faturalar (Invoices)** - Purchase invoice management
- **İrsaliyeler (Delivery Notes)** - Delivery note tracking
- **Siparişler (Orders)** - Purchase order management

#### 3. **GİDERLER (Expenses)**
- **Masraflar (Costs)** - Expense tracking
- **Çalışanlar (Employees)** - Employee management
- **Faturalar (Invoices)** - Expense invoice management
- **Gider Pusulaları (Expense Slips)** - Expense slip system

#### 4. **ÜRÜN VE HİZMETLER (Products & Services)**
- **Ürünler (Products)** - Product catalog management
- **Hizmetler (Services)** - Service management
- **Fiyat Listeleri (Price Lists)** - Pricing management
- **Depo Transferleri (Warehouse Transfers)** - Inventory transfers
- **Toplu İşlemler (Bulk Operations)** - Bulk processing

#### 5. **RAPORLAR (Reports)**
- **Rapor Setleri (Report Sets)** - Report configuration
- **Rapor Al (Generate Report)** - Report generation

#### 6. **FİNANS (Finance)**
- **Ödemeler (Payments)** - Payment processing
- **Tahsilatlar (Collections)** - Collection management
- **Kasalar (Cash Registers)** - Cash register management
- **Banka Hesapları (Bank Accounts)** - Bank account management
- **POS Hesapları (POS Accounts)** - POS system integration
- **Çek ve Senetler (Checks & Bills)** - Check and bill management
- **Kredi Kartları (Credit Cards)** - Credit card processing
- **Krediler (Loans)** - Loan management
- **Hesaplararası Transferler (Inter-account Transfers)** - Account transfers

## 🛠️ Technical Features

### **Authentication & Security**
- Laravel Breeze authentication system
- User registration and login
- Password reset functionality
- Secure session management

### **Database & Models**
- 15+ Eloquent models with proper relationships
- Comprehensive database migrations
- Sample data seeding
- SQLite database (production ready)

### **Validation & Forms**
- Server-side validation for all forms
- Turkish language error messages
- Responsive form design
- Real-time validation feedback

### **User Interface**
- Modern, dark-themed design
- Responsive layout (mobile-friendly)
- Turkish language interface
- Intuitive navigation structure
- Professional business application look

## 🚀 Getting Started

### **Prerequisites**
- PHP 8.2 or higher
- Composer
- SQLite support

### **Installation**

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd RonexCari
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate --seed
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

6. **Access the application**
   - URL: `http://localhost:8000`
   - Register a new account or use:
     - **Admin:** admin@ronexcari.com
     - **Test User:** test@example.com

## 📋 Sample Data

The application comes with pre-loaded sample data:

### **Customers**
- Ahmet Yılmaz (Yılmaz Ticaret A.Ş.)
- Fatma Demir (Demir İnşaat Ltd.)

### **Suppliers**
- Mehmet Kaya (Kaya Malzemeleri A.Ş.)
- Ayşe Özkan (Özkan Hizmetler Ltd.)

### **Products**
- Laptop Bilgisayar (₺15,000)
- Ofis Sandalyesi (₺2,500)

### **Services**
- Web Tasarım Hizmeti (₺5,000)
- Muhasebe Danışmanlığı (₺2,000/month)

### **Employees**
- Ali Veli (Satış Temsilcisi)
- Zeynep Kaya (Muhasebe Uzmanı)

### **Expenses**
- Ofis Kira Ödemesi (₺15,000)
- Elektrik Faturası (₺2,500)

## 🎯 Usage

### **Navigation**
- Use the sidebar navigation to access different modules
- Each module has full CRUD operations (Create, Read, Update, Delete)
- All forms include comprehensive validation

### **Key Features**
- **Dashboard:** Overview of business operations
- **Customer Management:** Add, edit, view, and manage customers
- **Product Catalog:** Manage products and services
- **Financial Tracking:** Monitor payments, expenses, and collections
- **Reporting:** Generate business reports

## 🔧 Development

### **Project Structure**
```
app/
├── Http/Controllers/
│   ├── Sales/          # Sales module controllers
│   ├── Purchases/      # Purchases module controllers
│   ├── Products/       # Products module controllers
│   ├── Expenses/       # Expenses module controllers
│   └── Finance/        # Finance module controllers
├── Models/             # Eloquent models
└── ...

resources/views/
├── sales/              # Sales module views
├── purchases/          # Purchases module views
├── products/           # Products module views
├── expenses/           # Expenses module views
├── finance/            # Finance module views
└── components/         # Reusable components
```

### **Database Schema**
- **Users:** Authentication and user management
- **Customers:** Customer information and relationships
- **Suppliers:** Supplier information and relationships
- **Products:** Product catalog with pricing and inventory
- **Services:** Service offerings and pricing
- **Employees:** Employee information and management
- **Expenses:** Expense tracking and categorization
- **Invoices:** Sales and purchase invoices
- **Orders:** Sales and purchase orders
- **Payments:** Payment processing and tracking

## 📱 Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🌐 Language Support

- **Primary Language:** Turkish
- **Interface:** Fully localized in Turkish
- **Error Messages:** Turkish language support
- **Form Labels:** Turkish business terminology

## 🔒 Security Features

- CSRF protection on all forms
- SQL injection prevention
- XSS protection
- Secure password hashing
- Session security
- Input validation and sanitization

## 📈 Performance

- Optimized database queries
- Efficient Eloquent relationships
- Cached routes and configuration
- Minimal JavaScript dependencies
- Fast page load times

## 🚀 Production Ready

The application is production-ready with:
- Laravel 12 (latest stable version)
- Comprehensive error handling
- Logging and monitoring
- Database optimization
- Security best practices
- Scalable architecture

## 📞 Support

For support and questions:
- Check the Laravel documentation
- Review the code comments
- Test with sample data provided

---

**Built with ❤️ using Laravel 12**