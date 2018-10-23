<?php
/**
 * 主配置文件
 * @author luojianglai
 * @date   2018-04-24
 */

//自动载入配置文件,//RUN_MODEL false 非开发模式载入online下的文件
return loadConfig();
function loadConfig()
{
    $conf = [];
    $dir = CONF_PATH;
    if (RUN_MODEL != 'dev') {
        $dir = CONF_PATH . RUN_MODEL . '/';
    }
    if (is_dir($dir) && $dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (filetype($dir . $file) == 'file') {
                $fileInfo = pathinfo($file);
                if ($fileInfo['extension'] == 'php' && !in_array($fileInfo['filename'], ['config'])) {
                    $tmp = include $dir . $file;
                    $conf = array_merge($conf, $tmp);
                }
            }
        }
        closedir($dh);
    }
    return $conf;
}