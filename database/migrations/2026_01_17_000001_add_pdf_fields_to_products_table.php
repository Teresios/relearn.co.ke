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
            // Add PDF file storage fields
            $table->string('pdf_file_path')->nullable()->after('file_size');
            $table->string('pdf_file_name')->nullable()->after('pdf_file_path');
            $table->bigInteger('pdf_file_size')->nullable()->after('pdf_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('pdf_file_path');
            $table->dropColumn('pdf_file_name');
            $table->dropColumn('pdf_file_size');
        });
    }
};
