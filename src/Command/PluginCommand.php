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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Table;

#[Command]
class PluginCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:plugin');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('make a SForum plugin');
    }

    public function handle()
    {
        // 插件名
        $plugin_name = $this->ask('插件名');
        $this->comment('插件名：' . $plugin_name);

        // 插件目录名
        $this->info('插件目录名以大写英文字母开头，只能包含字母、数字');
        $plugin_dir = $this->ask('插件目录名');

        if (is_dir(plugin_path($plugin_dir))) {
            $this->error('插件目录已存在');
            return;
        }

        $this->comment('插件目录名：' . $plugin_dir);

        // 插件描述
        $plugin_desc = $this->ask('插件描述','插件描述');
        $this->comment('插件描述：' . $plugin_desc);

        // 插件作者
        $plugin_author = $this->ask('插件作者','SForum');
        $this->comment('插件作者：' . $plugin_author);

        // 插件链接
        $plugin_link = $this->ask('插件链接','https://www.sforum.cn');
        $this->comment('插件链接：' . $plugin_link);

        // 插件版本号
        $plugin_version = $this->ask('插件版本号', '1.0.0');
        $this->comment('插件版本号：' . $plugin_version);

        // 插件依赖SForum版本号
        $plugin_sforum_version = $this->ask('依赖SForum版本号', 'v2.2.2');
        $this->comment('依赖SForum版本号：' . $plugin_sforum_version);

        // 开始创建插件
        $this->info('开始创建插件');

        $plugin_json = file_get_contents(plugin_path('SFDev/src/stub/PluginData.stub'));
        $plugin_class = file_get_contents(plugin_path('SFDev/src/stub/Plugin.stub'));

        // 生产插件json文件
        $plugin_json = str_replace([
            '{plugin_name}',
            '{plugin_desc}',
            '{plugin_version}',
            '{plugin_author}',
            '{plugin_link}',
            '{plugin_sforum_version}',
        ], [
            $plugin_name,
            $plugin_desc,
            $plugin_version,
            $plugin_author,
            $plugin_link,
            $plugin_sforum_version,
        ], $plugin_json);

        // 生成插件类文件
        $plugin_class = str_replace([
            '%NAMESPACE%',
            '%CLASS%',
        ], [
            'App\Plugins\\' . $plugin_dir,
            $plugin_dir,
        ], $plugin_class);

        // 生成composer.json文件
        $composer_json = file_get_contents(plugin_path('SFDev/src/stub/composer.stub'));

        // 生成.gitignore文件
        $gitignore = file_get_contents(plugin_path('SFDev/src/stub/.gitignore.stub'));

        // 创建插件基本目录
        mkdir(plugin_path($plugin_dir));
        mkdir(plugin_path($plugin_dir . '/src'));
        mkdir(plugin_path($plugin_dir . '/resources'));
        mkdir(plugin_path($plugin_dir . '/resources/views'));

        // 创建插件json文件
        file_put_contents(plugin_path($plugin_dir . '/' . $plugin_dir . '.json'), $plugin_json);
        // 创建插件类文件
        file_put_contents(plugin_path($plugin_dir . '/' . $plugin_dir . '.php'), $plugin_class);
        // 创建插件.dirName文件
        file_put_contents(plugin_path($plugin_dir . '/.dirName'), $plugin_dir);
        // 创建composer.json文件
        file_put_contents(plugin_path($plugin_dir . '/composer.json'), $composer_json);
        // 创建.gitignore文件
        file_put_contents(plugin_path($plugin_dir . '/.gitignore'), $gitignore);

        // 输出结果
        $table = new Table($this->output);
        $table
            ->setHeaders(['插件名', '插件目录', '插件作者','插件版本号','依赖SForum版本号'])
            ->setRows([
                [$plugin_name, plugin_path($plugin_dir), $plugin_author, $plugin_version, $plugin_sforum_version],
            ]);
        $table->setHeaderTitle('插件创建成功！');
        $table->render();
    }
}
