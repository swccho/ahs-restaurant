<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('cover_path')->nullable()->after('logo_path');
            $table->string('theme_color', 20)->nullable()->after('cover_path');
            $table->string('email', 120)->nullable()->after('whatsapp');
            $table->string('google_map_url', 500)->nullable()->after('address');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('google_map_url');
            $table->decimal('min_order_amount', 10, 2)->default(0)->after('delivery_fee');
            $table->boolean('delivery_enabled')->default(true)->after('min_order_amount');
            $table->boolean('pickup_enabled')->default(true)->after('delivery_enabled');
            $table->string('estimated_delivery_time', 60)->nullable()->after('pickup_enabled');
            $table->json('opening_hours')->nullable()->after('estimated_delivery_time');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'cover_path',
                'theme_color',
                'email',
                'google_map_url',
                'delivery_fee',
                'min_order_amount',
                'delivery_enabled',
                'pickup_enabled',
                'estimated_delivery_time',
                'opening_hours',
            ]);
        });
    }
};
