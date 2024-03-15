<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateMqCategory extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('mq_category',['comment'=>'免签二维码分类'])
            ->addColumn('name','string',['limit'=>'32','default'=>'','null'=>false,'comment'=>'名称'])
            ->addColumn('weight','integer',['limit'=>\Phinx\Db\Adapter\MysqlAdapter::INT_TINY,'default'=>10,'comment'=>'排序'])
            ->addColumn('createtime','integer',['default'=>0,'comment'=>'添加时间'])
            ->addColumn('updatetime','integer',['default'=>0,'comment'=>'修改时间'])
            ->create();
    }
}
