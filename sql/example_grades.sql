USE school_db;

-- Insert example grades with additional columns
INSERT INTO grades (student_id, subject_id, grade, grade_type, semester, academic_year, created_at) VALUES
(3, 1, 85.5, 'quiz', 'Fall', '2025-2026', NOW()),
(3, 2, 90.0, 'exam', 'Fall', '2025-2026', NOW()),
(4, 1, 78.0, 'quiz', 'Fall', '2025-2026', NOW()),
(4, 2, 82.5, 'exam', 'Fall', '2025-2026', NOW()),
(5, 1, 88.0, 'quiz', 'Fall', '2025-2026', NOW()),
(5, 2, 91.0, 'exam', 'Fall', '2025-2026', NOW());
