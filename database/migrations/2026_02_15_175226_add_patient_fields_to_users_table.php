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
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('profile_image');
            $table->string('address')->nullable()->after('contact_number');
            $table->date('date_of_birth')->nullable()->after('address');
            $table->string('gender', 30)->nullable()->after('date_of_birth');
            $table->date('registration_date')->nullable()->after('gender');
            $table->string('patient_status', 20)->default('inactive')->after('registration_date');

            $table->index(['role', 'patient_status'], 'users_role_patient_status_idx');
            $table->index('registration_date', 'users_registration_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_patient_status_idx');
            $table->dropIndex('users_registration_date_idx');
            $table->dropColumn([
                'contact_number',
                'address',
                'date_of_birth',
                'gender',
                'registration_date',
                'patient_status',
            ]);
        });
    }
};
