<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateMqOrderTable extends Migrator
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

        $this->table('mq_order',['comment'=>'收款记录'])
            ->addColumn('orderno','string',['limit'=>32,'null'=>false,'default'=>'','comment'=>'系统订单号'])
            ->addColumn('price','decimal',['precision'=>10,'scale'=>2,'signed'=>false,'default'=>0,'comment'=>'订单金额'])
            ->addColumn('realprice','decimal',['precision'=>10,'scale'=>2,'signed'=>false,'default'=>0,'comment'=>'实际付款金额'])
            ->addColumn('type','string',['limit'=>24,'comment'=>'类型'])
            ->addColumn('mq_account_id','integer',['default'=>0,'null'=>false,'comment'=>'二维码账号'])
            ->addColumn('status','enum',['values'=>'0,1,2','default'=>'0','null'=>false,'comment'=>'订单状态:0=等待支付,1=支付成功,2=支付超时'])
            ->addColumn('createtime','integer',['default'=>0,'comment'=>'添加时间'])
            ->addColumn('updatetime','integer',['default'=>0,'comment'=>'修改时间'])
            ->addIndex(['orderno'],['unique'=>true,'name'=>'idx_orderno'])
            ->addIndex(['realprice','status','createtime'])
            ->addIndex(['price','status','createtime'])
            ->create();

    }
}
