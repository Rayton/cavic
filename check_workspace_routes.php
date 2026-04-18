<?php
$files = [
    'resources/views/backend/admin/dashboard-admin.blade.php',
    'resources/views/backend/admin/action_center/index.blade.php',
    'resources/views/backend/admin/loan/workspace.blade.php',
    'resources/views/backend/admin/member/workspace.blade.php',
    'resources/views/backend/admin/finance/index.blade.php',
    'resources/views/backend/admin/reports/index.blade.php',
    'resources/views/backend/admin/administration/index.blade.php',
];
$names = [];
foreach ($files as $file) {
    $content = file_get_contents($file);
    preg_match_all('/route\([\'\"]([^\'\"]+)[\'\"]/', $content, $matches);
    foreach ($matches[1] as $name) {
        $names[$name] = true;
    }
}
$routes = json_decode(file_get_contents('route-list.json'), true);
$existing = [];
foreach ($routes as $route) {
    if (!empty($route['name'])) {
        $existing[$route['name']] = true;
    }
}
ksort($names);
foreach (array_keys($names) as $name) {
    echo (isset($existing[$name]) ? 'OK   ' : 'MISS '), $name, PHP_EOL;
}
