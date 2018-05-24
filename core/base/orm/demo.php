<?php
/**
 * Created by PhpStorm.
 * User: ht
 * Date: 2017/10/26
 * Time: 21:11
 */
use core\base\orm\Model;

require 'Model.php';



class User extends Model
{
    protected $_tableName = 'user';

    protected function getName($value)
    {
        return $value . 999;
    }
}

$user = new User();

$user->setDatabase('hahaha');

// 基本的增删改查
//$row = $user->insert(['name' => 'ht','age' => 1]);
//var_dump($row);

// 批量插入1：
//$row = $user->batchInsert([['name'=>'ht1','age'=>111],['name'=>'ht2','age'=>222]]);
//var_dump($row);

// 批量插入2：
//$row = $user->batchInsert([['hh1',100],['hh2',200]],['name','age']);
//var_dump($row);

//$row = $user->where(['id' => 521])->delete();
//var_dump($row);

//$row = $user->where(['id' => 522])->update(['name' => 'hhh']);
//var_dump($row);

//$one = $user->one();
//var_dump($one);

// 查询后用对象方式访问属性
//var_dump($user->name);

//$list = $user->all();
//var_dump($list);
//$returnObjects = Model::returnObjects($list,$user);
//
//foreach ($returnObjects as $k => $item)
//{
//    var_dump($item->name);
//}

// 各种查询
//$user->setIsForceMaster(true);
//$one = $user->where(['id' => 534, 'name' => 'ht2'])->orWhere(['id' => 522])->filterWhere(['id' => null])->order(['id' => 'desc', 'age' => 'asc'])->one();
//var_dump($one);
//
//$one = $user->where(['id' => ['in', [522, 523]]])->one();
//var_dump($one);
//
//$one = $user->where(['name' => ['like', '%ht1']])->one();
//var_dump($one);
//
//$one = $user->where(['id' => ['between', [523,525]]])->one();
//var_dump($one);
//
//$one = $user->where(['id' => ['>', '5']])->one();
//var_dump($one);
//
//$one = $user->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
//$one = $user->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
//$one = $user->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
//$one = $user->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '5']])->one();
//var_dump($one);

//$user->setIsForceMaster(false);

// 事务
//$user->transaction(function() use($user){
//    $row = $user->insert(['name' => 'ht111','age' => 1]);
//    var_dump($row);
//    //return true;
//});

// 原生
//$users = $user->query('select * from user limit 1');
//var_dump($users);

// 根据主键删改查
//$r = $user->deleteByPk(529);
//var_dump($r);

//$r = $user->updateByPk(530, ['name' => 111, 'age' => 111]);
//var_dump($r);

//$r = $user->getByPk(542);
//var_dump($r);

echo '<pre>';
print_r($user->getSQL());
//var_dump($user->getLastSQL());




