<?php
/**
 * Ensures the product image upload directory exists and is group-writable (0775).
 * Run from project root: php scripts/ensure_uploads_permissions.php
 */
declare(strict_types=1);

$cfg = require __DIR__ . '/../includes/config.php';
$dir = rtrim($cfg['uploads']['dir'], '/');

if (!is_dir($dir)) {
    if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "Failed to create directory: {$dir}\n");
        exit(1);
    }
    echo "Created: {$dir}\n";
} else {
    echo "Directory exists: {$dir}\n";
}

if (!chmod($dir, 0775)) {
    fwrite(STDERR, "Failed to chmod 0775: {$dir}\n");
    exit(1);
}

echo "Set mode 0775 (owner/group read+write+execute, others read+execute).\n";
