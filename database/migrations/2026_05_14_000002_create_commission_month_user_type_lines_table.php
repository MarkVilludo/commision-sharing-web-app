<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_month_user_type_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_month_report_id')
                ->constrained('commission_month_reports')
                ->cascadeOnDelete();
            $table->foreignId('user_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_type_name');
            $table->decimal('percentage', 6, 3);
            $table->unsignedInteger('recipient_count')->default(0);
            $table->decimal('total_salary', 14, 2)->default(0);
            $table->decimal('total_commission', 14, 2)->default(0);
            $table->decimal('total_remaining', 14, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['commission_month_report_id', 'user_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_month_user_type_lines');
    }
};
