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
        Schema::create('loan_approver_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('approval_level')->unique()->comment('1=Trustee 1, 2=Trustee 2, 3=Secretary, 4=Chairman');
            $table->string('approval_level_name', 50);
            $table->bigInteger('approver_member_id')->unsigned()->nullable();
            $table->integer('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->foreign('approver_member_id')->references('id')->on('members')->onDelete('set null');
            $table->unique(['approval_level', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_approver_settings');
    }
};
