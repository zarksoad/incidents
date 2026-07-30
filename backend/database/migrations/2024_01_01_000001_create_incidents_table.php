<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['baja', 'media', 'alta', 'critica']);
            $table->enum('status', ['abierto', 'en_progreso', 'cerrado', 'vencido']);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date');
            $table->timestamps();

            $table->index('status', 'idx_incidents_status');
            $table->index('priority', 'idx_incidents_priority');
            $table->index('assigned_id', 'idx_incidents_assigned_id');
            $table->index('due_date', 'idx_incidents_due_date');
            $table->index('user_id', 'idx_incidents_user_id');
            $table->index('title', 'idx_incidents_title');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE incidents ADD FULLTEXT ft_incidents_search (title, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
