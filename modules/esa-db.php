<?php


class DB {
    private static $instance = NULL;
    var $link;
    var $host;
    var $db;
    var $user;
    var $pass;
    var $showError = true;
    var $isConnected = false;

    private function __construct() { }

    public function __destruct() {
        if ( $this->isConnected ) {
            $this->close();
        }
    }

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    function setConnectionParameters($host,$db,$user,$pass) {
        $this->host = $host;
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
    }
    
    function connect($type = 1) {
        
        $this->link = mysqli_connect($this->host, $this->user, $this->pass);
        if (!$this->link) {
            $this->errorShow('Database',mysqli_connect_errno()," Cannot connect with database\n".formatBacktrace(debug_backtrace()));
        }
        
        if ($type == 1) {
            $dbSelect = mysqli_select_db($this->link,$this->db);
            if (!$dbSelect) {
                $this->errorShow('Database',mysqli_connect_errno()," Cannot select database\n".formatBacktrace(debug_backtrace()));
            }
            $this->isConnected = true;
            $result = mysqli_query($this->link, 'SET NAMES utf8');
        }
    }

    // execute query
    function query ($query) {
        if (!$this->link) {
            return 0;
        } else {
            $result = mysqli_query($this->link, $query);
            if (!$result) {
                $this->errorShow('Database',mysqli_error($this->link)," <pre>\n".$query."</pre>\n".formatBacktrace(debug_backtrace()));
            } else {
                return $result;
            }
        }
    }

    // execute query 
    function execute($query, $params) {
        if (!$this->link) {
            return 0;
        } else {
            $result = mysqli_execute_query($this->link, $query, $params);
            if (!$result) {
                $this->errorShow('Database',mysqli_error($this->link)," <pre>\n".$query."</pre>\n".formatBacktrace(debug_backtrace()));
            } else {
                return $result;
            }
        }
    }

    // rows count
    function count ($result) {
        $count = mysqli_num_rows($result);
        return $count;
    }

    // single cell data result
    function result ($results,$no,$field) {
        $result = mysqli_result($results,$no,$field);
        return $result;
    }

    // data row array - indexes by numbers
    function rowByIndexes ($result) {
        $row = mysqli_fetch_row($result);
        return $row;
    }

    // data row array - indexes by column name and numbers
    function rowByNames ($result) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        return $row;
    }

    // last inserted id
    function lastId () {
        $last_id = mysqli_insert_id ($this->link);
        return $last_id;
    }


    // close database connection
    function close () {
        if ($this->link != NULL) {
            mysqli_close($this->link);
            $this->isConnected = false;
        }
    }

    function errorShow($title,$mysqlError,$description) {
        if ($this->showError) {
            echo $title."\n";
            echo $mysqlError.' '.$description."\n";
        }
        
        exit();
    }
}

?>