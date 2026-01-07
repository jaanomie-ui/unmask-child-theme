<?php
/**
 * ISO Filters Component
 *
 * Multi-row filter bar for ISO Board.
 * Row 1: Type (all, seeking, offering)
 * Row 2: Category (photographer, model, collaborator, connection)
 * Row 3: Factory toggle
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="iso-filter-bar" id="isoFilterBar">
    <!-- Row 1: Type filters -->
    <div class="iso-filter-row">
        <button type="button" class="iso-filter-btn active" data-filter="type" data-value="">all</button>
        <button type="button" class="iso-filter-btn" data-filter="type" data-value="seeking">seeking</button>
        <button type="button" class="iso-filter-btn" data-filter="type" data-value="offering">offering</button>
    </div>

    <!-- Row 2: Category filters -->
    <div class="iso-filter-row">
        <button type="button" class="iso-filter-btn" data-filter="category" data-value="photographer">photographer</button>
        <button type="button" class="iso-filter-btn" data-filter="category" data-value="model">model</button>
        <button type="button" class="iso-filter-btn" data-filter="category" data-value="collaborator">collaborator</button>
        <button type="button" class="iso-filter-btn" data-filter="category" data-value="connection">connection</button>
    </div>

    <!-- Row 3: Factory toggle -->
    <div class="iso-filter-row">
        <button type="button" class="iso-filter-btn iso-filter-factory active" data-filter="factory" data-value="yes">hosting at the factory</button>
    </div>
</div>
