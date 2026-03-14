<?php
// api/controllers/AIController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$apiKey = getenv('GROQ_API_KEY') ?: ($_ENV['GROQ_API_KEY'] ?? '');

if (empty($apiKey)) {
    echo json_encode(['success' => false, 'error' => 'Groq API Key not found in .env']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$prompt = $data['prompt'] ?? '';

if (empty($prompt)) {
    echo json_encode(['success' => false, 'error' => 'Prompt is required']);
    exit;
}

$api_url = "https://api.groq.com/openai/v1/chat/completions";

$payload = [
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a professional library management consultant. Provide clear, data-driven insights and actionable advice for the library administrator based on the provided metrics.'],
        ['role' => 'user', 'content' => $prompt]
    ],
    'temperature' => 0.7,
    'max_tokens' => 1024
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . trim($apiKey)
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['success' => false, 'error' => 'Curl error: ' . curl_error($ch)]);
} else {
    $result = json_decode($response, true);
    if ($http_code === 200) {
        if (isset($result['choices'][0]['message']['content'])) {
            echo json_encode(['success' => true, 'answer' => $result['choices'][0]['message']['content']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid API Response Format', 'details' => $result]);
        }
    } else {
        // Return clear error message from Groq if available
        $errorMessage = $result['error']['message'] ?? 'Unknown API Error';
        echo json_encode([
            'success' => false, 
            'error' => "AI API error ($http_code): $errorMessage", 
            'details' => $result
        ]);
    }
}

curl_close($ch);
