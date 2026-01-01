<?php
require_once '../../config/config.php';

// Destroy session
session_destroy();

setFlashMessage('success', 'Logout berhasil!');
redirect('/pages/auth/login.php');
