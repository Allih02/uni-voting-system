-- Create the voter registration database
CREATE DATABASE VoterRegistrationDB;
USE VoterRegistrationDB;

-- Create the users table
CREATE TABLE users (
    registration_number VARCHAR(20) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL DEFAULT TO_BASE64('password123'),
    vote_status TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create an index on email for fast lookups
CREATE INDEX idx_users_email ON users(email);

-- Create the password_reset_tokens table
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_registration_number VARCHAR(20) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_registration_number) REFERENCES users(registration_number) ON DELETE CASCADE
);

-- Create an index on token for quick lookups
CREATE INDEX idx_reset_token ON password_reset_tokens(token);

-- Stored procedure to generate a password reset token
DELIMITER $$
CREATE PROCEDURE GenerateResetToken(IN user_reg_no VARCHAR(20))
BEGIN
    DECLARE reset_token VARCHAR(255);
    DECLARE expiration_time DATETIME;
    
    -- Generate a unique token
    SET reset_token = UUID();
    SET expiration_time = NOW() + INTERVAL 1 HOUR;
    
    -- Insert the token into the password_reset_tokens table
    INSERT INTO password_reset_tokens (user_registration_number, token, expires_at, used)
    VALUES (user_reg_no, reset_token, expiration_time, FALSE);
    
    -- Return the token (could be sent via email in an application)
    SELECT reset_token AS generated_token;
END $$
DELIMITER ;

-- Stored procedure to authenticate user and log in
DELIMITER $$
CREATE PROCEDURE AuthenticateUser(IN user_email VARCHAR(100), IN user_password VARCHAR(255))
BEGIN
    DECLARE stored_password VARCHAR(255);
    DECLARE user_reg_no VARCHAR(20);
    
    -- Retrieve stored password for the given email
    SELECT registration_number, password INTO user_reg_no, stored_password FROM users WHERE email = user_email;
    
    -- Check if user exists and password matches
    IF stored_password IS NOT NULL AND stored_password = TO_BASE64(user_password) THEN
        SELECT 'Login Successful' AS message, user_reg_no AS registration_number;
    ELSE
        SELECT 'Invalid Credentials' AS message;
    END IF;
END $$
DELIMITER ;

-- Sample insert query for testing
-- INSERT INTO users (registration_number, first_name, last_name, email, password)
-- VALUES ('AB-1234-5678', 'John', 'Doe', 'john.doe@example.com', TO_BASE64('password123'));
