<?php
require_once __DIR__ . '/smtp_client.php';

/**
 * Send notifications instantly - all content in English to avoid spam
 */
function send_notification_instantly($to, $type, $data)
{
    if (empty($to))
        return ['success' => false, 'message' => 'No recipient email'];

    // SMTP configuration from .env
    $user = getenv('SMTP_USER') ?: 'info@ontomeel.com';
    $pass = getenv('SMTP_PASS');
    $host = getenv('SMTP_HOST') ?: 'ontomeel.com';
    $port = getenv('SMTP_PORT') ?: 465;

    if (!$pass) {
        error_log("SMTP_PASS not found in environment variables.");
    }

    $config = [
        'host' => $host,
        'port' => $port,
        'user' => $user,
        'pass' => $pass
    ];

    $subject = "";
    $title = "";
    $content = "";
    $color = "#2563eb"; // Professional blue

    switch ($type) {
        case 'order_placed':
            $subject = "Order Confirmed - #" . $data['invoice_no'];
            $title = "Thank You for Your Order!";
            $color = "#16a34a";
            $content = "
                <p>Dear <strong>" . htmlspecialchars($data['name']) . "</strong>,</p>
                <p>Your order <strong>#" . $data['invoice_no'] . "</strong> has been successfully placed and is being processed.</p>
                <div style='background: #f8fafc; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #e2e8f0;'>
                    <p style='margin: 0 0 10px 0; color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: bold;'>Order Details</p>
                    <p style='margin: 5px 0;'><strong>Amount:</strong> BDT " . number_format($data['amount'], 2) . "</p>
                    <p style='margin: 5px 0;'><strong>Shipping Address:</strong> " . htmlspecialchars($data['address']) . "</p>
                </div>
                <p>We will notify you once your order is shipped.</p>
            ";
            break;

        case 'borrow_active':
            $subject = "Book Borrowed - #" . $data['invoice_no'];
            $title = "Book Borrowing Confirmed";
            $color = "#7c3aed";
            $content = "
                <p>Dear " . htmlspecialchars($data['name']) . ",</p>
                <p>You have successfully borrowed the book <strong>'" . htmlspecialchars($data['book_title']) . "'</strong>.</p>
                <div style='background: #f5f3ff; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #ddd6fe;'>
                    <p style='margin: 0; color: #7c3aed; font-weight: bold; font-size: 16px;'>Return Due Date: " . htmlspecialchars($data['due_date']) . "</p>
                    <p style='margin: 10px 0 0 0; font-size: 13px; color: #6b7280;'>Please return the book on time so others can enjoy it too.</p>
                </div>
            ";
            break;

        case 'account_created':
            $subject = "Welcome to Ontomeel Bookshop - Your Membership ID: " . $data['invoice_no'];
            $title = "Welcome Aboard!";
            $color = "#2563eb";
            $content = "
                <p>Dear <strong>" . htmlspecialchars($data['name']) . "</strong>,</p>
                <p>Your membership account has been successfully created at our POS terminal.</p>
                <div style='background: #eff6ff; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #bfdbfe;'>
                    <p style='margin: 5px 0;'><strong>Membership ID:</strong> " . htmlspecialchars($data['invoice_no']) . "</p>
                    <p style='margin: 5px 0;'><strong>" . htmlspecialchars($data['address']) . "</strong></p>
                </div>
                <p>You can now use your Membership ID to borrow books or earn rewards on every purchase.</p>
            ";
            break;

        default:
            $subject = "Ontomeel Bookshop - Notification";
            $title = "Account Update";
            $content = "<p>You have a new update on your Ontomeel Bookshop account. Please log in to your dashboard to view details.</p>";
            break;
    }

    $from_name = (strpos($type, 'order') !== false) ? "Ontomeel Orders" : "Ontomeel Bookshop";
    $config['from_name'] = $from_name;
    $config['reply_to'] = $user;

    $html_message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333333;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px;'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden;'>
                        <tr>
                            <td style='background: " . $color . "; color: #ffffff; padding: 30px; text-align: center;'>
                                <h1 style='margin: 0; font-size: 24px; font-weight: bold;'>" . $title . "</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px;'>
                                " . $content . "
                                <div style='text-align: center; margin-top: 25px;'>
                                    <a href='https://ontomeel.com/dashboard' style='display: inline-block; padding: 12px 30px; background: " . $color . "; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>View Dashboard</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style='background: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #e9ecef;'>
                                <p style='margin: 0 0 10px 0; font-size: 12px; color: #adb5bd;'>This is an automated message. Please do not reply to this email.</p>
                                <p style='margin: 20px 0 0 0; font-size: 11px; color: #999999;'>Ontomeel Bookshop | Bangladesh</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>";

    return send_smtp_email($to, $subject, $html_message, $config, true);
}

function queueNotification($pdo, $to, $type, $payload) {
    try {
        $stmt = $pdo->prepare("INSERT INTO email_queue (recipient, type, payload) VALUES (?, ?, ?)");
        $stmt->execute([$to, $type, json_encode($payload)]);
        
        // Internal trigger for worker
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $workerUrl = $protocol . "://" . $host . "/ontomeel-pos/api/shared/email_worker.php";
        
        $parts = parse_url($workerUrl);
        if ($parts) {
            $port = isset($parts['port']) ? $parts['port'] : ($parts['scheme'] === 'https' ? 443 : 80);
            $host_conn = ($parts['scheme'] === 'https' ? "ssl://" : "") . $parts['host'];
            $fp = @fsockopen($host_conn, $port, $errno, $errstr, 1);
            if ($fp) {
                $out = "GET " . ($parts['path'] ?? '/') . " HTTP/1.1\r\n";
                $out .= "Host: " . $parts['host'] . "\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                fclose($fp);
            }
        }
    } catch (Exception $e) {
        error_log("POS Email Queue Error: " . $e->getMessage());
    }
}
