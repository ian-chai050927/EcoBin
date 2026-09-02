<?php

namespace EcoBin\Services;

/**
 * Small render helpers shared across views. Keeps "what color is this
 * status" and "what does a stat card look like" defined in exactly one
 * place instead of re-implemented per page.
 */
final class View
{
    /**
     * Status string -> .eco-status-* class, per the map documented in
     * ecobin.css. Anything not recognised falls back to the neutral
     * "cancelled" style rather than silently rendering unstyled.
     */
    private const STATUS_CLASSES = [
        // Collection lifecycle
        'Pending'     => 'eco-status-pending',
        'Assigned'    => 'eco-status-assigned',
        'In Progress' => 'eco-status-progress',
        'Completed'   => 'eco-status-completed',
        'Cancelled'   => 'eco-status-cancelled',

        // User account
        'Active'      => 'eco-status-completed',
        'Suspended'   => 'eco-status-rejected',

        // Recycling submissions
        'Approved'    => 'eco-status-completed',
        'Rejected'    => 'eco-status-rejected',

        // Appointments
        'Confirmed'   => 'eco-status-assigned',

        // Recycling centre availability
        'Open'        => 'eco-status-completed',
        'Full'        => 'eco-status-pending',
        'Closed'      => 'eco-status-cancelled',
    ];

    /**
     * Renders `<span class="eco-status eco-status-x">Status</span>`.
     * Use this everywhere a status is shown — do not re-derive the
     * color with an if/else or ternary chain in a view.
     */
    public static function statusBadge(string $status): string
    {
        $class = self::STATUS_CLASSES[$status] ?? 'eco-status-cancelled';

        return '<span class="eco-status ' . $class . '">'
            . Security::e($status)
            . '</span>';
    }

    /**
     * Renders one .eco-stat-card. $icon is a Bootstrap Icons class,
     * e.g. "bi-truck". Pass null to omit the icon block.
     */
    public static function statCard(string $label, string $value, ?string $icon = null): string
    {
        $iconHtml = $icon !== null
            ? '<div class="eco-stat-icon"><i class="bi ' . Security::e($icon) . '"></i></div>'
            : '';

        return '<div class="eco-stat-card">'
            . $iconHtml
            . '<div class="eco-stat-label">' . Security::e($label) . '</div>'
            . '<div class="eco-stat-number">' . Security::e($value) . '</div>'
            . '</div>';
    }
}