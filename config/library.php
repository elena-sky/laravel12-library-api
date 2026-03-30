<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Book list cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | Only GET /api/v1/books list responses use this TTL; the catalog version
    | key is stored without expiry.
    |
    */
    'book_list_cache_ttl' => (int) env('BOOK_LIST_CACHE_TTL', 300),
];
