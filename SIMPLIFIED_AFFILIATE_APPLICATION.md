# Simplified Affiliate Application Form - Update Summary

## Changes Made

### 1. **Updated Application Form** (`resources/views/affiliate/apply.blade.php`)
- **Removed Fields:**
  - Phone Number (separate field)
  - Preferred Mode of Contact dropdown
  - Social Media Handles
  - Short Bio / Motivation
  - How did you hear about us?

- **Kept Fields:**
  - Full Legal Name
  - Email Address
  - County (dropdown)
  - MPESA Phone Number for Payouts

- **Phone Number Format Improvement:**
  - Changed from requiring `254723071290` format
  - Now accepts `0723071290` format (or just `723071290`)
  - Uses input group with `+254` prefix for clarity
  - Limited to 9 digits with regex validation

### 2. **Updated Controller** (`app/Http/Controllers/AffiliateApplicationController.php`)
- Simplified validation rules - only validates:
  - `county` (required, string, max 60)
  - `payment_details` (required, exactly 9 digits, regex)
  - For guests: `full_name`, `email` (additional)

- Phone number handling:
  - Accepts 9-digit format from user
  - Automatically prepends `254` country code before storing
  - Stores as `254723071290` format in database

- Data stored in `application_data` JSON:
  ```json
  {
    "full_name": "John Doe",
    "email": "john@example.com",
    "county": "Nairobi",
    "payment_details": "254723071290"
  }
  ```

### 3. **Updated Admin Views**
- **Show View** (`resources/views/admin/affiliate/applications/show.blade.php`):
  - Displays county field
  - Displays MPESA number from application_data
  - Handles both guest and authenticated user applications

- **Index View** (`resources/views/admin/affiliate/applications/index.blade.php`):
  - Pulls name and email from `application_data` JSON for guest applications
  - Falls back to user record for authenticated applications
  - Properly displays guest applicants in the pending applications list

### 4. **Database Migration** (no schema changes needed)
- Created migration `2026_01_17_000005_simplify_affiliate_application_form.php`
- Documents the data structure change in JSON
- No actual database schema modifications required
- `AffiliateApplication` table structure remains unchanged

## User Flow (Simplified)

### For Guests:
1. Click "Start Your Application"
2. Enter: Full Name, Email, County, MPESA Number (9 digits)
3. Submit form
4. See confirmation with their email

### For Authenticated Users:
1. Click "Apply as Affiliate"
2. Name/Email pre-filled and read-only
3. Enter: County, MPESA Number (9 digits)
4. Submit form
5. See status page

## Database Data Structure

```json
{
  "full_name": "Jane Smith",
  "email": "jane@example.com",
  "county": "Kiambu",
  "payment_details": "254721234567"
}
```

## Benefits

✅ **Simpler user experience** - Only essential fields
✅ **Better phone number UX** - 9-digit format is more familiar in Kenya
✅ **Less data collection** - Reduces user friction in signup
✅ **Maintains consistency** - Single MPESA field for all transactions
✅ **Compatible with existing data** - No migrations needed, backward compatible

## Testing Checklist

- [ ] Guest can apply with 0723071290 format (9 digits)
- [ ] Guest can apply with 723071290 format (without 0)
- [ ] Phone number properly converted to 254... format in database
- [ ] Admin can view guest applications with correct data
- [ ] Admin can view authenticated user applications with correct data
- [ ] Form validation rejects invalid phone numbers
- [ ] Form validation requires all mandatory fields
- [ ] Confirmation page shows correct email

## Files Modified

1. `resources/views/affiliate/apply.blade.php` - Simplified form
2. `app/Http/Controllers/AffiliateApplicationController.php` - Updated validation
3. `resources/views/admin/affiliate/applications/show.blade.php` - Admin details view
4. `resources/views/admin/affiliate/applications/index.blade.php` - Admin list view
5. `database/migrations/2026_01_17_000005_simplify_affiliate_application_form.php` - Documentation
