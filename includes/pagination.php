<?php
function generate_pagination($total_pages, $current_page, $base_url = '?')
{
    if ($total_pages <= 1) {
        return '';
    }

    $html = '<ul class="pagination justify-content-center">';
    $window = 2; // Show 2 links before and after the current page

    // First page link
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=1">First</a></li>';
    }

    // Previous page link
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=' . ($current_page - 1) . '">Previous</a></li>';
    }

    // Page numbers
    if ($current_page > $window + 1) {
        $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
    }

    for ($i = max(1, $current_page - $window); $i <= min($total_pages, $current_page + $window); $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . 'page=' . $i . '">' . $i . '</a></li>';
    }

    if ($current_page < $total_pages - $window) {
        $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
    }

    // Next page link
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=' . ($current_page + 1) . '">Next</a></li>';
    }

    // Last page link
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=' . $total_pages . '">Last</a></li>';
    }

    $html .= '</ul>';
    return $html;
}
?>