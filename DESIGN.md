---
version: alpha
name: TenantForge
description: Production-grade multi-tenant B2B SaaS starter kit powered by Laravel, Inertia, Vue 3, and Tailwind CSS.
colors:
  background: "hsl(0 0% 100%)"
  foreground: "hsl(0 0% 3.9%)"
  card: "hsl(0 0% 100%)"
  card-foreground: "hsl(0 0% 3.9%)"
  primary: "hsl(0 0% 9%)"
  primary-foreground: "hsl(0 0% 98%)"
  secondary: "hsl(0 0% 92.1%)"
  secondary-foreground: "hsl(0 0% 9%)"
  muted: "hsl(0 0% 96.1%)"
  muted-foreground: "hsl(0 0% 45.1%)"
  accent: "hsl(0 0% 96.1%)"
  accent-foreground: "hsl(0 0% 9%)"
  border: "hsl(0 0% 92.8%)"
  input: "hsl(0 0% 89.8%)"
  ring: "hsl(0 0% 3.9%)"
typography:
  sans:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
  mono:
    fontFamily: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace
rounded:
  base: 0.5rem
---

# Design System

## Overview

TenantForge employs a high-density, engineering-first aesthetic tailored for modern B2B SaaS platforms. The visual direction favors precision, crisp contrast, geometric card structures, subtle borders, and intentional typography.

## Colors

The interface strictly adheres to semantic color tokens defined in `resources/css/app.css`. Accent usage is restrained: primary brand accents are reserved for active interactive targets, status indicators, and high-priority call-to-actions.

## Themes

| Token | Light Value | Dark Value |
|---|---|---|
| `background` | `hsl(0 0% 100%)` | `hsl(0 0% 3.9%)` |
| `foreground` | `hsl(0 0% 3.9%)` | `hsl(0 0% 98%)` |
| `card` | `hsl(0 0% 100%)` | `hsl(0 0% 3.9%)` |
| `primary` | `hsl(0 0% 9%)` | `hsl(0 0% 98%)` |
| `secondary` | `hsl(0 0% 92.1%)` | `hsl(0 0% 14.9%)` |
| `muted` | `hsl(0 0% 96.1%)` | `hsl(0 0% 16.08%)` |
| `border` | `hsl(0 0% 92.8%)` | `hsl(0 0% 14.9%)` |

## Typography

- Headings must use `text-balance` to avoid awkward typographic rags or orphan words.
- Long-form descriptions and body paragraphs must use `text-pretty`.
- Numeric data, currency pricing, counters, and statistics must use `tabular-nums` to maintain horizontal stability during dynamic updates.
- Font family defaults to `Instrument Sans` for sans-serif text and monospace for code/configuration identifiers.

## Layout

- Use `min-h-dvh` or `h-dvh` for full viewport containers instead of `h-screen`.
- Square icons and status containers must use `size-*` instead of separate `w-*` and `h-*`.
- Maintain a strict z-index scale (avoid arbitrary `z-[...]`).
- Respect `safe-area-inset` on mobile navigation and sticky headers.

## Elevation & Depth

- Use subtle borders (`border-border` / `border-slate-800/80`) and standard Tailwind shadow tiers (`shadow-sm`, `shadow-md`).
- Never use colorful blur orbs or intense glow shadows as primary affordances.

## Components

- **Buttons & Links**: Interactive controls must have visible focus rings (`focus-visible:ring-2`) and accessible labels (`aria-label` for icon-only triggers).
- **Cards**: Structural content blocks use `rounded-xl` or `rounded-2xl` with a crisp outer border and subtle background separation.
- **Badges**: Use compact, high-contrast pills with status dots or subtle borders.

## Do's and Don'ts

- **Do** use `text-balance` for all headline text.
- **Do** use `tabular-nums` for prices, counters, and metrics.
- **Do** limit accent color to one primary brand shade per view.
- **Don't** use multi-color or purple gradients across headings or hero backgrounds.
- **Don't** use large blurry colored background orbs.
- **Don't** use arbitrary letter-spacing (`tracking-*`) unless specifically required.
- **Don't** mix primitive libraries or hand-craft accessible keyboard patterns.
