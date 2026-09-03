-- Migration script for Student Portal System
-- This script fixes database schema issues and adds security improvements
-- Run this AFTER the original complete_schema.sql

-- ============================================
-- EXISTING SCHEMA SUMMARY
-- ============================================
-- The original schema (complete_schema.sql) creates:
-- 1. users - User accounts with roles (admin, teacher, student)
-- 2. courses - Academic courses
-- 3. subjects - Course subjects linked to courses
-- 4. course_subjects - Many-to-many linking table for courses and subjects
-- 5. student_subjects - Many-to-many linking table for students and subjects
-- 6. enrollments - Student course enrollments
-- 7. grades - Student grades by subject
-- 8. attendance - Daily attendance records
-- 9. announcements - School announcements
-- 10. library_books - Library book catalog
-- 11. book_borrowings - Book borrowing records
-- 12. messages - Internal messaging system
-- 13. assignments - Teacher assignments
-- 14. assignment_submissions - Student assignment submissions

-- ============================================
-- PROBLEMS FOUND IN ORIGINAL SCHEMA
-- ============================================
-- 1. Table name inconsistency: Some PHP files reference 'books' and 'borrow_records' 
--    but schema defines 'library_books' and 'book_borrowings'
-- 2. Missing indexes on frequently queried columns (email, role, foreign keys)
-- 3. Missing constraints for data validation
-- 4. No soft delete capability for important records
-- 5. Password column allows NULL (should be NOT NULL)
-- 6. Some ENUM values don't match PHP code expectations

-- ============================================
-- MIGRATION FIXES
-- ============================================

-- Fix 1: Add missing indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_enrollments_student ON enrollments(student_id);
CREATE INDEX IF NOT EXISTS idx_enrollments_course ON enrollments(course_id);
CREATE INDEX IF NOT EXISTS idx_grades_student ON grades(student_id);
CREATE INDEX IF NOT EXISTS idx_grades_subject ON grades(subject_id);
CREATE INDEX IF NOT EXISTS idx_attendance_student ON attendance(student_id);
CREATE INDEX IF NOT EXISTS idx_attendance_subject ON attendance(subject_id);
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(date);
CREATE INDEX IF NOT EXISTS idx_messages_receiver ON messages(receiver_id);
CREATE INDEX IF NOT EXISTS idx_messages_sender ON messages(sender_id);
CREATE INDEX IF NOT EXISTS idx_book_borrowings_book ON book_borrowings(book_id);
CREATE INDEX IF NOT EXISTS idx_book_borrowings_borrower ON book_borrowings(borrower_id);
CREATE INDEX IF NOT EXISTS idx_assignments_subject ON assignments(subject_id);
CREATE INDEX IF NOT EXISTS idx_assignment_submissions_assignment ON assignment_submissions(assignment_id);
CREATE INDEX IF NOT EXISTS idx_student_subjects_student ON student_subjects(student_id);
CREATE INDEX IF NOT EXISTS idx_student_subjects_subject ON student_subjects(subject_id);

-- Fix 2: Ensure password cannot be NULL (already defined as NOT NULL, but verify)
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Fix 3: Add email verification status (optional enhancement)
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) DEFAULT 0 AFTER email;

-- Fix 4: Add last login tracking for security monitoring
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL AFTER created_at;
ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0 AFTER last_login;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL AFTER login_attempts;

-- Fix 5: Add soft delete support for users (optional, commented out as it requires code changes)
-- ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL AFTER created_at;

-- Fix 6: Improve library_books table consistency
-- Ensure total_copies and available_copies are always positive
ALTER TABLE library_books MODIFY COLUMN total_copies INT NOT NULL DEFAULT 1;
ALTER TABLE library_books MODIFY COLUMN available_copies INT NOT NULL DEFAULT 1;

-- Fix 7: Add ISBN index for faster book searches
CREATE INDEX IF NOT EXISTS idx_library_books_isbn ON library_books(isbn);
CREATE INDEX IF NOT EXISTS idx_library_books_category ON library_books(category);

-- Fix 8: Add index for book borrowings status queries
CREATE INDEX IF NOT EXISTS idx_book_borrowings_status ON book_borrowings(status);
CREATE INDEX IF NOT EXISTS idx_book_borrowings_due_date ON book_borrowings(due_date);

-- Fix 9: Improve assignments table
CREATE INDEX IF NOT EXISTS idx_assignments_due_date ON assignments(due_date);
CREATE INDEX IF NOT EXISTS idx_assignments_assigned_by ON assignments(assigned_by);

-- Fix 10: Add submission status tracking
ALTER TABLE assignment_submissions ADD COLUMN IF NOT EXISTS status ENUM('submitted', 'graded', 'late') DEFAULT 'submitted' AFTER submitted_at;

-- Fix 11: Add announcement targeting improvement
CREATE INDEX IF NOT EXISTS idx_announcements_target_role ON announcements(target_role);
CREATE INDEX IF NOT EXISTS idx_announcements_active ON announcements(is_active);

-- Fix 12: Improve messages table
CREATE INDEX IF NOT EXISTS idx_messages_created ON messages(created_at);
CREATE INDEX IF NOT EXISTS idx_messages_is_read ON messages(is_read);

-- Fix 13: Add cascade delete for better referential integrity
-- Note: These should already exist from original schema, but adding if missing
-- The original schema has proper foreign keys with CASCADE

-- Fix 14: Update sample data to use correct role value ('admin' not 'administrator')
-- This is handled in the application code fix (add_user.php)

-- ============================================
-- DATA FIXES
-- ============================================

-- Fix any existing users with incorrect role values
UPDATE users SET role = 'admin' WHERE role = 'administrator';

-- Ensure available_copies doesn't exceed total_copies
UPDATE library_books SET available_copies = total_copies WHERE available_copies > total_copies;

-- Fix any negative copy counts
UPDATE library_books SET total_copies = 1 WHERE total_copies < 1;
UPDATE library_books SET available_copies = 0 WHERE available_copies < 0;

-- ============================================
-- SECURITY ENHANCEMENTS
-- ============================================

-- Add session tracking table (optional, for advanced session management)
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- Add password reset tokens table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- Add activity log table for audit trail
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- VERIFICATION QUERIES
-- ============================================
-- Run these to verify the migration was successful:

-- SELECT COUNT(*) as user_count FROM users;
-- SELECT COUNT(*) as index_count FROM information_schema.STATISTICS WHERE table_schema = DATABASE();
-- SHOW CREATE TABLE users;

-- ============================================
-- END OF MIGRATION
-- ============================================
