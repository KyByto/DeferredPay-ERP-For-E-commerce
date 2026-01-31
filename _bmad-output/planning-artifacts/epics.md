stepsCompleted: [1, 2, 3, 4]
workflowType: 'epics'
lastStep: 4
status: 'complete'
completedAt: '2026-01-28'
inputDocuments: ['_bmad-output/planning-artifacts/prd.md', '_bmad-output/planning-artifacts/architecture.md']
---

# aramis-gestion - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for aramis-gestion, decomposing the requirements from the PRD, UX Design if it exists, and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

**Intégration & Commandes (Shopify)**
*   **FR1 :** L'Utilisateur peut synchroniser manuellement les commandes Shopify via un bouton dédié.
*   **FR2 :** Le système ne doit importer que les commandes ayant le statut "Confirmé" sur Shopify.
*   **FR3 :** Le système doit récupérer et stocker l'URL de l'image du produit et son nom pour chaque ligne de commande.
*   **FR4 :** L'Utilisateur peut visualiser la liste des commandes synchronisées avec leurs détails (N° commande, Nom client, Date, Produits + Photos).

**Logistique (Génération PDF)**
*   **FR5 :** L'Utilisateur peut sélectionner plusieurs commandes (Bulk Selection) pour une action groupée.
*   **FR6 :** L'Utilisateur peut générer un document PDF récapitulatif des produits à acheter pour les commandes sélectionnées.
*   **FR7 :** Le document PDF doit inclure pour chaque produit : l'image, le nom, et la quantité totale à récupérer.

**Gestion des Dettes Fournisseurs**
*   **FR8 :** L'Utilisateur peut créer, modifier ou supprimer des fiches Fournisseurs.
*   **FR9 :** L'Utilisateur peut ajouter manuellement une dette (montant + description) à un fournisseur spécifique.
*   **FR10 :** L'Utilisateur peut enregistrer un paiement vers un fournisseur (diminution du solde).
*   **FR11 :** Le système doit calculer et afficher le solde total dû par fournisseur en temps réel.

**Gestion de la Trésorerie (Caisse & Livreur)**
*   **FR12 :** Le système doit suivre un solde virtuel pour la "Société de Livraison".
*   **FR13 :** L'Utilisateur peut enregistrer un "Relevé d'argent" depuis la Société vers la Caisse.
*   **FR14 :** L'Utilisateur peut ajouter manuellement des entrées de cash en Caisse avec une source (ex: Papa, Salaire).
*   **FR15 :** L'Utilisateur peut enregistrer des frais (sorties de caisse) avec un type (ex: Achat USD, Cartons) et une description.
*   **FR16 :** Le système doit interdire un paiement ou un relevé si le montant est supérieur au solde disponible (Caisse ou Société).

**Tableau de Bord & Historique**
*   **FR17 :** L'Utilisateur peut visualiser un tableau de bord avec les KPIs : Total Caisse, Total Dettes, Total chez Livreur.
*   **FR18 :** L'Utilisateur peut consulter l'historique chronologique de toutes les transactions (Paiements, Relevés, Frais, Entrées).

### NonFunctional Requirements

**Performance**
*   **NFR1 :** L'interface mobile (Filament) doit s'afficher en moins de 3 secondes en 4G standard.
*   **NFR2 :** La génération du PDF "Liste à acheter" (Bulk Action) doit prendre moins de 5 secondes pour une sélection de 50 commandes.

**Sécurité & Données**
*   **NFR3 :** L'accès au dashboard doit être protégé par une authentification standard Laravel/Filament.
*   **NFR4 :** Les clés API Shopify doivent être stockées uniquement dans les variables d'environnement (.env).
*   **NFR5 :** Mise en place d'une sauvegarde automatique quotidienne de la base de données.

**Fiabilité (Intégrité Financière)**
*   **NFR6 :** Le système doit garantir l'atomicité des transactions financières pour éviter toute corruption des soldes en cas d'erreur système.

