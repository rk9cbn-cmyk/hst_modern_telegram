<?php
// webhook.php - обработчик вебхуков от Telegram
$token = "ВАШ_ТОКЕН_БОТА";
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) exit;

$chat_id = $update["message"]["chat"]["id"] ?? $update["callback_query"]["message"]["chat"]["id"] ?? null;
$text = $update["message"]["text"] ?? "";
$data = $update["web_app_data"]["data"] ?? null;

if ($data) {
    // Получены данные из Mini App
    $appData = json_decode($data, true);
    
    if ($appData['action'] == 'comp_finished') {
        $reply = "🏆 Соревнование завершено!\n";
        $reply .= "📊 Скорость: {$appData['speed']} зн/мин\n";
        $reply .= "📝 Текстов: {$appData['logs']}\n";
        $reply .= "🕐 " . date('H:i:s', strtotime($appData['timestamp']));
        
        sendMessage($chat_id, $reply);
    }
}

if ($text == "/start") {
    $keyboard = [
        'keyboard' => [
            [['text' => '🎯 Открыть тренажёр', 'web_app' => ['url' => 'https://rk9cbn-cmyk.github.io/hst_modern/']]]
        ],
        'resize_keyboard' => true
    ];
    
    sendMessage($chat_id, "👋 Привет! Я бот-тренажёр азбуки Морзе.\n\nНажми кнопку ниже чтобы начать тренировку:", $keyboard);
}

function sendMessage($chat_id, $text, $reply_markup = null) {
    global $token;
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $post = ['chat_id' => $chat_id, 'text' => $text];
    if ($reply_markup) $post['reply_markup'] = json_encode($reply_markup);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>