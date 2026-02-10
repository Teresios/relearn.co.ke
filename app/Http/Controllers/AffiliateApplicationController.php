<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\AffiliateWelcomeMail;

class AffiliateApplicationController extends Controller
{
    // No middleware - allow both guests and authenticated users
    
    /**
     * Show the affiliate application form to any user (guest or authenticated).
     * Prevents duplicate applications for authenticated users.
     */
    public function create()
    {
        // For authenticated users, prevent re-application if already an affiliate
        if (Auth::check()) {
            $existing = AffiliateApplication::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->first();

            if ($existing) {
                return redirect()->route('affiliate.status');
            }
        }

        return view('affiliate.apply');
    }

    /**
     * Handle the submission of the affiliate application.
     * Works for both authenticated and guest users.
     */
    public function store(Request $request)
    {
        $isGuest = !Auth::check();

        // Validate all fields - simplified form
        $rules = [
            'county' => 'required|string|max:60',
            'payment_details' => 'required|string|max:9|regex:/^[0-9]{9}$/',
        ];

        // For guests, validate personal info; for authenticated users it comes from their account
        if ($isGuest) {
            $rules['full_name'] = 'required|string|max:120';
            $rules['email'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);

        // For authenticated users, prevent re-application if already an affiliate
        if (Auth::check()) {
            $existing = AffiliateApplication::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->first();

            if ($existing) {
                return redirect()->route('affiliate.status');
            }
        } else {
            // For guests, check if email already exists in users table
            $existingUser = \App\Models\User::where('email', $validated['email'])->first();
            if ($existingUser) {
                return redirect()->back()
                    ->with('error', 'This email is already registered. Please log in to access your affiliate dashboard.');
            }

            // Prevent duplicate applications from same email within 24 hours
            $recentApplication = AffiliateApplication::where('application_data->email', $validated['email'])
                ->where('created_at', '>=', now()->subDay())
                ->first();

            if ($recentApplication) {
                return redirect()->back()
                    ->with('error', 'You already have an affiliate application. Check your email for your unique dashboard link.');
            }
        }

        // Format phone number with country code
        $formattedPhone = '254' . $validated['payment_details'];

        // Build application data
        if ($isGuest) {
            $application_data = [
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'county' => $validated['county'],
                'payment_details' => $formattedPhone,
            ];
        } else {
            // For authenticated users, use their account details
            $user = Auth::user();
            $application_data = [
                'full_name' => $user->name,
                'email' => $user->email,
                'county' => $validated['county'],
                'payment_details' => $formattedPhone,
            ];
        }

        // Generate setup token
        $setupToken = Str::random(64);
        $email = $isGuest ? $validated['email'] : Auth::user()->email;
        $fullName = $isGuest ? $validated['full_name'] : Auth::user()->name;

        $application = AffiliateApplication::create([
            'user_id' => Auth::id() ?? null,  // null for guest users
            'application_data' => json_encode($application_data),
            'status' => 'approved',  // Instant approval
            'setup_token' => $setupToken,
            'setup_token_expires_at' => now()->addDays(7),  // Token expires in 7 days
            'credentials_set' => false,
        ]);

        // Send welcome email with setup link
        $setupLink = route('affiliate.setup.show', $setupToken);
        Mail::to($email)->send(new AffiliateWelcomeMail($email, $fullName, $setupLink));

        if ($isGuest) {
            return redirect()->route('affiliate.status.guest', ['email' => $email])
                ->with('success', 'Congratulations! Your affiliate application has been accepted. Check your email for setup instructions.');
        } else {
            return redirect()->route('affiliate.status')
                ->with('success', 'Congratulations! You are now an approved affiliate. Check your email for setup instructions.');
        }
    }

    /**
     * Show status page for authenticated users' affiliate applications.
     */
    public function status()
    {
        $user = Auth::user();

        // Determine if the user is an affiliate (either via user record or approved application)
        $isAffiliate = false;
        // If the user model has an is_affiliate property, use it. Otherwise, check approved applications.
        if (isset($user->is_affiliate)) {
            $isAffiliate = (bool) $user->is_affiliate;
        } else {
            $isAffiliate = AffiliateApplication::where('user_id', $user->id)
                ->where('status', 'approved')
                ->exists();
        }

        return view('affiliate.status', [
            'isAffiliate' => $isAffiliate,
        ]);
    }

    /**
     * Show status page for guest affiliate applications.
     */
    public function statusGuest(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('products.index');
        }

        return view('affiliate.status-guest', [
            'email' => $email,
        ]);
    }

