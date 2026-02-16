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
        Schema::create('patient_status_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('current_status', 20);
            $table->string('requested_status', 20);
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_request_note');
            $table->text('doctor_decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'status']);
            $table->index(['patient_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_status_change_requests');
    }
};
