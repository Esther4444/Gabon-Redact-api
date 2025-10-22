<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Francisation des tables métier (les tables système Laravel restent en anglais)
     */
    public function up(): void
    {
        // 1. Renommer les tables de l'application (tables système Laravel non touchées)
        Schema::rename('profiles', 'profils');
        Schema::rename('folders', 'dossiers');
        // 'articles' reste en français
        Schema::rename('comments', 'commentaires');
        Schema::rename('media', 'medias');
        // 'notifications' reste en français
        // 'messages' reste en français
        Schema::rename('team_invitations', 'invitations_equipe');
        Schema::rename('publication_schedules', 'planifications_publication');
        Schema::rename('analytics_events', 'evenements_analytiques');
        Schema::rename('audit_logs', 'journaux_audit');
        Schema::rename('article_workflow', 'workflow_articles');

        // 2. Renommer les colonnes custom de la table 'users' (colonnes standard Laravel conservées)
        Schema::table('users', function (Blueprint $table) {
            // On garde les colonnes standard Laravel (name, email, password, etc.)
            // On francise uniquement nos colonnes custom
            $table->renameColumn('is_active', 'est_actif');
            $table->renameColumn('last_login_at', 'derniere_connexion_le');
            $table->renameColumn('failed_login_attempts', 'tentatives_connexion_echouees');
            $table->renameColumn('locked_until', 'verrouille_jusqu_au');
        });

        // 3. Renommer les colonnes de la table 'profils'
        Schema::table('profils', function (Blueprint $table) {
            // user_id reste en anglais pour cohérence avec la table users
            $table->renameColumn('full_name', 'nom_complet');
            $table->renameColumn('avatar_url', 'url_avatar');
            $table->renameColumn('role', 'role'); // reste en français
            $table->renameColumn('preferences', 'preferences'); // reste en français
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 4. Renommer les colonnes de la table 'dossiers'
        Schema::table('dossiers', function (Blueprint $table) {
            // owner_id reste lié à users donc on garde la cohérence
            $table->renameColumn('name', 'nom');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 5. Renommer les colonnes de la table 'articles'
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('title', 'titre');
            $table->renameColumn('content', 'contenu');
            $table->renameColumn('status', 'statut');
            $table->renameColumn('folder_id', 'dossier_id');
            // created_by, assigned_to restent liés à users donc on garde cohérence
            $table->renameColumn('seo_title', 'titre_seo');
            $table->renameColumn('seo_description', 'description_seo');
            $table->renameColumn('seo_keywords', 'mots_cles_seo');
            $table->renameColumn('published_at', 'publie_le');
            $table->renameColumn('metadata', 'metadonnees');
            $table->renameColumn('workflow_status', 'statut_workflow');
            // current_reviewer_id reste lié à users
            $table->renameColumn('submitted_at', 'soumis_le');
            $table->renameColumn('reviewed_at', 'relu_le');
            $table->renameColumn('approved_at', 'approuve_le');
            $table->renameColumn('rejection_reason', 'raison_rejet');
            $table->renameColumn('workflow_history', 'historique_workflow');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
            $table->renameColumn('deleted_at', 'supprime_le');
        });

        // 6. Renommer les colonnes de la table 'workflow_articles'
        Schema::table('workflow_articles', function (Blueprint $table) {
            // *_user_id restent liés à users donc on garde cohérence
            $table->renameColumn('action', 'action'); // reste
            $table->renameColumn('status', 'statut');
            $table->renameColumn('comment', 'commentaire');
            $table->renameColumn('action_at', 'action_le');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 7. Renommer les colonnes de la table 'commentaires'
        Schema::table('commentaires', function (Blueprint $table) {
            // author_id reste lié à users
            $table->renameColumn('body', 'contenu');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 8. Renommer les colonnes de la table 'messages'
        Schema::table('messages', function (Blueprint $table) {
            // sender_id, recipient_id restent liés à users
            $table->renameColumn('subject', 'sujet');
            $table->renameColumn('body', 'contenu');
            $table->renameColumn('is_read', 'est_lu');
            $table->renameColumn('parent_message_id', 'message_parent_id');
            $table->renameColumn('attachments', 'pieces_jointes');
            $table->renameColumn('read_at', 'lu_le');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 9. Renommer les colonnes de la table 'notifications'
        Schema::table('notifications', function (Blueprint $table) {
            // user_id reste lié à users
            $table->renameColumn('type', 'type'); // reste
            $table->renameColumn('title', 'titre');
            $table->renameColumn('message', 'message'); // reste
            $table->renameColumn('read', 'lu');
            $table->renameColumn('data', 'donnees');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 10. Renommer les colonnes de la table 'medias'
        Schema::table('medias', function (Blueprint $table) {
            // user_id reste lié à users
            $table->renameColumn('disk', 'disque');
            $table->renameColumn('path', 'chemin');
            $table->renameColumn('mime_type', 'type_mime');
            $table->renameColumn('size_bytes', 'taille_octets');
            $table->renameColumn('meta', 'metadonnees');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 11. Renommer les colonnes de la table 'invitations_equipe'
        Schema::table('invitations_equipe', function (Blueprint $table) {
            $table->renameColumn('email', 'email'); // reste
            $table->renameColumn('role', 'role'); // reste
            $table->renameColumn('token', 'jeton');
            // invited_by reste lié à users
            $table->renameColumn('expires_at', 'expire_le');
            $table->renameColumn('accepted_at', 'accepte_le');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 12. Renommer les colonnes de la table 'planifications_publication'
        Schema::table('planifications_publication', function (Blueprint $table) {
            $table->renameColumn('scheduled_for', 'planifie_pour');
            $table->renameColumn('channel', 'canal');
            $table->renameColumn('status', 'statut');
            $table->renameColumn('failure_reason', 'raison_echec');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 13. Renommer les colonnes de la table 'evenements_analytiques'
        Schema::table('evenements_analytiques', function (Blueprint $table) {
            // user_id reste lié à users
            $table->renameColumn('event_type', 'type_evenement');
            $table->renameColumn('properties', 'proprietes');
            $table->renameColumn('occurred_at', 'survenu_le');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });

        // 14. Renommer les colonnes de la table 'journaux_audit'
        Schema::table('journaux_audit', function (Blueprint $table) {
            // actor_id reste lié à users
            $table->renameColumn('action', 'action'); // reste
            $table->renameColumn('entity_type', 'type_entite');
            $table->renameColumn('entity_id', 'entite_id');
            $table->renameColumn('context', 'contexte');
            $table->renameColumn('occurred_at', 'survenu_le');
            $table->renameColumn('created_at', 'cree_le');
            $table->renameColumn('updated_at', 'modifie_le');
        });
    }

    /**
     * Reverse the migrations.
     * Restauration des noms anglais
     */
    public function down(): void
    {
        // Restaurer les colonnes dans l'ordre inverse

        // 14. Restaurer journaux_audit
        Schema::table('journaux_audit', function (Blueprint $table) {
            $table->renameColumn('type_entite', 'entity_type');
            $table->renameColumn('entite_id', 'entity_id');
            $table->renameColumn('contexte', 'context');
            $table->renameColumn('survenu_le', 'occurred_at');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 13. Restaurer evenements_analytiques
        Schema::table('evenements_analytiques', function (Blueprint $table) {
            $table->renameColumn('type_evenement', 'event_type');
            $table->renameColumn('proprietes', 'properties');
            $table->renameColumn('survenu_le', 'occurred_at');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 12. Restaurer planifications_publication
        Schema::table('planifications_publication', function (Blueprint $table) {
            $table->renameColumn('planifie_pour', 'scheduled_for');
            $table->renameColumn('canal', 'channel');
            $table->renameColumn('statut', 'status');
            $table->renameColumn('raison_echec', 'failure_reason');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 11. Restaurer invitations_equipe
        Schema::table('invitations_equipe', function (Blueprint $table) {
            $table->renameColumn('jeton', 'token');
            $table->renameColumn('expire_le', 'expires_at');
            $table->renameColumn('accepte_le', 'accepted_at');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 10. Restaurer medias
        Schema::table('medias', function (Blueprint $table) {
            $table->renameColumn('disque', 'disk');
            $table->renameColumn('chemin', 'path');
            $table->renameColumn('type_mime', 'mime_type');
            $table->renameColumn('taille_octets', 'size_bytes');
            $table->renameColumn('metadonnees', 'meta');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 9. Restaurer notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('titre', 'title');
            $table->renameColumn('lu', 'read');
            $table->renameColumn('donnees', 'data');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 8. Restaurer messages
        Schema::table('messages', function (Blueprint $table) {
            $table->renameColumn('sujet', 'subject');
            $table->renameColumn('contenu', 'body');
            $table->renameColumn('est_lu', 'is_read');
            $table->renameColumn('message_parent_id', 'parent_message_id');
            $table->renameColumn('pieces_jointes', 'attachments');
            $table->renameColumn('lu_le', 'read_at');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 7. Restaurer commentaires
        Schema::table('commentaires', function (Blueprint $table) {
            $table->renameColumn('contenu', 'body');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 6. Restaurer workflow_articles
        Schema::table('workflow_articles', function (Blueprint $table) {
            $table->renameColumn('statut', 'status');
            $table->renameColumn('commentaire', 'comment');
            $table->renameColumn('action_le', 'action_at');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 5. Restaurer articles
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('titre', 'title');
            $table->renameColumn('contenu', 'content');
            $table->renameColumn('statut', 'status');
            $table->renameColumn('dossier_id', 'folder_id');
            $table->renameColumn('titre_seo', 'seo_title');
            $table->renameColumn('description_seo', 'seo_description');
            $table->renameColumn('mots_cles_seo', 'seo_keywords');
            $table->renameColumn('publie_le', 'published_at');
            $table->renameColumn('metadonnees', 'metadata');
            $table->renameColumn('statut_workflow', 'workflow_status');
            $table->renameColumn('soumis_le', 'submitted_at');
            $table->renameColumn('relu_le', 'reviewed_at');
            $table->renameColumn('approuve_le', 'approved_at');
            $table->renameColumn('raison_rejet', 'rejection_reason');
            $table->renameColumn('historique_workflow', 'workflow_history');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
            $table->renameColumn('supprime_le', 'deleted_at');
        });

        // 4. Restaurer dossiers
        Schema::table('dossiers', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 3. Restaurer profils
        Schema::table('profils', function (Blueprint $table) {
            $table->renameColumn('nom_complet', 'full_name');
            $table->renameColumn('url_avatar', 'avatar_url');
            $table->renameColumn('cree_le', 'created_at');
            $table->renameColumn('modifie_le', 'updated_at');
        });

        // 2. Restaurer users (colonnes custom seulement)
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('est_actif', 'is_active');
            $table->renameColumn('derniere_connexion_le', 'last_login_at');
            $table->renameColumn('tentatives_connexion_echouees', 'failed_login_attempts');
            $table->renameColumn('verrouille_jusqu_au', 'locked_until');
        });

        // 1. Restaurer les noms de tables (tables système NON touchées)
        Schema::rename('workflow_articles', 'article_workflow');
        Schema::rename('journaux_audit', 'audit_logs');
        Schema::rename('evenements_analytiques', 'analytics_events');
        Schema::rename('planifications_publication', 'publication_schedules');
        Schema::rename('invitations_equipe', 'team_invitations');
        Schema::rename('medias', 'media');
        Schema::rename('commentaires', 'comments');
        Schema::rename('dossiers', 'folders');
        Schema::rename('profils', 'profiles');
    }
};
