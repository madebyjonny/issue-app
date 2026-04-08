<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('color', 7)->default('#8b5cf6');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epics');
    }
};
