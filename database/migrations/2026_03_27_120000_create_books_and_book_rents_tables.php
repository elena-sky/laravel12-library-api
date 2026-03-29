<?php

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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('genre');
            $table->text('description')->nullable();
            $table->unsignedInteger('total_copies');
            $table->unsignedInteger('available_copies');
            $table->timestamps();

            $table->index('title');
            $table->index('author');
            $table->index('genre');
            $table->index('available_copies');
        });

        Schema::create('book_rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('status', 32);
            $table->timestamp('rented_at');
            $table->timestamp('due_date');
            $table->timestamp('returned_at')->nullable();
            $table->unsignedTinyInteger('reading_progress')->default(0);
            $table->unsignedInteger('extended_count')->default(0);
            $table->timestamps();

            $table->index(['book_id', 'status']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'ALTER TABLE books ADD CONSTRAINT books_total_copies_non_negative '
                .'CHECK (total_copies >= 0)'
            );
            DB::statement(
                'ALTER TABLE books ADD CONSTRAINT books_available_copies_non_negative '
                .'CHECK (available_copies >= 0)'
            );
            DB::statement(
                'ALTER TABLE books ADD CONSTRAINT books_available_lte_total '
                .'CHECK (available_copies <= total_copies)'
            );

            DB::statement(
                'ALTER TABLE book_rents ADD CONSTRAINT book_rents_reading_progress_range '
                .'CHECK (reading_progress >= 0 AND reading_progress <= 100)'
            );
            DB::statement(
                'ALTER TABLE book_rents ADD CONSTRAINT book_rents_extended_count_non_negative '
                .'CHECK (extended_count >= 0)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_rents');
        Schema::dropIfExists('books');
    }
};
