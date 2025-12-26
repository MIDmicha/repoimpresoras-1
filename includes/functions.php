<?php
/**
 * Funciones de utilidad del sistema
 */

/**
 * Sanitizar datos de entrada
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Verificar si el usuario está autenticado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function hasRole($rol_id) {
    return isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == $rol_id;
}

/**
 * Redirigir a otra página
 */
function redirect($url) {
    header("Location: " . BASE_URL . "/" . $url);
    exit();
}

/**
 * Mostrar mensaje flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Obtener y limpiar mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatear fecha y hora
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i:s') {
    if (empty($datetime)) return '-';
    $timestamp = strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Generar hash de contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validar email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Obtener IP del cliente
 */
function getClientIP() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Obtener User Agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validar archivo subido
 */
function validateUploadedFile($file, $allowedTypes = [], $maxSize = MAX_FILE_SIZE) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al subir el archivo'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'El archivo es demasiado grande'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    return ['success' => true];
}

/**
 * Subir archivo
 */
function uploadFile($file, $destination) {
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $destination . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Error al guardar el archivo'];
}

/**
 * Registrar auditoría
 */
function logAudit($pdo, $tabla, $id_registro, $accion, $datos_anteriores = null, $datos_nuevos = null) {
    try {
        $sql = "INSERT INTO auditoria (tabla, id_registro, accion, datos_anteriores, datos_nuevos, id_usuario, ip_usuario, user_agent) 
                VALUES (:tabla, :id_registro, :accion, :datos_anteriores, :datos_nuevos, :id_usuario, :ip_usuario, :user_agent)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tabla' => $tabla,
            ':id_registro' => $id_registro,
            ':accion' => $accion,
            ':datos_anteriores' => $datos_anteriores ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
            ':datos_nuevos' => $datos_nuevos ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
            ':id_usuario' => $_SESSION['user_id'] ?? null,
            ':ip_usuario' => getClientIP(),
            ':user_agent' => getUserAgent()
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error en auditoría: " . $e->getMessage());
        return false;
    }
}

/**
 * Paginar resultados
 */
function paginate($total_records, $current_page = 1, $records_per_page = RECORDS_PER_PAGE) {
    $total_pages = ceil($total_records / $records_per_page);
    $current_page = max(1, min($total_pages, $current_page));
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'records_per_page' => $records_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Generar opciones de select
 */
function generateSelectOptions($array, $selected = null, $key_field = 'id', $value_field = 'nombre') {
    $html = '';
    foreach ($array as $item) {
        $selected_attr = ($item[$key_field] == $selected) ? 'selected' : '';
        $html .= "<option value='{$item[$key_field]}' {$selected_attr}>{$item[$value_field]}</option>";
    }
    return $html;
}

/**
 * Registrar en auditoría
 */
function registrarAuditoria($db, $tabla, $id_registro, $accion, $datos_anteriores = null, $datos_nuevos = null) {
    try {
        // Obtener IP del usuario
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        // Obtener User Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // ID del usuario actual
        $idUsuario = $_SESSION['user_id'] ?? null;
        
        // Convertir arrays a JSON
        $datosAnterioresJson = $datos_anteriores ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null;
        $datosNuevosJson = $datos_nuevos ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null;
        
        // Insertar en auditoría
        $sql = "INSERT INTO auditoria 
                (tabla, id_registro, accion, datos_anteriores, datos_nuevos, id_usuario, ip_usuario, user_agent) 
                VALUES 
                (:tabla, :id_registro, :accion, :datos_anteriores, :datos_nuevos, :id_usuario, :ip_usuario, :user_agent)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':tabla', $tabla);
        $stmt->bindParam(':id_registro', $id_registro);
        $stmt->bindParam(':accion', $accion);
        $stmt->bindParam(':datos_anteriores', $datosAnterioresJson);
        $stmt->bindParam(':datos_nuevos', $datosNuevosJson);
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->bindParam(':ip_usuario', $ip);
        $stmt->bindParam(':user_agent', $userAgent);
        
        return $stmt->execute();
    } catch (Exception $e) {
        // No detener la ejecución si falla la auditoría
        error_log("Error al registrar auditoría: " . $e->getMessage());
        return false;
    }
}
?>

