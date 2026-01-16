-- ============================================
-- SQL Script to Update Feature Icons
-- Replace numbers with relevant Bootstrap icons
-- ============================================
-- This script can be run directly in phpMyAdmin
-- or MySQL command line without shell access
-- ============================================

-- Update icons based on feature titles
-- This uses CASE statements to map titles to appropriate Bootstrap icons

UPDATE features f
INNER JOIN feature_translations ft ON f.id = ft.feature_id
SET f.icon = CASE
    -- Multi-Branch Management
    WHEN LOWER(ft.title) LIKE '%multi-branch%' OR LOWER(ft.title) LIKE '%branch management%' THEN '<i class="bi bi-building"></i>'

    -- Member Account Management
    WHEN LOWER(ft.title) LIKE '%member account%' OR LOWER(ft.title) LIKE '%account management%' THEN '<i class="bi bi-wallet2"></i>'

    -- Loan Management
    WHEN LOWER(ft.title) LIKE '%loan management%' OR (LOWER(ft.title) LIKE '%loan%' AND LOWER(ft.title) NOT LIKE '%payment%') THEN '<i class="bi bi-cash-coin"></i>'

    -- Deposits & Withdrawals
    WHEN LOWER(ft.title) LIKE '%deposits%withdrawals%' OR LOWER(ft.title) LIKE '%deposits%withdraw%' OR (LOWER(ft.title) LIKE '%deposits%' AND LOWER(ft.title) LIKE '%withdraw%') THEN '<i class="bi bi-arrow-repeat"></i>'
    WHEN LOWER(ft.title) LIKE '%deposit%' AND LOWER(ft.title) NOT LIKE '%withdraw%' THEN '<i class="bi bi-arrow-down-circle"></i>'
    WHEN LOWER(ft.title) LIKE '%withdraw%' AND LOWER(ft.title) NOT LIKE '%deposit%' THEN '<i class="bi bi-arrow-up-circle"></i>'

    -- Online Payment Integration
    WHEN LOWER(ft.title) LIKE '%online payment%' OR LOWER(ft.title) LIKE '%payment integration%' THEN '<i class="bi bi-credit-card"></i>'
    WHEN LOWER(ft.title) LIKE '%payment%' AND LOWER(ft.title) NOT LIKE '%loan%' THEN '<i class="bi bi-credit-card-2-front"></i>'

    -- Automated Financial Reports
    WHEN LOWER(ft.title) LIKE '%financial reports%' OR LOWER(ft.title) LIKE '%automated%report%' THEN '<i class="bi bi-graph-up"></i>'
    WHEN LOWER(ft.title) LIKE '%report%' THEN '<i class="bi bi-graph-up-arrow"></i>'

    -- Expense & Fund Management
    WHEN LOWER(ft.title) LIKE '%expense%' OR LOWER(ft.title) LIKE '%fund management%' THEN '<i class="bi bi-cash-stack"></i>'
    WHEN LOWER(ft.title) LIKE '%fund%' THEN '<i class="bi bi-bank"></i>'

    -- Security & Data Protection
    WHEN LOWER(ft.title) LIKE '%security%' OR LOWER(ft.title) LIKE '%data protection%' THEN '<i class="bi bi-shield-check"></i>'
    WHEN LOWER(ft.title) LIKE '%protection%' THEN '<i class="bi bi-shield-lock"></i>'

    -- Member Self-Service Portal
    WHEN LOWER(ft.title) LIKE '%self-service%' OR LOWER(ft.title) LIKE '%member portal%' THEN '<i class="bi bi-person-circle"></i>'
    WHEN LOWER(ft.title) LIKE '%portal%' THEN '<i class="bi bi-window"></i>'

    -- General keyword matches
    WHEN LOWER(ft.title) LIKE '%savings%' THEN '<i class="bi bi-piggy-bank"></i>'
    WHEN LOWER(ft.title) LIKE '%account%' AND LOWER(ft.title) NOT LIKE '%member%' THEN '<i class="bi bi-wallet2"></i>'
    WHEN LOWER(ft.title) LIKE '%transaction%' THEN '<i class="bi bi-arrow-left-right"></i>'
    WHEN LOWER(ft.title) LIKE '%member%' AND LOWER(ft.title) NOT LIKE '%account%' AND LOWER(ft.title) NOT LIKE '%portal%' THEN '<i class="bi bi-people"></i>'
    WHEN LOWER(ft.title) LIKE '%customer%' THEN '<i class="bi bi-person"></i>'
    WHEN LOWER(ft.title) LIKE '%mobile%' THEN '<i class="bi bi-phone"></i>'
    WHEN LOWER(ft.title) LIKE '%online%' AND LOWER(ft.title) NOT LIKE '%payment%' THEN '<i class="bi bi-globe"></i>'
    WHEN LOWER(ft.title) LIKE '%bank%' THEN '<i class="bi bi-bank"></i>'
    WHEN LOWER(ft.title) LIKE '%money%' THEN '<i class="bi bi-currency-dollar"></i>'
    WHEN LOWER(ft.title) LIKE '%manage%' OR LOWER(ft.title) LIKE '%management%' THEN '<i class="bi bi-gear"></i>'
    WHEN LOWER(ft.title) LIKE '%dashboard%' THEN '<i class="bi bi-speedometer2"></i>'

    -- Default fallback (keep existing if it's a valid icon, otherwise use default)
    ELSE f.icon
END
WHERE
    -- Only update if icon is a number, contains numbers, or is a number-based Bootstrap icon
    (
        f.icon REGEXP '^[0-9]+$'
        OR f.icon REGEXP '>[0-9]+<'
        OR f.icon REGEXP 'bi-[0-9]+(-circle|-square|-fill)'
        OR f.icon = ''
        OR f.icon IS NULL
        OR (f.icon NOT LIKE '%<i%' OR (f.icon NOT LIKE '%bi-%' AND f.icon NOT LIKE '%fa-%'))
    );

-- Update any remaining features with numeric icons using fallback icons
-- This handles cases where title matching didn't work
UPDATE features f
INNER JOIN feature_translations ft ON f.id = ft.feature_id
SET f.icon = CASE (f.id % 9)
    WHEN 0 THEN '<i class="bi bi-building"></i>'
    WHEN 1 THEN '<i class="bi bi-wallet2"></i>'
    WHEN 2 THEN '<i class="bi bi-cash-coin"></i>'
    WHEN 3 THEN '<i class="bi bi-arrow-repeat"></i>'
    WHEN 4 THEN '<i class="bi bi-credit-card"></i>'
    WHEN 5 THEN '<i class="bi bi-graph-up"></i>'
    WHEN 6 THEN '<i class="bi bi-cash-stack"></i>'
    WHEN 7 THEN '<i class="bi bi-shield-check"></i>'
    WHEN 8 THEN '<i class="bi bi-person-circle"></i>'
    ELSE '<i class="bi bi-star-fill"></i>'
END
WHERE
    -- Only update if still has numeric icon or invalid icon
    (
        f.icon REGEXP '^[0-9]+$'
        OR f.icon REGEXP '>[0-9]+<'
        OR f.icon REGEXP 'bi-[0-9]+(-circle|-square|-fill)'
        OR f.icon = ''
        OR f.icon IS NULL
        OR (f.icon NOT LIKE '%<i%' OR (f.icon NOT LIKE '%bi-%' AND f.icon NOT LIKE '%fa-%'))
    );

-- Final verification: Update any remaining invalid icons
UPDATE features
SET icon = '<i class="bi bi-star-fill"></i>'
WHERE
    icon = ''
    OR icon IS NULL
    OR (icon NOT LIKE '%<i%' OR (icon NOT LIKE '%bi-%' AND icon NOT LIKE '%fa-%'));

-- ============================================
-- Verification Query (Optional - run to check results)
-- ============================================
-- SELECT
--     f.id,
--     f.icon,
--     ft.title,
--     ft.content
-- FROM features f
-- LEFT JOIN feature_translations ft ON f.id = ft.feature_id
-- ORDER BY f.id;
-- ============================================
