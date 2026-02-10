<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sample_file_path')) {
                $table->string('sample_file_path')->nullable()->after('zip_file_size');
            }
            if (!Schema::hasColumn('products', 'sample_file_name')) {
                $table->string('sample_file_name')->nullable()->after('sample_file_path');
            }
            if (!Schema::hasColumn('products', 'sample_file_size')) {
                $table->unsignedBigInteger('sample_file_size')->nullable()->after('sample_file_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sample_file_path', 'sample_file_name', 'sample_file_size']);
        });
    }
};
