<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('author_name')->nullable()->after('user_id');
            $table->string('author_email')->nullable()->after('author_name');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_email']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
