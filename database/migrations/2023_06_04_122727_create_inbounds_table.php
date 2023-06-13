<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbounds', function (Blueprint $table) {
            $table->id();

            $table->integer('node_id')->nullable();

            $table->string('protocol')->nullable();
            $table->string('type')->nullable();
            $table->string('port')->nullable();
            $table->string('tag')->nullable();
            $table->string('listen')->nullable();

            $table->json('stream_settings')->nullable();
            $table->json('network_settings')->nullable();
            $table->json('tls_settings')->nullable();
            $table->json('rule_Settings')->nullable();
            $table->json('dns_settings')->nullable();
            $table->json('rules')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inbounds');
    }
};
