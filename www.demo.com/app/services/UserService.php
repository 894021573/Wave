<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/1/5
 */
namespace app\services;

use app\models\User;
use core\framework\Service;

class UserService extends Service
{
    public function test()
    {
        return true;
    }

    public function getName()
    {
        return $this->jsonR(['1','msg']);
    }

    public function testOrm()
    {
        $userModel = new User();

        // 切换数据库
        //$userModel->setDatabase('hahaha');

        // 增
        $rowCount = $userModel->insert(['name' => 'ht','age' => 100]); // 返回影响行数，且插入的数据被赋值给当前模型的attributes
        $rowCount = $userModel->batchInsert([['name' => 'ht111','age' => 111100],['name' => 'ht222','age' => 222]]); // 返回影响行数
        $rowCount = $userModel->batchInsert([['ht111', 111100], ['ht222', 222]], ['name', 'age']); // 返回影响行数

        // 删
        $userModel->where(['id' => 589])->delete(); // 返回影响行数

        // 改
        $userModel->where(['id' => 591])->update(['name' => 'update-ht','age'=>666]); // 返回影响行数，且更新的数据被赋值给当前模型的attributes

        // 查
        $one = $userModel->one(); // 查一行，返回数组，且把返回数据存入模型的attributes中

        $all = $userModel->all(); // 查多行，返回数组
        $models = Model::returnObjects($all,$userModel); // 把多维数组放入模型数组中

        // 根据主键删
        $r = $userModel->deleteByPk(529); // 主键名默认是id，可以在模型中通过重写$_primaryKey属性来重新设置。返回影响行数

        // 根据主键改
        $r = $userModel->updateByPk(530, ['name' => 111, 'age' => 111]); // 主键名默认是id，可以在模型中通过重写$_primaryKey属性来重新设置。返回影响行数，且更新的数据被赋值给当前模型的attributes

        // 根据主键查
        $r = $userModel->getByPk(542);

        //带条件，有关联的查询
        $userModel->setIsForceMaster(true);
        $one = $userModel->where(['id' => 534, 'name' => 'ht2'])->orWhere(['id' => 522])->filterWhere(['id' => null])->order(['id' => 'desc', 'age' => 'asc'])->one();

        $one = $userModel->where(['id' => ['in', [522, 523]]])->one();

        $one = $userModel->where(['name' => ['like', '%ht1']])->one();

        $one = $userModel->where(['id' => ['between', [523,525]]])->one();

        $one = $userModel->where(['id' => ['>', '5']])->one();

        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
        $one = $userModel->setAlias('u')->leftJoin('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
        $one = $userModel->setAlias('u')->rightJoin('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();

        // 是否强制从主库查询
        $userModel->setIsForceMaster(false);

        // 事务
        $userModel->transaction(function() use($userModel){
            $row = $userModel->insert(['name' => 'ht111','age' => 1]);
            //return true;
        });

        // 原生
        $users = $userModel->query('select * from user limit 1');
        //$users = $userModel->rowCount('xxx');

        // 切换数据库连接名称
        $userModel->setConnectionName('aa');

        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '777']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '888']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '999']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '6665']])->one();
    }
}