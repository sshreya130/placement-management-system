USE college_db;

-- 1. FUNCTION: Check Eligibility
DROP FUNCTION IF EXISTS check_eligibility;

DELIMITER $$

CREATE FUNCTION check_eligibility(
    p_student_id INT,
    p_drive_id INT
)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE v_cgpa DECIMAL(3,2);
    DECLARE v_cutoff DECIMAL(3,2);

    SELECT cgpa INTO v_cgpa
    FROM STUDENT
    WHERE student_id = p_student_id;

    SELECT cgpa_cutoff INTO v_cutoff
    FROM JOB_DRIVE
    WHERE drive_id = p_drive_id;

    IF v_cgpa >= v_cutoff THEN
        RETURN 'ELIGIBLE';
    ELSE
        RETURN 'NOT ELIGIBLE';
    END IF;
END$$

DELIMITER ;

-- 2. PROCEDURE: Apply for Job
DROP PROCEDURE IF EXISTS apply_job;

DELIMITER $$

CREATE PROCEDURE apply_job(
    IN p_student_id INT,
    IN p_drive_id INT
)
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_new_app_id INT;

    SET v_status = check_eligibility(p_student_id, p_drive_id);

    IF v_status = 'ELIGIBLE' THEN
        SELECT IFNULL(MAX(application_id), 300) + 1
        INTO v_new_app_id
        FROM APPLICATION;

        INSERT INTO APPLICATION(application_id, student_id, drive_id, application_date, status)
        VALUES (v_new_app_id, p_student_id, p_drive_id, CURDATE(), 'Applied');

        SELECT 'Application Successful' AS message;
    ELSE
        SELECT 'Student Not Eligible' AS message;
    END IF;
END$$

DELIMITER ;

-- 3. TRIGGER: Prevent Ineligible Applications
DROP TRIGGER IF EXISTS trg_check_eligibility;

DELIMITER $$

CREATE TRIGGER trg_check_eligibility
BEFORE INSERT ON APPLICATION
FOR EACH ROW
BEGIN
    DECLARE v_cgpa DECIMAL(3,2);
    DECLARE v_cutoff DECIMAL(3,2);

    SELECT cgpa INTO v_cgpa
    FROM STUDENT
    WHERE student_id = NEW.student_id;

    SELECT cgpa_cutoff INTO v_cutoff
    FROM JOB_DRIVE
    WHERE drive_id = NEW.drive_id;

    IF v_cgpa < v_cutoff THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student not eligible for this drive';
    END IF;
END$$

DELIMITER ;

-- 4. PROCEDURE WITH CURSOR: Placement Report
DROP PROCEDURE IF EXISTS placement_report;

DELIMITER $$

CREATE PROCEDURE placement_report()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_name VARCHAR(50);
    DECLARE v_status VARCHAR(20);

    DECLARE placement_cursor CURSOR FOR
        SELECT name, placement_status
        FROM STUDENT;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    DROP TEMPORARY TABLE IF EXISTS placement_output;

    CREATE TEMPORARY TABLE placement_output (
        student_name VARCHAR(50),
        placement_status VARCHAR(20)
    );

    OPEN placement_cursor;

    read_loop: LOOP
        FETCH placement_cursor INTO v_name, v_status;
        IF done = 1 THEN
            LEAVE read_loop;
        END IF;

        INSERT INTO placement_output
        VALUES (v_name, v_status);
    END LOOP;

    CLOSE placement_cursor;

    SELECT * FROM placement_output;
END$$

DELIMITER ;

SELECT check_eligibility(1, 201) AS eligibility;
SELECT check_eligibility(2, 201) AS eligibility;

CALL apply_job(1, 201);
CALL apply_job(2, 201);

SELECT * FROM APPLICATION;

CALL placement_report();