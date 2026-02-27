<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ config('app.name', 'Hisab Nikash') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f9fc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --heading: #1e293b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px 16px 60px;
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
            padding: 32px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #eef5ff 100%);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 36px;
            color: var(--heading);
        }

        .header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .content {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 32px 24px;
        }

        h2 {
            color: var(--heading);
            font-size: 24px;
            margin: 32px 0 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border);
        }

        h2:first-child {
            margin-top: 0;
        }

        h3 {
            color: var(--heading);
            font-size: 18px;
            margin: 24px 0 12px;
        }

        p {
            margin: 0 0 16px;
            color: var(--text);
        }

        ul, ol {
            margin: 0 0 16px;
            padding-left: 24px;
        }

        li {
            margin-bottom: 8px;
            color: var(--text);
        }

        strong {
            color: var(--heading);
        }

        .highlight {
            background: #fef3c7;
            padding: 16px;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            margin: 16px 0;
        }

        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 14px;
        }

        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .header h1 {
                font-size: 28px;
            }
            .content {
                padding: 24px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Privacy Policy</h1>
            <p>Last Updated: February 28, 2026</p>
        </div>

        <div class="content">
            <p>
                At <strong>Hisab Nikash</strong>, we are committed to protecting your privacy and ensuring the security of your personal information. 
                This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our mobile application and related services.
            </p>

            <h2>1. Information We Collect</h2>

            <h3>1.1 Personal Information</h3>
            <p>When you register and use Hisab Nikash, we may collect:</p>
            <ul>
                <li><strong>Account Information:</strong> Name, email address, phone number</li>
                <li><strong>Authentication Data:</strong> Password (encrypted), Google account information (if using Google Sign-In)</li>
                <li><strong>Profile Information:</strong> User preferences, subscription plan details</li>
            </ul>

            <h3>1.2 Financial Data</h3>
            <p>The app allows you to manage your personal finances. This data includes:</p>
            <ul>
                <li>Transaction records (income and expenses)</li>
                <li>Client/Customer information you manually enter</li>
                <li>Book (ledger) names and categories</li>
                <li>Financial summaries and reports</li>
            </ul>

            <div class="highlight">
                <strong>Important:</strong> All financial data is stored locally on your device and/or securely on our servers (when using cloud sync). 
                We do NOT share your financial information with third parties for marketing or advertising purposes.
            </div>

            <h3>1.3 Automatically Collected Information</h3>
            <ul>
                <li><strong>Device Information:</strong> Device type, operating system, unique device identifiers</li>
                <li><strong>Usage Data:</strong> App features used, crash reports, performance data</li>
                <li><strong>Log Data:</strong> IP address, access times, app errors</li>
            </ul>

            <h2>2. How We Use Your Information</h2>

            <p>We use the collected information for the following purposes:</p>
            <ul>
                <li><strong>Service Delivery:</strong> To provide and maintain the Hisab Nikash app functionality</li>
                <li><strong>Account Management:</strong> To create and manage your user account</li>
                <li><strong>Cloud Sync:</strong> To synchronize your data across devices (when enabled)</li>
                <li><strong>Customer Support:</strong> To respond to your inquiries and provide technical assistance</li>
                <li><strong>App Improvement:</strong> To analyze usage patterns and improve our services</li>
                <li><strong>Security:</strong> To detect, prevent, and address technical issues and fraudulent activity</li>
                <li><strong>Communication:</strong> To send important updates, notifications, and subscription information</li>
            </ul>

            <h2>3. Data Storage and Security</h2>

            <h3>3.1 Local Storage</h3>
            <p>
                Financial transaction data is primarily stored locally on your device using encrypted local database (Hive). 
                This data remains on your device unless you explicitly enable cloud synchronization.
            </p>

            <h3>3.2 Cloud Storage</h3>
            <p>
                If you choose to enable cloud sync with your account, your data is securely transmitted and stored on our servers 
                hosted at <strong>https://hnapp.protiva.org</strong>. We use industry-standard encryption (HTTPS/TLS) for data transmission.
            </p>

            <h3>3.3 Security Measures</h3>
            <ul>
                <li>End-to-end encryption for data transmission</li>
                <li>Secure authentication using Firebase Authentication</li>
                <li>Regular security audits and updates</li>
                <li>Password hashing and secure storage</li>
                <li>Access controls and user authentication</li>
            </ul>

            <h2>4. Data Sharing and Disclosure</h2>

            <p>We do NOT sell, trade, or rent your personal information to third parties. We may share information only in the following limited circumstances:</p>

            <h3>4.1 Service Providers</h3>
            <ul>
                <li><strong>Firebase (Google):</strong> For authentication and cloud infrastructure</li>
                <li><strong>Google Mobile Ads:</strong> For displaying advertisements (if applicable)</li>
                <li><strong>Cloud Hosting:</strong> For server infrastructure and data storage</li>
            </ul>

            <h3>4.2 Legal Requirements</h3>
            <p>We may disclose your information if required by law or in response to valid legal requests by public authorities.</p>

            <h3>4.3 Business Transfers</h3>
            <p>In the event of a merger, acquisition, or asset sale, your information may be transferred. We will provide notice before your data is transferred.</p>

            <h2>5. Third-Party Services</h2>

            <p>Our app integrates with the following third-party services:</p>

            <h3>5.1 Firebase Authentication</h3>
            <p>
                We use Firebase Authentication for secure user login. Please review 
                <a href="https://firebase.google.com/support/privacy" target="_blank">Google's Privacy Policy</a> for more information.
            </p>

            <h3>5.2 Google Sign-In</h3>
            <p>
                If you choose to sign in with Google, we receive basic profile information (name, email, profile picture) as permitted by Google's OAuth system.
            </p>

            <h3>5.3 Google Mobile Ads</h3>
            <p>
                We may display advertisements using Google AdMob. Google may collect device advertising identifiers and other information. 
                Review <a href="https://policies.google.com/privacy" target="_blank">Google's Advertising Privacy Policy</a> for details.
            </p>

            <h2>6. Your Privacy Rights</h2>

            <p>You have the following rights regarding your personal data:</p>

            <ul>
                <li><strong>Access:</strong> Request a copy of the personal data we hold about you</li>
                <li><strong>Correction:</strong> Request corrections to inaccurate or incomplete data</li>
                <li><strong>Deletion:</strong> Request deletion of your account and associated data</li>
                <li><strong>Data Portability:</strong> Request export of your data in a machine-readable format</li>
                <li><strong>Opt-Out:</strong> Disable cloud sync and use the app in offline mode</li>
                <li><strong>Marketing Communications:</strong> Unsubscribe from promotional emails</li>
            </ul>

            <p><strong>To exercise these rights, please contact us at:</strong> <a href="mailto:support@hisabnikash.app">support@hisabnikash.app</a></p>

            <h2>7. Data Retention</h2>

            <ul>
                <li><strong>Active Accounts:</strong> We retain your data as long as your account is active</li>
                <li><strong>Account Deletion:</strong> Upon request, we will delete your account and data within 30 days</li>
                <li><strong>Backup Data:</strong> Backup copies may be retained for up to 90 days for disaster recovery purposes</li>
                <li><strong>Legal Obligations:</strong> We may retain certain data as required by law or for legitimate business purposes</li>
            </ul>

            <h2>8. Children's Privacy</h2>

            <p>
                Hisab Nikash is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13. 
                If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.
            </p>

            <h2>9. International Data Transfers</h2>

            <p>
                Your data may be transferred to and stored on servers located outside your country of residence. 
                By using Hisab Nikash, you consent to the transfer of your information to our servers and facilities.
            </p>

            <h2>10. Cookies and Tracking Technologies</h2>

            <p>
                Our mobile app does not use cookies. However, our web services may use cookies for authentication and session management. 
                Third-party services (like Google Ads) may use tracking technologies in accordance with their own privacy policies.
            </p>

            <h2>11. Changes to This Privacy Policy</h2>

            <p>
                We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page 
                and updating the "Last Updated" date. We encourage you to review this Privacy Policy periodically.
            </p>

            <h2>12. Contact Us</h2>

            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:</p>

            <ul>
                <li><strong>Email:</strong> <a href="mailto:support@hisabnikash.app">support@hisabnikash.app</a></li>
                <li><strong>Developer Email:</strong> <a href="mailto:m1s3rys1gn4l@gmail.com">m1s3rys1gn4l@gmail.com</a></li>
                <li><strong>Phone:</strong> +8801842566315</li>
                <li><strong>WhatsApp:</strong> +8801842566315</li>
                <li><strong>Website:</strong> <a href="https://hnapp.protiva.org" target="_blank">https://hnapp.protiva.org</a></li>
            </ul>

            <h2>13. Offline Mode</h2>

            <p>
                Hisab Nikash offers an <strong>Offline Mode</strong> where all your data is stored exclusively on your device. 
                In offline mode, no data is transmitted to our servers, and you can use the app completely independently.
            </p>

            <h2>14. Data Deletion Request</h2>

            <p>To request deletion of your account and all associated data:</p>
            <ol>
                <li>Send an email to <a href="mailto:support@hisabnikash.app">support@hisabnikash.app</a> with subject "Data Deletion Request"</li>
                <li>Include your registered email address and phone number for verification</li>
                <li>We will process your request within 30 days</li>
                <li>You will receive a confirmation email once your data has been deleted</li>
            </ol>

            <h2>15. Consent</h2>

            <p>
                By using Hisab Nikash, you consent to this Privacy Policy and agree to its terms. 
                If you do not agree with this policy, please discontinue use of the app.
            </p>

            <div class="footer">
                <p><strong>Hisab Nikash - Simple. Secure. Smart Finance Tracking.</strong></p>
                <p>© 2025-2026 Hisab Nikash. All rights reserved.</p>
                <p>Developed by Sohanur Rahman</p>
                <a href="/" class="back-link">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
