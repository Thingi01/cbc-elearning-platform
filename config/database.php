<?php
/**
 * Database Configuration
 * Works both in Codespaces and local environments
 */

// Detect environment
$isCodespaces = getenv('CODESPACES') === 'true';
$isLocal = !$isCodespaces;

// Database configuration
if ($isCodespaces) {
    // Codespaces environment (Docker)
    define('DB_HOST', 'db');
    define('DB_NAME', 'cbc_hub');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'rootpassword');
} else {
    // Local WAMP environment
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'cbc_hub');
    define('DB_USER', 'root');
    define('DB_PASSWORD', ''); // Usually empty for WAMP
}

// Connection charset
define('DB_CHARSET', 'utf8mb4');

// Other app configuration
define('APP_URL', $isCodespaces ? 'https://' . getenv('CODESPACE_NAME') . '-8080.app.github.dev' : 'http://localhost');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Test connection on first load (optional - remove in production)
if (php_sapi_name() !== 'cli') {
    try {
        $db = Database::getInstance();
        // Connection successful
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
    }
}