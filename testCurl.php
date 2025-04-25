<?php
//функция для тестирования запросов библиотеки curl

function sendRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();

    if ($data !== null) {
        // если данные — массив, превращаем в строку формата key=value
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded', // если не JSON
            'Accept: application/json',
        ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    echo "Response:\n$response\n\n";
}


//  Примеры вызовов: раскомментируй нужный 

// GET all tasks
// sendRequest('http://todolist/api.php/tasks');

// GET one task
// sendRequest('http://todolist/api.php/tasks/2');

// POST new task
//sendRequest('http://todolist/api.php/tasks', 'POST', [
//    'title' => 'Новая задача',
//    'description' => 'Описание задачи',
//    'status' => 'open'
//]);

// PUT update task
//sendRequest('http://todolist/api.php/tasks/3', 'POST', [
//    '_method' => 'PUT',
//    'title' => 'Обновлено',
//    'description' => 'Новое описание',
//    'status' => 'done'
//]);

// DELETE task
//sendRequest('http://todolist/api.php/tasks/1', [
//    '_method' => 'DELETE'
//]);
?>