stepsCompleted: [1, 2, 3, 4, 5, 6, 7, 8]
inputDocuments: ['_bmad-output/planning-artifacts/prd.md']
workflowType: 'architecture'
project_name: 'aramis-gestion'
user_name: 'kybyto'
date: '2026-01-28'
lastStep: 8
status: 'complete'
completedAt: '2026-01-28'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis
...
## Starter Template Evaluation
...
## Core Architectural Decisions
...
## Implementation Patterns & Consistency Rules

### Naming Patterns

**Database Naming Conventions:**
*   Tables : `snake_case` pluriel (ex: `financial_transactions`, `suppliers`).
*   Colonnes : `snake_case` (ex: `amount_dzd`, `is_paid`).
*   Foreign Keys : `singular_id` (ex: `supplier_id`).

**Code Naming Conventions:**
*   Classes : `PascalCase` (Standard PSR-4).
*   Méthodes/Variables : `camelCase`.
*   Filament Resources : `ModelNameResource`.

### Structure Patterns

**Logic Placement:**
*   **INTERDICTION** d'écrire des calculs financiers dans les Models, Controllers ou Vues.
*   **TreasuryEngine :** `app/Services/TreasuryEngine.php` centralise toutes les opérations de solde (addDebt, paySupplier, collectMoney).
*   **Shopify Integration :** `app/Services/ShopifyService.php` isole les appels API.

**Filament Actions:**
*   Les logiques complexes (comme la génération PDF Bulk) doivent être encapsulées dans des classes `Action` dédiées dans `app/Filament/Actions` plutôt que dans des closures anonymes.

### Format Patterns

**Data Formats:**
*   **Monnaie :** Stockage strict en `DECIMAL(15, 2)` ou `BIGINT` pour éviter les erreurs d'arrondi flottant.
*   **Dates :** Stockage UTC en base, affichage localisé via les helpers Filament.

**Error Handling:**
*   Exceptions métiers (ex: `InsufficientFundsException`) doivent être catchées et transformées en `Filament\Notifications` conviviales pour l'utilisateur.

### Enforcement Guidelines

**All AI Agents MUST:**
1.  Passer par `TreasuryEngine` pour toute modification de solde.
2.  Utiliser les migrations Laravel standard pour les changements DB.
3.  Ne jamais hardcoder de clés API.

## Project Structure & Boundaries

### Complete Project Directory Structure

```text
aramis-gestion/
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── OrderResource.php        # Commandes Shopify sync
│   │   │   ├── SupplierResource.php     # Fournisseurs & Dettes
│   │   │   ├── TransactionResource.php   # Journal financier (Read-Only)
│   │   │   └── ExpenseResource.php       # Frais journaliers & USD
│   │   ├── Actions/
│   │   │   └── Order/
│   │   │       └── GenerateRestockPdf.php # Logique Bulk PDF
│   │   └── Widgets/
│   │       └── StatsOverview.php         # Widgets Dashboard
│   ├── Models/
│   │   ├── Order.php
│   │   ├── Supplier.php
│   │   └── FinancialTransaction.php      # Table Pivot Finance
│   ├── Services/
│   │   ├── TreasuryEngine.php            # Moteur de calcul (Unique source of truth)
│   │   └── ShopifyService.php            # Client API
│   └── Jobs/
│       └── SyncShopifyOrders.php         # Tâches asynchrones
├── database/
│   └── migrations/                       # Schéma DB
├── resources/
│   └── views/
│       └── pdf/
│           └── restock-list.blade.php    # Template PDF
└── .env                                  # Configuration secrets
```

### Architectural Boundaries

**API Boundaries:**
L'intégration Shopify est isolée dans `ShopifyService`. Aucun autre composant ne doit manipuler les payloads bruts de Shopify.

**Service Boundaries:**
Le `TreasuryEngine` est la seule interface autorisée pour modifier les soldes. Filament (UI) communique avec le moteur via des méthodes explicites.

**Data Boundaries:**
La table `financial_transactions` est immuable. Toute erreur doit être corrigée par une transaction d'ajustement (audit trail complet).

### Requirements to Structure Mapping