    /**
     * Show the setup credentials form for a new affiliate (guest or authenticated).
     */
    public function setupShow($token)
    {
        \Log::info('Setup attempt', [
            'token' => $token,
            'token_length' => strlen($token),
        ]);

        // Find the application with this token
        $application = AffiliateApplication::where('setup_token', $token)
            ->where('credentials_set', false)
            ->first();

        // Debug logging
        \Log::info('Application query result', [
            'found' => $application ? true : false,
            'token_searched' => $token,
        ]);

        if (!$application) {
            // Try to find ANY record with this token to debug
            $anyRecord = AffiliateApplication::where('setup_token', $token)->first();
            \Log::warning('Setup link not found', [
                'token' => $token,
                'any_record_exists' => $anyRecord ? true : false,
                'record_credentials_set' => $anyRecord ? $anyRecord->credentials_set : null,
            ]);

            return redirect()->route('products.index')
                ->with('error', 'Setup link expired or invalid. Please apply for affiliate again.');
        }

        // Check if token has expired
        if ($application->setup_token_expires_at) {
            $expiresAt = $application->setup_token_expires_at;
            
            // Convert to Carbon if it's a string
            if (is_string($expiresAt)) {
                $expiresAt = \Carbon\Carbon::parse($expiresAt);
            }
            
            if ($expiresAt->isPast()) {
                \Log::warning('Setup link expired', [
                    'token' => $token,
                    'expires_at' => $application->setup_token_expires_at,
                ]);

                return redirect()->route('products.index')
                    ->with('error', 'Setup link has expired. Please apply for affiliate again.');
            }
        }

        $applicationData = json_decode($application->application_data, true);
        $email = $applicationData['email'] ?? null;
        $fullName = $applicationData['full_name'] ?? null;

        return view('affiliate.setup-credentials', [
            'token' => $token,
            'email' => $email,
            'fullName' => $fullName,
        ]);
    }

    public function setupStore(Request $request, $token)
    {
        \Log::info('=== setupStore() method called ===', ['token' => substr($token, 0, 10) . '...']);
        
        // Find the application
        $application = AffiliateApplication::where('setup_token', $token)
            ->where('credentials_set', false)
            ->first();

        if (!$application) {
            return redirect()->route('products.index')
                ->with('error', 'Setup link expired or invalid.');
        }

        // Check if token has expired
        if ($application->setup_token_expires_at) {
            $expiresAt = $application->setup_token_expires_at;
            
            // Convert to Carbon if it's a string
            if (is_string($expiresAt)) {
                $expiresAt = \Carbon\Carbon::parse($expiresAt);
            }
            
            if ($expiresAt->isPast()) {
                return redirect()->route('products.index')
                    ->with('error', 'Setup link has expired. Please apply for affiliate again.');
            }
        }

        // Validate password
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $applicationData = json_decode($application->application_data, true);
        $email = $applicationData['email'];
        $fullName = $applicationData['full_name'];

        \Log::info('Processing setup for user', ['email' => $email, 'fullName' => $fullName, 'user_id_on_app' => $application->user_id]);

        // If this is a guest application (user_id is null), create a new user
        if (!$application->user_id) {
            \Log::info('Guest application - creating user account');
            
            // Check if user with this email already exists
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                \Log::info('User does not exist - creating new user', ['email' => $email]);
                
                $user = \App\Models\User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => bcrypt($validated['password']),
                    'is_affiliate' => true,
                    'email_verified_at' => now(),  // Auto-verify email
                ]);
                
                \Log::info('User created successfully', ['user_id' => $user->id, 'email' => $email]);
            } else {
                \Log::info('User already exists - updating', ['user_id' => $user->id, 'email' => $email]);
                
                // User exists but not affiliated, update their password and affiliate status
                $user->update([
                    'password' => bcrypt($validated['password']),
                    'is_affiliate' => true,
                ]);
            }

            $application->update([
                'user_id' => $user->id,
                'credentials_set' => true,
                'setup_token' => null,  // Invalidate token
            ]);
        } else {
            \Log::info('Authenticated application - updating existing user');
            
            // Authenticated user - just update their password
            $user = Auth::user();
            $user->update([
                'password' => bcrypt($validated['password']),
                'is_affiliate' => true,
            ]);

            $application->update([
                'credentials_set' => true,
                'setup_token' => null,  // Invalidate token
            ]);
        }

        \Log::info('About to create Affiliate record', ['user_id' => $user->id, 'user_email' => $user->email]);

        // Create Affiliate record if it doesn't exist
        $affiliate = \App\Models\Affiliate::where('user_id', $user->id)->first();
        if (!$affiliate) {
            \Log::info('No existing Affiliate record found - creating one');
            
            try {
                $newAffiliate = \App\Models\Affiliate::create([
                    'user_id' => $user->id,
                    'active' => true,
                    'approved_at' => now(),
                ]);
                
                \Log::info('✓ Affiliate record created successfully', [
                    'affiliate_id' => $newAffiliate->id,
                    'user_id' => $user->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('✗ Failed to create affiliate record', [
                    'user_id' => $user->id,
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                ]);
                throw $e;  // Re-throw so we see the error
            }
        } else {
            \Log::info('Affiliate record already exists', [
                'affiliate_id' => $affiliate->id,
                'user_id' => $user->id,
            ]);
        }

        \Log::info('Logging user in and redirecting to dashboard', ['user_id' => $user->id]);

        // Log the user in (for guests, they now have an account)
        Auth::login($user);

        return redirect()->route('affiliate.dashboard')
            ->with('success', 'Your affiliate account is set up! Welcome to your dashboard.');
    }
}