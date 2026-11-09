<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('part_number')->index();
            $table->string('brand');
            $table->string('category');

            $table->json('car_brand_compatibility')->nullable();
            $table->json('car_model_compatibility')->nullable();

            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->decimal('price', 15, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(5);

            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->json('dimensions_cm')->nullable();

            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
