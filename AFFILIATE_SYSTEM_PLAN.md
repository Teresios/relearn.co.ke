# Affiliate System Implementation Plan

## 📋 Overview
Complete implementation of a comprehensive affiliate marketing system with application process, commission tracking, link generation, and automated weekly payouts.

---

## 🎯 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    LANDING PAGE                              │
│  [Apply as Affiliate Button] → Affiliate Application Form    │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│            USER AUTHENTICATION & STATUS                      │
│  [Login] → [Check Application Status] → Status Page         │
└──────────────────┬──────────────────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
        ▼                     ▼
   REJECTED              APPROVED
   [Show Reason]         ▼
                    ┌─────────────────────┐
                    │ AFFILIATE DASHBOARD │
                    ├─────────────────────┤
                    │ • Commission Balance│
                    │ • Unique Links Gen. │
                    │ • Performance Stats │
                    │ • Payment History   │
                    └─────────────────────┘
                         │
        ┌────────────────┴────────────────┐
        ▼                                  ▼
   [Generate Links]          [View Commission]
   /affiliate/dashboard      • Real-time balance
                             • Per-product earnings
                             • Non-withdrawable
                                  │
                                  ▼
                    ┌──────────────────────┐
                    │ AUTOMATED PAYMENTS   │
                    ├──────────────────────┤
                    │ Every Wednesday:     │
                    │ • Calculate balance  │
                    │ • Send M-Pesa payment│
                    │ • Create payout log  │
                    │ • Notify affiliate   │
                    └──────────────────────┘
```

---

## 📊 Database Schema (Already Exists)

### AffiliateApplication Table
```sql
- id
- user_id (FK)
- application_data (JSON: name, email, phone, mpesa_number, bank_details, bio, social_links)
- status (pending, approved, rejected)
- admin_feedback (nullable text)
- created_at, updated_at
```

### Affiliate Table
```sql
- id
- user_id (FK, unique)
- code (unique referral code)
- approved_at (timestamp)
- active (boolean)
- created_at, updated_at
```

### AffiliateLink Table
```sql
- id
- affiliate_id (FK)
- product_id (FK)
- unique_code (unique tracking code)
- created_at, updated_at
```

### AffiliateCommission Table
```sql
- id
- affiliate_id (FK)
- order_id (FK, nullable)
- amount (commission amount - 30% of sale)
- status (pending, paid, cancelled)
- payout_id (FK, nullable - which payout this belongs to)
- created_at, updated_at
```

### Payout Table
```sql
- id
- affiliate_id (FK)
- amount (total payout)
- payment_method (mpesa)
- phone_number (target mpesa number)
- status (pending, sent, failed, cancelled)
- mpesa_response (JSON - API response)
- sent_at (when payment was sent)
- created_at, updated_at
```

---

## 🔧 Implementation Phases

### Phase 1: Landing Page CTA Button
**Files to modify:**
- `resources/views/welcome.blade.php` or `resources/views/home/index.blade.php`

**Changes:**
- Add "Become an Affiliate" button in header/navigation
- Add affiliate section in hero or above footer
- Link to `/affiliate/apply` route

---

### Phase 2: Affiliate Application Form
**Files to create/modify:**
- `app/Http/Controllers/AffiliateApplicationController.php` ✅ (exists)
- `resources/views/affiliate/apply.blade.php` (create)
- Route: `POST /affiliate/apply`

**Form Fields:**
- Full Name
- Email (prefilled if authenticated)
- Phone Number
- M-Pesa Number (for payouts)
- Bank Details (optional)
- Why are you interested in this? (Bio/motivation)
- Social Media Links (Twitter, Facebook, YouTube, TikTok)
- Terms & Conditions Acceptance
- Submit Button

**Validation:**
- Phone: Valid Kenya format (254/0/7...)
- M-Pesa: Valid format
- Email: Unique, valid email
- Name: Min 3 characters

---

### Phase 3: Application Status Page
**Files to create/modify:**
- `resources/views/affiliate/status.blade.php` (create)
- `app/Http/Controllers/AffiliateApplicationController.php` → `status()` method
- Route: `GET /status` or `GET /affiliate/status`

**Display:**
- Current Status: Pending / Approved / Rejected
- If Approved:
  - ✅ Button to go to Dashboard
  - Show affiliate code
- If Rejected:
  - Show rejection reason
  - Option to reapply after 30 days
- If Pending:
  - "We're reviewing your application" message
  - Timeline: "Usually reviewed within 24-48 hours"

---

### Phase 4: Affiliate Link Generation
**Files to create/modify:**
- `resources/views/affiliate/dashboard.blade.php` → Add link generation section
- `app/Http/Controllers/AffiliateController.php` → `createLink()` method
- `app/Models/AffiliateLink.php` (update relationships)
- Route: `POST /affiliate/link/create`

**Feature:**
- Show all products available for promotion
- For each product: Button to "Generate Affiliate Link"
- Display generated links with:
  - Unique code (e.g., ABC123XYZ)
  - Full URL: `https://relearn.co.ke/ref/ABC123XYZ?product_id=23`
  - Copy to clipboard button
  - QR code generator
  - Created date
  - Clicks count (from analytics)
  - Sales count (from this link)

