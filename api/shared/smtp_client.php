<?php
/**
 * SMTP Client for Secure Email Sending
 * Sends email via SMTP with SSL/TLS and Authentication
 */

function get_smtp_response($socket, $debug = false)
{
    $res = "";
    while ($str = fgets($socket, 515)) {
        $res .= $str;
        if (substr($str, 3, 1) == " ")
            break;
    }
    if ($debug)
        echo "S <- $res<br>";
    return $res;
}

function send_smtp_email($to, $subject, $message, $config, $is_html = false)
{
    if (empty($to))
        return ["success" => false, "message" => "Recipient empty"];

    $host = $config['host'];
    $port = $config['port'];
    $user = $config['user'];
    $pass = $config['pass'];
    $from_name = $config['from_name'] ?? "Ontomeel Bookshop";
    $reply_to = $config['reply_to'] ?? $user;

    $debug_log = [];
    $debug_log[] = "Connecting to ssl://$host:$port";

    // Create Socket with SSL context
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = @stream_socket_client(
        "ssl://$host:$port",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        $debug_log[] = "Connection failed: $errstr ($errno)";
        return ["success" => false, "message" => "Connection failed: $errstr ($errno)", "debug" => implode("\n", $debug_log)];
    }

    $debug_log[] = "Connected successfully";

    // Server Greeting
    $res = get_smtp_response($socket, false);
    $debug_log[] = "Greeting: " . substr($res, 0, 50);

    // EHLO - use the actual host
    fwrite($socket, "EHLO $host\r\n");
    $res = get_smtp_response($socket, false);
    $debug_log[] = "EHLO response: " . substr($res, 0, 100);

    // AUTH LOGIN
    fwrite($socket, "AUTH LOGIN\r\n");
    $res = get_smtp_response($socket, false);
    $debug_log[] = "AUTH LOGIN: " . substr($res, 0, 50);

    if (strpos($res, "334") === false) {
        fclose($socket);
        $debug_log[] = "AUTH LOGIN failed - server didn't respond with 334";
        return ["success" => false, "message" => "AUTH LOGIN failed: " . substr($res, 0, 100), "debug" => implode("\n", $debug_log)];
    }

    // Send username
    fwrite($socket, base64_encode($user) . "\r\n");
    $res = get_smtp_response($socket, false);
    $debug_log[] = "Username sent: " . substr($res, 0, 50);

    // Send password
    fwrite($socket, base64_encode($pass) . "\r\n");
    $res = get_smtp_response($socket, false);
    $debug_log[] = "Password sent, response: " . substr($res, 0, 50);

    if (strpos($res, "235") === false) {
        fclose($socket);
        $debug_log[] = "Authentication FAILED - 235 not received";
        return ["success" => false, "message" => "Authentication failed: " . substr($res, 0, 100), "debug" => implode("\n", $debug_log)];
    }

    $debug_log[] = "Authentication successful!";

    // MAIL FROM
    fwrite($socket, "MAIL FROM: <$user>\r\n");
    get_smtp_response($socket);

    // RCPT TO
    fwrite($socket, "RCPT TO: <$to>\r\n");
    get_smtp_response($socket);

    // DATA
    fwrite($socket, "DATA\r\n");
    get_smtp_response($socket);

    // Encode Subject and From Name
    $encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $encoded_from_name = "=?UTF-8?B?" . base64_encode($from_name) . "?=";

    // Boundary for multipart
    $boundary = "----=_Part_" . md5(time() . uniqid());
    $domain = "ontomeel.com";
    $msg_id = "<" . time() . "." . uniqid() . "@" . $domain . ">";
    $date = date('r');

    // Create Plain Text version
    $text_message = $is_html ? strip_tags(str_replace(['<br>', '</p>', '<div>', '</div>', '<span>', '</span>'], "\n", $message)) : $message;
    $text_message = html_entity_decode($text_message, ENT_QUOTES, 'UTF-8');
    $encoded_text = quoted_printable_encode($text_message);
    $encoded_html = $is_html ? quoted_printable_encode($message) : "";

    // Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Date: $date\r\n";
    $headers .= "Message-ID: $msg_id\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "From: $encoded_from_name <$user>\r\n";
    $headers .= "Reply-To: $encoded_from_name <$reply_to>\r\n";
    $headers .= "Return-Path: <$user>\r\n";
    $headers .= "Subject: $encoded_subject\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: Ontomeel Mailer\r\n";
    $headers .= "Importance: normal\r\n";
    $headers .= "Auto-Submitted: auto-generated\r\n";
    $headers .= "X-Auto-Response-Suppress: OOF, DR, RN, NRN\r\n";
    $headers .= "Content-Language: en-US\r\n";

    if ($is_html) {
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $headers .= "List-Unsubscribe: <mailto:unsubscribe@ontomeel.com>, <https://ontomeel.com/unsubscribe>\r\n";
        $headers .= "Precedence: transactional\r\n\r\n";

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= $encoded_text . "\r\n\r\n";

        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= $encoded_html . "\r\n\r\n";
        $body .= "--$boundary--\r\n";
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $headers .= "List-Unsubscribe: <mailto:unsubscribe@ontomeel.com>\r\n";
        $headers .= "Precedence: transactional\r\n\r\n";
        $body = $encoded_text;
    }

    fwrite($socket, $headers . $body . "\r\n.\r\n");
    $res = get_smtp_response($socket);
    $debug_log[] = "DATA response: " . substr($res, 0, 50);

    // QUIT
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    $debug_log[] = "Email sent!";

    return (strpos($res, "250") !== false || strpos($res, "200") !== false)
        ? ["success" => true, "debug" => implode("\n", $debug_log)]
        : ["success" => false, "message" => $res, "debug" => implode("\n", $debug_log)];
}
