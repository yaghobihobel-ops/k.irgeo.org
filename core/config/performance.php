<?php

return [
    // تغییر (بهینه‌سازی سرعت): پیکربندی TTL کش برای بخش‌های مختلف برنامه.
    'cache_ttl' => [
        'page' => 300,
        'blog_list' => 300,
        'blog' => 600,
        'blog_latest' => 600,
        'frontend' => 600,
        'policy' => 600,
        'placeholder_image' => 3600,
        'language' => 1440,
    ],
];
