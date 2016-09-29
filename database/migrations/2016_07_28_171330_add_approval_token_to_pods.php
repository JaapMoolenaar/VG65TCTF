<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalTokenToPods extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('pods', function (Blueprint $table) {
      $table->string( 'approval_token' );
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('pods', function (Blueprint $table) {
      $table->dropColumn( 'approval_token' );
    });
  }
}
