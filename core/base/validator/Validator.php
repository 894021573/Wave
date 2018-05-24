<?php
/**
 * 验证类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\validator;

class Validator
{
    private $_errors = [];
    private $_attributes = [];

    const REQUIRED = 'required';
    const INT = 'int';
    const EMAIL = 'email';
    const URL = 'url';
    const IP = 'ip';
    const FLOAT = 'float';
    const BOOLEAN = 'boolean';
    const REGEXP = 'regexp';
    const CALLBACK = 'callback';

    const RANGE = 'range';
    const LENGTH = 'length';
    const IN = 'in';

    private $_requiredTags = [];

    // 描述，子类继承该属性以重写提示语
    protected $_messages = [
        self::REQUIRED => '{tag} should be required',
        self::INT => '{tag} is not a valid int',
        self::EMAIL => '{tag} is not a valid mailbox',
        self::URL => '{tag} is not a valid URL',
        self::IP => '{tag} is not a valid IP',
        self::FLOAT => '{tag} is not a valid decimal',
        self::BOOLEAN => '{tag} is not a valid boolean value',
        self::REGEXP => '{tag} is not a valid regexp',
        self::CALLBACK => '{tag} is not pass ({funcName}) filter',
        self::RANGE => '{tag} is not in range {min} ~ {max}',
        self::LENGTH => '{tag} is not in length {min} ~ {max}',
        self::IN => '{tag} is not in ({list})',
    ];

    /**
     *
     * @param array $data 验证数据数组
     * 没传scene，则会验证所有规则，传了，只验证对应scene的规则；非required的字段，只有在被required或者满足required规则的情况下，才进行其他规则验证
     * [
     * [['name','age'], self::REQUIRED, 'message' => '自定义提示','on' => 'login'],
     * [['name','age'], self::INT, [1, 2],'message' => '{tag} must in {min} {max}'],
     * ['name', self::LENGTH, 6],
     * ['x', self::EMAIL],
     * ['xx', self::URL],
     * ['xxx', self::IP],
     * ['xxxx', self::FLOAT],
     * ['xxxxx', self::BOOLEAN],
     * ['xxxxxx', self::REGEXP, '/\d+/'],
     * ['xxxxxxx', self::CALLBACK, 'a'],
     * ]
     * @param string $scene 验证场景
     */
    public function __construct(array $data, $scene = '')
    {
        foreach ($data as $key => $val) {
            $this->_attributes[$key] = $val;
        }

        $rules = $this->rules();

        // 没有写场景的规则，给个空场景
        foreach ($rules as $k => $rule) {
            if (!array_key_exists('on', $rule)) {
                $rules[$k]['on'] = '';
            }
        }

        // 逐一验证
        foreach ($rules as $rule) {
            $this->addValidation($rule, $scene);
        }
    }

    private function addValidation($rule,$scene)
    {
        // 场景不匹配，跳过验证
        if($scene != $rule['on'])
        {
            return;
        }

        $tags = (array)$rule[0];
        $valueRule = $rule[1];
        foreach ($tags as $tag)
        {
            $value = isset($this->_attributes[$tag]) ? $this->_attributes[$tag] : null;
            $this->validateRule($tag,$value,$valueRule,$rule);
        }
    }

    private function validateRule($tag,$value,$valueRule,$rule)
    {
        $check = new Check();
        $result = true;
        if($valueRule == self::REQUIRED)
        {
            $result = $check->checkRequired($value);
            if($result)
            {
                $this->_requiredTags[] = $tag;
            }
        }else
        {
            if(isset($this->_attributes[$tag]))
            {
                $this->_requiredTags[] = $tag;
            }
        }

        if(in_array($tag,$this->_requiredTags))
        {
            switch ($valueRule)
            {
                case self::INT:
                    $result = $check->checkInt($value);
                    break;
                case self::EMAIL:
                    $result = $check->checkEmail($value);
                    break;
                case self::URL:
                    $result = $check->checkURL($value);
                    break;
                case self::IP:
                    $result = $check->checkIP($value);
                    break;
                case self::FLOAT:
                    $result = $check->checkFloat($value);
                    break;
                case self::BOOLEAN:
                    $result = $check->checkBoolean($value);
                    break;
                case self::REGEXP:
                    if(!isset($rule[2]))
                    {
                        throw new \Exception('regexp is empty');
                    }
                    $result = $check->checkRegexp($value,$rule[2]);
                    break;
                case self::CALLBACK:
                    if (!isset($rule[2]))
                    {
                        throw new \Exception('callback is empty');
                    }
                    $replaceArray['{funcName}'] = $rule[2];
                    $result = call_user_func_array([$this, $rule[2]], [$tag, $value]);
                    break;
                case self::RANGE:
                    if (!isset($rule[2]))
                    {
                        throw new \Exception('range is empty');
                    }
                    $replaceArray['{min}'] = $rule[2][0];
                    $replaceArray['{max}'] = $rule[2][1];

                    $result = $check->checkRange($value, $rule[2]);
                    break;
                case self::LENGTH:
                    if (!isset($rule[2]))
                    {
                        throw new \Exception('length is empty');
                    }
                    $replaceArray['{min}'] = $rule[2][0];
                    $replaceArray['{max}'] = $rule[2][1];
                    $result = $check->checkLength($value, $rule[2]);
                    break;
                case self::IN:
                    if (!isset($rule[2]))
                    {
                        throw new \Exception('list is empty');
                    }
                    $replaceArray['{list}'] = implode(',',$rule[2]);
                    $result = $check->checkList($value, $rule[2]);
                    break;
            }
        }

        if($result === false)
        {
            $message = isset($rule['message']) ? $rule['message'] : $this->_messages[$valueRule];
            $replaceArray['{tag}'] = $tag;
            $this->_errors[$tag][] = $this->processMessage($replaceArray, $message);
        }
    }

    private function processMessage($replaceArray,$message)
    {
        foreach ($replaceArray as $k => $item)
        {
            $message = str_replace($k,$item,$message);
        }

        return $message;
    }

    public function rules()
    {
        return [];
    }

    /**
     * 获取错误信息数组
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * 获取第一条错误
     *
     * @return mixed|string
     */
    public function getFirstError()
    {
        if (empty($this->getErrors())) {
            return '';
        } else {
            return current(current($this->getErrors()));
        }
    }

    public function __get($key)
    {
        return isset($this->_attributes[$key]) ? $this->_attributes[$key] : '';
    }
}