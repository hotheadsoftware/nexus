<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->index()->constrained();
            $table->string('name');
            $table->string('panel');
            $table->string('logo')->nullable();
            $table->jsonb('colors')->nullable();
            $table->boolean('allow_registration')->default(true);
            $table->string('headline')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // A tenant should have exactly one brand per panel.
            // We can't enforce existence, but we can enforce uniqueness.
            $table->unique(['tenant_id', 'panel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
