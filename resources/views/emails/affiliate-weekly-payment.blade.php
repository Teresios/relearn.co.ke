<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header-warning { background-color: #ff9800; color: white; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
        .header-warning h1 { margin: 0; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table td, table th { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background-color: #f8f9fa; font-weight: bold; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .section h3 { color: #333; }
        .button { display: inline-block; padding: 12px 24px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .button:hover { background-color: #218838; }
        .button-secondary { background-color: #ff9800; }
        .button-secondary:hover { background-color: #e68900; }
        .footer { background-color: #f8f9fa; padding: 20px; border-radius: 4px; font-size: 12px; color: #666; margin-top: 30px; text-align: center; }
        .badge { display: inline-block; padding: 4px 8px; background-color: #d4edda; color: #155724; border-radius: 3px; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
        .encouragement-box { background-color: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .encouragement-box h3 { color: #ff9800; margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        @if($paymentData['is_zero_commission'] ?? false)
            {{-- Zero Commission Header --}}
            <div class="header-warning">
                <h1>📊 Your Weekly Affiliate Activity Report</h1>
            </div>

            <p>Hello <strong>{{ $affiliate->user->name }}</strong>,</p>

            <p>Here's your weekly affiliate activity report. While you haven't earned any commission this week, every click and referral counts!</p>

            <div class="encouragement-box">
                <h3>💪 Keep Up the Great Work!</h3>
                <p>No commission this week? Don't worry! Building a successful affiliate presence takes time and effort. Here are some ways to boost your earnings:</p>
                <ul>
                    <li><strong>Be more active:</strong> Share your affiliate links across your networks daily</li>
                    <li><strong>Quality over quantity:</strong> Share links with people who genuinely need the products</li>
                    <li><strong>Multiple platforms:</strong> Use WhatsApp, email, social media, and forums to reach more people</li>
                    <li><strong>Provide value:</strong> Explain why the product is valuable and how it can help your audience</li>
                    <li><strong>Follow up:</strong> Remind your contacts about the products regularly</li>
                    <li><strong>Track performance:</strong> Check your dashboard to see which links are working best</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 20px 0; padding: 20px; background-color: #f0f8f4; border-radius: 4px;">
                <a href="{{ route('affiliate.dashboard') }}" class="button" style="display: inline-block; padding: 15px 40px; font-size: 16px; font-weight: bold; margin: 0 10px;">📊 View Your Dashboard</a>
                <a href="https://chat.whatsapp.com/DLX7QB56K85BhMljBn5CtQ" class="button button-secondary" style="display: inline-block; padding: 15px 40px; font-size: 16px; font-weight: bold; margin: 0 10px;">💬 Join Our WhatsApp Group</a>
            </div>
        @else
            {{-- Positive Commission Header --}}
            <div class="header">
                <h1>💰 Your Weekly Affiliate Earnings Payment</h1>
            </div>

            <p>Hello <strong>{{ $affiliate->user->name }}</strong>,</p>

            <p>Your affiliate earnings have been <strong>paid!</strong> Commission earned: <strong style="color: #28a745; font-size: 18px;">Ksh {{ number_format($paymentData['commission_earned'] ?? 0, 2) }}</strong></p>

            <div style="text-align: center; margin: 20px 0; padding: 20px; background-color: #f0f8f4; border-radius: 4px;">
                <a href="{{ route('affiliate.dashboard') }}" class="button" style="display: inline-block; padding: 15px 40px; font-size: 16px; font-weight: bold; margin: 0 10px;">📊 Dashboard</a>
                <a href="https://chat.whatsapp.com/DLX7QB56K85BhMljBn5CtQ" class="button" style="display: inline-block; padding: 15px 40px; font-size: 16px; font-weight: bold; background-color: #25D366; margin: 0 10px;">💬 WhatsApp</a>
            </div>
        @endif

        <div class="section">
            <h2>📊 Weekly Statistics</h2>
            <table>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td><strong>Week Period</strong></td>
                    <td>{{ $paymentData['week_start'] }} to {{ $paymentData['week_end'] }}</td>
                </tr>
                <tr>
                    <td><strong>Affiliate Code</strong></td>
                    <td><code>{{ $affiliate->code }}</code></td>
                </tr>
                <tr>
                    <td><strong>Links Generated</strong></td>
                    <td>{{ $paymentData['links_generated'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Total Clicks This Week</strong></td>
                    <td>{{ $paymentData['total_clicks_this_week'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Total Sales</strong></td>
                    <td>{{ $paymentData['total_sales'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Products Sold</strong></td>
                    <td>{{ $paymentData['products_sold'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Conversion Rate</strong></td>
                    <td>{{ $paymentData['conversion_rate'] ?? 0 }}%</td>
                </tr>
                <tr>
                    <td><strong>Total Conversion Value</strong></td>
                    <td>Ksh {{ number_format($paymentData['total_conversion_value'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Commission Earned</strong></td>
                    <td><strong style="color: #28a745;">Ksh {{ number_format($paymentData['commission_earned'] ?? 0, 2) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Payment Status</strong></td>
                    <td>
                        @if($paymentData['commission_earned'] > 0)
                            <span class="badge">✅ Paid</span>
                        @else
                            <span class="badge badge-warning">⏳ No Sales This Week</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if(isset($paymentData['products_detail']) && count($paymentData['products_detail']) > 0)
            <div class="section">
                <h2>🛍️ Products Sold This Week</h2>
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                    </tr>
                    @foreach($paymentData['products_detail'] as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['count'] }}</td>
                            <td>Ksh {{ number_format($product['price'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
        </div>

        <div class="footer">
            <p>Thank you for being part of our affiliate program! Keep up the great work! 🎉</p>
            <p>If you have any questions about your earnings or need support, please don't hesitate to reach out to us.</p>
            <p style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px;">
                <strong>{{ config('app.name') }} Team</strong><br>
                P.S. Make sure your affiliate links are up to date and shared actively for maximum conversions!
            </p>
        </div>
    </div>
</body>
</html>
