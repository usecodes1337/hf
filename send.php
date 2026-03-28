<?php
// Разрешаем браузеру отправлять сюда данные (решает проблему CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка предварительного запроса браузера (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получаем данные из формы сайта
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->name) && !empty($data->phone)) {
    
    // ВАШ ТОКЕН MAX
    $max_bot_token = 'f9LHodD0cOLvuekJkgTYrppadPYU4abe5K1ZNc2d-wsIWbad6TyWrarVNJRPyfVrWGE8HC9Sc9bwUvwTQW_N';
    $max_chat_id = '-72492452752012'; 

    // Формируем красивое сообщение
    $message = !empty($data->message) ? $data->message : 'Не указаны';
    $text = "🛠 *Новая заявка (HARDFORMA)*\n\n👤 *Имя:* {$data->name}\n📞 *Телефон:* {$data->phone}\n💬 *Пожелания:* {$message}";

    // Настраиваем отправку в MAX
    $ch = curl_init('https://platform-api.max.ru/message/markdown');
    $payload = json_encode([
        'chat_id' => $max_chat_id,
        'text' => $text
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $max_bot_token
    ]);

    // Выполняем запрос
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Проверяем ответ от сервера MAX
    if ($httpcode == 200) {
        http_response_code(200);
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(500);
        // Если ошибка, выводим ответ MAX, чтобы понять причину
        echo json_encode(["status" => "error", "details" => $response]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "details" => "Пустые поля формы"]);
}
?>