<?php

namespace Config;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private string $server;
    private string $database;
    private string $auth;
    private string $username;
    private string $password;
    private string $encrypt;
    private string $trustCert;

    public function __construct()
    {
        $this->loadEnv(__DIR__ . '/../.env');

        $this->server    = $_ENV['DB_SERVER']     ?? 'localhost';
        $this->database  = $_ENV['DB_NAME']       ?? 'AlqomichiDB';
        $this->auth      = strtolower($_ENV['DB_AUTH'] ?? 'sql');
        $this->username  = $_ENV['DB_USER']       ?? '';
        $this->password  = $_ENV['DB_PASSWORD']   ?? '';
        $this->encrypt   = $_ENV['DB_ENCRYPT']    ?? '0';
        $this->trustCert = $_ENV['DB_TRUST_CERT'] ?? '1';
    }

    private function loadEnv(string $path): void
    {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    public function connect(): PDO
    {
        try {
            $dsn = "sqlsrv:Server={$this->server};Database={$this->database};Encrypt={$this->encrypt};TrustServerCertificate={$this->trustCert}";

            $user = ($this->auth === 'windows') ? null : $this->username;
            $pass = ($this->auth === 'windows') ? null : $this->password;

            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Error al conectar con la base de datos SQL Server.', 500, $e);
        }
    }
}
