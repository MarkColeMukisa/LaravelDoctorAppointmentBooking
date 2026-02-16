<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        if (config('database.default') === 'pgsql') {
            $this->withinTransaction = false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcement_banners', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 120)->nullable();
            $table->string('message');
            $table->string('link_url')->nullable();
            $table->string('link_label', 60)->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_banners');
    }
};
