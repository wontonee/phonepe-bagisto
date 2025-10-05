# PhonePe Payment Gateway - Implementation Checklist

Use this checklist to ensure proper installation and testing.

---

## 📋 Pre-Installation Checklist

- [ ] Bagisto 2.x installed and working
- [ ] PHP 8.1+ installed
- [ ] Composer available
- [ ] PhonePe Business Account created
- [ ] PhonePe Sandbox credentials obtained
- [ ] Access to Bagisto admin panel
- [ ] Database backup taken

---

## 🔧 Installation Checklist

### 1. Package Setup

- [ ] Package located in `packages/Wontonee/Phonepe/`
- [ ] Added to `composer.json` autoload section
- [ ] Ran `composer dump-autoload`
- [ ] Service provider added to `config/app.php`
- [ ] Ran `php artisan config:cache`
- [ ] Ran `php artisan route:cache`

### 2. Asset Publishing

- [ ] Ran `php artisan vendor:publish --tag=Phonepe-assets`
- [ ] PhonePe logo placed in `public/vendor/wontonee/Phonepe/images/`
- [ ] Assets accessible via browser

### 3. Configuration

- [ ] Accessed Admin → Configuration → Sales → Payment Methods
- [ ] PhonePe section visible
- [ ] Configured all required fields:
  - [ ] Title
  - [ ] Environment (Sandbox)
  - [ ] Client ID
  - [ ] Client Secret
  - [ ] Client Version
  - [ ] Order Expiry
  - [ ] Status (Enabled)
- [ ] Saved configuration successfully

---

## ✅ Verification Checklist

### 1. Routes

Test these URLs (replace `yoursite.com` with your domain):

- [ ] `yoursite.com/Phonepe-redirect` - Should redirect or show error
- [ ] `yoursite.com/Phonepe-callback` - Should show error (needs session)
- [ ] `yoursite.com/Phonepe-cancel` - Should redirect to cart

### 2. Admin Panel

- [ ] PhonePe shows in payment methods list
- [ ] Configuration page loads without errors
- [ ] Can save configuration
- [ ] Configuration persists after save
- [ ] Can upload payment method icon

### 3. Frontend

- [ ] PhonePe option appears at checkout
- [ ] PhonePe icon displays correctly
- [ ] Can select PhonePe as payment method
- [ ] Payment method description shows

---

## 🧪 Testing Checklist

### Sandbox Testing

#### Test 1: Successful Payment
- [ ] Added product to cart (₹100 or more)
- [ ] Proceeded to checkout
- [ ] Filled billing/shipping details
- [ ] Selected PhonePe payment
- [ ] Clicked "Place Order"
- [ ] Redirected to PhonePe sandbox
- [ ] Completed test payment
- [ ] Redirected back to shop
- [ ] Order created successfully
- [ ] Order status: "Processing"
- [ ] Invoice generated
- [ ] Cart cleared
- [ ] Success page displayed

#### Test 2: Failed Payment
- [ ] Added product to cart
- [ ] Proceeded to checkout
- [ ] Selected PhonePe payment
- [ ] Clicked "Place Order"
- [ ] Redirected to PhonePe sandbox
- [ ] Cancelled or failed payment
- [ ] Redirected back to shop
- [ ] Error message displayed
- [ ] Cart still active
- [ ] No order created
- [ ] Can retry payment

#### Test 3: Minimum Amount
- [ ] Added product < ₹1 to cart
- [ ] Proceeded to checkout
- [ ] Selected PhonePe payment
- [ ] Error shown: "Minimum order amount is ₹1"

#### Test 4: Session Handling
- [ ] Started payment flow
- [ ] Cleared browser cookies
- [ ] Returned from PhonePe
- [ ] Appropriate error shown

---

## 🔍 Debugging Checklist

### Logs

- [ ] Checked `storage/logs/laravel.log`
- [ ] Found PhonePe log entries
- [ ] No critical errors present
- [ ] OAuth token generation logged
- [ ] Payment creation logged
- [ ] Payment status check logged
- [ ] Order creation logged

### Database

Check `core_config` table for PhonePe configuration:

