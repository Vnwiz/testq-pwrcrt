<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');
    $decodedData = json_decode($data, true);

    if ($decodedData && is_array($decodedData)) {
        // Путь к файлу, куда будут сохраняться данные
        $file = 'coords_data.txt';
        
        // Открываем файл для добавления данных
        $handle = fopen($file, 'a');
        
        if ($handle) {
            foreach ($decodedData as $entry) {
                $line = sprintf("X: %d, Y: %d, Time Spent: %d ms\n", $entry['x'], $entry['y'], $entry['timeSpent']);
                fwrite($handle, $line);
            }
            fclose($handle);
            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Cannot open file']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>