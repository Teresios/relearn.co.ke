# Affiliate System - Current Status & Implementation Progress

## ✅ Completed Phases

### Phase 1: Landing Page CTA Button ✅
- **Status**: COMPLETE
- **Implementation**: CTA button added to `resources/views/home.blade.php`
- **Features**:
  - "Apply to Become an Affiliate" button in footer CTA section
  - Conditional display for guests and authenticated users
  - Link to `/affiliate/apply` route

### Phase 2: Affiliate Application Form ✅
- **Status**: COMPLETE
- **Implementation**: `resources/views/affiliate/apply.blade.php`
- **Features**:
  - Full Name (auto-filled from user profile)
  - Email (auto-filled from user profile)
  - Phone (auto-filled from user profile)
  - County Dropdown (all 47 Kenya counties)
  - Preferred Mode of Contact (Email, Phone, WhatsApp, SMS)
  - Social Media Handles
  - Short Bio/Motivation (max 500 chars)
  - M-Pesa Payment Details (auto-filled)
  - Referral Source
  - Privacy notice and consent
- **Form Submission**: POST to `affiliate.apply.submit` route
- **Validation**: All required fields validated

### Phase 3: Application Status Page ✅
- **Status**: COMPLETE
- **Implementation**: `resources/views/affiliate/status.blade.php`
- **Features**:
  - Three states: Approved, Pending, Not Applied
  - Approved: Shows success icon, link to dashboard
  - Pending: Shows hourglass icon, waiting message
  - Not Applied: Shows error icon, link to apply form
- **Route**: `/affiliate/status` and `/status`

---

## 🔄 In-Progress Phases

### Phase 4: Affiliate Link Generation
- **Status**: PARTIALLY COMPLETE
- **Dashboard**: `resources/views/affiliate/dashboard.blade.php`
- **Features Needed**:
  - [ ] Product listing for affiliate to choose
  - [ ] Generate unique affiliate link button
  - [ ] Display generated links with unique code
  - [ ] Copy to clipboard functionality
  - [ ] QR code generator
  - [ ] Link statistics (clicks, conversions)

### Phase 5: Affiliate Dashboard & Stats
- **Status**: PARTIALLY COMPLETE
- **Current Implementation**:
  - Available Earnings display
  - Total Clicks tracking
  - Total Referrals
  - Conversions count
- **Features Needed**:
  - [ ] Complete commission calculations
  - [ ] Top products by sales
  - [ ] Recent sales list
  - [ ] Payment history
  - [ ] Real-time balance updates

---

## ⏳ Not Started Phases

### Phase 6: Commission Tracking Logic
- Calculate 30% commission on each sale through affiliate link
- Track in `AffiliateCommission` table
- Update in real-time on dashboard

### Phase 7: Admin Affiliate Management
- Approve/Reject applications with feedback
- View affiliate performance
- Manage payout records

### Phase 8: Automated Weekly Payouts
- Process payouts every Wednesday at 10 AM
- Send M-Pesa payments
- Create payout records
- Send email notifications

### Phase 9: Testing & Deployment
- End-to-end testing
- Production deployment

---

## 📊 Database Tables Overview