- [ ] `sales.payment_methods.Phonepe.client_id`
- [ ] `sales.payment_methods.Phonepe.client_secret`
- [ ] `sales.payment_methods.Phonepe.environment`
- [ ] `sales.payment_methods.Phonepe.active`

### Sessions

Check session storage:

- [ ] Sessions working correctly
- [ ] Session data persists across redirects
- [ ] Session cleared after successful payment

---

## 🚨 Common Issues Checklist

### Issue: PhonePe Not Showing

- [ ] Service provider registered
- [ ] Config cache cleared
- [ ] Payment method enabled in admin
- [ ] No errors in logs

### Issue: OAuth Token Failed

- [ ] Client ID correct
- [ ] Client Secret correct
- [ ] Client Version set to "1"
- [ ] Environment setting correct (Sandbox/Production)
- [ ] Server can access PhonePe APIs
- [ ] No firewall blocking HTTPS requests

### Issue: Payment Redirect Not Working

- [ ] Route registered: `Phonepe.process`
- [ ] Route cache cleared
- [ ] No CSRF token issues
- [ ] Session working correctly

### Issue: Callback Failed

- [ ] Callback URL accessible
- [ ] Session maintained
- [ ] No middleware blocking
- [ ] Payment status API working

### Issue: Order Not Created

- [ ] Payment status = "COMPLETED"
- [ ] Cart exists in session
- [ ] No validation errors
- [ ] Invoice creation successful
- [ ] Check logs for errors

---

## 📊 Performance Checklist

- [ ] OAuth token generation: < 5 seconds
- [ ] Payment creation: < 5 seconds
- [ ] Payment status check: < 5 seconds
- [ ] Order creation: < 10 seconds
- [ ] Total payment flow: < 30 seconds

---

## 🔒 Security Checklist

- [ ] Client Secret not exposed in frontend
- [ ] HTTPS enabled (for production)
- [ ] Sessions secured
- [ ] No sensitive data in logs (review)
- [ ] Payment verification server-side only
- [ ] CSRF protection enabled

---

## 📱 Browser Compatibility

Test in these browsers:

- [ ] Chrome/Edge (Latest)
- [ ] Firefox (Latest)
- [ ] Safari (Latest)
- [ ] Mobile Chrome (Android)
- [ ] Mobile Safari (iOS)

---

## 🎯 Production Readiness Checklist

Before going live:

### Configuration
- [ ] Changed Environment to "Production"
- [ ] Updated to Production Client ID
- [ ] Updated to Production Client Secret
- [ ] Tested production credentials in sandbox first
- [ ] SSL certificate installed and working
- [ ] Custom payment icon uploaded (optional)

### Testing
- [ ] Completed all sandbox tests
- [ ] Tested with real PhonePe production credentials
- [ ] Tested small amount transaction (₹1-10)
- [ ] Verified order creation
- [ ] Verified invoice generation
- [ ] Tested refund process (if Phase 3 implemented)

### Documentation
- [ ] Admin trained on configuration
- [ ] Payment flow documented
- [ ] Support contact saved
- [ ] Backup procedures documented

### Monitoring
- [ ] Log monitoring set up
- [ ] Error alerting configured
- [ ] Performance monitoring enabled
- [ ] Regular backup scheduled

### Compliance
- [ ] Terms and conditions updated
- [ ] Privacy policy updated
- [ ] Refund policy defined
- [ ] Customer support prepared

---

## ✅ Final Sign-Off

- [ ] All tests passed
- [ ] No critical errors
- [ ] Documentation complete
- [ ] Team trained
- [ ] Ready for production

---

## 📞 Support Contacts

**Wontonee Support**
- Email: dev@wontonee.com
- WhatsApp: +91 9711381236
- Website: https://www.wontonee.com

**PhonePe Support**
- Dashboard: https://business.phonepe.com/
- Docs: https://developer.phonepe.com/
- Support: Via PhonePe Business Dashboard

---

## 📝 Notes Section

Use this space for your notes during installation:

```
Date: ______________
Tested By: ______________
Environment: Sandbox / Production
Issues Found: 




Resolved: Yes / No
Production Date: ______________
```

---

**Checklist Version: 1.0**  
**Last Updated: October 5, 2025**

---

✨ **Installation Complete!** ✨

Once all items are checked, your PhonePe integration is ready!
