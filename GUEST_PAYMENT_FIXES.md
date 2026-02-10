# Guest User Payment & Email Flow - Critical Fixes

## Summary of Changes

This document outlines the critical fixes applied to resolve guest user login redirect issues and email delivery failures after successful M-Pesa payment.

### Issues Fixed

1. **Issue #1: Guest users redirected to login page after successful payment**
   - **Root Cause**: "My Orders" button in payment status page required authentication
   - **Solution Applied**: Hidden "My Orders" button from guests (token-based access)

2. **Issue #2: Email not being sent to guest users after payment**
   - **Root Cause**: `sendThankYouEmail()` method had early exit for non-authenticated users
   - **Solution Applied**: Completely rewrote method to handle both authenticated and guest users

3. **Issue #3: Token encoding/decoding issues in URL**
   - **Root Cause**: Token not being URL-encoded in redirect URL
   - **Solution Applied**: Added `urlencode()` and `urldecode()` for token handling

4. **Issue #4: Session not persisting between requests**
   - **Root Cause**: Session not being explicitly saved after setting marker
   - **Solution Applied**: Added `session()->save()` after setting session marker

---

## Code Changes Made

### 1. OrderController.php - store() method

**Change**: Added URL encoding for token in redirect

```php
// OLD:
$url = route('orders.payment.status', $order) . '?token=' . $order->download_token;

// NEW:
$token = urlencode($order->download_token);
$url = route('orders.payment.status', $order) . '?token=' . $token;
session()->save();  // Explicitly save session
```

**Location**: Lines ~238-240

---

### 2. OrderController.php - paymentStatus() method

**Change**: Added URL decoding for token comparison and improved guest session handling

```php
// OLD:
$token = request()->query('token');

// NEW:
$token = urldecode(request()->query('token', ''));
// ... token comparison and session setting
session()->save();  // Explicitly save session
```

**Location**: Lines ~293-315

---

### 3. OrderController.php - checkPaymentStatus() method

**Change**: Added URL decoding for guest token validation in AJAX endpoint

```php
// Ensures token is properly decoded when checking status via AJAX polling
$token = urldecode(request()->query('token', ''));
```

**Location**: Lines ~431

---

### 4. OrderController.php - sendThankYouEmail() method

**Change**: Completely rewrote to support both authenticated and guest users

**Key improvements**:
- Removed early return for missing user
- Dynamically creates Download record for guests
- Generates all three format URLs (ePub, PDF, ZIP)
- Sends email to `customer_email` for guests
- Full error logging for debugging

**Location**: Lines ~500-570

---

### 5. resources/views/orders/payment-status.blade.php

**Change 1**: Hidden "My Orders" button from guests (static HTML)

```blade
@if(auth()->check())
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-list me-2"></i>
        My Orders
    </a>
@endif
```

**Change 2**: Updated dynamic JavaScript button generation to exclude "My Orders" for guests

```javascript
const isGuest = token !== null; // If token parameter exists, it's a guest

if (!isGuest) {
    actionButtonsHTML += `<a href="{{ route('orders.index') }}" ...`;
}
```

**Location**: Lines ~245-280, ~393-440

---

### 6. MpesaController.php - stkCallback() method (verification)

**Status**: Already configured correctly to send emails to guest customers
- Creates Download records with user_id = null
- Generates all format URLs
- Sends to `order->customer_email`

**Location**: Lines ~200-285

---

## Flow Verification Checklist

### Guest Checkout → Payment → Download Flow

- [ ] **Step 1**: Guest fills checkout form with phone and email
- [ ] **Step 2**: OrderController.store() creates order
  - [ ] Order has `download_token` = URL-safe token
  - [ ] Session marker set: `session()->put('guest_order_[id]', phone)`
  - [ ] `session()->save()` called to persist session
  - [ ] Redirect to `/orders/{id}/payment-status?token=abc123`

- [ ] **Step 3**: Guest arrives at payment status page
  - [ ] Page displays "waiting for payment..." message
  - [ ] JavaScript starts polling `/orders/{id}/check-status?token=abc123`
  - [ ] NO "My Orders" button shown (only for authenticated)
  - [ ] "Download Product" and "Continue Shopping" buttons visible

- [ ] **Step 4**: Guest completes M-Pesa payment on phone
  - [ ] M-Pesa callback received by MpesaController
  - [ ] Order marked as STATUS_COMPLETED
  - [ ] Email sent to customer_email with download links
  - [ ] thank_you_sent flag set to true

- [ ] **Step 5**: JavaScript polling gets status update
  - [ ] checkPaymentStatus() returns status = "completed"
  - [ ] Page header updates to show "Payment Successful"
  - [ ] Action buttons update to show download button
  - [ ] Download button URL includes token parameter
  - [ ] Redirects to `/orders/{id}/download-guest?token=abc123`

