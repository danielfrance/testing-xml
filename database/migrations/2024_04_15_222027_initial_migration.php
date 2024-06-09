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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('slug')->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained();
        });

        Schema::create('files', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->string('extension')->nullable();
            $table->foreignId('team_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('filing_types', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tax_id_types', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('countries', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->boolean('us_territory')->default(false);
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('states', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('abbreviation');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('tribes', function(Blueprint $table){
            $table->id();
            $table->string('name', 755);
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('filings', function(Blueprint $table){
            $table->id();
            $table->foreignId('filing_type_id')->constrained();

            $table->string('status')->default('Draft');
            $table->date('prepared_date')->nullable();
            $table->foreignId('team_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('company_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained();
            $table->boolean('get_fincen')->default(false);
            $table->boolean('foreign_pooled_investment')->default(false);
            $table->boolean('existing_reporting_company')->default(false);
            $table->foreignId('filing_id')->constrained();
            $table->string('legal_name');
            $table->json('alternate_name')->nullable();
            $table->foreignId('tax_id_type_id')->constrained('tax_id_types');
            $table->foreignId('tax_id_country_id')->constrained('countries');
            $table->string('tax_id_number');
            $table->string('formation_type')->default('domestic');
            $table->foreignId('country_formation_id')->constrained('countries');
            $table->foreignId('state_formation_id')->nullable()->constrained('states');
            $table->foreignId('tribal_formation_id')->nullable()->constrained('tribes');
            $table->string('tribal_other_name')->nullable();
            $table->string('current_street_address');
            $table->string('current_city');
            $table->foreignId('current_state_id')->constrained('states');
            $table->foreignId('current_country_id')->constrained('countries');
            $table->string('zip');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained();
            $table->string('fincen_id')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->date('dob')->nullable();
            $table->string('address_type')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->string('zip')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->foreignId('id_document_country')->nullable()->constrained('countries');
            $table->foreignId('id_document_state')->nullable()->constrained('states');
            $table->foreignId('id_document_tribe')->nullable()->constrained('tribes');
            $table->string('tribal_other_name')->nullable();
            $table->foreignId('id_document_file_id')->nullable()->constrained('files')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('info_verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users', 'id')
            ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('beneficial_owners', function(Blueprint $table){
            $table->id();
            $table->foreignId('team_id')->constrained();
            $table->boolean('parent_guardian')->default(false);
            $table->string('fincen_id')->nullable();
            $table->boolean('exempt_entity')->default(false);
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->string('zip')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->foreignId('id_document_country')->nullable()->constrained('countries');
            $table->foreignId('id_document_state')->nullable()->constrained('states');
            $table->foreignId('id_document_tribe')->nullable()->constrained('tribes');
            $table->string('tribal_other_name')->nullable();
            $table->foreignId('id_document_file_id')->nullable()->constrained('files')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('info_verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users', 'id')
            ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('beneficial_owner_filing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficial_owner_id')->constrained('beneficial_owners');
            $table->foreignId('filing_id')->constrained('filings');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_applicant_filing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_applicant_id')->constrained('company_applicants');
            $table->foreignId('filing_id')->constrained('filings');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status')->default('pending');
            $table->string('token')->unique();
            $table->string('type'); //team, owner, applicant
            $table->foreignId('team_id')->constrained('teams');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });
        Schema::dropIfExists('invites');

        Schema::table('company_applicant_filing', function (Blueprint $table) {
            $table->dropForeign(['company_applicant_id']);
            $table->dropForeign(['filing_id']);
        });

        Schema::table('beneficial_owner_filing', function (Blueprint $table) {
            $table->dropForeign(['beneficial_owner_id']);
            $table->dropForeign(['filing_id']);
        });

        Schema::dropIfExists('company_applicant_filing');
        Schema::dropIfExists('beneficial_owner_filing');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });


        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('country');
            $table->dropColumn('zip');
            $table->dropColumn('phone');
            $table->dropColumn('deleted_at');
        });

        

        Schema::table('beneficial_owners', function(Blueprint $table){
            $table->dropForeign(['id_document_file_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['id_document_country']);
            $table->dropForeign(['id_document_state']);
            $table->dropForeign(['id_document_tribe']);

            $table->dropColumn('id_document_file_id');
            $table->dropColumn('team_id');
            $table->dropColumn('state_id');
            $table->dropColumn('country_id');
            $table->dropColumn('id_document_country');
            $table->dropColumn('id_document_state');
            $table->dropColumn('id_document_tribe');
        
        });

        Schema::table('company_applicants', function (Blueprint $table) {
            $table->dropForeign(['id_document_file_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['id_document_country']);
            $table->dropForeign(['id_document_state']);
            $table->dropForeign(['id_document_tribe']);

            $table->dropColumn('id_document_file_id');
            $table->dropColumn('team_id');
            $table->dropColumn('state_id');
            $table->dropColumn('country_id');
            $table->dropColumn('id_document_country');
            $table->dropColumn('id_document_state');
            $table->dropColumn('id_document_tribe');
        
        });
        Schema::dropIfExists('company_applicants');
        Schema::dropIfExists('beneficial_owners');
        Schema::dropIfExists('company_info');
        Schema::dropIfExists('filings');
        Schema::dropIfExists('tribes');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('tax_id_types');
        Schema::dropIfExists('filing_types');
        Schema::dropIfExists('files');

    }
};
