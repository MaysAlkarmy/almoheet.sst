<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings_fatora', function (Blueprint $table) {
            $table->dropForeign('settings_fatora_location_id_foreign');

        // 2. الآن يمكنك حذف الـ Unique Index بدون مشاكل
        $table->dropUnique('settings_fatora_location_id_unique');

        // 3. إضافة القيد الفريد المركب الجديد (الشركة والفرع معاً)
        $table->unique(['business_id', 'location_id'], 'business_location_unique');

        // 4. إعادة بناء المفتاح الأجنبي لـ location_id
        $table->foreign('location_id')
              ->references('id')
              ->on('business_locations')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings_fatora', function (Blueprint $table) {
            //
        });
    }
};
