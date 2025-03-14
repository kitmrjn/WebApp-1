<?php
// functions.php

// Function to calculate relative time
function time_ago($datetime) {
    $now = new DateTime;
    $created_at = new DateTime($datetime);
    $interval = $now->diff($created_at);

    if ($interval->y > 0) {
        return $interval->y == 1 ? '1 year ago' : $interval->y . ' years ago';
    } elseif ($interval->m > 0) {
        return $interval->m == 1 ? '1 month ago' : $interval->m . ' months ago';
    } elseif ($interval->d > 0) {
        return $interval->d == 1 ? '1 day ago' : $interval->d . ' days ago';
    } elseif ($interval->h > 0) {
        return $interval->h == 1 ? '1 hr ago' : $interval->h . ' hrs ago';
    } elseif ($interval->i > 0) {
        return $interval->i == 1 ? '1 min ago' : $interval->i . ' mins ago';
    } else {
        return 'Just now';
    }
}
?>