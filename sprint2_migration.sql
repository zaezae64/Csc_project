-- Admin user (password: admin123)
INSERT INTO user (username, usertype, account_status, FlairTags, password_hash, password_hint)
VALUES ('testadmin', 'admin', 'active', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test');

-- Moderator user (password: admin123)
INSERT INTO user (username, usertype, account_status, FlairTags, password_hash, password_hint)
VALUES ('testmod', 'moderator', 'active', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test');

-- Regular user (password: admin123)
INSERT INTO user (username, usertype, account_status, FlairTags, password_hash, password_hint)
VALUES ('testuser', 'standard', 'active', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test');