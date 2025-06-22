SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Users;
TRUNCATE TABLE SportsFields;
TRUNCATE TABLE Events;
TRUNCATE TABLE EventParticipants;
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto-increment counters
ALTER TABLE Users AUTO_INCREMENT = 1;
ALTER TABLE SportsFields AUTO_INCREMENT = 1;
ALTER TABLE Events AUTO_INCREMENT = 1;

-- Insert admin user
INSERT INTO Users (username, email, password_hash, is_admin, reputation_score) VALUES
    ('admin', 'admin@sportsiasi.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 100);

-- Insert regular users
INSERT INTO Users (username, email, password_hash, reputation_score) VALUES
                                                                         ('andrei_iasi', 'andrei@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 15),
                                                                         ('maria_sport', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8),
                                                                         ('ionut_football', 'ionut@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 25),
                                                                         ('elena_tennis', 'elena@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30);

-- Insert sports fields in Iași (real locations)
INSERT INTO SportsFields (name, location, address, type, amenities, opening_hours) VALUES
                                                                                       ('Teren Fotbal Copou', ST_PointFromText('POINT(27.5726 47.1765)'), 'Parcul Copou, Iași', 'football',
                                                                                        '["goals", "lighting", "changing_rooms"]',
                                                                                        '{"weekdays": "08:00-22:00", "weekend": "09:00-20:00"}'),

                                                                                       ('Teren Baschet Tudor', ST_PointFromText('POINT(27.5872 47.1689)'), 'Str. Tudor Vladimirescu, Iași', 'basketball',
                                                                                        '["hoops", "benches", "water_fountain"]',
                                                                                        '{"weekdays": "07:00-23:00", "weekend": "08:00-22:00"}'),

                                                                                       ('Teren Tenis Palas', ST_PointFromText('POINT(27.6014 47.1585)'), 'Complexul Palas, Iași', 'tennis',
                                                                                        '["nets", "lighting", "seating", "lockers"]',
                                                                                        '{"weekdays": "06:00-24:00", "weekend": "07:00-23:00"}'),

                                                                                       ('Sala Sporturilor Nicolina', ST_PointFromText('POINT(27.6128 47.1493)'), 'Str. Sărărie, Iași', 'multi-sport',
                                                                                        '["indoor", "lockers", "showers", "equipment_rental"]',
                                                                                        '{"monday": "08:00-21:00", "tuesday": "08:00-21:00", "wednesday": "08:00-21:00", "thursday": "08:00-21:00", "friday": "08:00-21:00", "saturday": "09:00-18:00", "sunday": "closed"}'),

                                                                                       ('Teren Volei Tătărași', ST_PointFromText('POINT(27.5637 47.1821)'), 'Parcul Tătărași, Iași', 'volleyball',
                                                                                        '["net", "sand", "benches"]',
                                                                                        '{"summer": "07:00-22:00", "winter": "08:00-20:00"}');

-- Insert events
INSERT INTO Events (title, description, organizer_id, field_id, sport_type, start_time, end_time, max_participants) VALUES
                                                                                                                        ('Fotbal amical Copou', 'Meci amical de fotbal pentru toți pasionații', 2, 1, 'football', '2026-12-15 17:00:00', '2026-12-15 19:00:00', 14),

                                                                                                                        ('Turneu baschet weekend', 'Turneu pentru jucători intermediari și avansați', 3, 2, 'basketball', '2026-12-16 10:00:00', '2026-12-16 14:00:00', 10),

                                                                                                                        ('Sesiune tenis începători', 'Lecții pentru începători cu antrenor certificat', 5, 3, 'tennis', '2026-12-17 11:00:00', '2026-12-17 13:00:00', 4),

                                                                                                                        ('Volei pe plajă', 'Volei recreativ pe nisip', 4, 5, 'volleyball', '2026-12-18 16:00:00', '2026-12-18 18:00:00', 12);

-- Insert event participants
INSERT INTO EventParticipants (event_id, user_id, status) VALUES
                                                              (1, 3, 'confirmed'),
                                                              (1, 4, 'confirmed'),
                                                              (1, 5, 'confirmed'),
                                                              (2, 2, 'confirmed'),
                                                              (2, 4, 'confirmed'),
                                                              (3, 2, 'pending'),
                                                              (3, 3, 'confirmed'),
                                                              (4, 2, 'confirmed'),
                                                              (4, 3, 'confirmed'),
                                                              (4, 5, 'confirmed');

-- Update event participant counts
UPDATE Events e
SET current_participants = (
    SELECT COUNT(*)
    FROM EventParticipants ep
    WHERE ep.event_id = e.event_id AND ep.status = 'confirmed'
);