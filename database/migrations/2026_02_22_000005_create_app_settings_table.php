<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('pos_name')->default('Fast Food POS');
            $table->string('color_scheme')->default((string) config('branding.default_scheme', 'sunset'));
            $table->timestamps();
        });

        DB::table('app_settings')->insert(array_merge(
            ['id' => 1],
            AppSetting::defaults(),
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
