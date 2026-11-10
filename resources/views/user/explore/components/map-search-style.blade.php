<style>
    .filter-card.active { background-color: color-mix(in srgb, var(--color-primary) 8%, white); }
    .filter-card:not(.active) > span:first-child { opacity: .55; }
    .filter-check { display: flex; width: 1.25rem; height: 1.25rem; flex: none; align-items: center; justify-content: center; border: 1.5px solid var(--color-primary); border-radius: .375rem; background: var(--color-primary); color: white; font-size: .875rem; line-height: 1; }
    .filter-card:not(.active) .filter-check { background: white; color: transparent; }
</style>
