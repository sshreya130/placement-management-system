USE college_db;

-- A. UPDATE QUERY
-- Update an application's status

UPDATE APPLICATION
SET status = 'Selected'
WHERE application_id = 301;

SELECT * 
FROM APPLICATION
WHERE application_id = 301;


-- B. DELETE QUERY
-- Delete a resume record
DELETE FROM RESUME
WHERE resume_id = 503;

SELECT * FROM RESUME;


-- C. HAVING QUERY
-- Drives with more than 1 application

SELECT drive_id, COUNT(*) AS total_applications
FROM APPLICATION
GROUP BY drive_id
HAVING COUNT(*) > 1;


-- D. ANOTHER AGGREGATE QUERY
-- Company-wise number of applicants

SELECT c.company_name, COUNT(a.application_id) AS total_applicants
FROM COMPANY c
JOIN JOB_DRIVE j ON c.company_id = j.company_id
LEFT JOIN APPLICATION a ON j.drive_id = a.drive_id
GROUP BY c.company_name;


-- E. ALTER TABLE EXAMPLE
-- Add a new column for student city

ALTER TABLE STUDENT
ADD city VARCHAR(30);

DESCRIBE STUDENT;


-- F. UPDATE THE NEW COLUMN

UPDATE STUDENT
SET city = 'Mohali'
WHERE student_id = 1;

UPDATE STUDENT
SET city = 'Delhi'
WHERE student_id = 2;

UPDATE STUDENT
SET city = 'Chandigarh'
WHERE student_id = 3;

UPDATE STUDENT
SET city = 'Ludhiana'
WHERE student_id = 4;

SELECT student_id, name, city
FROM STUDENT;


-- G. TRIGGER
-- Automatically update student placement status
-- when application status becomes 'Selected'

DROP TRIGGER IF EXISTS trg_update_placement_status;

DELIMITER $$

CREATE TRIGGER trg_update_placement_status
AFTER UPDATE ON APPLICATION
FOR EACH ROW
BEGIN
    IF NEW.status = 'Selected' THEN
        UPDATE STUDENT
        SET placement_status = 'Placed'
        WHERE student_id = NEW.student_id;
    END IF;
END$$

DELIMITER ;

-- Test trigger
UPDATE APPLICATION
SET status = 'Selected'
WHERE application_id = 302;

UPDATE STUDENT
SET placement_status = 'Placed'
WHERE student_id IN (
    SELECT student_id
    FROM APPLICATION
    WHERE status = 'Selected'
);

SELECT student_id, name, placement_status
FROM STUDENT;


-- H. TRANSACTION CONTROL
SET autocommit = 0;

SAVEPOINT before_change;

INSERT INTO STUDENT
VALUES (10, 'TestUser', 'test@gmail.com', 'CSE', 7.0, '9999999999', 2026, 'Not Placed', 'Patiala');

SELECT * 
FROM STUDENT
WHERE student_id = 10;

ROLLBACK TO before_change;

SELECT * 
FROM STUDENT
WHERE student_id = 10;

INSERT INTO STUDENT
VALUES (11, 'FinalUser', 'final@gmail.com', 'IT', 8.0, '8888888888', 2026, 'Not Placed', 'Delhi');

COMMIT;

SET autocommit = 1;

-- derived dynamically
SELECT s.name,
CASE 
    WHEN EXISTS (
        SELECT 1 FROM APPLICATION a 
        WHERE a.student_id = s.student_id 
        AND a.status = 'Selected'
    ) THEN 'Placed'
    ELSE 'Not Placed'
END AS placement_status
FROM STUDENT s;

-- I. EXTRA USEFUL REPORT QUERY
-- Students selected in drives with company names
SELECT s.name, c.company_name, a.status
FROM STUDENT s
JOIN APPLICATION a ON s.student_id = a.student_id
JOIN JOB_DRIVE j ON a.drive_id = j.drive_id
JOIN COMPANY c ON j.company_id = c.company_id
WHERE a.status = 'Selected';


-- J. EXTRA USEFUL QUERY
-- Students not yet placed

SELECT student_id, name, placement_status
FROM STUDENT;

SELECT student_id, name, branch, cgpa
FROM STUDENT
WHERE placement_status = 'Not Placed';