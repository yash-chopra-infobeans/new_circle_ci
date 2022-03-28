<?php

// VIP hotfix
// More info: https://docs.wpvip.com/how-tos/code-quality-and-best-practices/write-asynchronous-publishing-actions/

// Prevent cron jobs from being registered for each published post.
add_filter( 'wpcom_async_transition_post_status_should_offload', '__return_false' );
