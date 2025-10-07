# PhonePe Payment Gateway for Bagisto

Professional PhonePe payment gateway integration for Bagisto e-commerce platform with OAuth 2.0 authentication.

---

## 🚀 Features

### Payment Processing
- **OAuth 2.0 Authentication**: Secure token-based API authentication
- **Multiple Payment Methods**: UPI, Cards, Net Banking, and Wallets
- **Environment Support**: Sandbox for testing, Production for live transactions
- **Automatic Amount Conversion**: Handles paisa conversion automatically
- **Order Management**: Seamless integration with Bagisto order system

### Security & Reliability
- **Secure Token Generation**: OAuth 2.0 access token with automatic expiry handling
- **Payment Verification**: Server-side payment status verification
- **Session Management**: Secure session handling for payment data
- **Comprehensive Logging**: Detailed logs for debugging and monitoring

### Admin Features
- **Easy Configuration**: Simple admin panel setup
- **Environment Toggle**: Switch between Sandbox and Production
- **Customizable**: Configure order expiry time
- **Payment Method Icon**: Upload custom payment method icon

---

## 📋 Requirements

- Bagisto 2.x
- PHP 8.1 or higher
- PhonePe Business Account
- Client ID and Client Secret from PhonePe Dashboard

---

## 📦 Installation

### Step 1: Install via Composer

```bash
composer require wontonee/phonepe
```

### Step 2: Publish Assets

```bash
php artisan vendor:publish --tag=phonepe-assets
```

### Step 3: Clear Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan optimize:clear
```

---

## ⚙️ Configuration

### 1. Get PhonePe Credentials

1. Login to [PhonePe Business Dashboard](https://business.phonepe.com/)
2. Navigate to **Developer Settings**
3. Copy your **Client ID** and **Client Secret**

### 2. Configure in Bagisto Admin

1. Go to **Admin Panel → Configuration → Sales → Payment Methods**
2. Find **PhonePe** in the payment methods list
3. Configure the following fields:

| Field | Description | Required |
|-------|-------------|----------|
| **Title** | Display name for payment method | Yes |
| **Description** | Description shown to customers | No |
| **Payment Method Icon** | Upload custom icon (100x50px recommended) | No |
| **Environment** | Select Sandbox or Production | Yes |
| **Client ID** | Your PhonePe Client ID | Yes |
| **Client Secret** | Your PhonePe Client Secret | Yes |
| **Client Version** | API Client Version (Default: 1) | Yes |
| **Checkout Type** | Select Standard or Express (Default: Standard) | Yes |
| **Status** | Enable/Disable payment method | Yes |

4. Click **Save Configuration**

---

## 🔧 How It Works

### Payment Flow

1. **Customer Checkout**: Customer selects PhonePe and places order
2. **OAuth Authentication**: System generates OAuth access token
3. **Payment Creation**: Payment order created via PhonePe API
4. **Redirect to PhonePe**: Customer redirected to PhonePe payment page
5. **Payment Processing**: Customer completes payment on PhonePe
6. **Callback**: PhonePe redirects back with payment status
7. **Verification**: System verifies payment status with PhonePe
8. **Order Creation**: Order created if payment successful

### API Endpoints

**Sandbox:**
- Auth: `https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token`
- Payment: `https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay`

**Production:**
- Auth: `https://api.phonepe.com/apis/identity-manager/v1/oauth/token`
- Payment: `https://api.phonepe.com/apis/pg/checkout/v2/pay`

---

## 🧪 Testing

### Sandbox Testing

1. Set **Environment** to **Sandbox**
2. Use your PhonePe sandbox credentials
3. Test with PhonePe test payment methods

### Test Credentials

Contact PhonePe support for sandbox test credentials.

---

## 📝 Important Notes

### Amount Handling
- PhonePe requires amounts in **paisa** (₹10 = 1000 paisa)
- Minimum order amount: **₹1 (100 paisa)**
- System automatically converts rupees to paisa

### Order Expiry
- Minimum: 300 seconds (5 minutes)
- Maximum: 3600 seconds (60 minutes)
- Default: 1800 seconds (30 minutes)
- Fixed at 30 minutes for optimal user experience

### Payment States
- `PENDING`: Payment initiated but not completed
- `COMPLETED`: Payment successful
- `FAILED`: Payment failed

---

## 🔍 Troubleshooting

### Common Issues

**1. "PhonePe credentials not configured"**
- Solution: Check Client ID and Client Secret in admin configuration

**2. "Minimum order amount is ₹1"**
- Solution: Ensure cart total is at least ₹1

**3. "Payment verification failed"**
- Solution: Check PhonePe API credentials and environment setting

**4. OAuth token generation failed**
- Solution: Verify Client ID, Client Secret, and network connectivity

**5. Route not found errors**
- Solution: Run `php artisan route:cache` and `php artisan config:cache`

### Debug Logs

Check logs in `storage/logs/laravel.log` for detailed error information.

---

## 🆘 Support

For support, contact:
- **Email**: dev@wontonee.com
- **Website**: https://www.wontonee.com
- **WhatsApp**: +91 9711381236

---

## 📄 License

This package is proprietary software by Wontonee.

---

## 🔮 Upcoming Features (Phase 3)

- ✅ Webhook integration for real-time updates
- ✅ Refund functionality from admin panel
- ✅ Order status API integration
- ✅ Fallback mechanism for missed payments

---

## 📚 Additional Resources

- [PhonePe Developer Documentation](https://developer.phonepe.com/)
- [PhonePe Business Dashboard](https://business.phonepe.com/)
- [Bagisto Documentation](https://bagisto.com/en/documentation/)

---

**Developed with ❤️ by Wontonee**
