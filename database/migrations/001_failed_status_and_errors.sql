-- Run on existing databases created before `failed` status / error columns.

USE custode_builder;

ALTER TABLE sites
    MODIFY status ENUM('generating', 'preview', 'paid', 'editing', 'deployed', 'live', 'failed') DEFAULT 'generating',
    ADD COLUMN generation_error TEXT NULL AFTER live_url;

ALTER TABLE generation_logs
    ADD COLUMN error_message TEXT NULL AFTER duration_ms;
