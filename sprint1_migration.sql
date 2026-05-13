-- =============================================
-- MediaArchive Sprint 1 - Auth Column Migration
-- Run this in phpMyAdmin BEFORE using login pages
-- =============================================

-- Add password hash column to user table
ALTER TABLE `user`
    ADD COLUMN `password_hash`   VARCHAR(255) NOT NULL DEFAULT '' AFTER `FlairTags`,
    ADD COLUMN `hint_question`   VARCHAR(255) NOT NULL DEFAULT '' AFTER `password_hash`,
    ADD COLUMN `hint_answer`     VARCHAR(255) NOT NULL DEFAULT '' AFTER `hint_question`;

-- Example: Insert a test user (password = "password")
INSERT INTO `user` (`username`, `usertype`, `account_status`, `FlairTags`, `password_hash`, `hint_question`, `hint_answer`)
VALUES (
    'testuser',
    'member',
    'active',
    '',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'What was the name of your first pet?',
    'fido'
);