### Additional Requirements

**Architecture - Initialisation**
*   **Starter Template :** Laravel 11 + Filament v3
*   **Commande Initiale :** `laravel new aramis-gestion --git --database=mysql --phpunit && composer require filament/filament:"^3.2" -W && php artisan filament:install --panels`

**Architecture - Data**
*   **Modèle Transactionnel :** Utilisation d'une table `financial_transactions` immuable.
*   **Types de Données :** Montants stockés en `DECIMAL(15, 2)` ou `BIGINT`.

**Architecture - Structure & Services**
*   **Service Layer :** `TreasuryEngine` pour la logique financière, `ShopifyService` pour l'API.
*   **Filament :** Utilisation de `GenerateRestockPdfAction` pour la logique PDF.

### FR Coverage Map

*   **FR1 (Sync Shopify) :** Epic 2 - Intégration Shopify
*   **FR2 (Import Confirmé) :** Epic 2 - Intégration Shopify
*   **FR3 (Images/Noms) :** Epic 2 - Intégration Shopify
*   **FR4 (Liste Commandes) :** Epic 2 - Intégration Shopify
*   **FR5 (Bulk Select) :** Epic 3 - Logistique Terrain (PDF)
*   **FR6 (Générer PDF) :** Epic 3 - Logistique Terrain (PDF)
*   **FR7 (Contenu PDF) :** Epic 3 - Logistique Terrain (PDF)
*   **FR8 (CRUD Fournisseurs) :** Epic 1 - Initialisation & Socle Financier
*   **FR9 (Ajout Dette) :** Epic 1 - Initialisation & Socle Financier
*   **FR10 (Paiement Fournisseur) :** Epic 1 - Initialisation & Socle Financier
*   **FR11 (Solde Fournisseur) :** Epic 1 - Initialisation & Socle Financier
*   **FR12 (Solde Livreur) :** Epic 1 - Initialisation & Socle Financier
*   **FR13 (Relevé Livreur) :** Epic 1 - Initialisation & Socle Financier
*   **FR14 (Entrée Caisse) :** Epic 1 - Initialisation & Socle Financier
*   **FR15 (Frais) :** Epic 1 - Initialisation & Socle Financier
*   **FR16 (Validation Soldes) :** Epic 1 - Initialisation & Socle Financier
*   **FR17 (Dashboard KPIs) :** Epic 4 - Tableau de Bord & Pilotage
*   **FR18 (Historique Transac) :** Epic 4 - Tableau de Bord & Pilotage

## Epic List

### Epic 1: Initialisation & Socle Financier
Mise en place de l'architecture Laravel/Filament et du moteur financier (TreasuryEngine). Permet la gestion complète des flux d'argent (Dettes, Caisse, Livreur) de manière manuelle mais sécurisée.
**FRs covered:** FR8, FR9, FR10, FR11, FR12, FR13, FR14, FR15, FR16

### Story 1.1: Initialisation du Projet Laravel & Filament

As a Developer,
I want to initialize the Laravel project with Filament v3 and MySQL,
So that I have a secure foundation for the application.

**Acceptance Criteria:**

**Given** A fresh VPS or local environment with PHP 8.2+ and Composer
**When** I run the installation command defined in Architecture
**Then** A new Laravel 11 project is created
**And** Filament v3 is installed and the Admin Panel is accessible at `/admin`
**And** The database connection is configured in `.env`
**And** The default User model allows login

### Story 1.2: Gestion CRUD des Fournisseurs

As a Gestionnaire,
I want to create, edit, and list my suppliers,
So that I can identify who I owe money to.

**Acceptance Criteria:**

**Given** I am logged into the Dashboard
**When** I navigate to the "Fournisseurs" resource
**Then** I can see a list of existing suppliers
**And** I can create a new supplier with a Name and Contact Info
**And** I cannot delete a supplier if they have associated financial transactions

