<?php

namespace App\Controller;

use App;
use Zoco;

class Db extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function changeDb() {
        $result = $this->db('database')->query('show tables')->fetchAll();
        var_dump($result);
    }

    public function selectDb() {
        $this->sdb->from('dbLog');
        $this->sdb->where('logtype=5');
        $this->sdb->order('addTime');
        $this->sdb->limit(5);
        $currentPage = empty($_GET['page']) ? 1 : intval($_GET['page']);
        $this->sdb->page($currentPage);
        $this->sdb->pageSize(5);
        $this->sdb->paging();
        $page = $this->sdb->pager->render();
        $res = $this->sdb->getAll();
        echo $page;
        echo $this->sdb->getSql();
        var_dump($res);
    }

    public function selectDbGetOne() {
        $this->sdb->from('dbLog');
        $this->sdb->where('logtype=5');
        $this->sdb->order('addTime');
        $this->sdb->limit(5);
        $res = $this->sdb->getOne();
        var_dump($res);
    }

    public function selectEqual() {
        $this->sdb->from('dbLog');
        $this->sdb->equal(1);
        $res = $this->sdb->getAll();
        var_dump($res);
    }

    public function selectDbInsert() {
        $this->sdb->from('dbLog');
        $arr = array(
            'msg' => 'insert',
            'logType' => '1',
        );
        $this->sdb->insert($arr);
    }

    public function selectDbUpdate() {
        $this->sdb->from('dbLog');
        $this->sdb->equal(5);
        $arr = array(
            'msg' => 'updated',
            'logType' => '1',
        );
        $this->sdb->update($arr);
    }

    public function selectDbDelete() {
        $this->sdb->from('dbLog');
        $this->sdb->equal(5);
        $this->sdb->delete();
    }

    public function selectDbCount(){
        $this->sdb->from('dbLog');
        echo $this->sdb->count();
    }

    public function dbInsert() {
        $arr = array(
            'msg' => 'insert',
            'logType' => '1',
        );
        $this->db->insert($arr,'dbLog');
    }

    public function dbUpdate() {
        $arr = array(
            'msg' => 'insert',
            'logType' => '2',
        );
        $this->db->update(2,$arr,'dbLog');
    }

    public function dbDelete() {
        $this->db->delete(3,'dbLog');
    }

    public function modelPut() {
        $model = Model('DbLog');
        $arr   = array(
            'addTime' => date('Y-m-d H:i:s'),
            'logType' => 5,
            'msg'     => '19999990000',
        );
        $id    = $model->put($arr);
        echo $id;
    }

    public function modelGet() {
        $model = Model('DbLog');
        $log   = $model->get(1);
        var_dump($log->get());
    }

    public function modelGets() {
        $model           = Model('DbLog');
        $gets['logType'] = 5;
        var_dump($model->gets($gets));
    }

    public function modelPageSize() {
        $model            = Model('DbLog');
        $gets['logType']  = 5;
        $gets['page']     = empty($_GET['page']) ? 1 : intval($_GET['page']);
        $gets['pageSize'] = 5;
        $list             = $model->gets($gets, $pager);

        foreach ($list as $li) {
            echo "{$li['id']}: {$li['msg']}<br/>\n";
        }
        echo $pager->render();
    }

    public function modelChange() {
        $model    = Model('DbLog');
        $log      = $model->get(1);
        $log->msg = '13800008888';
        $log->save();
    }

    public function modelDelete() {
        $model = Model('DbLog');
        $log   = $model->get(1);
        $log->delete();
    }

    public function modelDeletes() {
        $model    = Model('DbLog');
        $model->dels(
            array(
                'where' => 'logtype < 5',
                )
        );
    }
}