USE school_db;

-- Insert example announcements with correct column names
INSERT INTO announcements (title, content, posted_by, target_role, is_active, created_at) VALUES
('Welcome to the New Semester', 'We are excited to welcome all students to the new semester. Please check your schedules and stay updated.', 1, 'all', TRUE, NOW()),
('Library Hours Extended', 'The library will now be open until 8 PM on weekdays to accommodate student needs.', 2, 'all', TRUE, NOW()),
('Upcoming Maintenance', 'There will be scheduled maintenance on the school portal this weekend. Expect downtime from 10 PM Saturday to 6 AM Sunday.', 1, 'all', TRUE, NOW());
