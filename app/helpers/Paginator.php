<?php
// FILE: /app/helpers/Paginator.php

class Paginator {
    protected $totalItems;
    protected $itemsPerPage;
    protected $currentPage;
    protected $totalPages;

    public function __construct($totalItems, $itemsPerPage = ITEMS_PER_PAGE, $currentPage = 1) {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = ceil($totalItems / $itemsPerPage);
    }

    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function getLimit() {
        return $this->itemsPerPage;
    }

    public function getTotalPages() {
        return $this->totalPages;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function hasNextPage() {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPreviousPage() {
        return $this->currentPage > 1;
    }

    public function render($baseUrl) {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav><ul class="pagination">';

        // Previous button
        if ($this->hasPreviousPage()) {
            $prevPage = $this->currentPage - 1;
            $html .= '<li><a href="' . $baseUrl . '?page=' . $prevPage . '">Previous</a></li>';
        }

        // Page numbers
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);

        if ($start > 1) {
            $html .= '<li><a href="' . $baseUrl . '?page=1">1</a></li>';
            if ($start > 2) {
                $html .= '<li>...</li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $this->currentPage ? ' class="active"' : '';
            $html .= '<li' . $active . '><a href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }

        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $html .= '<li>...</li>';
            }
            $html .= '<li><a href="' . $baseUrl . '?page=' . $this->totalPages . '">' . $this->totalPages . '</a></li>';
        }

        // Next button
        if ($this->hasNextPage()) {
            $nextPage = $this->currentPage + 1;
            $html .= '<li><a href="' . $baseUrl . '?page=' . $nextPage . '">Next</a></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }
}
