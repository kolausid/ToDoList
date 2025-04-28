<?php
header("Content-Type: application/json");
require_once 'db.php';

file_put_contents('log.txt', print_r([
    'method' => $_SERVER['REQUEST_METHOD'],
    '_method' => $_POST['_method'] ?? null,
    'uri' => $_SERVER['REQUEST_URI'],
    'input' => file_get_contents("php://input")
], true), FILE_APPEND);

// получение и определение метода
$method = $_SERVER['REQUEST_METHOD'];

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Эмуляция методов PUT, DELETE через POST
if ($method === 'POST' and isset($data['_method'])) {
    $method = strtoupper($data['_method']);
}

//получаем путь и разбиваем, чтобы не использовать регулярки
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$segments = explode('/', $uri);

// /tasks вывод всех задач
if ($method === 'GET' and $uri === '/api.php/tasks') {
    try {
        $stmt = $pdo->query("SELECT * FROM tasks");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode([
            'success' => true,
            'tasks' => $tasks
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} 

// создание задачи /tasks
if ($method === 'POST' and $uri === '/api.php/tasks') {
    //$data = json_decode(file_get_contents("php://input"), true);
    //parse_str(file_get_contents("php://input"), $data);

    // Валидация: все поля должны быть заполнены
    if (empty($data['title']) || empty($data['description']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Все поля должны быть заполнены'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Проверка уникальности title
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE title = ?");
    $stmt->execute([$data['title']]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Задача с таким названием уже существует'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    //запросы
    $sql = "INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':status' => $data['status']
    ]);
    
    // вывод с последним созданным id
    echo json_encode(['success' => true, 'task_id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// получение одной задачи /task/id
if ($method === 'GET' and $segments[2] === 'tasks' and isset($segments[3])) {
    $taskId = (int)$segments[3];
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($task) {
        echo json_encode(['success' => true, 'task' => $task], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'задача не найдена'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
} 

// обновить задачу /tasks/id
if ($method === 'PUT' and $segments[2] === 'tasks' and isset($segments[3])) {
    $taskId = (int)$segments[3];
    //$data = json_decode(file_get_contents("php://input"), true);
    //parse_str(file_get_contents("php://input"), $data);

    // Валидация: все поля должны быть заполнены
    if (empty($data['title']) || empty($data['description']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Все поля должны быть заполнены'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Проверка уникальности title для других задач (не самой обновляемой)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE title = ? AND id != ?");
    $stmt->execute([$data['title'], $taskId]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Задача с таким названием уже существует'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?");
    $stmt->execute([$data['title'], $data['description'], $data['status'], $taskId]);
    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => "задача $taskId обновлена"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['success' => false, 'message' => 'задача не найдена'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;

} 

// удаление задач /tasks/id

if ($method === 'DELETE' and $segments[2] === 'tasks' and isset($segments[3])) {
    $taskId = (int)$segments[3];

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    if ($stmt->rowCount()) {
    echo json_encode(['success' => true, 'message' => "задача $taskId удалена"]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'задача не найдена']);
    }
    exit;
}

?>