<?php

return [
    /*
    |----------------------------------------------------------------------
    | Allowlist optional
    |----------------------------------------------------------------------
    | Wenn enabled = false, sind alle Komponenten erlaubt (wirklich "alle").
    | Wenn enabled = true, dann wird anhand von allowed_names ODER
    | allowed_class_prefixes gefiltert (ODER-Logik).
    |
    | Beispiele:
    |  - Nur Winter.Blog:
    |    'enabled' => true,
    |    'allowed_class_prefixes' => ['Winter\\Blog\\Components\\'],
    |
    |  - Nur bestimmte Aliases:
    |    'enabled' => true,
    |    'allowed_names' => ['blogPosts', 'blogPost', 'blogCategories'],
    */
    'allowlist' => [
        'enabled' => false,
        'allowed_names' => [],
        'allowed_class_prefixes' => [],
    ],

    /*
    |----------------------------------------------------------------------
    | Optional: Nur innerhalb Static Pages Kontext ausführen
    |----------------------------------------------------------------------
    | Wenn true, rendert dyncomponent nur wenn die Seite die Komponente
    | "staticPage" enthält (RainLab.Pages / Winter.Pages).
    */
    'static_pages_only' => false,

    /*
    |----------------------------------------------------------------------
    | Optional: Fehlerausgabe
    |----------------------------------------------------------------------
    | Wenn true, wird bei unbekannter/gesperrter Komponente ein HTML-Hinweis
    | gerendert (hilfreich beim Debuggen).
    */
    'fail_loud' => true,
];
