<?php

namespace App\Providers;

use App\Repositories\AdminInterface;
use App\Repositories\AdminRepository;
use App\Repositories\ArticleInterface;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\ExampleInterface;
use App\Repositories\ExampleRepository;
use App\Repositories\PasswordResetInterface;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CategoryInterface::class, CategoryRepository::class);
        $this->app->bind(AdminInterface::class, AdminRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(ArticleInterface::class, ArticleRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
