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
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->string('first_name')->after('user_id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
        });

        // Migrate data if any exists
        $profiles = DB::table('admin_profiles')->get();
        foreach ($profiles as $profile) {
            DB::table('admin_profiles')->where('id', $profile->id)->update([
                'first_name' => $profile->first_name_ar ?? $profile->first_name_en,
                'last_name' => $profile->last_name_ar ?? $profile->last_name_en,
            ]);
        }

        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn(['first_name_ar', 'last_name_ar', 'first_name_en', 'last_name_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->string('first_name_ar')->nullable();
            $table->string('last_name_ar')->nullable();
            $table->string('first_name_en')->nullable();
            $table->string('last_name_en')->nullable();
        });

        $profiles = DB::table('admin_profiles')->get();
        foreach ($profiles as $profile) {
            DB::table('admin_profiles')->where('id', $profile->id)->update([
                'first_name_ar' => $profile->first_name,
                'first_name_en' => $profile->first_name,
                'last_name_ar' => $profile->last_name,
                'last_name_en' => $profile->last_name,
            ]);
        }

        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
