<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/1/5
 */
use core\ext\util\Util;

?>
<form action="http://www.demo.com/upload" enctype="multipart/form-data" method="post">
    <input type="file" name="file[]">
    <input type="file" name="file[]">
    <input type="hidden" name="<?php echo CSRF_TOKEN?>" value="<?php echo Util::generateCSRF()?>">
    <input type="submit" value="提交">
</form>
