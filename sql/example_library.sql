-- Example data for library system
-- Run this after creating the tables with library_schema.sql

-- Insert sample books
INSERT INTO books (book_title, author, isbn, category, total_copies, available_copies, description, added_by, created_at) VALUES
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'Fiction', 3, 3, 'A classic novel about racial injustice in the American South.', 1, NOW()),
('1984', 'George Orwell', '978-0-452-28423-4', 'Dystopian', 2, 2, 'A dystopian social science fiction novel and cautionary tale.', 1, NOW()),
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Fiction', 4, 4, 'A novel set in the Jazz Age on Long Island.', 2, NOW()),
('Pride and Prejudice', 'Jane Austen', '978-0-14-143951-8', 'Romance', 2, 2, 'A romantic novel of manners.', 2, NOW()),
('The Catcher in the Rye', 'J.D. Salinger', '978-0-316-76948-0', 'Fiction', 3, 3, 'A controversial novel about teenage rebellion.', 1, NOW()),
('Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', '978-0-7475-3269-9', 'Fantasy', 5, 5, 'The first book in the Harry Potter series.', 2, NOW()),
('The Lord of the Rings', 'J.R.R. Tolkien', '978-0-544-00203-5', 'Fantasy', 2, 2, 'An epic high-fantasy novel.', 1, NOW()),
('Dune', 'Frank Herbert', '978-0-441-17271-9', 'Science Fiction', 3, 3, 'A science fiction novel set in a distant future.', 2, NOW()),
('The Hitchhiker\'s Guide to the Galaxy', 'Douglas Adams', '978-0-345-39180-3', 'Science Fiction', 4, 4, 'A comedy science fiction series.', 1, NOW()),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '978-0-06-231609-7', 'Non-Fiction', 2, 2, 'A book about the history of humankind.', 2, NOW())
ON DUPLICATE KEY UPDATE total_copies = VALUES(total_copies), available_copies = VALUES(available_copies);

-- Insert sample borrow records (assuming user IDs 1=admin, 2=teacher, 3=student)
-- Adjust user IDs based on your actual users table if different
INSERT INTO borrow_records (book_id, borrower_id, borrow_date, due_date, status, issued_by) VALUES
(1, 1, '2023-10-01', '2023-10-15', 'returned', 1),
(2, 1, '2023-10-05', '2023-10-19', 'borrowed', 2),
(3, 1, '2023-10-10', '2023-10-24', 'borrowed', 1),
(6, 1, '2023-10-12', '2023-10-26', 'returned', 2),
(7, 1, '2023-10-15', '2023-10-29', 'overdue', 1),
(4, 1, '2023-10-20', '2023-11-03', 'borrowed', 2),
(5, 1, '2023-10-22', '2023-11-05', 'borrowed', 1),
(1, 2, '2023-10-01', '2023-10-15', 'returned', 1),
(2, 2, '2023-10-05', '2023-10-19', 'borrowed', 2),
(3, 2, '2023-10-10', '2023-10-24', 'borrowed', 1),
(6, 2, '2023-10-12', '2023-10-26', 'returned', 2),
(7, 2, '2023-10-15', '2023-10-29', 'overdue', 1),
(4, 2, '2023-10-20', '2023-11-03', 'borrowed', 2),
(5, 2, '2023-10-22', '2023-11-05', 'borrowed', 1),
(1, 3, '2023-10-01', '2023-10-15', 'returned', 1),
(2, 3, '2023-10-05', '2023-10-19', 'borrowed', 2),
(3, 3, '2023-10-10', '2023-10-24', 'borrowed', 1),
(6, 3, '2023-10-12', '2023-10-26', 'returned', 2),
(7, 3, '2023-10-15', '2023-10-29', 'overdue', 1),
(4, 3, '2023-10-20', '2023-11-03', 'borrowed', 2),
(5, 3, '2023-10-22', '2023-11-05', 'borrowed', 1);

-- Update available copies based on borrows
UPDATE books SET available_copies = available_copies - 1 WHERE id IN (2, 3);
UPDATE books SET available_copies = available_copies - 1 WHERE id = 7; -- overdue still counts as borrowed
