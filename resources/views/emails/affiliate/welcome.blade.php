@component('mail::message')
# Welcome to Relearn Affiliate Program! 🎉

Hello {{ $fullName }},

Congratulations! Your application to join the **Relearn Affiliate Program** has been **instantly approved**!

We're excited to have you on board. You're now ready to start earning **30% commissions** on every book sale you refer.

## Set Up Your Login

To get started, please click the link below to set up your login credentials:

@component('mail::button', ['url' => $setupLink, 'color' => 'success'])
Set Up Your Login
@endcomponent

This link is unique to your application and will expire in **24 hours**. Make sure to set up your credentials as soon as possible to access your affiliate dashboard.

## What You Can Do Now

Once logged in, you'll be able to:
- **Generate unlimited affiliate links** for any product in our catalog
- **Track clicks and conversions** in real-time
- **Monitor your earnings** from referrals
- **Access your dashboard** to manage your affiliate links
- **Receive automatic payouts** every Wednesday via M-Pesa

## Your Affiliate Email
**{{ $email }}**

This is the email address associated with your affiliate account. You'll use this to log in to your dashboard.

## Need Help?

If you have any questions or need support, don't hesitate to reach out to our team at **support@relearn.co.ke**.

We're here to help you succeed! 

Happy earning! 💰

@component('mail::subcopy')
If you're having trouble clicking the "Set Up Your Login" button, copy and paste this URL into your web browser:
{{ $setupLink }}
@endcomponent

Thanks,<br>
The Relearn Team
@endcomponent
