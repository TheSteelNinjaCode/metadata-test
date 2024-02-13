<?php

namespace Lib\Prisma\Classes;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(\DOCUMENT_PATH);
$dotenv->load();

class Prisma {
    private $pdo;

    public $User;
    public $UserRole;
    public $Post;
    public $Profile;
    public $Product;

    public function __construct() {
        $this->initializePDO();

        $this->User = new User($this->pdo);
        $this->UserRole = new UserRole($this->pdo);
        $this->Post = new Post($this->pdo);
        $this->Profile = new Profile($this->pdo);
        $this->Product = new Product($this->pdo);
    }

    private function initializePDO() {
        $databaseUrl = $_ENV['DATABASE_URL'];
        if (!$databaseUrl) {
            throw new \Exception('DATABASE_URL not set in .env file.');
        }
        $pattern = '/:\/\/(.*?):(.*?)@/';
        preg_match($pattern, $databaseUrl, $matches);
        $dbUser = $matches[1] ?? '';
        $dbPassword = $matches[2] ?? '';
        $databaseUrlWithoutCredentials = preg_replace($pattern, '://', $databaseUrl);
        $parsedUrl = parse_url($databaseUrlWithoutCredentials);
        $dbProvider = $parsedUrl['scheme'] ?? '';
        $dbName = isset($parsedUrl['path']) ? substr($parsedUrl['path'], 1) : '';
        $dbHost = $parsedUrl['host'] ?? '';
        $dbPort = $parsedUrl['port'] ?? ($dbProvider === 'mysql' ? 3306 : 5432);
        if ($dbProvider === 'mysql') {
            $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8";
        } elseif ($dbProvider === 'postgresql') {
            $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
        } else {
            throw new \Exception("Unsupported database provider: $dbProvider");
        }
        try {
            $this->pdo = new \PDO($dsn, $dbUser, $dbPassword);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception("Connection error: " . $e->getMessage());
        }
    }

}