### Story 1.3: Architecture Financière (Transactions Immuables)

As a Developer,
I want to implement the `financial_transactions` table and `TreasuryEngine` service,
So that all financial movements are recorded securely and immutably.

**Acceptance Criteria:**

**Given** The database schema
**When** I run the migration
**Then** The `financial_transactions` table exists with columns: type, amount, source_id, destination_id, description
**And** The `TreasuryEngine` service class is created
**And** Unit tests verify that `TreasuryEngine` can record a simple transaction

### Story 1.4: Gestion des Dettes (Ajout Manuel)

As a Gestionnaire,
I want to manually record a new debt for a supplier,
So that I can track what I owe after a purchase.

**Acceptance Criteria:**

**Given** I am on a Supplier's view
**When** I click "Ajouter Dette"
**Then** A modal opens asking for Amount and Description
**When** I confirm
**Then** A transaction is created via `TreasuryEngine`
**And** The Supplier's displayed balance updates to reflect the increased debt

### Story 1.5: Gestion de la Caisse (Frais & Entrées)

As a Gestionnaire,
I want to record expenses (like USD purchase) and cash inflows (like Salary),
So that my physical cashbox matches the system balance.

**Acceptance Criteria:**

**Given** I am on the Dashboard
**When** I access the "Journal de Caisse" (ExpenseResource)
**Then** I can add a "Sortie" (Expense) specifying type (e.g., Achat USD) and amount
**And** I can add an "Entrée" (Income) specifying source (e.g., Apport Perso)
**And** The calculated "Cash on Hand" balance updates immediately

### Story 1.6: Paiement Fournisseurs

As a Gestionnaire,
I want to register a payment to a supplier,
So that my debt decreases and my cash balance reflects the payment.

**Acceptance Criteria:**

**Given** I have a positive Cash balance
**When** I click "Payer" on a Supplier
**Then** I can enter an amount up to my current Cash balance
**And** The transaction decreases my Cash and decreases the Supplier's debt
**Given** I try to pay more than my available Cash
**Then** The system blocks the action with a "Solde insuffisant" notification (FR16)

### Story 1.7: Gestion Société de Livraison (Relevés)

As a Gestionnaire,
I want to track money held by the delivery company and record withdrawals (relevés),
So that I know where my money is.

**Acceptance Criteria:**

**Given** The "Société de Livraison" has a positive balance
**When** I record a "Relevé"
**Then** The amount moves from "Société" to "Caisse"
**And** The transaction history shows this transfer

### Epic 2: Intégration Shopify
Connexion à l'API Shopify pour automatiser la récupération des commandes confirmées. Permet de visualiser les commandes en attente de traitement directement dans le dashboard.
**FRs covered:** FR1, FR2, FR3, FR4

### Story 2.1: Service d'Intégration Shopify

As a Developer,
I want to implement a secure `ShopifyService`,
So that the application can communicate with the Shopify REST API.

**Acceptance Criteria:**

**Given** Valid Shopify API credentials in `.env`
**When** I call the `ShopifyService`, it successfully authenticates
**Then** I can fetch a raw list of orders from the Shopify store
**And** API errors are logged and handled without crashing the app

### Story 2.2: Modélisation des Commandes (Order)

As a Developer,
I want to create the `orders` table and Model,
So that I can store synchronized order data locally.

**Acceptance Criteria:**

**Given** The database schema
**When** I run the migration
**Then** The `orders` table exists with columns: shopify_id, customer_name, total_price, status, items (JSON), and order_date
**And** The `Order` model is associated with this table

### Story 2.3: Job de Synchronisation (Confirmé Only)

As a Developer,
I want to create a background Job to sync orders,
So that only "Confirmed" orders are imported with their product images.

**Acceptance Criteria:**

**Given** Orders exist on Shopify with various statuses
**When** the `SyncShopifyOrders` job runs
**Then** Only orders with status "Confirmed" are saved to the local database
**And** For each line item, the product name and CDN image URL are captured (FR3)
**And** Existing orders are updated instead of duplicated

