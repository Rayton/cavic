-- ============================================
-- SQL Script to Update Feature Icons
-- Replace numbers with relevant Bootstrap icons
-- ============================================
-- INSTRUCTIONS:
-- 1. Open phpMyAdmin or MySQL command line
-- 2. Select your database
-- 3. Copy and paste this entire script
-- 4. Click "Go" or execute
-- ============================================

-- Step 1: Update icons based on feature titles (most specific matches first)
UPDATE features f
INNER JOIN feature_translations ft ON f.id = ft.feature_id
SET f.icon = CASE
    WHEN LOWER(ft.title) LIKE '%multi-branch%' OR LOWER(ft.title) LIKE '%branch management%' THEN '<i class="bi bi-building"></i>'
    WHEN LOWER(ft.title) LIKE '%member account%' OR LOWER(ft.title) LIKE '%account management%' THEN '<i class="bi bi-wallet2"></i>'
    WHEN LOWER(ft.title) LIKE '%loan management%' THEN '<i class="bi bi-cash-coin"></i>'
    WHEN (LOWER(ft.title) LIKE '%deposits%' AND LOWER(ft.title) LIKE '%withdraw%') THEN '<i class="bi bi-arrow-repeat"></i>'
    WHEN LOWER(ft.title) LIKE '%deposit%' THEN '<i class="bi bi-arrow-down-circle"></i>'
    WHEN LOWER(ft.title) LIKE '%withdraw%' THEN '<i class="bi bi-arrow-up-circle"></i>'
    WHEN LOWER(ft.title) LIKE '%online payment%' OR LOWER(ft.title) LIKE '%payment integration%' THEN '<i class="bi bi-credit-card"></i>'
    WHEN LOWER(ft.title) LIKE '%financial reports%' OR LOWER(ft.title) LIKE '%automated%report%' THEN '<i class="bi bi-graph-up"></i>'
    WHEN LOWER(ft.title) LIKE '%expense%' OR LOWER(ft.title) LIKE '%fund management%' THEN '<i class="bi bi-cash-stack"></i>'
    WHEN LOWER(ft.title) LIKE '%security%' OR LOWER(ft.title) LIKE '%data protection%' THEN '<i class="bi bi-shield-check"></i>'
    WHEN LOWER(ft.title) LIKE '%self-service%' OR LOWER(ft.title) LIKE '%member portal%' THEN '<i class="bi bi-person-circle"></i>'
    WHEN LOWER(ft.title) LIKE '%loan%' THEN '<i class="bi bi-cash-coin"></i>'
    WHEN LOWER(ft.title) LIKE '%savings%' THEN '<i class="bi bi-piggy-bank"></i>'
    WHEN LOWER(ft.title) LIKE '%account%' THEN '<i class="bi bi-wallet2"></i>'
    WHEN LOWER(ft.title) LIKE '%transaction%' THEN '<i class="bi bi-arrow-left-right"></i>'
    WHEN LOWER(ft.title) LIKE '%member%' THEN '<i class="bi bi-people"></i>'
    WHEN LOWER(ft.title) LIKE '%report%' THEN '<i class="bi bi-graph-up-arrow"></i>'
    WHEN LOWER(ft.title) LIKE '%payment%' THEN '<i class="bi bi-credit-card"></i>'
    WHEN LOWER(ft.title) LIKE '%bank%' THEN '<i class="bi bi-bank"></i>'
    WHEN LOWER(ft.title) LIKE '%manage%' OR LOWER(ft.title) LIKE '%management%' THEN '<i class="bi bi-gear"></i>'
    ELSE f.icon
END
WHERE f.icon REGEXP '^[0-9]+$'
   OR f.icon REGEXP '>[0-9]+<'
   OR f.icon REGEXP 'bi-[0-9]+(-circle|-square|-fill)'
   OR f.icon = ''
   OR f.icon IS NULL
   OR f.icon NOT LIKE '%bi-%';

-- Step 2: Update any remaining features with fallback icons based on ID
UPDATE features f
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
WHERE f.icon REGEXP '^[0-9]+$'
   OR f.icon REGEXP '>[0-9]+<'
   OR f.icon REGEXP 'bi-[0-9]+(-circle|-square|-fill)'
   OR f.icon = ''
   OR f.icon IS NULL
   OR f.icon NOT LIKE '%bi-%';

-- Step 3: Final cleanup - set default icon for any remaining invalid entries
UPDATE features
SET icon = '<i class="bi bi-star-fill"></i>'
WHERE icon = '' OR icon IS NULL OR icon NOT LIKE '%bi-%';

-- ============================================
-- VERIFICATION (Optional - uncomment to run)
-- ============================================
-- SELECT f.id, f.icon, ft.title
-- FROM features f
-- LEFT JOIN feature_translations ft ON f.id = ft.feature_id
-- ORDER BY f.id;
