<?php
/**
 * Modelo de Usuario
 */

class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password) {
        $sql = "SELECT u.id, u.username, u.password, u.nombre_completo, u.email, u.id_rol, u.activo, r.nombre as rol_nombre
                FROM " . $this->table . " u
                INNER JOIN roles r ON u.id_rol = r.id
                WHERE u.username = :username AND u.activo = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Actualizar último acceso
                $this->updateLastAccess($user['id']);
                return $user;
            }
        }
        
        return false;
    }

    /**
     * Actualizar último acceso
     */
    private function updateLastAccess($user_id) {
        $sql = "UPDATE " . $this->table . " SET ultimo_acceso = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAll($activo = null) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM " . $this->table . " u
                INNER JOIN roles r ON u.id_rol = r.id";
        
        if ($activo !== null) {
            $sql .= " WHERE u.activo = :activo";
        }
        
        $sql .= " ORDER BY u.nombre_completo";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($activo !== null) {
            $stmt->bindParam(':activo', $activo);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM " . $this->table . " u
                INNER JOIN roles r ON u.id_rol = r.id
                WHERE u.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear usuario
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (username, password, nombre_completo, email, telefono, id_rol, activo) 
                VALUES (:username, :password, :nombre_completo, :email, :telefono, :id_rol, :activo)";
        
        $stmt = $this->conn->prepare($sql);
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':nombre_completo', $data['nombre_completo']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':id_rol', $data['id_rol']);
        $stmt->bindParam(':activo', $data['activo']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualizar usuario
     */
    public function update($id, $data) {
        $sql = "UPDATE " . $this->table . " 
                SET username = :username, 
                    nombre_completo = :nombre_completo, 
                    email = :email, 
                    telefono = :telefono, 
                    id_rol = :id_rol, 
                    activo = :activo";
        
        // Si se proporciona una nueva contraseña
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':nombre_completo', $data['nombre_completo']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':id_rol', $data['id_rol']);
        $stmt->bindParam(':activo', $data['activo']);
        
        if (!empty($data['password'])) {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
        }
        
        return $stmt->execute();
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleActive($id, $activo) {
        $sql = "UPDATE " . $this->table . " SET activo = :activo WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':activo', $activo);
        return $stmt->execute();
    }
    
    /**
     * Alias para toggleActive (compatibilidad)
     */
    public function toggleEstado($id, $activo) {
        return $this->toggleActive($id, $activo);
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE " . $this->table . " SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Verificar si existe username
     */
    public function usernameExists($username, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE username = :username";
        
        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtener todos los roles
     */
    public function getRoles() {
        $sql = "SELECT * FROM roles WHERE activo = 1 ORDER BY nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
