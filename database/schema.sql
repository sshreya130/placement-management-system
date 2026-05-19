USE college_db;

-- CREATE TABLES
-- STUDENT
CREATE TABLE STUDENT (
    student_id INT PRIMARY KEY,
    name VARCHAR(50),
    email VARCHAR(50),
    branch VARCHAR(20),
    cgpa DECIMAL(3,2),
    phone VARCHAR(15),
    graduation_year INT,
    placement_status VARCHAR(20)
);

-- COMPANY
CREATE TABLE COMPANY (
    company_id INT PRIMARY KEY,
    company_name VARCHAR(50),
    industry_type VARCHAR(30),
    hr_email VARCHAR(50),
    package_offered DECIMAL(10,2)
);

-- SKILL
CREATE TABLE SKILL (
    skill_id INT PRIMARY KEY,
    skill_name VARCHAR(50)
);


-- PLACEMENT_CELL
CREATE TABLE PLACEMENT_CELL (
    admin_id INT PRIMARY KEY,
    name VARCHAR(50),
    email VARCHAR(50),
    role VARCHAR(20)
);

-- STUDENT_SKILLS
CREATE TABLE STUDENT_SKILLS (
    student_id INT,
    skill_id INT,
    PRIMARY KEY (student_id, skill_id),
    FOREIGN KEY (student_id) REFERENCES STUDENT(student_id),
    FOREIGN KEY (skill_id) REFERENCES SKILL(skill_id)
);

-- JOB_DRIVE
CREATE TABLE JOB_DRIVE (
    drive_id INT PRIMARY KEY,
    company_id INT,
    admin_id INT,
    role VARCHAR(30),
    cgpa_cutoff DECIMAL(3,2),
    deadline DATE,
    drive_date DATE,
    status VARCHAR(20),
    FOREIGN KEY (company_id) REFERENCES COMPANY(company_id),
    FOREIGN KEY (admin_id) REFERENCES PLACEMENT_CELL(admin_id)
);

-- APPLICATION
CREATE TABLE APPLICATION (
    application_id INT PRIMARY KEY,
    student_id INT,
    drive_id INT,
    application_date DATE,
    status VARCHAR(20),
    FOREIGN KEY (student_id) REFERENCES STUDENT(student_id),
    FOREIGN KEY (drive_id) REFERENCES JOB_DRIVE(drive_id)
);

ALTER TABLE APPLICATION 
MODIFY application_id INT NOT NULL AUTO_INCREMENT;

-- TEST_RESULT
CREATE TABLE TEST_RESULT (
    result_id INT PRIMARY KEY,
    application_id INT,
    test_score DECIMAL(5,2),
    interview_score DECIMAL(5,2),
    remarks VARCHAR(50),
    FOREIGN KEY (application_id) REFERENCES APPLICATION(application_id)
);

ALTER TABLE TEST_RESULT 
DROP FOREIGN KEY test_result_ibfk_1;

ALTER TABLE TEST_RESULT
ADD CONSTRAINT test_result_ibfk_1
FOREIGN KEY (application_id)
REFERENCES APPLICATION(application_id);

CREATE TABLE RESUME (
    resume_id INT PRIMARY KEY,
    student_id INT,
    resume_link VARCHAR(100),
    last_updated DATE,
    FOREIGN KEY (student_id) REFERENCES STUDENT(student_id)
);

DROP TABLE RESUME;

CREATE TABLE DRIVE_SKILLS (
    drive_id INT,
    skill_id INT,
    PRIMARY KEY (drive_id, skill_id),
    FOREIGN KEY (drive_id) REFERENCES JOB_DRIVE(drive_id),
    FOREIGN KEY (skill_id) REFERENCES SKILL(skill_id)
);

-- INSERT DATA

-- STUDENT DATA
INSERT INTO STUDENT VALUES (1, 'Shreya', 'shreya@gmail.com', 'CSE', 8.5, '9876543210', 2026, 'Not Placed');
INSERT INTO STUDENT VALUES (2, 'Rahul', 'rahul@gmail.com', 'IT', 7.2, '9876543211', 2026, 'Placed');
INSERT INTO STUDENT VALUES (3, 'Molika', 'molika@gmail.com', 'CSE', 9.1, '9876543212', 2026, 'Placed');
INSERT INTO STUDENT VALUES (4, 'Siddharth', 'sid@gmail.com', 'ECE', 6.8, '9876543213', 2026, 'Not Placed');

