<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_month_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_month');
            $table->decimal('gross_total', 14, 2);
            $table->decimal('total_commission', 14, 2)->default(0);
            $table->decimal('total_remaining', 14, 2)->default(0);
            $table->timestamps();

            $table->unique('report_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_month_reports');
    }
};
