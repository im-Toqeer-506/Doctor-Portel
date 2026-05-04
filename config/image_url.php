<?php

/**
 * URL path prefix for this project root (e.g. '' or '/Doctor-Portel') so uploads work from any page depth.
 */
function portal_base_url(): string
{
    if (empty($_SERVER['DOCUMENT_ROOT'])) {
        return '';
    }
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    $projectRoot = realpath(dirname(__DIR__));
    if ($docRoot === false || $projectRoot === false) {
        return '';
    }
    $docRoot = str_replace('\\', '/', $docRoot);
    $projectRoot = str_replace('\\', '/', $projectRoot);
    if ($projectRoot === $docRoot) {
        return '';
    }
    $prefix = $docRoot . '/';
    if (strpos($projectRoot, $prefix) !== 0) {
        return '';
    }
    return substr($projectRoot, strlen($docRoot));
}

/**
 * Public URL for a doctor profile image, or stock placeholder when missing / invalid.
 *
 * @param string|null $storedPath Value from doctors.image (e.g. uploads/doctors/file.jpg)
 */
function doctor_profile_image_url(?string $storedPath): string
{
    $placeholder = 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=60';
    if ($storedPath === null) {
        return $placeholder;
    }
    $storedPath = trim($storedPath);
    if ($storedPath === '') {
        return $placeholder;
    }
    if (strpos($storedPath, '..') !== false) {
        return $placeholder;
    }
    $normalized = str_replace('\\', '/', $storedPath);
    $fullPath = dirname(__DIR__) . '/' . ltrim($normalized, '/');
    if (!is_file($fullPath)) {
        return $placeholder;
    }
    $base = portal_base_url();
    $urlPath = '/' . ltrim($normalized, '/');
    return ($base === '' ? '' : rtrim($base, '/')) . $urlPath;
}
