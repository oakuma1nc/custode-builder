CREATE DATABASE IF NOT EXISTS custode_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE custode_builder;

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    business_name VARCHAR(255) NOT NULL,
    business_type ENUM('restaurant', 'cafe', 'bar', 'bakery', 'hotel', 'retail', 'service', 'other') DEFAULT 'restaurant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    preview_token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('generating', 'preview', 'paid', 'editing', 'deployed', 'live', 'failed') DEFAULT 'generating',
    brief_json TEXT NOT NULL,
    html_content LONGTEXT,
    css_content LONGTEXT,
    gjs_components LONGTEXT,
    gjs_styles LONGTEXT,
    deploy_path VARCHAR(255),
    live_url VARCHAR(255),
    generation_error TEXT NULL,
    generated_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    deployed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    stripe_session_id VARCHAR(255) NOT NULL,
    stripe_payment_intent VARCHAR(255),
    amount_cents INT NOT NULL,
    currency VARCHAR(3) DEFAULT 'CHF',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_type ENUM('setup', 'monthly') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id)
);

CREATE TABLE generation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    prompt_tokens INT,
    completion_tokens INT,
    cost_usd DECIMAL(10,6),
    model VARCHAR(50) DEFAULT 'claude-sonnet-4-6',
    duration_ms INT,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id)
);
