
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('Lainnya'); // Coffee, Non-Coffee, Snack, dll
            $table->text('description')->nullable();
            $table->unsignedInteger('price');                // Rupiah, tanpa desimal
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
