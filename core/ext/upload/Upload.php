<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/4/12
 */
namespace core\ext\upload;

class Upload
{
    private $_files = [];
    private $_allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    private $_maxSize = 1000000; //限制文件上传大小（字节）

    private $_errors = [];

    public function setAllowedTypes($allowedTypes)
    {
        $this->_allowedTypes = $allowedTypes;
    }

    public function __construct()
    {
        $this->processFiles();

        foreach ($this->_files as $k => $file) {
            $this->checkUploadError($k, $file);
            $this->checkType($k, $file);
            $this->checkSize($k, $file);

            // 没错误，上传
            if(empty($this->_errors[$k]))
            {
                // 上传
//                var_dump($file);
                $this->saveUpload($file);
            }
        }

        var_dump($this->_errors);
    }

    private function processFiles()
    {
        $newFiles = [];
        if (count($_FILES) == 1) {
            $currentFiles = current($_FILES);
            if (is_array($currentFiles['name']) && count($currentFiles['name']) > 1) {
                for ($i = 0; $i < count($currentFiles['name']); $i++) {
                    $newFiles[$i]['name'] = $currentFiles['name'][$i];
                    $newFiles[$i]['type'] = $currentFiles['type'][$i];
                    $newFiles[$i]['tmp_name'] = $currentFiles['tmp_name'][$i];
                    $newFiles[$i]['error'] = $currentFiles['error'][$i];
                    $newFiles[$i]['size'] = $currentFiles['size'][$i];
                }
            } else {
                $newFiles[] = $_FILES;
            }
        }

        // 过滤空的
        foreach ($newFiles as $k => $file)
        {
            if(empty($file['name'])){
                unset($newFiles[$k]);
            }
        }

        $this->_files = $newFiles;
    }

    private function checkUploadError($k, $file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
//                UPLOAD_ERR_OK => '上传成功',
                UPLOAD_ERR_INI_SIZE => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
                UPLOAD_ERR_FORM_SIZE => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
                UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
                UPLOAD_ERR_NO_FILE => '没有文件被上传',
                UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
                UPLOAD_ERR_CANT_WRITE => '文件写入失败',
                UPLOAD_ERR_EXTENSION => 'php文件上传扩展没有打开',
            ];
            $this->_errors[$k]['upload_error'] = isset($uploadErrors[$file['error']]) ? $uploadErrors[$file['error']] : '未知的upload_error';
            $this->_errors[$k]['upload_error'] = '【' . $k . '】' . $this->_errors[$k]['upload_error'];
        }
    }

    private function checkType($k, $file)
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), $this->_allowedTypes)) {
            $this->_errors[$k]['type'] = '【' . $k . '】' . '类型不能为' . $extension;
        }
    }

    private function checkSize($k, $file)
    {
        if($file['size'] > $this->_maxSize){
            $this->_errors[$k]['size'] = '【' . $k . '】' . '文件过大,上传的文件不能超过' . $this->_maxSize . '个字节';
        }
    }

    private function getNewFileName()
    {

    }

    public function getFileInfo()
    {

    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function saveUpload($file)
    {
        if(is_uploaded_file($file['tmp_name'])){
            move_uploaded_file($file['tmp_name'],'./uploads/111.jpg');
        }
    }
}