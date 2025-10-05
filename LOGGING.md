# PhonePe Payment Gateway - Logging System

## Overview
The PhonePe payment gateway uses a dedicated logging channel to keep all PhonePe-related logs separate from the main Laravel logs.

## Log File Location
```
storage/logs/Phonepe.log
```

All PhonePe payment gateway activities are logged to this dedicated file, making it easier to:
- Debug payment issues
- Track payment flow
- Monitor API responses
- Audit payment transactions

## Log Configuration
The logging configuration is defined in `config/logging.php`:

```php
'Phonepe' => [
    'driver'               => 'daily',
    'path'                 => storage_path('logs/Phonepe.log'),
    'level'                => env('LOG_LEVEL', 'debug'),
    'days'                 => 14,
    'replace_placeholders' => true,
],
```

**Features:**
- **Daily rotation**: Logs are rotated daily
- **14-day retention**: Old logs are automatically deleted after 14 days
- **Configurable level**: Log level can be controlled via `LOG_LEVEL` environment variable
- **Separate from main logs**: PhonePe logs don't clutter `laravel.log`

## What Gets Logged

### 1. Payment Initiation
```
[INFO] Redirecting to payment gateway
- merchant_order_id
- phonepe_order_id
- amount
```

### 2. OAuth Token Generation
```
[INFO] OAuth token generated successfully
- expires_at

[ERROR] Failed to get access token
- status
- response

[ERROR] OAuth token generation failed
- error
```

### 3. Payment Order Creation
```
[INFO] Payment order created successfully
- merchant_order_id
- phonepe_order_id

[ERROR] Payment creation failed
- status
- response
- merchant_order_id

[ERROR] Payment creation exception
- error
- merchant_order_id
```

### 4. Payment Callback
```
[INFO] Payment callback received
- merchant_order_id
- phonepe_order_id
- status

[WARNING] Payment not successful
- merchant_order_id
- phonepe_order_id
- state

[ERROR] Callback - Missing session data
- cart_id
- merchant_order_id

[ERROR] Callback processing failed
- error
- trace
```

### 5. Payment Status Check
```
[ERROR] Payment status check failed
- status
- response
- merchant_order_id

[ERROR] Payment status check exception
- error
- merchant_order_id
```

### 6. Order Creation
```
[INFO] Order created successfully
- order_id
- phonepe_order_id

[ERROR] Order creation failed
- error
- trace
- phonepe_order_id
```

### 7. Payment Cancellation
```
[INFO] Payment cancelled by user
```

## Session Flash Messages

The controller uses Bagisto's standard session flash messages for user feedback:

### Success Messages
```php
session()->flash('success', 'Order placed successfully');
```

### Warning Messages
```php
session()->flash('warning', 'Payment was cancelled. You can try again.');
session()->flash('warning', 'Payment was not completed. Please try again.');
```

### Error Messages
```php
session()->flash('error', 'PhonePe payment initiation failed. Please try again.');
session()->flash('error', 'Payment session expired. Please try again.');
session()->flash('error', 'Payment verification failed. Please contact support.');
session()->flash('error', 'Order creation failed. Please contact support with your payment details.');
```

## Viewing Logs

### Via Command Line (Windows PowerShell)
```powershell
# View latest logs (last 50 lines)
Get-Content storage/logs/Phonepe.log -Tail 50

# View logs in real-time
Get-Content storage/logs/Phonepe.log -Wait

# View today's log file
Get-Content storage/logs/Phonepe-2025-10-05.log

# Search for specific merchant order ID
Select-String -Path storage/logs/Phonepe.log -Pattern "ORD-1728123456-1234"

# Search for errors only
Select-String -Path storage/logs/Phonepe.log -Pattern "ERROR"
```

### Via Laravel Tinker
```php
// Read last 1000 characters
File::get(storage_path('logs/Phonepe.log'));

// Read specific log file
File::get(storage_path('logs/Phonepe-2025-10-05.log'));
```

## Best Practices

### 1. Monitor Payment Issues
Check `Phonepe.log` when investigating:
- Failed payments
- Callback issues
- API errors
- Order creation problems

### 2. Log Retention
- Default retention: 14 days
- Adjust in `config/logging.php` if needed:
  ```php
  'days' => 30, // Keep logs for 30 days
  ```

### 3. Production Environment
In production, consider:
- Setting `LOG_LEVEL=info` to reduce log size
- Implementing log monitoring/alerting
- Regular log backup before rotation

### 4. Debugging Tips
```php
// Temporarily increase log verbosity
LOG_LEVEL=debug php artisan optimize:clear

// Check if log channel is working
Log::channel('Phonepe')->info('Test log entry');

// View log permissions
ls -la storage/logs/Phonepe*.log
```

## Troubleshooting

### Log File Not Created
1. Check directory permissions:
   ```powershell
   # Ensure storage/logs is writable
   icacls storage\logs
   ```

2. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. Test logging manually:
   ```bash
   php artisan tinker
   Log::channel('Phonepe')->info('Test');
   exit
   ```

### Log File Too Large
1. Reduce log level in `.env`:
   ```
   LOG_LEVEL=warning
   ```

2. Reduce retention days in `config/logging.php`:
   ```php
   'days' => 7,
   ```

3. Manually clean old logs:
   ```powershell
   Get-ChildItem storage/logs/Phonepe-*.log | Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-7)} | Remove-Item
   ```

## Support Information

When reporting issues, please include:
1. Relevant entries from `storage/logs/Phonepe.log`
2. Merchant Order ID
3. PhonePe Order ID (if available)
4. Timestamp of the issue
5. User-visible error message

Example:
```
[2025-10-05 10:30:45] local.ERROR: Payment status check failed 
{"status":400,"merchant_order_id":"ORD-1728123456-1234","response":{"message":"Bad Request"}}
```

## Related Files
- Controller: `packages/Wontonee/Phonepe/src/Http/Controllers/PhonepeController.php`
- Service: `packages/Wontonee/Phonepe/src/Services/PhonepeService.php`
- Config: `config/logging.php`
- Logs: `storage/logs/Phonepe*.log`
