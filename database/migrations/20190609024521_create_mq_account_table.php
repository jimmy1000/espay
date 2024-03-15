<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateMqAccountTable extends Migrator
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
        $this->table('mq_account',['comment'=>'免签账号'])
            ->addColumn('category_id','integer',['default'=>0,'comment'=>'分类编号'])
            ->addColumn('type','string',['limit'=>24,'comment'=>'账号类型'])
            ->addColumn('qr','string',['limit'=>255,'null'=>false,'comment'=>'二维码内容'])
            ->addColumn('todaymoney','decimal',['precision'=>10,'scale'=>2,'signed'=>false,'default'=>0,'comment'=>'当天已收额度'])
            ->addColumn('today','string',['null'=>false,'default'=>'','comment'=>'当前日期'])
            ->addColumn('maxmoney','decimal',['precision'=>10,'scale'=>2,'signed'=>false,'default'=>0,'comment'=>'每天最多收款'])
            ->addColumn('status','enum',['values'=>'0,1','default'=>'1','null'=>false,'comment'=>'状态:0=未开启,1=已开启'])
            ->addColumn('createtime','integer',['default'=>0,'comment'=>'添加时间'])
            ->addColumn('updatetime','integer',['default'=>0,'comment'=>'修改时间'])
            ->create();
    }
}
