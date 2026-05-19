USE college_db;

-- 1. View all students
SELECT * FROM STUDENT;

-- 2. View all companies
SELECT * FROM COMPANY;

/*-- 3. Eligible students (CGPA only)
SELECT s.name, j.drive_id
FROM STUDENT s
JOIN JOB_DRIVE j 
ON s.cgpa >= j.cgpa_cutoff;*/

-- 4. Eligible students (CGPA + Skills)
SELECT s.name, j.drive_id, c.company_name
FROM STUDENT s
JOIN JOB_DRIVE j 
    ON s.cgpa >= j.cgpa_cutoff
JOIN COMPANY c 
    ON j.company_id = c.company_id
WHERE NOT EXISTS (
    SELECT ds.skill_id
    FROM DRIVE_SKILLS ds
    WHERE ds.drive_id = j.drive_id
    AND ds.skill_id NOT IN (
        SELECT ss.skill_id
        FROM STUDENT_SKILLS ss
        WHERE ss.student_id = s.student_id
    )
);

-- 5. Students selected in companies
SELECT c.company_name, s.name
FROM STUDENT s
JOIN APPLICATION a 
    ON s.student_id = a.student_id
JOIN JOB_DRIVE j 
    ON a.drive_id = j.drive_id
JOIN COMPANY c 
    ON j.company_id = c.company_id
WHERE a.status = 'Selected';

-- 6. Count applications per drive
SELECT drive_id, COUNT(*) AS total_applications
FROM APPLICATION
GROUP BY drive_id;

-- 7. Company-wise applicants (Aggregate)

SELECT c.company_name, COUNT(a.application_id) AS total_applicants
FROM COMPANY c
JOIN JOB_DRIVE j ON c.company_id = j.company_id
LEFT JOIN APPLICATION a ON j.drive_id = a.drive_id
GROUP BY c.company_name;

-- 8. Highest test scores
SELECT s.name, t.test_score
FROM STUDENT s
JOIN APPLICATION a 
    ON s.student_id = a.student_id
JOIN TEST_RESULT t 
    ON a.application_id = t.application_id
ORDER BY t.test_score DESC;

-- 9. Students not placed
SELECT student_id, name, branch, cgpa
FROM STUDENT
WHERE placement_status = 'Not Placed';

-- 10. Correct placement status (Dynamic)
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

-- 11. Create a VIEW
DROP VIEW IF EXISTS eligible_students_view;

CREATE VIEW eligible_students_view AS
SELECT s.name, j.drive_id
FROM STUDENT s
JOIN JOB_DRIVE j 
    ON s.cgpa >= j.cgpa_cutoff;

-- 12. Use the VIEW
SELECT * FROM eligible_students_view;

SELECT COUNT(*) FROM test_result;