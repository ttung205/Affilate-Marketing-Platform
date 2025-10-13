<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fraud Detection Settings
    |--------------------------------------------------------------------------
    |
    | Configure fraud detection thresholds and rules for the affiliate system.
    |
    */

    // Rate Limiting
    'rate_limits' => [
        'max_clicks_per_ip_per_hour' => env('FRAUD_MAX_CLICKS_PER_HOUR', 10),
        'max_clicks_per_ip_per_day' => env('FRAUD_MAX_CLICKS_PER_DAY', 50),
        'max_clicks_per_link_per_ip_per_day' => env('FRAUD_MAX_CLICKS_PER_LINK_PER_DAY', 3),
    ],

    // Risk Scores (threshold for blocking)
    'risk_scores' => [
        'fraud_threshold' => 50, // Score >= 50 is considered fraud
        'auto_block_threshold' => 100, // Score >= 100 auto-blocks IP
        'suspicious_threshold' => 30, // Score >= 30 flags as suspicious
    ],

    // Bot Detection
    'bot_detection' => [
        'enabled' => env('FRAUD_BOT_DETECTION_ENABLED', true),
        
        // Known bot patterns (will be blocked)
        'bot_patterns' => [
            'bot', 'crawl', 'spider', 'scraper', 'curl', 'wget', 'python',
            'java', 'perl', 'ruby', 'php', 'scrape', 'harvest', 'extract',
            'archiver', 'validator', 'monitor', 'checker', 'scan', 'headless'
        ],
        
        // Legitimate bots (will be allowed for SEO)
        'legitimate_bots' => [
            'googlebot', 'bingbot', 'slackbot', 'twitterbot', 'facebookexternalhit',
            'linkedinbot', 'whatsapp', 'telegrambot', 'applebot', 'yandexbot'
        ],
        
        // Minimum user agent length
        'min_user_agent_length' => 20,
    ],

    // IP Blocking
    'ip_blocking' => [
        'enabled' => env('FRAUD_IP_BLOCKING_ENABLED', true),
        'block_duration_days' => 30, // How long to block an IP
        'manual_whitelist' => env('FRAUD_IP_WHITELIST', ''), // Comma-separated IPs
        'manual_blacklist' => env('FRAUD_IP_BLACKLIST', ''), // Comma-separated IPs
    ],

    // Publisher Self-Clicking Detection
    'self_click_detection' => [
        'enabled' => env('FRAUD_SELF_CLICK_DETECTION_ENABLED', true),
        'ip_lookback_days' => 7, // Check publisher's IPs from last N days
    ],

    // Rapid Click Detection
    'rapid_click_detection' => [
        'enabled' => env('FRAUD_RAPID_CLICK_DETECTION_ENABLED', true),
        'minimum_seconds_between_clicks' => 2, // Clicks faster than this are suspicious
    ],

    // Fraud Logging
    'logging' => [
        'enabled' => env('FRAUD_LOGGING_ENABLED', true),
        'log_channel' => env('FRAUD_LOG_CHANNEL', 'daily'), // Laravel log channel
        'retention_days' => 90, // Keep fraud logs for N days
    ],

    // Notifications
    'notifications' => [
        'enabled' => env('FRAUD_NOTIFICATIONS_ENABLED', true),
        'notify_admin_on_high_risk' => true, // Notify admin when risk >= 80
        'notify_admin_on_ip_block' => true,
        'admin_email' => env('FRAUD_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
    ],

    // Advanced Settings
    'advanced' => [
        // Enable fingerprint tracking (browser fingerprinting)
        'fingerprint_tracking' => env('FRAUD_FINGERPRINT_TRACKING', false),
        
        // Enable geolocation checking
        'geolocation_checking' => env('FRAUD_GEOLOCATION_CHECKING', false),
        
        // Enable VPN/Proxy detection
        'vpn_detection' => env('FRAUD_VPN_DETECTION', false),
        
        // Cache TTL for fraud detection data
        'cache_ttl_minutes' => 60,
    ],

];

