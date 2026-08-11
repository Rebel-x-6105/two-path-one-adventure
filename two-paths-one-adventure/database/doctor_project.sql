CREATE DATABASE IF NOT EXISTS doctor_project
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE doctor_project;

CREATE TABLE IF NOT EXISTS doc_proj_questions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_key VARCHAR(50) NOT NULL,
    eyebrow VARCHAR(80) NOT NULL,
    title VARCHAR(180) NOT NULL,
    description VARCHAR(255) NOT NULL,
    step_order TINYINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_proj_questions_key (question_key),
    UNIQUE KEY uq_doc_proj_questions_order (step_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doc_proj_options (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_id INT UNSIGNED NOT NULL,
    option_key VARCHAR(50) NOT NULL,
    title VARCHAR(120) NOT NULL,
    description VARCHAR(220) NOT NULL,
    summary_fragment VARCHAR(220) NOT NULL,
    icon_name VARCHAR(40) NOT NULL,
    option_order TINYINT UNSIGNED NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_proj_option_key (question_id, option_key),
    KEY idx_doc_proj_options_question (question_id, is_active, option_order),
    CONSTRAINT fk_doc_proj_options_question
        FOREIGN KEY (question_id) REFERENCES doc_proj_questions(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doc_proj_adventures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    adventure_code CHAR(36) NOT NULL,
    guest_name VARCHAR(60) NOT NULL,
    theme ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    final_summary TEXT NULL,
    status ENUM('started', 'in_progress', 'completed', 'accepted', 'restarted') NOT NULL DEFAULT 'started',
    started_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_proj_adventure_code (adventure_code),
    KEY idx_doc_proj_adventure_status (status),
    KEY idx_doc_proj_adventure_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doc_proj_adventure_choices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    adventure_id BIGINT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    option_id INT UNSIGNED NOT NULL,
    selected_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_proj_choice_per_question (adventure_id, question_id),
    KEY idx_doc_proj_choice_option (option_id),
    CONSTRAINT fk_doc_proj_choices_adventure
        FOREIGN KEY (adventure_id) REFERENCES doc_proj_adventures(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_doc_proj_choices_question
        FOREIGN KEY (question_id) REFERENCES doc_proj_questions(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_doc_proj_choices_option
        FOREIGN KEY (option_id) REFERENCES doc_proj_options(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doc_proj_invitation_responses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    adventure_id BIGINT UNSIGNED NOT NULL,
    response ENUM('accepted', 'restart') NOT NULL,
    preferred_date DATE NULL,
    note VARCHAR(500) NULL,
    responded_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_doc_proj_response_adventure (adventure_id, responded_at),
    CONSTRAINT fk_doc_proj_response_adventure
        FOREIGN KEY (adventure_id) REFERENCES doc_proj_adventures(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO doc_proj_questions
    (id, question_key, eyebrow, title, description, step_order, is_active)
VALUES
    (1, 'mood', 'First impression', 'What kind of energy should the day have?', 'Choose the feeling that sounds most like your perfect escape.', 1, 1),
    (2, 'starting_point', 'The opening scene', 'Where should our story begin?', 'Pick the first stop—the one that sets the tone for everything after it.', 2, 1),
    (3, 'main_activity', 'The main chapter', 'What should we do when the day opens up?', 'A beautiful place to wander, or something playful enough to make us forget the time?', 3, 1),
    (4, 'food_choice', 'The delicious pause', 'How should we handle the hunger part?', 'There is no adventure without a good food decision somewhere in the middle.', 4, 1),
    (5, 'ending', 'The final scene', 'How should the perfect day end?', 'Choose the last frame—the moment we would probably talk about later.', 5, 1)
ON DUPLICATE KEY UPDATE
    eyebrow = VALUES(eyebrow),
    title = VALUES(title),
    description = VALUES(description),
    step_order = VALUES(step_order),
    is_active = VALUES(is_active);

INSERT INTO doc_proj_options
    (id, question_id, option_key, title, description, summary_fragment, icon_name, option_order, is_active)
VALUES
    (1, 1, 'peaceful', 'Peaceful & Relaxing', 'Slow conversations, easy laughter, and nowhere we need to rush.', 'a peaceful, easygoing rhythm', 'calm', 1, 1),
    (2, 1, 'spontaneous', 'Fun & Spontaneous', 'A little unpredictability, plenty of energy, and a story to tell.', 'a playful, spontaneous energy', 'spark', 2, 1),

    (3, 2, 'coffee', 'A Cozy Café', 'Warm drinks, a comfortable corner, and time to settle into the day.', 'coffee at a cozy café', 'coffee', 1, 1),
    (4, 2, 'cold_treat', 'Juice or Ice Cream', 'Something fresh, something sweet, and an instantly cheerful beginning.', 'fresh juice or ice cream', 'scoop', 2, 1),

    (5, 3, 'explore', 'Explore Somewhere Beautiful', 'A scenic walk, hidden corners, and a place worth taking a few photos.', 'exploring somewhere beautiful', 'explore', 1, 1),
    (6, 3, 'play', 'Try Something Exciting', 'Games, friendly competition, and at least one unexpected moment.', 'trying something playful and exciting', 'play', 2, 1),

    (7, 4, 'street_food', 'Street-Food Adventure', 'A few small stops, bold flavours, and sharing the best finds.', 'a street-food adventure', 'street', 1, 1),
    (8, 4, 'restaurant', 'A Comfortable Restaurant', 'A relaxed table, good food, and a conversation that gets longer.', 'a comfortable restaurant', 'dine', 2, 1),

    (9, 5, 'sunset', 'Watch the Sunset', 'A quiet view, golden light, and no need to say much for a minute.', 'watching the sunset', 'sunset', 1, 1),
    (10, 5, 'movie', 'End With a Movie', 'A shared screen, snacks, and one last reason to stay a little longer.', 'a movie and something good to snack on', 'movie', 2, 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    summary_fragment = VALUES(summary_fragment),
    icon_name = VALUES(icon_name),
    option_order = VALUES(option_order),
    is_active = VALUES(is_active);
