---
stepsCompleted: ['step-01-init', 'step-03-success', 'step-04-journeys', 'step-05-domain', 'step-07-project-type', 'step-08-scoping', 'step-09-functional', 'step-10-nonfunctional', 'step-11-polish']
inputDocuments: ['Cahier-charges.txt', '_bmad-output/brainstorming/brainstorming-session-2026-01-28.md']
workflowType: 'prd'
briefCount: 0
researchCount: 0
brainstormingCount: 1
projectDocsCount: 0
classification:
  projectType: 'Web App (Dashboard Interne)'
  domain: 'E-commerce / Trésorerie'
  complexity: 'Faible (Single User)'
  projectContext: 'Greenfield'
  techStack: 'Laravel + Filament PHP'
---

# Product Requirements Document - Aramis Gestion

**Author:** kybyto
**Date:** 2026-01-28

## Executive Summary

Aramis Gestion est un outil interne de pilotage financier et logistique conçu pour une activité e-commerce de skincare. L'objectif est de remplacer le suivi manuel par un dashboard centralisé sous Laravel/Filament, permettant de suivre en temps réel les dettes fournisseurs, le cash en main et l'argent en transit chez le livreur, tout en simplifiant la préparation des commandes via Shopify.

## Success Criteria

### User Success
*   **Clarté financière :** Visibilité du cash dispo et des dettes en moins de 5 secondes.
*   **Efficacité terrain :** Génération instantanée de listes d'achats PDF pour le partenaire.
*   **Fiabilité :** Calcul automatique des soldes sans erreurs manuelles.

### Measurable Outcomes
*   Réduction de 80% du temps de réconciliation financière.
*   Zéro dépassement de budget publicitaire grâce au suivi strict de la caisse.

## Product Scope (MVP)

### Phase 1 : Cœur Opérationnel
*   **Sync Shopify :** Import manuel des commandes "Confirmées" (Nom + Image produit).
*   **Logistique :** Action groupée (Bulk) pour générer un PDF de stock à acheter.
*   **Finance :** Gestion des fournisseurs, dettes, paiements, caisse et relevés société de livraison.
*   **Dashboard :** Compteurs KPIs (Cash, Dettes, En attente).

### Phase 2 : Améliorations
*   Widgets de statistiques mensuelles (Ventes/Dépenses).
*   Suivi des frais de conversion USD (Achat Dollars).

## User Journeys

### 1. Le Gestionnaire (Admin Unique)
*   Confirme les commandes sur Shopify -> Synchronise sur Aramis -> Surveille sa trésorerie -> Paie les fournisseurs via la caisse.

### 2. Le Partenaire (Logistique)
*   Reçoit un PDF généré par l'utilisateur -> Achète les produits -> Remet au livreur.

## Domain-Specific Requirements : Le Cycle de l'Argent

Le système suit un flux de valeur strict entre quatre entités :
1.  **Dette Fournisseur :** Augmentée manuellement lors de l'achat, diminuée par un paiement via la Caisse.
2.  **Société de Livraison :** Solde augmenté lors d'une livraison réussie (Prix Shopify), diminué lors d'un relevé vers la Caisse.
3.  **Caisse (Cash) :** Pivot central. Reçoit l'argent du livreur ou des entrées manuelles (ex: Salaire). Paie les dettes et les frais.
4.  **Règle d'or :** Aucun paiement ou relevé ne peut excéder le solde disponible dans l'entité source.

## Technical & Project-Type Requirements

*   **Stack :** Laravel 11 + Filament PHP v3.
*   **Intégration :** API Shopify REST (Filtrage sur `status:confirmed`).
*   **Médias :** Les images produits sont affichées via les URLs CDN Shopify (pas de stockage local).
*   **Hébergement :** Serveur VPS (DigitalOcean).

## Functional Requirements

### Intégration & Commandes
*   **FR1 :** Bouton de synchronisation manuelle des commandes Shopify confirmées.
*   **FR2 :** Affichage des produits avec nom et image issue du CDN Shopify.

### Logistique
*   **FR3 :** Sélection groupée de commandes pour générer un PDF de préparation (Nom + Photo + Quantité).

### Gestion Financière
*   **FR4 :** Création/Gestion de fiches fournisseurs et de leurs soldes de dettes.
*   **FR5 :** Enregistrement des entrées de cash (Relevés livreur, Apports personnels).
*   **FR6 :** Journal des frais journaliers (Achat dollars, fournitures) impactant la caisse.
*   **FR7 :** Historique complet et immuable de toutes les transactions financières.

## Non-Functional Requirements

*   **NFR1 :** Performance mobile (Chargement < 3s en 4G).
*   **NFR2 :** Sécurité par authentification standard Filament.
*   **NFR3 :** Atomicité des calculs financiers pour prévenir toute corruption de solde.
*   **NFR4 :** Sauvegarde quotidienne de la base de données.