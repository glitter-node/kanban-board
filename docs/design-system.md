# Design System

## Core Principles

- UI is token-driven. Visual values come from `resources/css/tokens.css`.
- Structure lives in Tailwind utility layout classes and shared Blade components.
- Application UI must go through Blade UI components. Do not style screens ad hoc.
- Livewire and Alpine handle state and behavior only. UI rendering stays in Blade components.
- Raw Tailwind color classes are not allowed in app views.

## Token System

The token source of truth is `resources/css/tokens.css`.

### Base Surface Tokens

- `--background`
  Use for the app shell and full-page background.
- `--surface`
  Use for standard containers and primary content surfaces.
- `--surface-elevated`
  Use for higher-emphasis panels and raised blocks.

### Text Tokens

- `--text-primary`
  Use for headings, body text, and high-emphasis content.
- `--text-secondary`
  Use for supporting copy, metadata, labels, and helper text.
- `--text-muted`
  Reserved for lower-emphasis cases only when secondary text is still too strong.

### Structural Tokens

- `--border`
  Use for all borders and separators.

### Action and State Tokens

- `--primary`
  Primary call to action background and emphasis color.
- `--accent`
  Positive or accent state color.
- `--danger`
  Destructive action color.

## Depth System

Depth tokens are defined in `resources/css/tokens.css` and consumed in `resources/css/app.css`.

### Shadows

- `--shadow-sm`
  Subtle container lift.
- `--shadow-md`
  Elevated surface depth.
- `--shadow-lg`
  Primary card depth and major emphasis blocks.

### Glow

- `--glow-primary`
  Only for primary interactive emphasis.
- `--glow-accent`
  Only for accent emphasis when explicitly needed.

Glow is reserved for primary actions and featured emphasis blocks. Do not apply glow to generic containers.

### Gradient Layers

- `--gradient-surface`
  Surface layering and panel richness.
- `--gradient-page`
  Page-level atmospheric depth.

Gradient layers are for sections and shared primitives only. Do not create per-view custom gradients.

## Component Rules

Use the shared Blade UI layer as the only UI API.

### Containers

- `x-ui.section`
  Page and section wrapper with the shared gradient depth plane.
- `x-ui.surface`
  Standard surface container.
- `x-ui.surface variant="elevated"`
  Raised surface container.
- `x-ui.card`
  All card blocks and structured content panels.
- `x-ui.panel`
  Modal bodies, dropdown panels, and structured overlays.

### Actions

- `x-ui.button variant="primary"`
  All primary actions.
- `x-ui.button variant="secondary"`
  Secondary and neutral actions.
- `x-ui.button variant="danger"`
  Destructive actions.
- `x-ui.button variant="icon"`
  Icon-only controls.

### Form and Micro Components

- `x-ui.input`
  Shared form control styling.
- `x-ui.badge`
  All badges, chips, and count labels.
- `x-ui.modal`
  Modal shell and overlay behavior.

## Forbidden Patterns

The following are not allowed in Blade UI:

- `bg-zinc-*`
- `bg-gray-*`
- `bg-white`
- `bg-black`
- `text-white`
- `text-gray-*`
- `text-black`
- `border-zinc-*`
- `border-gray-*`
- `dark:*`
- direct `shadow-*` utilities for UI styling
- inline `style` attributes for visual presentation

If a view needs a new visual pattern, add or extend a shared UI component instead of styling the view directly.

## Enforcement

Run:

```bash
npm run ui:check
```

This executes `scripts/check-ui-consistency.sh` and fails when forbidden patterns are found in Blade files.

## CI Integration

The repository does not currently include a CI pipeline definition. When CI is added, `npm run ui:check` must run as part of the frontend validation step on push and pull request builds.
