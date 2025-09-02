<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use App\Helpers\SchemaHelper;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Post;

class SchemaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $routeName = Route::currentRouteName();
            $schema = null;

            // Product Page Schema
            if ($routeName === 'product.show' && $view->getData()['product'] ?? false) {
                $product = $view->getData()['product'];
                $schema = SchemaHelper::product(
                    $product,
                    $product->brand ?? null,
                    $product->reviews ?? []
                );
            }

            // Category Page Schema
            if ($routeName === 'category.show' && $view->getData()['category'] ?? false) {
                $schema = SchemaHelper::category($view->getData()['category']);
            }

            // Brand Page Schema
            if ($routeName === 'brand.show' && $view->getData()['brand'] ?? false) {
                $schema = SchemaHelper::brand($view->getData()['brand']);
            }

            // FAQ Page Schema
            if ($routeName === 'faq.index' && $view->getData()['faqs'] ?? false) {
                $schema = SchemaHelper::faq($view->getData()['faqs']);
            }

            // Blog / Article Page Schema
            if ($routeName === 'blog.show' && $view->getData()['post'] ?? false) {
                $schema = SchemaHelper::article($view->getData()['post']);
            }

            // Share schema with blade
            $view->with('schema', $schema);
        });
    }
}