*   **Gestion Commandes :** `OrderResource` + `ShopifyService`.
*   **Logistique :** `GenerateRestockPdf` (Filament Action) + `restock-list.blade.php`.
*   **Finance (Cycle de l'argent) :** `TreasuryEngine` + `FinancialTransaction`.
*   **Dashboard :** `StatsOverview` widget.

## Architecture Validation Results

### Coherence Validation ✅
Les décisions sont cohérentes : l'UI Filament consomme les services Laravel standard (TreasuryEngine, ShopifyService), garantissant une séparation nette entre présentation et logique financière critique. L'approche "Transaction-First" assure l'intégrité des données exigée.

### Requirements Coverage Validation ✅
100% des exigences fonctionnelles du PRD sont mappées à des composants structurels (Services, Jobs, Actions). Les exigences non-fonctionnelles (Performance mobile, Sécurité) sont adressées par les choix technologiques (Laravel 11, Filament v3, typage strict PHP 8.2+).

### Implementation Readiness Validation ✅
L'architecture est validée pour l'implémentation. La structure des dossiers est définie, les conventions de nommage sont fixées, et les frontières entre les services sont claires.

### Architecture Completeness Checklist

**✅ Requirements Analysis**
- [x] Project context thoroughly analyzed
- [x] Scale and complexity assessed
- [x] Technical constraints identified
- [x] Cross-cutting concerns mapped

**✅ Architectural Decisions**
- [x] Critical decisions documented with versions
- [x] Technology stack fully specified
- [x] Integration patterns defined
- [x] Performance considerations addressed

**✅ Implementation Patterns**
- [x] Naming conventions established
- [x] Structure patterns defined
- [x] Communication patterns specified
- [x] Process patterns documented

**✅ Project Structure**
- [x] Complete directory structure defined
- [x] Component boundaries established
- [x] Integration points mapped
- [x] Requirements to structure mapping complete

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION
**Confidence Level:** HIGH

**Key Strengths:**
*   Séparation stricte de la logique financière via le `TreasuryEngine`.
*   Audit trail complet grâce au pattern de transactions immuables.
*   Utilisation optimale des standards Filament pour une interface "Lean".

**AI Agent Guidelines:**
*   Respecter scrupuleusement les frontières entre `TreasuryEngine` et les Resources Filament.
*   Utiliser systématiquement les transactions SQL pour les opérations multi-comptes.
*   Se référer à `app/Services/ShopifyService.php` pour toute interaction externe.

**First Implementation Priority:**
Initialisation du projet via la commande : `laravel new aramis-gestion --git --database=mysql --phpunit`
workflowType: 'architecture'
project_name: 'aramis-gestion'
user_name: 'kybyto'
date: '2026-01-28'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis
...
## Starter Template Evaluation
...
## Core Architectural Decisions
...
## Implementation Patterns & Consistency Rules

### Naming Patterns

**Database Naming Conventions:**
*   Tables : `snake_case` pluriel (ex: `financial_transactions`, `suppliers`).
*   Colonnes : `snake_case` (ex: `amount_dzd`, `is_paid`).
*   Foreign Keys : `singular_id` (ex: `supplier_id`).

**Code Naming Conventions:**
*   Classes : `PascalCase` (Standard PSR-4).
*   Méthodes/Variables : `camelCase`.
*   Filament Resources : `ModelNameResource`.

### Structure Patterns

**Logic Placement:**
*   **INTERDICTION** d'écrire des calculs financiers dans les Models, Controllers ou Vues.
*   **TreasuryEngine :** `app/Services/TreasuryEngine.php` centralise toutes les opérations de solde (addDebt, paySupplier, collectMoney).
*   **Shopify Integration :** `app/Services/ShopifyService.php` isole les appels API.

**Filament Actions:**
*   Les logiques complexes (comme la génération PDF Bulk) doivent être encapsulées dans des classes `Action` dédiées dans `app/Filament/Actions` plutôt que dans des closures anonymes.

### Format Patterns

**Data Formats:**
*   **Monnaie :** Stockage strict en `DECIMAL(15, 2)` ou `BIGINT` pour éviter les erreurs d'arrondi flottant.
*   **Dates :** Stockage UTC en base, affichage localisé via les helpers Filament.

**Error Handling:**
*   Exceptions métiers (ex: `InsufficientFundsException`) doivent être catchées et transformées en `Filament\Notifications` conviviales pour l'utilisateur.

### Enforcement Guidelines

**All AI Agents MUST:**
1.  Passer par `TreasuryEngine` pour toute modification de solde.
2.  Utiliser les migrations Laravel standard pour les changements DB.
3.  Ne jamais hardcoder de clés API.

## Project Structure & Boundaries

### Complete Project Directory Structure

```text
aramis-gestion/
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── OrderResource.php        # Commandes Shopify sync
│   │   │   ├── SupplierResource.php     # Fournisseurs & Dettes
│   │   │   ├── TransactionResource.php   # Journal financier (Read-Only)
│   │   │   └── ExpenseResource.php       # Frais journaliers & USD
│   │   ├── Actions/
│   │   │   └── Order/
│   │   │       └── GenerateRestockPdf.php # Logique Bulk PDF
│   │   └── Widgets/
│   │       └── StatsOverview.php         # Widgets Dashboard
│   ├── Models/
│   │   ├── Order.php
│   │   ├── Supplier.php
│   │   └── FinancialTransaction.php      # Table Pivot Finance
│   ├── Services/
│   │   ├── TreasuryEngine.php            # Moteur de calcul (Unique source of truth)
│   │   └── ShopifyService.php            # Client API
│   └── Jobs/
│       └── SyncShopifyOrders.php         # Tâches asynchrones
├── database/
│   └── migrations/                       # Schéma DB
├── resources/
│   └── views/
│       └── pdf/
│           └── restock-list.blade.php    # Template PDF
└── .env                                  # Configuration secrets
```

### Architectural Boundaries

**API Boundaries:**
L'intégration Shopify est isolée dans `ShopifyService`. Aucun autre composant ne doit manipuler les payloads bruts de Shopify.

**Service Boundaries:**
Le `TreasuryEngine` est la seule interface autorisée pour modifier les soldes. Filament (UI) communique avec le moteur via des méthodes explicites.

**Data Boundaries:**
La table `financial_transactions` est immuable. Toute erreur doit être corrigée par une transaction d'ajustement (audit trail complet).

### Requirements to Structure Mapping

*   **Gestion Commandes :** `OrderResource` + `ShopifyService`.
*   **Logistique :** `GenerateRestockPdf` (Filament Action) + `restock-list.blade.php`.
*   **Finance (Cycle de l'argent) :** `TreasuryEngine` + `FinancialTransaction`.
*   **Dashboard :** `StatsOverview` widget.

