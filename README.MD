# 一、目录
* core --- 核心目录
    * base -- 框架依赖的类
    * ext ---第三方类
    * framework ---框架直接使用的类
    constant.php
    Wave.php --- 核心文件
* www -- 项目
    * app
        * commands
            * config.php
        * controllers
        * models
        * services
        * tests
        * validators
        * views
            * layout
                * layout.php
    * config
        database.php
        main.php
    * route
        route.php
    * runtime --- 存放运行期间生成的文件
    * index.php
    
# 二、控制器
* 创建
    * 示例1：app/controllers/UserController.php        
        ```$xslt
                namespace app\controllers;
                
                use core\framework\Controller;
                
                class UserController extends Controller
                {
                     public function index()
                     {
                         $name = $this->get();
                         $age = $this->post();
                         $address = Wave::makeRequest()->getSession();
                 
                         return $this->render('index');
                     }           
                }
        ```
    * 示例2 app/controllers/vip/UserController.php
        ```$xslt
                namespace app\controllers\vip;
                
                use core\framework\Controller;
                
                class UserController extends Controller
                {
                
                }
        ```
        
# 三、逻辑层
* 创建 app/services/UserService.php
    ```$xslt
        namespace app\services;
        
        use core\framework\Service;
        
        class UserService extends Service
        {
            public function getName()
            {
                // return $this->jsonR([]);
                return $this->json([]); # 输出JSON数据
            }
        }
    ```
    
# 四、模型层
* 创建：app/models/User.php
    ```$xslt
        namespace app\models;
        
        use core\framework\Model;
        
        class User extends Model
        {
            protected $_tableName = 'user';
        
            public function getName()
            {
                
            }
        }
    ```

# 五、视图层
* 创建：  app/views/user/index.php
* 位置：app\controllers\UserController类的视图文件默认位于 app/views/user/ 目录下
* 渲染视图
```$xslt
class UserController extends Controller
{
    public function index()
    {
        return $this->render('index'); # 载入app/views/layout/layout.php中的布局
    }
    
    public function indexPartial()
    {
        return $this->renderPartial('index'); # 不载入app/views/layout/layout.php中的布局
    }
}
```

# 六、运行
* URL方式
    * 自动路由
        * http://www.domain.com/index.php/user/index?a=1&b=2    访问app\controllers\UserController类的index方法
        * http://www.domain.com/index.php/vip/user/index?a=1&b=2    访问app\vip\controllers\UserController类的index方法
        * 要省略index.php，需对nginx或者apache进行配置
    * 手动路由
        * 创建 route\route.php 路由功能使用了FastRoute组件，参考https://github.com/nikic/FastRoute
        ```$xslt
            $route->addRoute('GET','/user','UserController@index'); # 访问app/controllers/UserController类的index方法
            $route->addRoute('GET','/vip_user','UserController@index'); # 访问app/controllers/vip/UserController类的index方法
        ```
        * 配置 开启/关闭 自动路由 config/main.php
        ```$xslt
           'auto_route' => true; // true:开启;false:关闭
        ```
* 命令行方式
    * 创建：F:\html\wave\com.wave_a.www\app\commands\Test.php
    ```
    namespace app\commands;
    
    use app\models\User;
    
    class Test
    {
        public function a($c, $d)
        {
            $u = new User();
            echo $c + $d;
        }
    }
    ```
    * 配置：在配置文件中添加要运行的数据 app\commands\config.php
    ```
    // 命令行执行的信息（类/方法/参数）
    
    return [
        'test' =>
            [
                'class' => '\app\commands\Test',
                'method' => 'a',
                'params' => [1, 2]
            ],
    ];
    ```
    * 运行
    ```
        php index.php test 1 2 //index.php是入口文件；test是配置文件中的key；1 2 表示方法的参数，如省略，会默认调用params的值。
    ```

# 七、调试面板 config/main.php
```$xslt
     'debug' => false, // 是否显示调试面板
```

