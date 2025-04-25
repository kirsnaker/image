<?php
header('Content-Type: application/json');

// Настройки
$uploadDir = 'uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Создаем папку для загрузок, если ее нет
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Проверяем, был ли загружен файл
if (!isset($_FILES['image'])) {
    echo json_encode(['error' => 'Файл не был загружен']);
    exit;
}

$file = $_FILES['image'];

// Проверка на ошибки загрузки
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Размер файла превышает допустимый',
        UPLOAD_ERR_FORM_SIZE => 'Размер файла превышает допустимый',
        UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
        UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
        UPLOAD_ERR_EXTENSION => 'Загрузка файла остановлена расширением'
    ];
    $error = $errorMessages[$file['error']] ?? 'Неизвестная ошибка загрузки';
    echo json_encode(['error' => $error]);
    exit;
}

// Проверка типа файла
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Недопустимый тип файла. Разрешены только JPG, PNG и GIF']);
    exit;
}

// Проверка размера файла
if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'Файл слишком большой. Максимальный размер: 5MB']);
    exit;
}

// Генерируем уникальное имя файла
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$destination = $uploadDir . $filename;

// Перемещаем загруженный файл
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Формируем URL к изображению
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $imageUrl = $protocol . '://' . $host . '/' . $destination;
    
    echo json_encode(['imageUrl' => $imageUrl]);
} else {
    echo json_encode(['error' => 'Ошибка при сохранении файла']);
}
?>