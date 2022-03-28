<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'IDG\\Publishing_Flow\\API\\Data\\Users' => $baseDir . '/inc/api/data/class-users.php',
    'IDG\\Publishing_Flow\\API\\Endpoints\\Author' => $baseDir . '/inc/api/endpoints/class-author.php',
    'IDG\\Publishing_Flow\\API\\Endpoints\\Post' => $baseDir . '/inc/api/endpoints/class-post.php',
    'IDG\\Publishing_Flow\\API\\Endpoints\\Taxonomy' => $baseDir . '/inc/api/endpoints/class-taxonomy.php',
    'IDG\\Publishing_Flow\\API\\Request' => $baseDir . '/inc/api/class-request.php',
    'IDG\\Publishing_Flow\\API\\Routes' => $baseDir . '/inc/api/class-routes.php',
    'IDG\\Publishing_Flow\\API\\Validator\\Post_ID_Exists' => $baseDir . '/inc/validator/class-post-id-exists.php',
    'IDG\\Publishing_Flow\\Auth' => $baseDir . '/inc/class-auth.php',
    'IDG\\Publishing_Flow\\Authors' => $baseDir . '/inc/class-authors.php',
    'IDG\\Publishing_Flow\\Cache' => $baseDir . '/inc/class-cache.php',
    'IDG\\Publishing_Flow\\Command\\Destroy' => $baseDir . '/inc/command/class-destroy.php',
    'IDG\\Publishing_Flow\\Command\\Publications' => $baseDir . '/inc/command/class-publications.php',
    'IDG\\Publishing_Flow\\Command\\Sync_Article' => $baseDir . '/inc/command/class-sync-article.php',
    'IDG\\Publishing_Flow\\Command\\Sync_Articles' => $baseDir . '/inc/command/class-sync-articles.php',
    'IDG\\Publishing_Flow\\Command\\Sync_Terms' => $baseDir . '/inc/command/class-sync-terms.php',
    'IDG\\Publishing_Flow\\Data\\Authors' => $baseDir . '/inc/data/class-authors.php',
    'IDG\\Publishing_Flow\\Data\\Content' => $baseDir . '/inc/data/class-content.php',
    'IDG\\Publishing_Flow\\Data\\Data' => $baseDir . '/inc/data/class-data.php',
    'IDG\\Publishing_Flow\\Data\\Featured_Image' => $baseDir . '/inc/data/class-featured-image.php',
    'IDG\\Publishing_Flow\\Data\\Images' => $baseDir . '/inc/data/class-images.php',
    'IDG\\Publishing_Flow\\Data\\Taxonomies' => $baseDir . '/inc/data/class-taxonomies.php',
    'IDG\\Publishing_Flow\\Deploy' => $baseDir . '/inc/class-deploy.php',
    'IDG\\Publishing_Flow\\Deploy\\Article' => $baseDir . '/inc/deploy/class-article.php',
    'IDG\\Publishing_Flow\\Deploy\\Author' => $baseDir . '/inc/deploy/class-author.php',
    'IDG\\Publishing_Flow\\Deploy\\Taxonomy' => $baseDir . '/inc/deploy/class-taxonomy.php',
    'IDG\\Publishing_Flow\\Embargo' => $baseDir . '/inc/class-embargo.php',
    'IDG\\Publishing_Flow\\Loader' => $baseDir . '/inc/class-loader.php',
    'IDG\\Publishing_Flow\\Sites' => $baseDir . '/inc/class-sites.php',
    'IDG\\Publishing_Flow\\Statuses' => $baseDir . '/inc/class-statuses.php',
    'IDG\\Publishing_Flow\\Statuses\\Draft' => $baseDir . '/inc/statuses/class-draft.php',
    'IDG\\Publishing_Flow\\Statuses\\On_Hold' => $baseDir . '/inc/statuses/class-on-hold.php',
    'IDG\\Publishing_Flow\\Statuses\\Publish' => $baseDir . '/inc/statuses/class-publish.php',
    'IDG\\Publishing_Flow\\Statuses\\Publish_Ready' => $baseDir . '/inc/statuses/class-publish-ready.php',
    'IDG\\Publishing_Flow\\Statuses\\Review_Ready' => $baseDir . '/inc/statuses/class-review-ready.php',
    'IDG\\Publishing_Flow\\Statuses\\Status' => $baseDir . '/inc/statuses/class-status.php',
    'IDG\\Publishing_Flow\\Statuses\\Transition\\Transition' => $baseDir . '/inc/statuses/transition/class-transition.php',
    'IDG\\Publishing_Flow\\Statuses\\Transition\\Transition_Interface' => $baseDir . '/inc/statuses/transition/class-tranisition-interface.php',
    'IDG\\Publishing_Flow\\Statuses\\Trash' => $baseDir . '/inc/statuses/class-trash.php',
    'IDG\\Publishing_Flow\\Statuses\\Updated' => $baseDir . '/inc/statuses/class-updated.php',
    'IDG\\Publishing_Flow\\Terms' => $baseDir . '/inc/class-terms.php',
    'IDG\\Publishing_Flow\\User_Profiles' => $baseDir . '/inc/class-user-profiles.php',
);