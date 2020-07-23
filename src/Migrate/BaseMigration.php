<?php

namespace SypherLev\Chassis\Migrate;

use SypherLev\Blueprint\Blueprint;
use SypherLev\Blueprint\QueryBuilders\QueryInterface;
use SypherLev\Blueprint\QueryBuilders\SourceInterface;
use SypherLev\Chassis\Error\ChassisException;

class BaseMigration extends Blueprint
{
    private $driver;
    private $dbuser;
    private $dbpass;
    private $db;
    private $dbhost;
    private $port;
    private $cliutil;

    public function __construct(SourceInterface $source, QueryInterface $query)
    {
        parent::__construct($source, $query);
    }

    public function setRawDatabaseParams(string $driver, string $user, string $pass, string $db, string $host, int $port, string $cliutil)
    {
        $this->driver = $driver;
        $this->dbuser = $user;
        $this->dbpass = $pass;
        $this->db = $db;
        $this->dbhost = $host;
        $this->port = $port;
        $this->cliutil = $cliutil;

        if ($cliutil == '') {
            $this->cliutil = $this->driver;
        }
    }

    public function create(string $database_prefix, string $migration_name): string
    {
        $migrations_folder = getenv('fileroot') . "/migrations";
        if (!file_exists($migrations_folder)) {
            mkdir($migrations_folder);
        }
        $filename = $database_prefix . '_' . time() . '_' . preg_replace("/[^a-zA-Z0-9_]/", "", $migration_name) . '.sql';
        $filepath = $migrations_folder . DIRECTORY_SEPARATOR . $filename;
        touch($filepath);
        $newmigration = [
            'filename' => $filename,
            'status' => 0,
            'last_update' => time()
        ];
        $check = $this->insert()
            ->table('migrations')
            ->add($newmigration)
            ->execute();
        if (!$check) {
            unlink($filepath);
            throw new ChassisException("Migration not added to the database; please check that the migrations table exists");
        }
        return $filename;
    }

    public function bootstrap(string $filename): bool
    {
        $thisdir = getenv('fileroot') . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;
        $filepath = $thisdir . $filename;
        if (file_exists($filepath)) {
            $this->runMigration($filepath);
            $newmigration = [
                'filename' => $filename,
                'status' => 1,
                'last_update' => time()
            ];
            $check = $this->insert()
                ->table('migrations')
                ->add($newmigration)
                ->execute();
            if ($check === true) {
                return true;
            } else {
                throw new ChassisException("Bootstrap process completed but bootstrap record not added to the migrations table");
            }
        } else {
            throw new ChassisException("Can't find bootstrap file at " . $filepath);
        }
    }

    public function backup(): string
    {
        $output = $this->runBackup();
        if (strlen($output) > 0) { // then something happened, check back
            return $output;
        } else { // then it executed without errors
            return "";
        }
    }

    public function migrate(string $database_prefix): array
    {
        $this->checkNew($database_prefix);
        $results = [];
        $migrations = $this->select()
            ->table('migrations')
            ->where(['status' => 0])
            ->orderBy(['last_update'])
            ->many();
        foreach ($migrations as $m) {
            $thisdir = getenv('fileroot') . '/migrations/';
            $filepath = $thisdir . $m->filename;
            if (file_exists($filepath)) {
                $this->runMigration($filepath);
                $this->update()
                    ->table('migrations')
                    ->where(['id' => $m->id])
                    ->set(['status' => 1])
                    ->execute();
                $results[] = [
                    'file' => $m->filename,
                    'output' => "No errors found"
                ];
            } else {
                $results[] = [
                    'file' => $m->filename,
                    'output' => "Migration halt on missing file: " . $m->filename
                ];
                return $results;
            }
        }
        return $results;
    }

    private function checkNew(string $database_prefix)
    {
        $filelist = array_diff(scandir(getenv('fileroot') . '/migrations'), array('.', '..'));
        foreach ($filelist as $file) {
            // check if this is a migration for this database by looking for the prefix
            if (strpos($file, $database_prefix) !== 0) {
                continue;
            }
            $check = $this->select()
                ->table('migrations')
                ->where(['filename' => $file])
                ->one();
            if (!isset($check->filename)) {
                // then this is a new migration, add it to the database
                // first fix the time issue
                if ($this->driver == "mysql") {
                    $last_update = time();
                } else {
                    $last_update = date("Y-m-d H:i:s", time());
                }
                $newmigration = [
                    'filename' => $file,
                    'status' => 0,
                    'last_update' => $last_update
                ];
                $check = $this->insert()
                    ->table('migrations')
                    ->add($newmigration)
                    ->execute();
                if (!$check) {
                    throw new ChassisException("New migration $file could not be added to the migrations table");
                }
            }
        }
    }

    private function runMigration(string $filename): bool
    {
        $output = $this->runSQLFile($filename);
        if (strlen($output) > 0) { // then something happened, check back
            throw new ChassisException($output);
        }
        return true;
    }

    // props to StackOverflow for this solution:
    // http://stackoverflow.com/questions/4027769/running-mysql-sql-files-in-php
    /**
     * @psalm-suppress ForbiddenCode
     */
    private function runSQLFile(string $path): string
    {
        $command = "";
        if ($this->driver == 'mysql') {
            $command = "{$this->cliutil} -u {$this->dbuser} -p{$this->dbpass} "
                . "-h {$this->dbhost} -D {$this->db} < {$path}";
        }
        if ($this->driver == 'pgsql') {
            $command = "PGPASSWORD={$this->dbpass} {$this->cliutil} -U {$this->dbuser} "
                . "-h {$this->dbhost} -p {$this->port} -d {$this->db} -f {$path}";
        }
        $output = shell_exec($command);
        if (is_null($output)) {
            return "";
        }
        return $output;
    }

    /**
     * @psalm-suppress ForbiddenCode
     */
    private function runBackup(): string
    {
        $folder = "databackups";
        if (!file_exists($folder)) {
            mkdir($folder, 0755);
        }
        $dumpcommand = "mysqldump -u{$this->dbuser} -p{$this->dbpass} -h {$this->dbhost} -x {$this->db} | gzip > ";
        if ($this->driver == 'pgsql') {
            $dumpcommand = "PGPASSWORD={$this->dbpass} pg_dump -U {$this->dbuser} -h {$this->dbhost} -x {$this->db} | gzip > ";
        }
        $filename = "{$this->db}-backup-" . date('Y-m-d--H-i-s', time()) . ".sql.gz";
        $dumpcommand .= $folder . '/' . $filename;
        $output = shell_exec($dumpcommand);
        if (is_null($output)) {
            $output = "";
        }
        return $output;
    }
}