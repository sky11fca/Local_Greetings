DROP TABLE EventParticipants;
DROP TABLE Events;
DROP TABLE SportsFields;
DROP TABLE Users;

CREATE TABLE Users (
                       user_id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password_hash VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       reputation_score INT DEFAULT 0,
                       is_admin BOOLEAN DEFAULT FALSE,
                       CONSTRAINT chk_email CHECK (email LIKE '%@%.%')
) ENGINE=InnoDB;

-- SportsFields table with spatial data
CREATE TABLE SportsFields (
                              field_id INT AUTO_INCREMENT PRIMARY KEY,
                              name VARCHAR(255) NOT NULL,
                              location POINT NOT NULL,
                              address VARCHAR(255),
                              type ENUM('football', 'basketball', 'tennis', 'volleyball', 'multi-sport') NOT NULL,
                              amenities JSON,
                              opening_hours JSON,
                              is_public BOOLEAN DEFAULT TRUE,
                              SPATIAL INDEX(location),
                              FULLTEXT INDEX(name, address)
) ENGINE=InnoDB;

-- Events table
CREATE TABLE Events (
                        event_id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        description TEXT,
                        organizer_id INT NOT NULL,
                        field_id INT NOT NULL,
                        sport_type ENUM('football', 'basketball', 'tennis', 'volleyball', 'other') NOT NULL,
                        start_time DATETIME NOT NULL,
                        end_time DATETIME NOT NULL,
                        max_participants INT,
                        current_participants INT DEFAULT 0,
                        status ENUM('upcoming', 'ongoing', 'completed', 'canceled') DEFAULT 'upcoming',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (organizer_id) REFERENCES Users(user_id) ON DELETE CASCADE,
                        FOREIGN KEY (field_id) REFERENCES SportsFields(field_id) ON DELETE RESTRICT,
                        CONSTRAINT chk_event_times CHECK (start_time < end_time)
) ENGINE=InnoDB;


-- Event participants
CREATE TABLE EventParticipants (
                                   event_id INT,
                                   user_id INT,
                                   status ENUM('pending', 'confirmed', 'rejected', 'waitlisted') DEFAULT 'pending',
                                   joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                   PRIMARY KEY (event_id, user_id),
                                   FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE,
                                   FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS AdminLogs (
                                         log_id INT AUTO_INCREMENT PRIMARY KEY,
                                         action VARCHAR(100) NOT NULL,
                                         details TEXT,
                                         timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                         INDEX idx_timestamp (timestamp),
                                         INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8mb3_unicode_ci;


-- Indexes for performance

CREATE INDEX idx_event_sport ON Events(sport_type, start_time);
CREATE INDEX idx_event_status ON Events(status, start_time);
CREATE INDEX idx_events_organizer ON Events(organizer_id);
CREATE INDEX idx_events_field ON Events(field_id);