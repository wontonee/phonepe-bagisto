# PhonePe Payment Gateway - Installation & Setup Guide

Complete guide for installing and configuring PhonePe payment gateway for Bagisto.

---

## 🎯 Prerequisites

Before starting, ensure you have:

✅ Bagisto 2.x installed and running  
✅ PHP 8.1 or higher  
✅ Composer installed  
✅ PhonePe Business Account  
✅ PhonePe API Credentials (Client ID & Client Secret)  

---

## 📦 Installation Steps

### Step 1: Add Package to Bagisto

If installing from a local package:

```bash
cd /path/to/your/bagisto
```

Make sure the package is in `packages/Wontonee/Phonepe/` directory.

### Step 2: Update Composer

Add the package to your `composer.json`:

```json
"autoload": {
    "psr-4": {
        "Wontonee\\Phonepe\\": "packages/Wontonee/Phonepe/src"
    }
}
```

### Step 3: Register Service Provider

Open `config/app.php` and add to the `providers` array:

```php
'providers' => [
    // ... other providers
    Wontonee\Phonepe\Providers\PhonepeServiceProvider::class,
],
```

### Step 4: Dump Autoload

```bash
composer dump-autoload
```

### Step 5: Publish Assets

```bash
php artisan vendor:publish --tag=Phonepe-assets --force
```

### Step 6: Clear All Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

---

## ⚙️ Configuration

### 1. Get PhonePe Credentials

#### For Sandbox (Testing):
1. Login to [PhonePe Business Dashboard](https://business.phonepe.com/)
2. Navigate to **Developer Settings** → **Sandbox**
3. Copy **Client ID** and **Client Secret**

#### For Production (Live):
1. Complete PhonePe account verification
2. Navigate to **Developer Settings** → **Production**
3. Copy **Client ID** and **Client Secret**

### 2. Configure in Bagisto Admin Panel

1. Login to Bagisto Admin Panel
2. Go to **Configuration** → **Sales** → **Payment Methods**
3. Scroll to **PhonePe** section
4. Fill in the configuration:

```
Title: PhonePe
Description: Pay securely using PhonePe - UPI, Cards, Net Banking & Wallets
Environment: Sandbox (for testing) or Production (for live)
Client ID: [Your PhonePe Client ID]
Client Secret: [Your PhonePe Client Secret]
Client Version: 1
Checkout Type: Standard
Status: Yes (Enable)
```

5. Click **Save Configuration**

### 3. Test the Integration

#### Sandbox Testing:

1. Set Environment to **Sandbox**
2. Add a product to cart
3. Proceed to checkout
4. Select **PhonePe** as payment method
5. Place order
6. You'll be redirected to PhonePe sandbox payment page
7. Complete test payment
8. Verify order creation in admin panel

---

## 🔍 Verification Checklist

After installation, verify:

- [ ] Package appears in `packages/Wontonee/Phonepe/`
- [ ] Service provider registered in `config/app.php`
- [ ] PhonePe appears in Admin → Payment Methods
- [ ] Routes work: Test `yourdomain.com/Phonepe-redirect`
- [ ] Assets published: Check `public/vendor/wontonee/Phonepe/`
- [ ] Configuration saved successfully
- [ ] Test order placement works
- [ ] Payment redirect to PhonePe works
- [ ] Callback handling works
- [ ] Order creation after payment works

---

## 🚨 Troubleshooting

### Issue 1: Package Not Found

**Error**: `Class 'Wontonee\Phonepe\Providers\PhonepeServiceProvider' not found`

**Solution**:
```bash
composer dump-autoload
php artisan config:cache
```

### Issue 2: Routes Not Working

**Error**: `Route [Phonepe.process] not defined`

**Solution**:
```bash
php artisan route:clear
php artisan route:cache
php artisan config:cache
```

### Issue 3: PhonePe Not Showing in Payment Methods

**Solution**:
1. Check `config/payment_methods.php` includes Phonepe config
2. Clear config cache: `php artisan config:cache`
3. Check database: `core_config` table should have Phonepe entries

### Issue 4: OAuth Token Generation Failed

**Error**: `Failed to get PhonePe access token`

**Solutions**:
- Verify Client ID and Client Secret are correct
- Check environment setting (Sandbox vs Production)
- Ensure server can make HTTPS requests to PhonePe APIs
- Check `storage/logs/laravel.log` for detailed error

### Issue 5: Payment Callback Not Working

**Solutions**:
- Ensure callback URL is accessible publicly
- Check session is maintained across redirects
- Verify no middleware blocking the callback route
- Check logs for errors during callback processing

---

## 📝 Configuration Reference

### Environment Variables

You can also configure via `.env` (optional):

```env
PHONEPE_ENVIRONMENT=sandbox
PHONEPE_CLIENT_ID=your_client_id
PHONEPE_CLIENT_SECRET=your_client_secret
PHONEPE_CLIENT_VERSION=1
```

### Database Configuration

PhonePe configuration is stored in `core_config` table:

```
sales.payment_methods.Phonepe.title
sales.payment_methods.Phonepe.description
sales.payment_methods.Phonepe.environment
sales.payment_methods.Phonepe.client_id
sales.payment_methods.Phonepe.client_secret
sales.payment_methods.Phonepe.client_version
sales.payment_methods.Phonepe.checkout_type
sales.payment_methods.Phonepe.order_expiry
sales.payment_methods.Phonepe.active
```

---

## 🔐 Security Best Practices

1. **Never commit credentials**: Keep Client Secret in environment variables
2. **Use Sandbox for testing**: Always test in sandbox before going live
3. **Enable HTTPS**: Production must use HTTPS
4. **Monitor logs**: Regularly check `storage/logs/laravel.log`
5. **Update regularly**: Keep package updated for security patches

---

## 🎓 Testing Scenarios

### Successful Payment Flow

1. Add product to cart → Total: ₹500
2. Select PhonePe payment
3. Place order
4. Redirected to PhonePe
5. Complete payment
6. Redirected back
7. Order created with status "Processing"
8. Invoice generated

### Failed Payment Flow

1. Add product to cart
2. Select PhonePe payment
3. Place order
4. Redirected to PhonePe
5. Cancel or fail payment
6. Redirected back with error message
7. Cart still active
8. Can retry payment

### Minimum Amount Test

1. Add product worth ₹0.50
2. Should show error: "Minimum order amount is ₹1"

---

## 📞 Support & Contact

Need help? Contact us:

- **Email**: dev@wontonee.com
- **Website**: https://www.wontonee.com
- **WhatsApp**: +91 9711381236
- **Support Hours**: Monday-Friday, 9 AM - 6 PM IST

---

## 📚 Additional Resources

- [PhonePe API Documentation](https://developer.phonepe.com/)
- [PhonePe Business Dashboard](https://business.phonepe.com/)
- [Bagisto Documentation](https://bagisto.com/en/documentation/)
- [Package GitHub Repository](https://github.com/wontonee/Phonepe)

---

## ✅ Post-Installation Checklist

After completing installation:

- [ ] Tested in Sandbox environment
- [ ] Verified successful payment flow
- [ ] Verified failed payment flow
- [ ] Checked order creation
- [ ] Checked invoice generation
- [ ] Reviewed logs for errors
- [ ] Documented production credentials securely
- [ ] Ready to switch to Production

---

**Installation completed successfully! 🎉**

For Phase 2 (Frontend Integration) and Phase 3 (Webhooks & Refunds), refer to the respective documentation files.

---

**Developed by Wontonee**
