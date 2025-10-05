# PhonePe Package Assets

This directory contains the assets for the PhonePe payment gateway extension.

## Publishing Assets

To publish the assets to the public directory, run:

```bash
php artisan vendor:publish --tag=phonepe-assets
```

This will copy all assets from this directory to:
```
public/vendor/wontonee/phonepe/
```

## Assets Included

- **images/phone.png** - Default PhonePe payment method icon (100x50px recommended)

## Default Image Path

After publishing, the default PhonePe icon will be available at:
```
public/vendor/wontonee/phonepe/images/phone.png
```

## Custom Icons

You can upload a custom icon through the admin panel:
1. Go to Configuration → Sales → Payment Methods → PhonePe
2. Upload your custom icon in the "Payment Method Icon" field
3. Recommended size: 100x50px (PNG, JPG, or WebP format)

## Force Republish

To overwrite existing published assets:
```bash
php artisan vendor:publish --tag=phonepe-assets --force
```
