-- Pages belong to profiles (users). A profile can have multiple business pages.
-- Pages can only exist under a profile.

CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_profile_slug (profile_id, slug),
    CONSTRAINT fk_pages_profile FOREIGN KEY (profile_id) REFERENCES users(id) ON DELETE CASCADE
);
