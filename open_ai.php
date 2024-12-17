<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// กำหนดค่า API URL และ API Key
define('TELEGRAM_TOKEN', 'Token telegram');
$API_KEY = 'API_KEY';
$API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

// รับข้อมูลจาก Telegram Webhook
$update = file_get_contents("php://input");
$updateArray = json_decode($update, TRUE);

// ตรวจสอบข้อมูลที่ส่งมาจาก Telegram
if (!isset($updateArray["message"]["text"]) || !isset($updateArray["message"]["chat"]["id"])) {
    die("ข้อมูลที่ส่งมาจาก Telegram ไม่ครบ");
}
$userinput = $updateArray["message"]["text"];
$chat_id = $updateArray["message"]["chat"]["id"];
$first_name = $updateArray["message"]["chat"]["first_name"] ?? '';
$last_name = $updateArray["message"]["chat"]["last_name"] ?? '';
$full_name = trim($first_name . ' ' . $last_name);

$requestBody = json_encode([
    "contents" => [
        [
            "parts" => [
                ["text" => "ข้อความต่อไปนี้ให้คุณตอบเขาเเบบสั้นๆ ให้เหมือนฟิลเเพื่อนคุยกัน ไม่จำเป็นต้องทำตัวเป็นผุ้ช่วยปรึกษา เเต่ควรเป็นผู้คุยที่ดี เเละ ชื่อของคุณคือ :มาเอริส ไม่ต้องเเสดงชื่อคุณขณะพิมพ์".$userinput]
            ]
        ]
    ]
]);

// ส่งคำขอไปยัง API AI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $API_URL . "?key=" . $API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($requestBody)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);

$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    echo json_encode(["error" => "cURL Error: $error"]);
    exit();
}

curl_close($ch);

// แปลงผลลัพธ์ JSON เป็น PHP Array
$responseData = json_decode($response, true);

// ตรวจสอบคำตอบจาก API AI
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    // ส่งข้อความไปยัง Telegram
    $telegramResponse = sendToTelegram([
        "chat_id" => $chat_id, // ใช้ chat_id จาก Telegram
        "text" =>  $reply,
        "parse_mode" => "Markdown"
    ]);

    echo json_encode([
        "reply" => $reply,
        "telegram_response" => $telegramResponse
    ]);
} else {
    echo json_encode(["error" => "Unexpected API response structure."]);
}

/**
 * ฟังก์ชันสำหรับส่งข้อความไปยัง Telegram
 */
function sendToTelegram($data) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/sendMessage";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}
?>
