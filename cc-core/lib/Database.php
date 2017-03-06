<?php

class Database
{
    protected $_connection;
    protected $_fetchMode = PDO::FETCH_ASSOC;
    protected $_lastStatement;

    public function __construct()
    {
        $this->_getConnection();
    }

    protected function _getConnection()
    {
        return $this->_connection instanceof PDO ? $this->_connection : $this->_connect();
    }

    protected function _connect()
    {
        try {
            $this->_connection = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            return $this->_connection;
        } catch (Exception $exception) {
            exit('CC-ERROR-200 CumulusClips has encountered an error and cannot continue.');
        }
    }

    protected function _reconnect()
    {
        $this->_connection = null;
        return $this->_connect();
    }

    public function basicQuery($sql)
    {
        $pdo = $this->_getConnection();
        $pdoStatement = $pdo->prepare($sql);
        $pdoStatement->execute();
        return $pdoStatement->fetchAll();
    }

    public function query($sql, $bindParams = array())
    {
        $pdo = $this->_getConnection();
        try {
            // Log query if requested
            if (LOG_QUERIES) {
                $params = json_encode($bindParams);
                $date = date('D M d Y H:i:s');
                App::Log(DATABASE_LOG, "[$date] [query] Query: $sql; Params: $params");
            }

            $pdoStatement = $pdo->prepare($sql);
            $pdoStatement->execute($bindParams);
            $this->_lastStatement = $pdoStatement;
            return $pdoStatement;
        } catch (Exception $exception) {
            $error = $exception->getMessage();
            $date = date('D M d Y H:i:s');
            $params = json_encode($bindParams);

            // Log Database Error
            $messageLog = "[$date] [error] ";
            $messageLog .= 'Error: ' . $error . '; ';
            $messageLog .= 'Query: ' . $sql . '; ';
            $messageLog .= 'Params: ' . $params;
            App::Log(DATABASE_LOG, $messageLog);

            // Send Notification
            $subject = 'Site Error Encountered ' . $date;
            $messageAlert = "An error was encountered on the website\n\n";
            $messageAlert .= 'Date: ' . $date . "\n\n";
            $messageAlert .= "Error:\n" . $error . "\n\n";
            $messageAlert .= "Query:$sql";
            $messageAlert .= "Params:\n" . $params;
            App::Alert($subject, $messageAlert);

            // Send user to error page
            if (!headers_sent()) {
                header("Location: " . HOST . "/system-error/");
            } else {
                echo '<script>top.location = "' . HOST . '/system-error/";</script>';
            }
            exit();
        }
    }

    public function fetchRow($sql, $bindParams = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $pdoStatement = $this->query($sql, $bindParams);
        return $pdoStatement->fetch($fetchMode);
    }

    public function fetchAll($sql, $bindParams = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $pdoStatement = $this->query($sql, $bindParams);
        return $pdoStatement->fetchAll($fetchMode);
    }

    public function rowCount()
    {
        return $this->_lastStatement->rowCount();
    }

    public function lastInsertId()
    {
        return $this->_connection->lastInsertId();
    }
}