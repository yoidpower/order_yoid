<?php
/**
 * YOID Power — Contact Form Handler
 * Uses PHPMailer to send form submissions to orders@yoidpower.com via SMTP.
 *
 * Endpoint: POST /send.php
 * Returns:  JSON { "ok": true } | { "ok": false, "msg": "..." }
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed.']);
    exit;
}

// ── Load environment variables ────────────────────────────────
// Values come from .env file passed via Docker env_file
$smtp_host     = getenv('SMTP_HOST')     ?: '';
$smtp_port     = (int)(getenv('SMTP_PORT') ?: 587);
$smtp_user     = getenv('SMTP_USER')     ?: '';
$smtp_pass     = getenv('SMTP_PASS')     ?: '';
$smtp_secure   = getenv('SMTP_SECURE')   ?: 'tls';   // 'tls' or 'ssl'
$to_email      = getenv('MAIL_TO')       ?: 'orders@yoidpower.com';
$from_email    = getenv('MAIL_FROM')     ?: $smtp_user;
$from_name     = getenv('MAIL_FROM_NAME') ?: 'YOID Power Website';

// ── Sanitise & validate input ─────────────────────────────────
function sanitise(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

$business_name  = sanitise($_POST['business_name']  ?? '');
$location_type  = sanitise($_POST['location_type']  ?? '');
$city_region    = sanitise($_POST['city_region']    ?? '');
$contact_email  = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
$message        = sanitise($_POST['message']        ?? '');

// Required field check
if (!$business_name || !$location_type || !$city_region || !$contact_email) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'msg' => 'Please fill in all required fields.']);
    exit;
}

// ── Autoload PHPMailer ────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ── Build email ───────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host        = $smtp_host;
    $mail->SMTPAuth    = true;
    $mail->Username    = $smtp_user;
    $mail->Password    = $smtp_pass;
    $mail->SMTPSecure  = $smtp_secure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port        = $smtp_port;
    $mail->CharSet     = 'UTF-8';

    // Recipients
    $mail->setFrom($from_email, $from_name);
    $mail->addAddress($to_email, 'YOID Orders');
    $mail->addReplyTo((string)$contact_email, $business_name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Station Request — {$business_name}";
    $mail->Body    = buildHtmlEmail($business_name, $location_type, $city_region, (string)$contact_email, $message);
    $mail->AltBody = buildTextEmail($business_name, $location_type, $city_region, (string)$contact_email, $message);

    $mail->send();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    error_log('[YOID send.php] Mailer error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode([
        'ok'  => false,
        'msg' => 'Failed to send your request. Please email us directly at orders@yoidpower.com',
    ]);
}

// ── Email templates ───────────────────────────────────────────
function buildHtmlEmail(
    string $business_name,
    string $location_type,
    string $city_region,
    string $contact_email,
    string $message
): string {
    $msg_html = nl2br($message ?: '(no message provided)');
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
    <body style="margin:0;padding:0;background:#f5f5f5;font-family:Inter,Arial,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:40px 0;">
        <tr><td align="center">
          <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;max-width:600px;">
            <!-- Header -->
            <tr>
              <td style="background:#0C0C14;padding:28px 36px;">
                <p style="margin:0;font-size:24px;font-weight:900;color:#ffffff;letter-spacing:0.1em;">YOID<span style="color:#E8175D;">.</span></p>
                <p style="margin:6px 0 0;font-size:12px;color:rgba(255,255,255,0.5);letter-spacing:0.08em;text-transform:uppercase;">New Station Request</p>
              </td>
            </tr>
            <!-- Body -->
            <tr>
              <td style="padding:36px;">
                <p style="margin:0 0 24px;font-size:16px;color:#333;line-height:1.6;">
                  A new station request has been submitted via <strong>order.yoidpower.com</strong>.
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                  <tr>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;width:40%;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#888;">Business Name</td>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:15px;color:#222;font-weight:600;">{$business_name}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#888;">Location Type</td>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:15px;color:#222;">{$location_type}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#888;">City / Region</td>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:15px;color:#222;">{$city_region}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#888;">Contact Email</td>
                    <td style="padding:12px 0;border-bottom:1px solid #f0f0f0;font-size:15px;color:#E8175D;"><a href="mailto:{$contact_email}" style="color:#E8175D;text-decoration:none;">{$contact_email}</a></td>
                  </tr>
                  <tr>
                    <td style="padding:16px 0 0;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#888;vertical-align:top;">Message</td>
                    <td style="padding:16px 0 0;font-size:15px;color:#444;line-height:1.6;">{$msg_html}</td>
                  </tr>
                </table>
                <p style="margin:28px 0 0;padding:16px;background:#fff5f8;border-radius:8px;font-size:13px;color:#888;border-left:3px solid #E8175D;">
                  Reply to this email to contact <strong>{$business_name}</strong> at {$contact_email}.
                </p>
              </td>
            </tr>
            <!-- Footer -->
            <tr>
              <td style="background:#f9f9f9;padding:20px 36px;font-size:12px;color:#aaa;border-top:1px solid #eee;">
                YOID Power &bull; orders@yoidpower.com &bull; order.yoidpower.com
              </td>
            </tr>
          </table>
        </td></tr>
      </table>
    </body>
    </html>
    HTML;
}

function buildTextEmail(
    string $business_name,
    string $location_type,
    string $city_region,
    string $contact_email,
    string $message
): string {
    return <<<TEXT
    NEW YOID STATION REQUEST
    ========================

    Business Name : {$business_name}
    Location Type : {$location_type}
    City / Region : {$city_region}
    Contact Email : {$contact_email}

    Message:
    {$message}

    ---
    YOID Power — orders@yoidpower.com
    TEXT;
}
