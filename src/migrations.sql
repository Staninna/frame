-- Create users table
CREATE TABLE users
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    naam       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    wachtwoord VARCHAR(255) NOT NULL
);

-- Create tasks table
CREATE TABLE tasks
(
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    title        VARCHAR(255) NOT NULL,
    beschrijving TEXT,
    status       VARCHAR(50)  NOT NULL,
    verval_datum INT UNSIGNED, -- unix timestamp
    prioriteit   VARCHAR(50)  NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Create sub_tasks table
CREATE TABLE sub_tasks
(
    id           INT AUTO_INCREMENT PRIMARY KEY,
    task_id      INT          NOT NULL,
    title        VARCHAR(255) NOT NULL,
    beschrijving TEXT,
    status       VARCHAR(50)  NOT NULL,
    FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_tasks_user_id ON tasks (user_id);
CREATE INDEX idx_sub_tasks_task_id ON sub_tasks (task_id);