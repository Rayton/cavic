<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leaders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id')->unsigned()->nullable();
            $table->string('position', 50)->comment('secretary or chairman');
            $table->integer('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            // Note: Unique constraint removed to allow multiple secretaries/chairpersons per tenant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaders');
    }
};
