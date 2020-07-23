<?php

namespace Tests\additional;

use PDO;

class MockPDO extends \PDO {

    private $mode = "success";
    private $count = 1;

    public function __construct ($dsn, $username = null, $passwd = null, $options = null) {
        if($username == 'exception') {
            throw new \PDOException("Throwing PDO Exception");
        }
        if($username == 'error' || $username == 'bad_insert') {
            $this->mode = "error";
        }
        if($username == 'empty') {
            $this->mode = "empty";
        }
        if($username == 'error_record') {
            $this->mode = 'error record';
        }
        if($username == 'new_migration') {
            $this->mode = 'new migration';
        }
        if($username == 'new_pmigration') {
            $this->mode = 'new postgres migration';
        }
        echo "Created ".$dsn;
    }

    public function prepare($statement, $options = null)
    {
        if(strpos($statement, 'error') !== false || $this->mode == 'error') {
            return new MockErrorStatement();
        }
        if($this->mode == 'empty') {
            return new MockEmptyStatement();
        }
        if($this->mode == 'error record') {
            return new MockErrorRecordStatement();
        }
        if($this->mode == 'new migration' && $this->count == 1) {
            $this->count++;
            return new MockEmptyStatement();
        }
        if($this->mode == 'new migration' && $this->count > 1) {
            return new MockNewMigration();
        }
        if($this->mode == 'new postgres migration' && $this->count == 1) {
            $this->count++;
            return new MockEmptyStatement();
        }
        if($this->mode == 'new postgres migration' && $this->count > 1) {
            return new MockNewPostgresMigration();
        }
        return new MockStatement();
    }
}

class MockStatement extends \PDOStatement {
    public function execute($input_parameters = null)
    {
        return true;
    }

    public function fetch ($fetch_style = null, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
        $fakerecord = new \stdClass();
        $fakerecord->column = "fake";
        return $fakerecord;
    }

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
        $fakerecord = new \stdClass();
        $fakerecord->last_update = "000000000";
        $fakerecord->filename = "mysql_1234567890_add_tables.sql";
        $fakerecord->id = 1;
        $fakerecord->status = 0;
        return [$fakerecord];
    }
}

class MockEmptyStatement extends \PDOStatement {
    public function execute($input_parameters = null)
    {
        return true;
    }

    public function fetch ($fetch_style = null, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
        return false;
    }

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
        return [];
    }
}

class MockErrorStatement extends \PDOStatement {
    public function execute($input_parameters = null)
    {
        return false;
    }
}

class MockErrorRecordStatement extends \PDOStatement {
    public function execute($input_parameters = null)
    {
        return true;
    }

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
        $fakerecord = new \stdClass();
        $fakerecord->last_update = "000000000";
        $fakerecord->filename = "mysql_1234567890_add_tables_error.sql";
        $fakerecord->id = 1;
        $fakerecord->status = 0;
        return [$fakerecord];
    }
}

class MockNewMigration extends \PDOStatement {

    public function execute($input_parameters = null)
    {
        return true;
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        $fakerecord = new \stdClass();
        $fakerecord->last_update = "000000000";
        $fakerecord->filename = "newm_1234567890_add_tables.sql";
        $fakerecord->id = 1;
        $fakerecord->status = 0;
        return $fakerecord;
    }

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
        $fakerecord = new \stdClass();
        $fakerecord->last_update = "000000000";
        $fakerecord->filename = "newm_1234567890_add_tables.sql";
        $fakerecord->id = 1;
        $fakerecord->status = 0;
        return [$fakerecord];
    }
}

class MockNewPostgresMigration extends \PDOStatement {

    public function execute($input_parameters = null)
    {
        return true;
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        $fakerecord = new \stdClass();
        $fakerecord->last_update = "2000-01-01 00:00:00";
        $fakerecord->filename = "newp_1234567890_add_tables.sql";
        $fakerecord->id = 1;
        $fakerecord->status = 0;
        return $fakerecord;
    }

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
        $fakerecord = new \stdClass();
        $fakerecord->last_update = "2000-01-01 00:00:00";
        $fakerecord->filename = "newp_1234567890_add_tables.sql";
        $fakerecord->id = 1;
        $fakerecord->status = 0;
        return [$fakerecord];
    }
}