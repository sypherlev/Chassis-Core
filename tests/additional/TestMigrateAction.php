<?php


namespace Tests\additional;

require_once "MockPDO.php";

use SypherLev\Chassis\Migrate\MigrateAction;

class TestMigrateAction extends MigrateAction
{
    public function init()
    {
        $this->setPDOClass(MockPDO::class);
        parent::init();
    }
}