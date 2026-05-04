<?php
/**
 * Smoke tests for doctor_profile_image_url / portal_base_url (no DB required).
 * Run from project root: php tests/smoke_image_url_test.php
 */

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config/image_url.php';

$failures = 0;

function assert_cond(bool $ok, string $msg): void
{
    global $failures;
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        $failures++;
    } else {
        fwrite(STDOUT, "ok  {$msg}\n");
    }
}

$placeholder = 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=60';

// --- Early-return paths (do not depend on DOCUMENT_ROOT) ---
assert_cond(doctor_profile_image_url(null) === $placeholder, 'null yields placeholder');
assert_cond(doctor_profile_image_url('') === $placeholder, 'empty string yields placeholder');
assert_cond(doctor_profile_image_url('uploads/doctors/does_not_exist_xyz.bin') === $placeholder, 'missing file yields placeholder');
assert_cond(doctor_profile_image_url('../uploads/evil.jpg') === $placeholder, 'path traversal yields placeholder');

// --- Real file on disk ---
$smokeFile = $projectRoot . '/uploads/doctors/_smoke_test_pixel.txt';
if (!is_dir(dirname($smokeFile))) {
    mkdir(dirname($smokeFile), 0775, true);
}
file_put_contents($smokeFile, 'ok');

// Document root = project root → base URL prefix empty
$_SERVER['DOCUMENT_ROOT'] = $projectRoot;
assert_cond(portal_base_url() === '', 'portal_base_url empty when project is document root');
$u = doctor_profile_image_url('uploads/doctors/_smoke_test_pixel.txt');
assert_cond($u === '/uploads/doctors/_smoke_test_pixel.txt', "nested docroot URL got {$u}");

// Document root = parent of project → base /Doctor-Portel or basename
$_SERVER['DOCUMENT_ROOT'] = dirname($projectRoot);
$base = portal_base_url();
$expectedBase = '/' . basename($projectRoot);
assert_cond($base === $expectedBase, "portal_base_url nested folder expected {$expectedBase} got {$base}");
$u2 = doctor_profile_image_url('uploads/doctors/_smoke_test_pixel.txt');
$expectedUrl = $expectedBase . '/uploads/doctors/_smoke_test_pixel.txt';
assert_cond($u2 === $expectedUrl, "full URL expected {$expectedUrl} got {$u2}");

@unlink($smokeFile);

if ($failures > 0) {
    fwrite(STDERR, "\n{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "\nAll smoke_image_url tests passed.\n");
exit(0);
