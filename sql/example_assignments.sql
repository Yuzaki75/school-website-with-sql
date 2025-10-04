USE school_db;

-- Insert example assignments
INSERT INTO assignments (title, description, subject_id, assigned_by, due_date) VALUES
('Introduction to Programming Assignment', 'Write a simple Python program that prints "Hello, World!" and explains basic syntax.', 1, 2, '2023-11-01 23:59:59'),
('Data Structures Quiz', 'Complete the quiz on arrays and linked lists.', 2, 2, '2023-11-05 23:59:59');

-- Insert example assignment submissions
INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, submitted_at) VALUES
(1, 3, 'Here is my Python code: print("Hello, World!")', NOW()),
(2, 3, 'Quiz answers: 1. Array is a data structure...', NOW());