### AffiliateApplication
```sql
CREATE TABLE affiliate_applications (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    application_data JSON,
    status ENUM('pending', 'approved', 'rejected'),
    admin_feedback TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Affiliate
```sql
CREATE TABLE affiliates (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    code VARCHAR(32) UNIQUE,
    approved_at TIMESTAMP,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### AffiliateLink
```sql
CREATE TABLE affiliate_links (
    id BIGINT PRIMARY KEY,
    affiliate_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    unique_code VARCHAR(32) UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### AffiliateCommission
```sql
CREATE TABLE affiliate_commissions (
    id BIGINT PRIMARY KEY,
    affiliate_id BIGINT NOT NULL,
    order_id BIGINT,
    amount DECIMAL(10, 2),
    status ENUM('pending', 'paid', 'cancelled'),
    payout_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Payout
```sql
CREATE TABLE payouts (
    id BIGINT PRIMARY KEY,
    affiliate_id BIGINT NOT NULL,
    amount DECIMAL(10, 2),
    payment_method VARCHAR(50),
    phone_number VARCHAR(20),
    status ENUM('pending', 'sent', 'failed', 'cancelled'),
    mpesa_response JSON,
    sent_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🚀 Next Steps (Immediate Action Items)

### Week 1: Complete Core Affiliate Features
1. **Phase 4**: Implement affiliate link generation
   - Create route to generate links
   - Display links in dashboard
   - Add copy/QR code features

2. **Phase 5**: Enhance dashboard with real statistics
   - Fix commission calculations
   - Add top products section
   - Add recent sales list

3. **Phase 6**: Implement commission tracking
   - Add affiliate_link_id to Order table
   - Track referrals through links
   - Calculate 30% commission on M-Pesa callback

### Week 2: Admin & Payment Features
4. **Phase 7**: Admin affiliate management UI
   - Application approval interface
   - Performance dashboard
   - Affiliate management

5. **Phase 8**: Weekly automated payouts
   - Create Laravel console command
   - Schedule weekly execution
   - M-Pesa payment integration
   - Email notifications

### Week 3: Testing & Deployment
6. **Phase 9**: Comprehensive testing
   - Test all affiliate features
   - Test payment processing
   - Deploy to production

---

## 📋 Files To Review/Modify

- [x] `resources/views/home.blade.php` - CTA button exists
- [x] `resources/views/affiliate/apply.blade.php` - Form exists
- [x] `resources/views/affiliate/status.blade.php` - Status page exists
- [ ] `resources/views/affiliate/dashboard.blade.php` - Enhance stats
- [ ] `app/Http/Controllers/AffiliateController.php` - Add link generation
- [ ] `app/Http/Controllers/MpesaController.php` - Add commission tracking
- [ ] `app/Models/Affiliate.php` - Add stat methods
- [ ] `app/Console/Commands/ProcessAffiliatePayouts.php` - Create
- [ ] Database migrations - May need adjustments

---

## 🔗 Key Routes

### Public Routes
- `GET /affiliate/apply` - Application form
- `POST /affiliate/apply` - Submit application
- `GET /ref/{code}` - Affiliate tracking link

### Authenticated Routes
- `GET /affiliate/status` - Application status
- `GET /affiliate/dashboard` - Affiliate dashboard
- `POST /affiliate/link/create` - Generate link
- `DELETE /affiliate/link/{id}` - Delete link
- `POST /affiliate/withdraw/send-otp` - Initiate withdrawal
- `POST /affiliate/withdraw/verify-otp` - Verify OTP
- `POST /affiliate/withdraw` - Request withdrawal

### Admin Routes
- `GET /admin/affiliate/applications` - List applications
- `PATCH /admin/affiliate/applications/{id}/approve` - Approve
- `PATCH /admin/affiliate/applications/{id}/reject` - Reject
- `GET /admin/affiliate/performance` - Performance dashboard

---

## 📧 Email Templates Needed

1. **Application Received**: Confirm submission
2. **Application Approved**: Congratulations, welcome to dashboard
3. **Application Rejected**: Rejection reason
4. **Weekly Payout**: Payment sent notification
5. **Withdrawal Request**: Confirmation

---

## 🎯 Commission Logic

```
When order paid via affiliate link:
├─ Get affiliate from link
├─ Calculate: commission = order_amount × 0.30
├─ Create AffiliateCommission record
│  └─ status: 'pending'
└─ On every Wednesday @ 10 AM:
   ├─ Sum all pending commissions per affiliate
   ├─ Create Payout record
   ├─ Send M-Pesa payment
   ├─ Update commission status: 'paid'
   └─ Send email notification
```

---

## 💡 Implementation Recommendations

1. **Commission Tracking**: Modify M-Pesa callback to check for affiliate links
2. **Link Generation**: Use `Str::random(32)` for unique codes
3. **Performance**: Add indexing on affiliate_id, user_id for queries
4. **Security**: Validate affiliate codes and prevent manipulation
5. **Testing**: Use sandbox M-Pesa for payment testing before production

