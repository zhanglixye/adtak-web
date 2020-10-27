<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
        $this->mapWebBizRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * 業務開発用
     *
     * routes/biz/ 配下のファイルを再帰的にすべて読み込む
     *
     */
    protected function mapWebBizRoutes()
    {
        $biz_route_file_paths = $this->globAll(base_path('routes/biz/'));

        foreach ($biz_route_file_paths as $biz_route_file_path) {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group($biz_route_file_path);
        }
    }

    public function globAll($dir)
    {
        // 指定されたディレクトリ内の一覧を取得
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::CURRENT_AS_FILEINFO |
                    \FilesystemIterator::KEY_AS_PATHNAME |
                    \FilesystemIterator::SKIP_DOTS
            )
        );

        $file_names = [];
        $file_paths = [];
        foreach ($files as $file_path => $file_info) {
            if (!$file_info->isFile() || preg_match("/^\./", $file_info->getFileName())) {
                continue;
            }

            $file_names[] = $file_info->getFileName();
            $file_paths[] = $file_path;
        }

        // bizディレクトリ配下に同一ファイル名があればオーバーライドされてしまうため通さない
        if ($file_names !== array_unique($file_names)) {
            throw new \Exception('duplicate biz_routing_files');
        }

        return $file_paths;
    }
}
