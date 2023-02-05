<?php

declare(strict_types=1);
/**
 * This file is part of zhuchunshu.
 * @link     https://github.com/zhuchunshu
 * @document https://github.com/zhuchunshu/SForum
 * @contact  laravel@88.com
 * @license  https://github.com/zhuchunshu/SForum/blob/master/LICENSE
 */
namespace App\Plugins\SFDev\src\Command;

use Alchemy\Zippy\Zippy;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class BuildCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('plugin:build');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('build SForum plugin');
    }

    public function handle()
    {
        $dir = $this->ask('插件目录名');
        if (! is_dir(plugin_path($dir))) {
            $this->error('插件目录不存在');
            return;
        }
        if (! is_dir(BASE_PATH . '/runtime/plugins')) {
            // 创建目录
            mkdir(BASE_PATH . '/runtime/plugins', 0777, true);
        }
        // 压缩插件

        // 文件
        $files = $this->get_files(plugin_path($dir));

        // 获取插件信息
        $plugin_info = json_decode(file_get_contents(plugin_path($dir) . '/' . $dir . '.json'), true);

        // 插件版本号
        $plugin_version = @$plugin_info['version'] ?: '1.0.0';

        // 生成插件文件名
        $filename = $dir . '-' . $plugin_version . '.zip';

        $zippy = Zippy::load();
        $path = BASE_PATH . '/runtime/plugins/' . $filename;
        $zippy->create($path, $files, true);

        $this->alert('插件打包成功，文件路径：' . $path);

    }

    private function get_files($path)
    {
        $all = scandir($path);
        // 删除.DS_Store
        if (in_array('.DS_Store', $all)) {
            unset($all['.DS_Store']);
        }
        // 删除vendor
        if (in_array('vendor', $all)) {
            unset($all['vendor']);
        }
        // 删除 . 和 ..
        foreach ($all as $k => $v) {
            if ($v == '.' || $v == '..') {
                unset($all[$k]);
            }
        }


        $files = [];
        foreach ($all as $item) {
            $files[] = $path . '/' . $item;
        }

        return $files;
    }
}