-- SKILLS
INSERT INTO SKILL VALUES (1, 'Java');
INSERT INTO SKILL VALUES (2, 'SQL');
INSERT INTO SKILL VALUES (3, 'Python');
INSERT INTO SKILL VALUES (4, 'C++');

-- STUDENT SKILLS
INSERT INTO STUDENT_SKILLS VALUES (1, 1);
INSERT INTO STUDENT_SKILLS VALUES (1, 2);
INSERT INTO STUDENT_SKILLS VALUES (2, 2);
INSERT INTO STUDENT_SKILLS VALUES (3, 3);
INSERT INTO STUDENT_SKILLS VALUES (4, 4);

-- COMPANY
INSERT INTO COMPANY VALUES (101, 'TCS', 'IT', 'hr@tcs.com', 7);
INSERT INTO COMPANY VALUES (102, 'Infosys', 'IT', 'hr@infosys.com', 6);
INSERT INTO COMPANY VALUES (103, 'Wipro', 'IT', 'hr@wipro.com', 5);

-- PLACEMENT CELL
INSERT INTO PLACEMENT_CELL VALUES (1, 'Admin1', 'admin1@gmail.com', 'Manager');
INSERT INTO PLACEMENT_CELL VALUES (2, 'Admin2', 'admin2@gmail.com', 'Coordinator');

-- JOB DRIVES
INSERT INTO JOB_DRIVE VALUES (201, 101, 1, 'Software Developer', 7.5, CURDATE(), CURDATE(), 'Open');
INSERT INTO JOB_DRIVE VALUES (202, 102, 2, 'System Engineer', 6.5, CURDATE(), CURDATE(), 'Open');
INSERT INTO JOB_DRIVE VALUES (203, 103, 1, 'Analyst', 6.0, CURDATE(), CURDATE(), 'Closed');

-- APPLICATIONS
INSERT INTO APPLICATION VALUES (301, 1, 201, CURDATE(), 'Applied');
INSERT INTO APPLICATION VALUES (302, 2, 202, CURDATE(), 'Applied');
INSERT INTO APPLICATION VALUES (303, 3, 201, CURDATE(), 'Selected');

-- TEST RESULTS
INSERT INTO TEST_RESULT VALUES (401, 301, 85, 90, 'Qualified');
INSERT INTO TEST_RESULT VALUES (402, 302, 70, 75, 'Qualified');
INSERT INTO TEST_RESULT VALUES (403, 303, 95, 92, 'Selected');

-- RESUME DATA
INSERT INTO RESUME VALUES (501, 1, 'resume_shreya.pdf', CURDATE());
INSERT INTO RESUME VALUES (502, 2, 'resume_rahul.pdf', CURDATE());
INSERT INTO RESUME VALUES (503, 3, 'resume_molika.pdf', CURDATE());

-- DRIVE SKILLS
INSERT INTO DRIVE_SKILLS VALUES (201, 1);
INSERT INTO DRIVE_SKILLS VALUES (201, 2);
INSERT INTO DRIVE_SKILLS VALUES (202, 2);
INSERT INTO DRIVE_SKILLS VALUES (203, 3);

-- VERIFY DATA
SHOW TABLES;
SELECT * FROM STUDENT;
SELECT * FROM COMPANY;
SELECT * FROM JOB_DRIVE;

SELECT 
    TABLE_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY ORDINAL_POSITION) AS columns
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = 'college_db'
GROUP BY 
    TABLE_NAME;
    
SHOW CREATE TABLE COMPANY;
SHOW CREATE TABLE JOB_DRIVE;
SHOW CREATE TABLE placement_cell;
SHOW CREATE TABLE student;
SHOW CREATE TABLE application;

UPDATE student
SET email = 'molikagoela@gmail.com'
WHERE student_id = 3;