### Story 2.4: Interface de Gestion des Commandes Filament

As a Gestionnaire,
I want to see my Shopify orders in the Dashboard and trigger a sync,
So that I can prepare my workflow.

**Acceptance Criteria:**

**Given** I am on the "Commandes" page in Filament
**When** I view the list
**Then** I see the order number, customer, date, and product images (FR4)
**And** A "Synchroniser Shopify" button is available in the top bar (FR1)
**When** I click the button, the sync job is triggered and a success notification is shown

### Epic 3: Logistique Terrain (PDF)
Création des outils de logistique. Permet de sélectionner plusieurs commandes et de générer un PDF optimisé pour l'achat sur le terrain (avec photos).
**FRs covered:** FR5, FR6, FR7

### Story 3.1: Template de Liste d'Achat (Groupé par Commande)

As a Developer,
I want to create a Blade template for the PDF,
So that products are displayed clearly and grouped by their parent order.

**Acceptance Criteria:**

**Given** A list of selected orders
**When** The template is rendered
**Then** Products are grouped under their respective Order Number (e.g., #1001 - Client Name) (FR7)
**And** Each product entry displays its photo (CDN URL), name, and quantity
**And** Each order group is visually separated from the next for easy reading

### Story 3.2: Action Bulk "Générer PDF de Stock"

As a Gestionnaire,
I want to select multiple orders and click a button,
So that I can download the preparation list.

**Acceptance Criteria:**

**Given** I am on the "Commandes" list in Filament
**When** I select one or more rows
**Then** A Bulk Action "Générer PDF de Stock" becomes available
**When** I click the action
**Then** The system collects the order data and triggers the PDF generation (FR5, FR6)

### Story 3.3: Service de Génération PDF (Performance)

As a Developer,
I want to implement the PDF conversion service,
So that the HTML template is transformed into a downloadable file quickly.

**Acceptance Criteria:**

**Given** 50 selected orders
**When** The PDF generation is triggered
**Then** The file is generated and download starts in less than 5 seconds (NFR2)
**And** The generated PDF preserves the layout and images (CDN images are correctly rendered)

### Epic 4: Tableau de Bord & Pilotage
Finalisation de l'interface de pilotage. Permet d'avoir une vue synthétique des KPIs financiers et un historique d'audit complet.
**FRs covered:** FR17, FR18

### Story 4.1: Widgets KPIs (Dashboard Central)

As a Gestionnaire,
I want to see my key financial balances on the main dashboard,
So that I can make quick decisions without navigating.

**Acceptance Criteria:**

**Given** I am logged into the Dashboard
**When** I view the homepage
**Then** I see three distinct Stats Widgets: "Cash en main", "Dettes Fournisseurs", and "Argent chez Livreur" (FR17)
**And** The values are calculated in real-time from the `financial_transactions` table
**And** The values are formatted correctly in local currency

### Story 4.2: Journal des Transactions Financières (Audit Trail)

As a Gestionnaire,
I want to see a full list of all financial movements,
So that I can audit errors and track my history.

**Acceptance Criteria:**

**Given** I am on the "Journal des Transactions" page
**When** I view the table
**Then** I see all transactions (Incomes, Expenses, Debt additions, Payments) in chronological order (FR18)
**And** The table is Read-Only (transactions cannot be edited or deleted to preserve audit integrity)
**And** I can filter by transaction type or date

### Story 4.3: Sécurité, Backup & Validation d'Intégrité

As a Developer,
I want to finalize the security and data integrity measures,
So that the application is production-ready.

**Acceptance Criteria:**

**Given** The application is deployed
**When** An unauthorized user tries to access `/admin`
**Then** They are redirected to the login page (NFR3)
**And** A daily database backup task is configured (NFR5)
**And** Automated tests verify that multi-account transfers (e.g., Delivery -> Cash) are atomic (NFR6)