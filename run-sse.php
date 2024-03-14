<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

date_default_timezone_set('Asia/Manila');

function sendSseMessage($id, $message, $event = 'message') {
    echo "id: $id" . PHP_EOL;
    echo "event: $event" . PHP_EOL;
    echo 'data: ' . json_encode($message) . PHP_EOL;
    echo PHP_EOL;
    ob_flush();
    flush();
}

while (true) {
    if (date('H:i') === '16:37') {
        sendSseMessage(1, 'This is a message from the server.');
        break;
    }

    sleep(30);
}

?>
