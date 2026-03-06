-- Link pages to communities.
-- Existing pages will get community_id = NULL until you backfill them.

ALTER TABLE pages
    ADD COLUMN community_id INT NULL AFTER profile_id;

ALTER TABLE pages
    ADD INDEX idx_pages_community_id (community_id);

ALTER TABLE pages
    ADD CONSTRAINT fk_pages_community
        FOREIGN KEY (community_id) REFERENCES communities(id)
        ON DELETE CASCADE;

-- Recommended (optional) uniqueness: slugs unique per community
-- (Run manually if you want this; may fail if you already have duplicates.)
-- CREATE UNIQUE INDEX unique_community_slug ON pages (community_id, slug);

