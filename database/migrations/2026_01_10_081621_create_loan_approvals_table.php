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
        Schema::create('loan_approvals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('loan_id')->unsigned();
            $table->integer('approval_level')->comment('1=Trustee 1, 2=Trustee 2, 3=Secretary, 4=Chairman');
            $table->string('approval_level_name', 50);
            $table->bigInteger('approver_member_id')->unsigned()->nullable();
            $table->integer('status')->default(0)->comment('0=Pending, 1=Approved, 2=Rejected');
            $table->text('remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('approved_by_user_id')->unsigned()->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('approver_member_id')->references('id')->on('members')->onDelete('set null');
            $table->index(['loan_id', 'approval_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_approvals');
    }
};
