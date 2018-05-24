<?php
namespace core\ext\directory;

/**
 * 目录以及文件操作类
 */
class Directory
{
    /**
     * 返回某个目录下所有文件的树形结构
     *
     * @param string $directoryName 目录名
     * @param array $tree 树形数组
     * @param string $extension 要取的文件后缀
     * @param boolean $isFullPath 是否返回文件的完整路径
     */
    public function listTrees($directoryName, &$tree = [], $extension = '', $isFullPath = false)
    {
        $iterator = new \DirectoryIterator($directoryName);
        while ($iterator->valid()) {
            $file = $iterator->current();
            if ($file->isDot()) {
                $iterator->next();
                continue;
            }

            if ($file->isDir()) {
                $this->listTrees($directoryName . $file->getFilename() . '/', $tree['directories'][$file->getFilename()], $extension , $isFullPath);
            } else {
                if ($file->getExtension() != $extension) {
                    $iterator->next();
                    continue;
                }

                $tree['files'][] = $isFullPath ? $directoryName . $file->getFilename() : $file->getFilename();
            }

            $iterator->next();
        }
    }

    public function makeDirectory()
    {

    }
}

//$d = new Directory();
//
//$tree = [];
//$directoryName = 'F:/html/wave/apis/';
//$d->listTrees($directoryName, $tree,'php',true);
//
//echo '<pre>';
//print_r($tree);




