-- ============================================
-- SQL Script to Populate Member Tenants
-- for Existing Members (Simplified Version)
-- ============================================
-- This script creates tenants for all existing members
-- that don't already have a member_tenant_id
-- ============================================

-- Step 1: Ensure "Members" package exists
INSERT INTO packages (
    name, package_type, cost, status, is_popular, discount, trial_days,
    user_limit, member_limit, branch_limit, account_type_limit, account_limit,
    member_portal, created_at, updated_at
)
SELECT 
    'Members', 'lifetime', 0.00, 1, 0, 0.00, 0,
    '1', '1', '1', '-1', '-1',
    1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE name = 'Members');

-- Step 2: Create tenants for members (using a loop-like approach with variables)
-- Note: This approach processes members one by one to handle unique slugs

-- First, create a stored procedure to handle the process
DELIMITER $$

DROP PROCEDURE IF EXISTS populate_member_tenants$$

CREATE PROCEDURE populate_member_tenants()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_member_id BIGINT;
    DECLARE v_first_name VARCHAR(191);
    DECLARE v_last_name VARCHAR(191);
    DECLARE v_main_tenant_id BIGINT;
    DECLARE v_main_tenant_slug VARCHAR(191);
    DECLARE v_main_tenant_name VARCHAR(191);
    DECLARE v_user_id BIGINT;
    DECLARE v_email VARCHAR(191);
    DECLARE v_photo VARCHAR(191);
    DECLARE v_status TINYINT;
    DECLARE v_slug VARCHAR(191);
    DECLARE v_tenant_id BIGINT;
    DECLARE v_counter INT;
    DECLARE v_members_package_id BIGINT;
    
    -- Get Members package ID
    SELECT id INTO v_members_package_id FROM packages WHERE name = 'Members' LIMIT 1;
    
    -- Cursor for members without tenant
    DECLARE member_cursor CURSOR FOR
        SELECT 
            m.id, m.first_name, m.last_name, m.tenant_id, 
            t.slug, t.name, m.user_id, m.email, m.photo, m.status
        FROM members m
        INNER JOIN tenants t ON m.tenant_id = t.id
        WHERE m.member_tenant_id IS NULL
          AND m.first_name IS NOT NULL
          AND m.last_name IS NOT NULL
        ORDER BY m.id;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN member_cursor;
    
    read_loop: LOOP
        FETCH member_cursor INTO 
            v_member_id, v_first_name, v_last_name, v_main_tenant_id,
            v_main_tenant_slug, v_main_tenant_name, v_user_id, v_email, v_photo, v_status;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate base slug
        SET v_slug = LOWER(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                CONCAT(v_main_tenant_slug, '-', v_first_name, '-', v_last_name),
                ' ', '-'), '.', ''), ',', ''), '(', ''), ')', ''), '[', ''), ']', ''), '{', ''), '}', ''), '/', '')
        );
        
        -- Check for duplicates and add counter if needed
        SET v_counter = 0;
        WHILE EXISTS (SELECT 1 FROM tenants WHERE slug = v_slug) DO
            SET v_counter = v_counter + 1;
            SET v_slug = CONCAT(v_slug, '-', v_counter);
        END WHILE;
        
        -- Create tenant
        INSERT INTO tenants (
            slug, name, membership_type, package_id, subscription_date, valid_to, status, created_at, updated_at
        ) VALUES (
            v_slug,
            CONCAT(v_main_tenant_name, ' - ', v_first_name, ' ', v_last_name),
            'member',
            v_members_package_id,
            CURDATE(),
            DATE_ADD(CURDATE(), INTERVAL 25 YEAR),
            1,
            NOW(),
            NOW()
        );
        
        SET v_tenant_id = LAST_INSERT_ID();
        
        -- Link member to tenant
        UPDATE members SET member_tenant_id = v_tenant_id WHERE id = v_member_id;
        
        -- Create admin user for tenant
        INSERT INTO users (
            name, email, password, user_type, tenant_id, tenant_owner, status, profile_picture, created_at, updated_at
        )
        SELECT 
            CONCAT(v_first_name, ' ', v_last_name),
            COALESCE(
                (SELECT email FROM users WHERE id = v_user_id LIMIT 1),
                v_email,
                CONCAT('member', v_member_id, '@example.com')
            ),
            COALESCE(
                (SELECT password FROM users WHERE id = v_user_id LIMIT 1),
                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
            ),
            'admin',
            v_tenant_id,
            1,
            v_status,
            COALESCE(v_photo, 'default.png'),
            NOW(),
            NOW()
        WHERE NOT EXISTS (
            SELECT 1 FROM users WHERE tenant_id = v_tenant_id AND tenant_owner = 1
        );
        
    END LOOP;
    
    CLOSE member_cursor;
END$$

DELIMITER ;

-- Execute the procedure
CALL populate_member_tenants();

-- Drop the procedure
DROP PROCEDURE IF EXISTS populate_member_tenants;

-- ============================================
-- Verification Queries
-- ============================================

-- Check how many members now have tenants
SELECT COUNT(*) as members_with_tenants 
FROM members 
WHERE member_tenant_id IS NOT NULL;

-- Check how many member tenants were created
SELECT COUNT(*) as member_tenants_created
FROM tenants t
INNER JOIN members m ON m.member_tenant_id = t.id
WHERE t.membership_type = 'member';

-- Check for any members still without tenants
SELECT m.id, m.first_name, m.last_name, m.email
FROM members m
WHERE m.member_tenant_id IS NULL
  AND m.first_name IS NOT NULL
  AND m.last_name IS NOT NULL;

-- ============================================
-- End of Script
-- ============================================
