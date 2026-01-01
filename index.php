<?php
require_once 'config/config.php';

// Check if logged in
if (isLoggedIn()) {
    // Redirect berdasarkan role
    if (isMember()) {
        redirect('/pages/member/dashboard.php');
    } else {
        redirect('/pages/dashboard.php');
    }
} else {
    // Show landing page instead of redirecting to login
    include 'landing.php';
    exit();
}
