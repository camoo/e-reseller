# AGENTS.md

## Project purpose

This project is a reusable reseller and white-label application base. Multiple
resellers, customers, or tenants may use the same core implementation while
having different branding, visual styles, enabled features, integrations, and
business rules.

## Core development rules

- Target PHP 8.4 or newer. Use modern, typed PHP and keep code compatible with
  the project's declared Composer constraints.
- Prefer configuration over hard-coded values. This includes branding,
  colors, typography, layouts, navigation, feature flags, limits, providers,
  URLs, email content, and tenant-specific behavior.
- Build reusable extension points for enhancements: interfaces, adapters,
  events, strategies, service providers, configuration objects, and explicit
  module boundaries are preferred over edits to shared core logic.
- Keep the base product tenant- and reseller-agnostic. Do not embed a single
  customer's name, logo, domain, colors, copy, workflow, or assumptions in the
  core implementation.
- Separate domain behavior from presentation and branding. A change to one
  reseller's appearance must not require changing another reseller's style.
- Treat tenant/reseller configuration as isolated and validated input. Define
  sensible defaults, document required settings, and fail clearly when a
  required configuration is missing.
- Preserve backwards compatibility for existing integrations and configuration
  keys where practical. When a breaking change is necessary, document the
  migration path.

## White-label and theming

- Use design tokens, theme configuration, and overridable templates/components
  rather than scattered CSS values or inline brand-specific markup.
- Keep brand assets, stylesheets, translations, and templates in an explicit
  tenant/reseller/theme layer, not in shared domain code.
- Prefer namespaced or scoped styles to prevent one tenant's CSS from leaking
  into another tenant's UI.
- Make it possible to change branding and layout without modifying business
  logic. New themes should be additive and should not require forks of the
  core application.
- Do not assume all users have the same navigation, enabled modules, wording,
  or visual identity.

## Architecture and implementation

- Follow the existing project structure and dependency boundaries.
- Apply MVC strictly: controllers coordinate requests and responses, models
  own persistence and data access concerns, and views handle presentation only.
  Do not put business rules, database queries, or tenant-specific decisions in
  views; do not turn controllers into large service or domain classes.
- Introduce Domain-Driven Design gradually. Extract meaningful domain concepts,
  value objects, entities, domain services, application services, and bounded
  contexts as the code evolves, without forcing a broad rewrite of stable
  legacy code.
- Move progressively toward hexagonal architecture: keep domain and application
  logic independent from frameworks, databases, queues, and third-party
  providers; connect them through ports (interfaces) and infrastructure
  adapters. Use this approach first for new modules and high-change areas.
- Keep controllers and UI layers thin; put business rules in testable domain or
  application services.
- Depend on abstractions at integration boundaries. Encapsulate vendor-specific
  behavior behind adapters so providers can be replaced per reseller.
- Use dependency injection and configuration objects instead of global state,
  hidden constants, or environment checks spread through the codebase.
- Sanitize and validate external input, authorize tenant-scoped access, and
  avoid exposing one tenant's data to another.
- Add tests for new behavior, especially configuration overrides, tenant
  isolation, extension points, and default behavior.

## Change checklist

Before completing a change, verify:

1. It works with the default configuration.
2. A reseller or tenant can override the relevant behavior or style without
   editing shared core code.
3. Existing tenants and integrations retain their behavior unless the change
   intentionally alters it.
4. New configuration keys, extension points, and migrations are documented.
5. Relevant tests, static analysis, formatting, and PHP 8.4 compatibility checks
   pass.

When choosing between a quick one-off customization and a reusable mechanism,
choose the reusable mechanism unless the scope is explicitly tenant-specific.
