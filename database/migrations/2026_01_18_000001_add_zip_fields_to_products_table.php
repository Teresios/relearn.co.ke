<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add ZIP file storage fields
            $table->string('zip_file_path')->nullable()->after('pdf_file_size');
            $table->string('zip_file_name')->nullable()->after('zip_file_path');
            $table->bigInteger('zip_file_size')->nullable()->after('zip_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('zip_file_path');
            $table->dropColumn('zip_file_name');
            $table->dropColumn('zip_file_size');
        });
    }
};
