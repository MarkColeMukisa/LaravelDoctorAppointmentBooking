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
        Schema::create('patient_status_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('previous_status', 20);
            $table->string('new_status', 20);
            $table->text('doctor_approval_note');
            $table->boolean('doctor_approval_granted')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('approved_at');
            $table->index(['patient_id', 'approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_status_audits');
    }
};