---

### Phase 5: Affiliate Dashboard & Stats
**Files to create/modify:**
- `resources/views/affiliate/dashboard.blade.php` (expand/create)
- `app/Http/Controllers/AffiliateController.php` → `dashboard()` method
- `app/Models/Affiliate.php` (add stats methods)

**Dashboard Sections:**
```
┌─────────────────────────────────────────┐
│     AFFILIATE DASHBOARD                 │
├─────────────────────────────────────────┤
│                                         │
│  ┌──────────┐  ┌──────────┐            │
│  │ Balance  │  │ Referrals│  ┌──────┐ │
│  │ Ksh 15,000│  │    5    │  │Sales │ │
│  └──────────┘  └──────────┘  │  3   │ │
│                              └──────┘ │
├─────────────────────────────────────────┤
│  THIS MONTH                             │
│  • Commission: Ksh 12,000               │
│  • New Referrals: 4                     │
│  • Conversion Rate: 60%                 │
├─────────────────────────────────────────┤
│  TOP PRODUCTS                           │
│  1. The Monk Who Sold His Ferrari (5)   │
│  2. Atomic Habits (2)                   │
│  3. Grit (1)                            │
├─────────────────────────────────────────┤
│  RECENT SALES                           │
│  Date | Product | Amount | Commission  │
│  ...                                    │
├─────────────────────────────────────────┤
│  PAYMENT HISTORY                        │
│  Date | Amount | Status | M-Pesa       │
│  ...                                    │
└─────────────────────────────────────────┘
```

**Key Metrics:**
- Current Balance: Non-withdrawable, display only
- Total Commission Earned (all-time)
- Total Referrals
- Active Links
- Conversion Rate
- Top Products
- Recent Sales
- Payment History

---

### Phase 6: Commission Tracking
**Files to modify:**
- `app/Http/Controllers/MpesaController.php` → In callback handling
- `app/Services/MpesaService.php`
- `app/Models/AffiliateCommission.php`

**Logic:**
1. When order created via affiliate link:
   - `Order` table stores `affiliate_link_id`
2. When payment successful (M-Pesa callback):
   - Check if order has `affiliate_link_id`
   - Get affiliate from link
   - Calculate: `commission = (order->amount * 0.30)`
   - Create `AffiliateCommission` record (status: `pending`)
3. Commission tracking:
   - Show in dashboard in real-time
   - Marked as "pending" until paid
   - Updated to "paid" after payout

---

### Phase 7: Admin Affiliate Management
**Files to create/modify:**
- `resources/views/admin/affiliate/applications.blade.php` (create)
- `resources/views/admin/affiliate/performance.blade.php` (create)
- `app/Http/Controllers/Admin/AdminAffiliateApplicationController.php`
- `app/Http/Controllers/Admin/AdminAffiliatePerformanceController.php` (create)

**Admin Features:**
1. **Applications Tab:**
   - List pending applications
   - View application details
   - Approve/Reject with feedback
   - Archive/Delete

2. **Affiliates Tab:**
   - List all approved affiliates
   - View performance stats
   - View commission breakdown
   - Disable/Enable affiliate
   - Manual payout override

3. **Payouts Tab:**
   - View payout history
   - Manual payout creation
   - M-Pesa integration status
   - Retry failed payments

4. **Reports:**
   - Total affiliate commission
   - Top affiliates by sales
   - Pending payouts
   - Monthly trends

---

### Phase 8: Automated Weekly Payouts
**Files to create/modify:**
- `app/Console/Commands/ProcessAffiliatePayouts.php` (create)
- `app/Models/Payout.php` (update)
- `config/schedule.php`

**Logic:**
```php
// Runs every Wednesday at 10 AM
1. Get all approved affiliates
2. For each affiliate:
   a. Calculate unpaid commission sum
   b. If amount > 0:
      - Create Payout record
      - Send M-Pesa payment
      - Mark commissions as "paid"
      - Send notification email
3. Log payout summary
```

**Schedule in `app/Console/Kernel.php`:**
```php
$schedule->command('process:affiliate-payouts')
    ->weeklyOn(3, '10:00'); // Every Wednesday at 10 AM
```

