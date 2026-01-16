-- ============================================
-- SQL Script to Populate Member Tenants
-- for Existing Members
-- ============================================
-- This script creates tenants for all existing members
-- that don't already have a member_tenant_id
-- ============================================

-- Step 1: Ensure "Members" package exists
-- If it doesn't exist, create it
INSERT INTO packages (
    name,
    package_type,
    cost,
    status,
    is_popular,
    discount,
    trial_days,
    user_limit,
    member_limit,
    branch_limit,
    account_type_limit,
    account_limit,
    member_portal,
    created_at,
    updated_at
)
SELECT
    'Members' as name,
    'lifetime' as package_type,
    0.00 as cost,
    1 as status,
    0 as is_popular,
    0.00 as discount,
    0 as trial_days,
    '1' as user_limit,
    '1' as member_limit,
    '1' as branch_limit,
    '-1' as account_type_limit,
    '-1' as account_limit,
    1 as member_portal,
    NOW() as created_at,
    NOW() as updated_at
WHERE NOT EXISTS (
    SELECT 1 FROM packages WHERE name = 'Members'
);

-- Get the Members package ID
SET @members_package_id = (SELECT id FROM packages WHERE name = 'Members' LIMIT 1);

-- Step 2: Create tenants for all members that don't have a member_tenant_id
-- This uses a temporary table to handle slug generation and uniqueness
DROP TEMPORARY TABLE IF EXISTS temp_member_tenants;

CREATE TEMPORARY TABLE temp_member_tenants AS
SELECT
    m.id as member_id,
    m.first_name,
    m.last_name,
    m.tenant_id as main_tenant_id,
    t.slug as main_tenant_slug,
    t.name as main_tenant_name,
    m.user_id,
    m.email,
    m.photo,
    m.status as member_status,
    -- Generate slug: main_tenant_slug-first_name-last_name (cleaned)
    LOWER(
        CONCAT(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                CONCAT(t.slug, '-', m.first_name, '-', m.last_name),
                ' ', '-'), '.', ''), ',', ''), '(', ''), ')', ''), '[', ''), ']', ''), '{', ''), '}', ''), '/', '')
        )
    ) as base_slug
FROM members m
INNER JOIN tenants t ON m.tenant_id = t.id
WHERE m.member_tenant_id IS NULL
  AND m.first_name IS NOT NULL
  AND m.last_name IS NOT NULL;

-- Handle duplicate slugs by adding a counter
-- First, create a copy of the temp table to avoid self-join issues
DROP TEMPORARY TABLE IF EXISTS temp_member_tenants_copy;

CREATE TEMPORARY TABLE temp_member_tenants_copy AS
SELECT * FROM temp_member_tenants;

-- Create a table with row numbers for duplicates
DROP TEMPORARY TABLE IF EXISTS temp_slug_counter;

CREATE TEMPORARY TABLE temp_slug_counter AS
SELECT
    t1.member_id,
    t1.base_slug,
    COUNT(t2.member_id) as slug_counter
FROM temp_member_tenants t1
LEFT JOIN temp_member_tenants_copy t2
    ON t1.base_slug = t2.base_slug
    AND t2.member_id <= t1.member_id
GROUP BY t1.member_id, t1.base_slug;

-- Also check against existing tenants and mark conflicts
UPDATE temp_slug_counter tsc
INNER JOIN temp_member_tenants tmt ON tsc.member_id = tmt.member_id
SET tsc.slug_counter = tsc.slug_counter + 1000
WHERE EXISTS (SELECT 1 FROM tenants t WHERE t.slug = tsc.base_slug);

-- Update slugs that have duplicates or conflicts
UPDATE temp_member_tenants tmt
INNER JOIN temp_slug_counter tsc ON tmt.member_id = tsc.member_id
SET tmt.base_slug = CONCAT(tmt.base_slug, '-', tsc.slug_counter)
WHERE tsc.slug_counter > 1;

-- Clean up
DROP TEMPORARY TABLE IF EXISTS temp_slug_counter;
DROP TEMPORARY TABLE IF EXISTS temp_member_tenants_copy;

-- Step 3: Insert tenants for all members
INSERT INTO tenants (
    slug,
    name,
    membership_type,
    package_id,
    subscription_date,
    valid_to,
    status,
    created_at,
    updated_at
)
SELECT
    base_slug as slug,
    CONCAT(main_tenant_name, ' - ', first_name, ' ', last_name) as name,
    'member' as membership_type,
    @members_package_id as package_id,
    CURDATE() as subscription_date,
    DATE_ADD(CURDATE(), INTERVAL 25 YEAR) as valid_to,
    1 as status,
    NOW() as created_at,
    NOW() as updated_at
FROM temp_member_tenants;

-- Step 4: Update members table to link to their new tenants
UPDATE members m
INNER JOIN temp_member_tenants tmt ON m.id = tmt.member_id
INNER JOIN tenants t ON t.slug = tmt.base_slug
SET m.member_tenant_id = t.id
WHERE m.member_tenant_id IS NULL;

-- Step 5: Create admin users for member tenants
-- For members that have a user_id (customer login), use that user's email and password
-- For members without user_id, create a new user with member's email
INSERT INTO users (
    name,
    email,
    password,
    user_type,
    tenant_id,
    tenant_owner,
    status,
    profile_picture,
    created_at,
    updated_at
)
SELECT
    CONCAT(tmt.first_name, ' ', tmt.last_name) as name,
    COALESCE(
        (SELECT email FROM users WHERE id = tmt.user_id LIMIT 1),
        tmt.email,
        CONCAT('member', tmt.member_id, '@example.com')
    ) as email,
    COALESCE(
        (SELECT password FROM users WHERE id = tmt.user_id LIMIT 1),
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- default password: 'password'
    ) as password,
    'admin' as user_type,
    t.id as tenant_id,
    1 as tenant_owner,
    tmt.member_status as status,
    COALESCE(tmt.photo, 'default.png') as profile_picture,
    NOW() as created_at,
    NOW() as updated_at
FROM temp_member_tenants tmt
INNER JOIN tenants t ON t.slug = tmt.base_slug
WHERE NOT EXISTS (
    SELECT 1 FROM users u
    WHERE u.tenant_id = t.id
    AND u.tenant_owner = 1
);

-- Step 6: Clean up temporary table
DROP TEMPORARY TABLE IF EXISTS temp_member_tenants;

-- ============================================
-- Verification Queries (Optional - run these to check results)
-- ============================================

-- Check how many members now have tenants
-- SELECT COUNT(*) as members_with_tenants
-- FROM members
-- WHERE member_tenant_id IS NOT NULL;

-- Check how many member tenants were created
-- SELECT COUNT(*) as member_tenants_created
-- FROM tenants t
-- INNER JOIN members m ON m.member_tenant_id = t.id
-- WHERE t.membership_type = 'member';

-- Check for any members still without tenants
-- SELECT m.id, m.first_name, m.last_name, m.email
-- FROM members m
-- WHERE m.member_tenant_id IS NULL
--   AND m.first_name IS NOT NULL
--   AND m.last_name IS NOT NULL;

-- ============================================
-- End of Script
-- ============================================
