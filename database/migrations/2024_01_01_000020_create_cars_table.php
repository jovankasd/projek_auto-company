<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('brand', 100);
            $table->string('model_name', 100);
            $table->smallInteger('year')->unsigned();
            $table->enum('category', ['SUV', 'Sedan', 'Sports', 'EV', 'MPV']);
            $table->string('color_name', 100);
            $table->string('color_hex', 7);
            $table->decimal('price', 15, 2);
            $table->json('engine_specs')->nullable();
            $table->json('dimensions')->nullable();
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->string('hero_image_transparent')->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->json('gallery_images')->nullable();
            $table->unsignedSmallInteger('stock_quantity')->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_available', 'sort_order'], 'idx_cars_available_sort');
            $table->index(['brand', 'category'], 'idx_cars_brand_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
