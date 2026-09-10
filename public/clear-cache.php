<?php

/**
 * Diagnostic & Cache Clearing Tool for Laravel on Shared / cPanel Hosting
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$cacheDir = $baseDir . '/bootstrap/cache';

$deletedFiles = [];
if (is_dir($cacheDir)) {
    $files = scandir($cacheDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.gitignore') {
            continue;
        }
        $fullPath = $cacheDir . '/' . $file;
        if (is_file($fullPath) && @unlink($fullPath)) {
            $deletedFiles[] = $file;
        }
    }
}

// Bootstrap Laravel to run Artisan commands
$artisanOutput = [];
try {
    require $baseDir . '/vendor/autoload.php';
    $app = require_once $baseDir . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    Illuminate\Support\Facades\Artisan::call('optimize:clear');
    $artisanOutput[] = Illuminate\Support\Facades\Artisan::output();
} catch (\Throwable $e) {
    $artisanOutput[] = 'Artisan Error: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cache Cleared Successfully</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.6; }
        .card { max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 24px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        h1 { color: #34d399; font-size: 20px; margin-top: 0; }
        ul { background: #0f172a; padding: 16px 32px; border-radius: 8px; border: 1px solid #334155; }
        pre { background: #0f172a; padding: 12px; border-radius: 8px; overflow-x: auto; color: #38bdf8; font-size: 13px; }
        a { color: #38bdf8; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✅ Laravel Cache Cleared Successfully!</h1>
        <p>The following bootstrap cache files were removed from <code>bootstrap/cache/</code>:</p>
        <ul>
            <?php if (empty($deletedFiles)): ?>
                <li>No cached files found (already cleared).</li>
            <?php else: ?>
                <?php foreach ($deletedFiles as $df): ?>
                    <li>Deleted: <strong><?= htmlspecialchars($df) ?></strong></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <h3>Artisan Optimize Output:</h3>
        <pre><?= htmlspecialchars(implode("\n", $artisanOutput)) ?></pre>

        <p style="margin-top: 24px;">
            👉 <a href="/e/opening-night-demo">Open Event Page</a> | <a href="/vendor/dashboard">Open Vendor Dashboard</a>
        </p>
    </div>
</body>
</html>
