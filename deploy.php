<?php
/**
 * GitHub Webhook Auto-Deploy Script
 * 
 * This script receives GitHub webhook POST requests and runs git pull
 * to auto-deploy changes to the live server.
 * 
 * Place this file in: ~/domains/relearn.co.ke/public_html/deploy.php
 * Webhook URL: https://www.relearn.co.ke/deploy.php
 */

// ============================================
// CONFIGURATION
// ============================================
// Read secret from .deploy-secret file (not tracked by git)
$secret_file = __DIR__ . '/.deploy-secret';
if (file_exists($secret_file)) {
    $secret = trim(file_get_contents($secret_file));
} else {
    $secret = getenv('DEPLOY_SECRET') ?: '';
}
$repo_path = __DIR__; // Git repo is in public_html
$branch = 'main';
$log_file = __DIR__ . '/storage/logs/deploy.log';

// ============================================
// SECURITY CHECKS
// ============================================
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verify GitHub signature
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (empty($signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'No signature provided']);
    exit;
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    logDeploy("❌ Invalid signature from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Only deploy on push to main branch
$data = json_decode($payload, true);
$ref = $data['ref'] ?? '';
if ($ref !== "refs/heads/{$branch}") {
    echo json_encode(['message' => "Ignored push to {$ref}, only deploying {$branch}"]);
    exit;
}

// ============================================
// DEPLOYMENT
// ============================================
logDeploy("🚀 Deployment started by push from " . ($data['pusher']['name'] ?? 'unknown'));

// Set HOME for composer (required when running from web context)
$home_dir = getenv('HOME') ?: '/home/relearnc';
$laravel_root = '/home/relearnc/domains/relearn.co.ke/relearn';
putenv("HOME={$home_dir}");
putenv("COMPOSER_HOME={$home_dir}/.composer");

$commands = [
    // Step 1: Pull latest code into public_html (git repo)
    "cd {$repo_path}",
    "export HOME={$home_dir}",
    "export COMPOSER_HOME={$home_dir}/.composer",
    "git fetch origin {$branch} 2>&1",
    "git reset --hard origin/{$branch} 2>&1",

    // Step 2: Sync updated files from public_html to relearn/ (the live Laravel root)
    // Sync key Laravel directories individually to avoid overwriting .env, storage, vendor, etc.
    "cp -af {$repo_path}/app {$laravel_root}/ 2>&1",
    "cp -af {$repo_path}/config {$laravel_root}/ 2>&1",
    "cp -af {$repo_path}/database {$laravel_root}/ 2>&1",
    "cp -af {$repo_path}/resources {$laravel_root}/ 2>&1",
    "cp -af {$repo_path}/routes {$laravel_root}/ 2>&1",
    "cp -af {$repo_path}/public {$laravel_root}/ 2>&1",
    "cp -f {$repo_path}/composer.json {$laravel_root}/ 2>&1",
    "cp -f {$repo_path}/composer.lock {$laravel_root}/ 2>&1",
    "cp -f {$repo_path}/artisan {$laravel_root}/ 2>&1",

    // Step 3: Run Laravel build commands in the live relearn/ directory
    "cd {$laravel_root}",
    "composer install --no-dev --optimize-autoloader --no-interaction 2>&1 || true",
    "php artisan config:cache 2>&1 || true",
    "php artisan route:cache 2>&1 || true",
    "php artisan view:cache 2>&1 || true",
    "php artisan migrate --force 2>&1 || true",
];

$output = [];
$full_command = implode(' && ', $commands);
exec($full_command, $output, $return_code);

$output_text = implode("\n", $output);
$status = $return_code === 0 ? 'success' : 'failed';

logDeploy("📋 Deploy {$status} (exit code: {$return_code})");
logDeploy("Output:\n{$output_text}");

http_response_code($return_code === 0 ? 200 : 500);
echo json_encode([
    'status' => $status,
    'output' => $output_text,
    'timestamp' => date('Y-m-d H:i:s'),
]);

// ============================================
// HELPERS
// ============================================
function logDeploy(string $message): void
{
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] {$message}\n";
    @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
}
