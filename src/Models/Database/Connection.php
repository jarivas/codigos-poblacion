<?php
declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

use Psr\Log\LoggerInterface;
use Exception;
use PDO;

class Connection {

    /**
     * @var null|Connection $instance
     */
    private static null|Connection $instance = null;

    /**
     * @var string $dsn
     */
    private string $dsn = 'sqlite:/home/luis/projects/codigos-poblacion-1/data//database.sqlite';

    /**
     * @var null|string $sshTunnel
     */
    private null|string $sshTunnel = null;

    /**
     * @var PDO $db
     */
    private PDO $db;

    /**
     * @var null|string $username
     */
    private null|string $username = null;

    /**
     * @var null|string $password
     */
    private null|string $password = null;

    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;


    public static function getInstance() : Connection
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;

    }//end getInstance()


    private function __construct(LoggerInterface|null $logger=null)
    {
        if (is_string($this->sshTunnel)) {
            shell_exec($this->sshTunnel);
        }

        $this->db = new PDO($this->dsn, $this->username, $this->password);

        $this->logger = (is_null($logger)) ? new Logger() : $logger;

    }//end __construct()


    /**
     * @param string $sql
     * @param bool|array<string, mixed> $params
     * @param string $className
     * @return bool|array<mixed> array of $className
     */
    public function get(string $sql, bool|array $params, string $className): bool|array
    {
        $this->log($sql, $params);
        
        $stmt = $this->db->prepare($sql);

        try {
            if (! $stmt->execute($params)) {
                throw new Exception(implode(', ', $stmt->errorInfo()));
            }
        } catch (Exception $e) {
            throw new Exception($sql.' '.$e->getMessage());
        }

        return $stmt->fetchAll(PDO::FETCH_CLASS, $className);

    }//end get()


    /**
     * @param string $sql
     * @param bool|array<string, string> $params
     */
    public function executeSql(string $sql, bool|array $params=false): void
    {
        $this->log($sql, $params);

        $stmt = $this->db->prepare($sql);

        try {
            if (! $stmt->execute($params)) {
                throw new Exception(implode(', ', $stmt->errorInfo()));
            }
        } catch (Exception $e) {
            throw new Exception($sql.' '.$e->getMessage());
        }

    }//end executeSql()


    /**
     * @param string $sql
     */
    public function exec(string $sql): void
    {
        $this->log($sql);

        if (! $this->db->exec($sql)) {
            $error = $this->db->errorInfo();

            if ($error[0] != "00000") {
                throw new Exception($sql.' '.implode(', ', $error));
            }
        }

    }//end exec()


    public function lastInsertId(): bool|string
    {
        return $this->db->lastInsertId();

    }//end lastInsertId()

    protected function log(string $sql, array $params = []): void
    {
        $p = json_encode($params);

        $this->logger->info("{$sql} :: {$p}");
    }//end log()


}//end class
