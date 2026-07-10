<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::dropIfExists('current_stocks');
    }

    public function down()
    {
        Schema::create('current_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique();
            $table->integer('current_stock')->default(0);
            $table->timestamp('updated_at')->nullable();
        });
    }
};
