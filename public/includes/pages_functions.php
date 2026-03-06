<?php
/**
 * Functions for business pages under profiles.
 *
 * Terminology:
 * - Profile: User account (users table). The parent entity.
 * - Community: A group (communities table). Pages can be discovered inside a community.
 * - Page: Business page owned by a profile and linked to a community.
 */

/**
 * Create a business page under a profile.
 *
 * @param mysqli $conn Database connection
 * @param int $profile_id The user (profile) ID that owns this page
 * @param int $community_id The community this page belongs to (must be a community the user is a member of)
 * @param array $data Page data: name (required), slug (optional, auto-generated), description (optional)
 * @return int|false The new page ID on success, false on failure
 */
function create_page_under_profile($conn, $profile_id, $community_id, $data) {
    $profile_id = (int) $profile_id;
    if ($profile_id <= 0) {
        return false;
    }

    $community_id = (int) $community_id;
    if ($community_id <= 0) {
        return false;
    }

    $name = trim($data['name'] ?? '');
    if ($name === '') {
        return false;
    }

    $description = trim($data['description'] ?? '');
    $slug = isset($data['slug']) && trim($data['slug']) !== ''
        ? slugify(trim($data['slug']))
        : slugify($name);

    if ($slug === '') {
        $slug = 'page-' . uniqid();
    }

    // Ensure profile exists
    $check = $conn->query("SELECT id FROM users WHERE id = '$profile_id'");
    if (!$check || $check->num_rows === 0) {
        return false;
    }

    // Ensure user is member of community
    $member = $conn->query("SELECT 1 FROM community_members WHERE user_id = '$profile_id' AND community_id = '$community_id' LIMIT 1");
    if (!$member || $member->num_rows === 0) {
        return false;
    }

    // Ensure slug is unique for this profile
    $slug_esc = $conn->real_escape_string($slug);
    $existing = $conn->query("SELECT id FROM pages WHERE profile_id = '$profile_id' AND slug = '$slug_esc'");
    if ($existing && $existing->num_rows > 0) {
        $slug = $slug . '-' . substr(uniqid(), -4);
        $slug_esc = $conn->real_escape_string($slug);
    }

    $name_esc = $conn->real_escape_string($name);
    $desc_esc = $conn->real_escape_string($description);

    $sql = "INSERT INTO pages (profile_id, community_id, name, slug, description) 
            VALUES ('$profile_id', '$community_id', '$name_esc', '$slug_esc', '$desc_esc')";

    if ($conn->query($sql) === true) {
        return (int) $conn->insert_id;
    }

    return false;
}

/**
 * Get all pages owned by a profile.
 *
 * @param mysqli $conn Database connection
 * @param int $profile_id The profile (user) ID
 * @return mysqli_result|false
 */
function get_pages_by_profile($conn, $profile_id) {
    $profile_id = (int) $profile_id;
    if ($profile_id <= 0) {
        return false;
    }
    return $conn->query("SELECT * FROM pages WHERE profile_id = '$profile_id' ORDER BY name ASC");
}

/**
 * Get all pages in a community.
 *
 * @param mysqli $conn Database connection
 * @param int $community_id
 * @return mysqli_result|false
 */
function get_pages_by_community($conn, $community_id) {
    $community_id = (int) $community_id;
    if ($community_id <= 0) return false;
    return $conn->query("SELECT * FROM pages WHERE community_id = '$community_id' ORDER BY name ASC");
}

/**
 * Get a page by ID (for public view).
 */
function get_page_by_id($conn, $page_id) {
    $page_id = (int) $page_id;
    if ($page_id <= 0) return null;
    $res = $conn->query("SELECT * FROM pages WHERE id = '$page_id'");
    return $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
}

/**
 * Get a single page by ID, only if it belongs to the given profile.
 *
 * @param mysqli $conn Database connection
 * @param int $page_id Page ID
 * @param int $profile_id Profile ID (owner)
 * @return array|null Page row or null
 */
function get_page_by_profile($conn, $page_id, $profile_id) {
    $page_id = (int) $page_id;
    $profile_id = (int) $profile_id;
    if ($page_id <= 0 || $profile_id <= 0) {
        return null;
    }
    $res = $conn->query("SELECT * FROM pages WHERE id = '$page_id' AND profile_id = '$profile_id'");
    return $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
}

/**
 * Get communities a user is a member of.
 *
 * @param mysqli $conn
 * @param int $user_id
 * @return mysqli_result|false
 */
function get_user_communities($conn, $user_id) {
    $user_id = (int) $user_id;
    if ($user_id <= 0) return false;
    return $conn->query("SELECT c.id, c.name FROM communities c JOIN community_members m ON c.id = m.community_id WHERE m.user_id = '$user_id' ORDER BY c.name ASC");
}

/**
 * Convert string to URL-friendly slug.
 */
function slugify($str) {
    $str = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $str);
    $str = preg_replace('/[\s\-]+/', '-', trim($str));
    return strtolower($str);
}