# 八、ORM 数据库操作示例
```
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

        // 带条件，有关联的查询
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
        $users = $userModel->query('select * from user limit 1'); // select
        //$users = $userModel->rowCount('xxx'); // update insert delete

        // 切换数据库连接名称
        $userModel->setConnectionName('aa'); 

        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '777']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '888']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '999']])->one();
        $one = $userModel->setAlias('u')->join('addr AS a','u.id = a.user_id')->where(['u.id' => ['>', '6665']])->one();
```
# 九、单元测试 url方式运行，暂不支持命令行
* 创建测试类 app\tests\IndexTest.php
```
namespace app\tests;

use core\base\unit_test\UnitTest;

class IndexTest extends UnitTest
{
    public function testTitle()
    {
        $service = new \app\services\UserService();
        $this->assertTrue($service->test());
    }

    public function testTitle2()
    {
        $service = new \app\services\UserService();
        $this->assertTrue($service->test());
    }
}
```
* 添加测试类
```
namespace app\tests;

use core\base\unit_test\RunTest;

class AllTest
{
    public function __construct()
    {
        $runTest = new RunTest();

        // add test class start

        $runTest->add(new IndexTest()); // 添加一个测试类
        $runTest->add(new IndexTest2()); // 再添加一个测试类

        // add test class end

        $runTest->run();
    }
}
```
* 运行测试脚本
```
namespace app\controllers;

use app\tests\AllTest;

class TestController
{
    public function index()
    {
        new AllTest();
    }
}
```
    
# 十、验证器
* 数据验证类是独立的，只要传要求的数据格式，就能验证。在哪一层进行数据验证？个人觉得放在逻辑层好，利于该层复用。
* 创建：app\validators\UserValidator.php
```
<?php
namespace app\validators;

use core\base\validator\Validator;

class UserValidator extends Validator
{
    public function rules()
    {
        // [验证字段,验证规则,规则选项,'message'=>'提示语','on'=>'验证场景']
        return
            [
                [['name', 'age'], self::REQUIRED, 'message' => '自定义提示', 'on' => 'login'],
                [['name', 'age'], self::REQUIRED, 'message' => '自定义提示'],
                [['age'], self::INT, 'message' => '{tag} must be int'],
                ['range',self::RANGE,[0,10],'message' => '最大值{max},最小值{min},标签{tag}'],
                ['address',self::LENGTH,[2,5]],
                ['list',self::IN,[1,2,3,4,5,6,7],'on'=>'login'],
                ['name', self::LENGTH, 6],
                ['x', self::EMAIL],
                ['xx', self::URL],
                ['xxx', self::IP],
                ['xxxx', self::FLOAT],
                ['xxxxx', self::BOOLEAN],
                ['xxxxxx', self::REGEXP, '/\d+/'], // 正则验证
                ['my', self::CALLBACK, 'myFunc'], // 自定义函数验证
            ];
    }

    public function myFunc($tag,$value)
    {
        if($value == 'ht')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
```
* 运行
```
    $data = ['name' => '111','age' => 'a','range' => 20,'address' => 'a','list' => 8,'my' => 'ht'];
    $v = new UserValidator($data,'login');
    
    var_dump($v->getErrors());
```
* 说明
    * 验证规则没有写明场景，则会被赋予默认空场景 'on' => ''，因此，数据验证的时候，会去找对应的场景验证
    * 非必填的验证(比如验证int，ip，正则等)：只有该项通必填项验证，才会去做非必填项验证
    * 如何通过必填项验证：
        * 通过必填项验证规则
        * 没有写必填项验证规则，但是该项的值满足必填项验证规则（非null且非空）

# 十一、缓存
* 静态页面缓存
    * 配置 config/main.php
    ```
        'view_cache' =>
            [
                'is_open_cache' => true, // 如果为false，不生成静态缓存文件,开发期间应设置为false
                'view_cache_dir' => PROJECT_DIR . 'runtime/view_cache/', // 静态文件缓存路径
                'expire_time' => 60, // 缓存时间,单位秒
            ],    
    ```
* 数据缓存