**M-Pesa Integration:**
- Use existing M-Pesa service to send payment
- Phone number from AffiliateApplication
- Create payment record
- Handle failures/retries

---

### Phase 9: Testing & Deployment
**Testing Checklist:**
- [ ] Application form submission and validation
- [ ] Application approval/rejection workflow
- [ ] Application status page
- [ ] Affiliate link generation
- [ ] Link tracking (click counts)
- [ ] Referral tracking through links
- [ ] Commission calculation (30% logic)
- [ ] Dashboard stats accuracy
- [ ] Weekly payout simulation
- [ ] M-Pesa payment integration
- [ ] Payment history display
- [ ] Admin approval flow
- [ ] Admin dashboard views

---

## 🔄 User Flow

### New Affiliate Journey
```
1. User visits landing page
   ↓
2. Clicks "Become an Affiliate" button
   ↓
3. Fills out application form
   ↓
4. Submits application (status: pending)
   ↓
5. Receives confirmation email
   ↓
6. Logs in to check status
   ↓
7. Status shows "Pending - We're reviewing your application"
   ↓
8. Admin approves application
   ↓
9. Affiliate receives approval email with dashboard link
   ↓
10. Logs in to dashboard
    ↓
11. Generates unique affiliate links for products
    ↓
12. Shares links (social media, blog, email, etc.)
    ↓
13. Users click affiliate link → buy product
    ↓
14. Commission calculated (30% of price)
    ↓
15. Commission shows in dashboard (pending)
    ↓
16. Every Wednesday at 10 AM:
    - Payout calculated
    - M-Pesa payment sent
    - Commission marked as paid
    - Email notification sent
```

---

## 📱 Routes Required

### Public Routes
```
GET  / affiliate/apply                    - Show application form
POST / affiliate/apply                    - Submit application
GET  / ref/{code}                         - Redirect with tracking
```

### Authenticated Routes
```
GET  / affiliate/status                   - View application status
GET  / affiliate/dashboard                - Affiliate dashboard
POST / affiliate/link/create              - Generate affiliate link
DELETE /affiliate/link/{id}               - Delete affiliate link
```

### Admin Routes
```
GET  / admin/affiliate/applications       - List applications
GET  / admin/affiliate/applications/{id}  - View application
PATCH / admin/affiliate/applications/{id}/approve   - Approve
PATCH / admin/affiliate/applications/{id}/reject    - Reject
GET  / admin/affiliate/performance        - Performance dashboard
GET  / admin/affiliate/payouts            - Payout history
```

---

## 🚀 Implementation Order

1. **Phase 1**: Add landing page CTA button (5 min)
2. **Phase 2**: Affiliate application form & submission (1-2 hours)
3. **Phase 3**: Application status page (1 hour)
4. **Phase 4**: Affiliate link generation (2 hours)
5. **Phase 5**: Dashboard & stats (2-3 hours)
6. **Phase 6**: Commission tracking in payment flow (1-2 hours)
7. **Phase 7**: Admin management (2-3 hours)
8. **Phase 8**: Automated payouts & scheduling (2 hours)
9. **Phase 9**: Testing & deployment (2+ hours)

**Total Estimated Time**: 14-20 hours of development

---

## 💾 Database Migrations Needed

Check if these exist, create if needed:
- `AffiliateApplication` table
- `Affiliate` table  
- `AffiliateLink` table
- `AffiliateCommission` table
- `Payout` table
- Add columns to `Order` table: `affiliate_link_id`, `referral_source`
- Add columns to `AffiliateCommission` table: `payout_id`, `status`

---

## 🔐 Security Considerations

1. **Unique Codes**: Use `Str::random(32)` for affiliate and link codes
2. **Rate Limiting**: Limit application submissions per user
3. **Validation**: Validate all inputs (phone, M-Pesa, etc.)
4. **Authorization**: Ensure users can only access their own data
5. **Commission Integrity**: Prevent manipulation of commission amounts
6. **Payment Safety**: Validate M-Pesa numbers before payment
7. **Link Hijacking**: Verify affiliate codes match request source

---

## 📧 Email Notifications

1. **Application Submitted**: Confirmation to user
2. **Application Approved**: Dashboard link and getting started guide
3. **Application Rejected**: Reason and option to reapply
4. **Weekly Payout**: Payment sent notification with amount
5. **Link Performance**: Weekly digest of top links (optional)

---

## 📈 Future Enhancements

- Performance bonuses (higher commission for top affiliates)
- Tiered commission structure
- Affiliate referral rewards (affiliate refers affiliate)
- Marketing materials library
- Real-time performance API
- Custom branding options
- Advanced analytics & reports
