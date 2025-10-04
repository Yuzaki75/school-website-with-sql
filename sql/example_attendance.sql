USE school_db;

-- Update subjects to have course_id
UPDATE subjects SET course_id = 1 WHERE subject_code = 'IT101';
UPDATE subjects SET course_id = 2 WHERE subject_code = 'CS201';
UPDATE subjects SET course_id = 3 WHERE subject_code = 'BA301';

-- Insert example attendance records (assuming student IDs 3,4,5 exist; adjust as needed)
INSERT INTO attendance (student_id, subject_id, date, status, marked_by) VALUES
(3, 1, '2023-10-01', 'present', 2),
(3, 1, '2023-10-02', 'absent', 2);
