# PhonePe Payment Gateway - Quick Start Guide

Get PhonePe payment gateway up and running in 5 minutes!

---

## 🚀 Quick Installation (5 Minutes)

### Step 1: Install Package (1 min)

```bash
cd /path/to/bagisto
composer dump-autoload
```

### Step 2: Register Provider (1 min)

Open `config/app.php` and add:

```php
'providers' => [
    // ... existing providers
    Wontonee\Phonepe\Providers\PhonepeServiceProvider::class,
],
```

### Step 3: Clear Caches (30 sec)

```bash
php artisan config:cache
php artisan route:cache
php artisan optimize:clear
```

### Step 4: Publish Assets (30 sec)

```bash
php artisan vendor:publish --tag=Phonepe-assets --force
```

### Step 5: Configure Admin (2 min)

1. Go to: **Admin → Configuration → Sales → Payment Methods → PhonePe**
2. Fill in:
   - **Environment**: Sandbox
   - **Client ID**: `your_client_id`
   - **Client Secret**: `your_client_secret`
   - **Client Version**: `1`
   - **Status**: Yes (Enable)
3. Click **Save Configuration**

---

## ✅ Test It! (2 Minutes)

### Quick Test

1. **Add product** to cart (₹100)
2. **Checkout** → Select PhonePe
3. **Place Order** → Redirects to PhonePe
4. **Complete Payment** → Returns to shop
5. **Success!** → Order created

---

## 🎯 What You Get

- ✅ PhonePe payment option at checkout
- ✅ UPI, Cards, Net Banking, Wallets
- ✅ Automatic order creation
- ✅ Invoice generation
- ✅ Payment verification
- ✅ Secure OAuth 2.0 authentication

---

## 📞 Need Help?

**Quick Support:**
- WhatsApp: +91 9711381236
- Email: dev@wontonee.com

**Documentation:**
- Full guide: `README.md`
- Installation: `INSTALLATION.md`
- Checklist: `CHECKLIST.md`

---

## ⚡ Common Quick Fixes

### PhonePe Not Showing?
```bash
php artisan config:cache
php artisan route:cache
```

### Routes Not Working?
```bash
php artisan route:clear
php artisan config:clear
```

### Payment Failed?
- Check Client ID & Client Secret
- Ensure Environment = Sandbox
- Check `storage/logs/laravel.log`

---

## 🎓 Next Steps

1. **Complete Testing** → See `CHECKLIST.md`
2. **Go Live** → Change to Production in admin
3. **Phase 3** → Add webhooks & refunds (coming soon)

---

**That's it! You're ready to accept PhonePe payments! 🎉**

---

*Quick Start Version: 1.0*  
*Wontonee - Making payments simple*