USE college_db;

INSERT INTO STUDENT 
(student_id, name, email, branch, cgpa, phone, graduation_year, placement_status)
VALUES
(5, 'Aarav Sharma', 'aarav@gmail.com', 'CSE', 8.2, '9876543214', 2026, 'Not Placed'),
(6, 'Ananya Verma', 'ananya@gmail.com', 'IT', 7.9, '9876543215', 2026, 'Not Placed'),
(7, 'Kabir Singh', 'kabir@gmail.com', 'ECE', 6.9, '9876543216', 2026, 'Not Placed'),
(8, 'Meera Kapoor', 'meera@gmail.com', 'CSE', 9.3, '9876543217', 2026, 'Placed'),
(9, 'Rohan Mehta', 'rohan@gmail.com', 'IT', 7.4, '9876543218', 2026, 'Not Placed'),
(10, 'Priya Nair', 'priya@gmail.com', 'ECE', 8.0, '9876543219', 2026, 'Not Placed'),
(11, 'Aditya Rao', 'aditya@gmail.com', 'CSE', 6.5, '9876543220', 2026, 'Not Placed'),
(12, 'Isha Gupta', 'isha@gmail.com', 'IT', 8.7, '9876543221', 2026, 'Placed'),
(13, 'Nikhil Jain', 'nikhil@gmail.com', 'ECE', 7.1, '9876543222', 2026, 'Not Placed'),
(14, 'Sneha Malhotra', 'sneha@gmail.com', 'CSE', 9.0, '9876543223', 2026, 'Placed'),
(15, 'Karan Bansal', 'karan@gmail.com', 'IT', 6.8, '9876543224', 2026, 'Not Placed'),
(16, 'Tanya Arora', 'tanya@gmail.com', 'ECE', 8.4, '9876543225', 2026, 'Not Placed'),
(17, 'Yash Khanna', 'yash@gmail.com', 'CSE', 7.6, '9876543226', 2026, 'Not Placed'),
(18, 'Neha Sinha', 'neha@gmail.com', 'IT', 8.9, '9876543227', 2026, 'Placed'),
(19, 'Dev Patel', 'dev@gmail.com', 'ECE', 7.3, '9876543228', 2026, 'Not Placed');
INSERT INTO STUDENT 
(student_id, name, email, branch, cgpa, phone, graduation_year, placement_status)
VALUES
(20, 'Ritika Sharma', 'ritika@gmail.com', 'CSE', 9.7, '9876543229', 2026, 'Placed');

INSERT INTO COMPANY 
(company_id, company_name, industry_type, hr_email, package_offered)
VALUES
(105, 'Microsoft', 'IT Services', 'hr@microsoft.com', 12.00),
(106, 'Amazon', 'E-Commerce', 'hr@amazon.com', 15.00),
(107, 'Accenture', 'Consulting', 'hr@accenture.com', 8.50);

INSERT INTO JOB_DRIVE
(drive_id, company_id, admin_id, role, cgpa_cutoff, deadline, drive_date, status)
VALUES
(206, 105, 1, 'Software Engineer', 9.5, '2026-05-08', '2026-05-10', 'Open'),
(207, 106, 1, 'SDE Intern', 8.0, '2026-05-10', '2026-05-12', 'Open'),
(208, 107, 1, 'Associate Consultant', 7.5, '2026-05-13', '2026-05-15', 'Open'),
(209, 101, 1, 'Backend Developer', 8.5, '2026-05-16', '2026-05-18', 'Open'),
(210, 103, 1, 'System Engineer', 6.0, '2026-05-18', '2026-05-20', 'Open');

INSERT INTO APPLICATION
(student_id, drive_id, application_date, status)
VALUES
(5, 210, '2026-05-01', 'Applied'),
(6, 210, '2026-05-01', 'Applied'),
(8, 208, '2026-05-02', 'Selected'),
(10, 207, '2026-05-03', 'Applied'),
(12, 209, '2026-05-04', 'Selected'),
(14, 209, '2026-05-05', 'Selected'),
(17, 208, '2026-05-06', 'Applied'); 