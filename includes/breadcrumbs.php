<?php
/**
 * Breadcrumbs Component
 * Practical 2 Intermediate Extension: Breadcrumb Navigation
 */

function render_breadcrumbs($crumbs = []) {
    if (empty($crumbs)) {
        return;
    }
    echo '<nav aria-label="Breadcrumb" class="breadcrumbs">';
    echo '<div class="container">';
    $total = count($crumbs);
    $i = 0;
    foreach ($crumbs as $title => $url) {
        $i++;
        if ($url && $i < $total) {
            echo '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($title) . '</a> &gt; ';
        } else {
            echo '<span aria-current="page">' . htmlspecialchars($title) . '</span>';
        }
    }
    echo '</div>';
    echo '</nav>';
}

