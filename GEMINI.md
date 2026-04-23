# GEMINI.md - Project Documentation

## Overview
The "Multi-Marketing Multinivel" plugin is a premium WordPress-based Learning Management System (LMS) designed for Elite member areas. It integrates Multi-level Marketing (MLM) logic, WooCommerce membership management, and real-time subscription synchronization via the ASAAS API.

## Project Structure
- `multinivel_marketing/`: Base plugin layer handling MLM logic, WooCommerce role-based discounts, and basic post types (`mlm_course`).
- `expressive-core/`: Core LMS engine. Handles "Elite" UI/UX, templates (Tailwind CSS), certificates, gamification, and member dashboards.
- `public/function/`: Contains external API synchronization logic (TypeScript/Deno) for managing member status via the ASAAS gateway.

## Development Standards
- **Backend:** WordPress Hook-based architecture following the Loader pattern.
- **Frontend:** Tailwind CSS (via CDN) utilized within `expressive-core/templates/` for a modern, responsive UI.
- **Naming Conventions:** 
    - Internal classes: `Multinivel_Marketing_...` or `Expressive_...`.
    - Post types: `mlm_` (legacy/base) vs `lms_` (core/current).
- **API Logic:** All external integrations (e.g., ASAAS) must be managed through `expressive-core` controllers to ensure consistency.

## Maintenance Alerts
- **Duplicate/Overlapping CPTs:** The plugin currently maintains two sets of CPTs (`mlm_` vs `lms_`). When adding or modifying course/lesson features, ensure changes are verified in both `includes/class-multinivel_marketing.php` and `expressive-core/includes/class-expressive-cpt.php`.
- **Security:** Ensure `public/function/` and `expressive-core/logs/` are protected from direct access via `.htaccess` to prevent exposure of source code, API logic, or logs.
- **Deployment:** When updating features, check both base plugin and core module hooks.

## Guidelines for AI Assistance
- **Always verify dual-structure:** Before changing course or member management logic, check if the change should propagate to both the `multinivel_marketing` base and the `expressive-core` engine.
- **Maintain Prefix Integrity:** Respect the existing `mlm_` and `lms_` nomenclature. Do not introduce new naming conventions unless refactoring an entire module.
- **Validation:** Every change involving API sync or member access must be accompanied by an update to the relevant test files if available.
