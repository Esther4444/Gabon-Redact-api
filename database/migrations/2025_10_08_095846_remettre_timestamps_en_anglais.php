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
        // Remettre les timestamps en anglais pour toutes les tables

        // Table: profils
        Schema::table('profils', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: dossiers
        Schema::table('dossiers', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: articles
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
            $table->renameColumn('supprime_le', 'deleted_at');
        });

        // Table: workflow_articles
        Schema::table('workflow_articles', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: commentaires
        Schema::table('commentaires', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: messages
        Schema::table('messages', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: medias
        Schema::table('medias', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: invitations_equipe
        Schema::table('invitations_equipe', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: planifications_publication
        Schema::table('planifications_publication', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: evenements_analytiques
        Schema::table('evenements_analytiques', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // Table: journaux_audit
        Schema::table('journaux_audit', function (Blueprint $table) {
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les timestamps en français

        Schema::table('profils', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
            $table->renameColumn('deleted_at', 'supprime_le');
        });

        Schema::table('workflow_articles', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('commentaires', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('medias', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('invitations_equipe', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('planifications_publication', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('evenements_analytiques', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        Schema::table('journaux_audit', function (Blueprint $table) {
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });
    }
};
