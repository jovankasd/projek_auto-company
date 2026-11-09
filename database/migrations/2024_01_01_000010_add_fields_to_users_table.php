<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('name');
            $table->string('role', 30)->default('customer')->after('phone_number');
            $table->string('avatar_path')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('avatar_path');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();

            $table->index(['role', 'is_active'], 'idx_users_role_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
            $table->dropColumn([
                'phone_number', 'role', 'avatar_path',
                'is_active', 'last_login_at', 'deleted_at',
            ]);
        });
    }
};