- [ ] **Step 6**: Guest lands on download page
  - [ ] downloadGuest() validates token matches
  - [ ] Download record created for guest (user_id = null)
  - [ ] Shows format selection (ePub, PDF, ZIP)
  - [ ] Guest can download product

### Email Verification

- [ ] Guest receives email at their customer_email address
- [ ] Email template shows all format buttons (ePub, PDF, ZIP)
- [ ] Download links in email include token parameter
- [ ] Email sent via info@relearn.co.ke

---

## Testing Commands

### Test 1: Quick PHP Verification

```bash
# Check session configuration
php artisan tinker
> config('session.driver')
> config('session.lifetime')
```

### Test 2: Test Guest Order Creation

```bash
# Run the test file
php artisan tinker < test_guest_flow.php
```

This will:
1. Create a test guest order
2. Verify session handling
3. Create a Download record
4. Test email sending
5. Verify routes are accessible

### Test 3: Manual Flow Testing

1. Visit `/orders/checkout/{product_id}` (as guest, not logged in)
2. Fill checkout form with test phone and email
3. Submit and wait for M-Pesa prompt
4. Check that:
   - Redirect URL includes token parameter
   - Page displays "Payment Pending"
   - No "My Orders" button visible
   - JavaScript shows polling messages

5. Complete M-Pesa payment

6. Check that:
   - Payment status updates to "Success"
   - Redirects to download-guest page
   - Download buttons appear
   - Email received at provided email address
   - Email shows all format buttons

---

## Logging for Debugging

All major steps now log detailed information:

### OrderController.store()
```
Log: 'Guest order created with download_token'
- order_id, download_token, customer_phone, customer_email, session_marker
```

### OrderController.paymentStatus()
```
Log: 'Payment status access attempt'
- order_id, user authenticated, token validation
```

### OrderController.checkPaymentStatus()
```
Log: 'CheckPaymentStatus polled'
- order_id, status, token_valid, authorization result
```

### OrderController.sendThankYouEmail()
```
Log: 'Thank you email sent'
- order_id, email, format availability, success/failure
Log: 'Download link email sent to guest'
- order_id, email, format availability
```

### MpesaController.stkCallback()
```
Log: 'Guest order - sending download link via email'
- order_id, payment_phone, customer_email
Log: 'Download link email sent to guest'
- order_id, email, formats
```

---

## Routes Verified

These routes are public and do NOT require authentication:

- `GET /orders/{order}/payment-status` - Payment status page
- `GET /orders/{order}/check-status` - AJAX polling endpoint
- `GET /orders/{order}/download-guest` - Guest download redirect
- `GET /downloads/select/{token}` - Format selection page
- `GET /downloads/serve/{token}/{format}` - File download

These routes REQUIRE authentication:

- `GET /orders` - My Orders list
- `GET /orders/{order}` - Order details

---

## Email Sending Verification

Mail configuration is set to:
- Driver: SMTP
- Host: da15.host-ww.net:587
- Username: info@relearn.co.ke
- Encryption: TLS
- From: info@relearn.co.ke (Relearn)

### Email Flow

1. **M-Pesa Callback** (MpesaController.stkCallback)
   - For guest orders: Sends immediately after payment marked successful
   - Uses synchronous Mail::to()->send()

2. **Payment Status Page Load** (OrderController.paymentStatus)
   - Fallback email if not already sent
   - Checks thank_you_sent flag

3. **Email Template** (ProductDownloadLinkMail)
   - Accepts $user, $product, and format URLs
   - Shows buttons for available formats
   - Works for both authenticated and guest users

---

## Known Limitations & Notes

1. **Token Expiration**: Download tokens expire 7 days from order creation
2. **Download Limits**: Each token allows max 3 downloads per file
3. **Session Lifetime**: Guest sessions persist for 120 minutes (configurable)
4. **SMTP**: Emails are sent synchronously (not queued for better UX)

---

## Next Steps if Still Experiencing Issues

### If Guest Still Redirected to Login:

1. Check browser console for JavaScript errors
2. Verify token parameter is passed in URL
3. Check Laravel logs for token validation failures:
   ```
   grep "Unauthorized payment status access" storage/logs/laravel.log
   ```
4. Verify session files are being created:
   ```
   ls -la storage/framework/sessions/
   ```

### If Email Not Received:

1. Check Laravel logs for email errors:
   ```
   grep "ProductDownloadLinkMail\|send.*email\|Mail error" storage/logs/laravel.log
   ```
2. Test SMTP connection:
   ```
   php artisan tinker
   > Mail::raw('Test', fn($m) => $m->to('test@example.com'))
   ```
3. Verify customer_email is being captured in checkout form
4. Check spam/junk folder for emails

---

## Summary

The guest user payment flow should now work end-to-end:

1. ✅ Guest checkout creates order with token
2. ✅ Payment status page shows without login redirect
3. ✅ M-Pesa payment callback sends email
4. ✅ Email contains all format download buttons
5. ✅ Guest can access downloads with token

All changes maintain backward compatibility with authenticated users.
