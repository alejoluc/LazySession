<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use alejoluc\LazySession\LazySession;

$session = new LazySession();

$page = isset($_GET['page']) ? $_GET['page'] : '';


switch ($page) {
    case 'logout':
        $session->clear();
        $session->flash('message', 'You have logged out');
        $session->flash('message-bgcolor', '#216baf');

        header('Location: ?page=');
        break;

    case 'login':
        $csrfToken = $_POST['csrfToken'];
        $username  = $_POST['username'];
        $password  = $_POST['password'];

        if (!$session->validateCsrfToken($csrfToken)) {
            $session->flash('message', 'The access token is not valid. Are you attempting something?');
            $session->flash('message-bgcolor', '#990a22');
            header('Location: ?page=');
            exit;
        }

        if ($username === 'demo' && $password === 'demo') {
            $session['logged-in']  = true;
            $session['username']   = $username;
            $session['user_level'] = 1;

            $session->flash('message', 'You have logged in as a normal user');
            $session->flash('message-bgcolor', '#0a6d19');

            header('Location: ?page=');
        } elseif ($username === 'admin' && $password === 'admin') {
            $session['logged-in']  = true;
            $session['username']   = $username;
            $session['user_level'] = 2;

            $session->flash('message', 'You have loggedd in as an administrator');
            $session->flash('message-bgcolor', '#0a6d19');

            header('Location: ?page=');
        } else {
            $session->flash('message', 'No user and password found');
            $session->flash('message-bgcolor', '#990a22');
            header('Location: ?page=');
        }
        break;

    case '':
    case 'home':

        echo '<h1>Home</h1>';

        if ($session->flashHas('message')) {
            $message = $session->flashGet('message');
            $messageBgColor = $session->flashGet('message-bgcolor');
            $divStyle = 'color: white; background-color: ' . $messageBgColor . ';padding:10px; font-weight: bold; text-align: center';
            echo '<div style="' . $divStyle . '"; margin-top: 10px; margin-bottom: 10px>' . $message .' </div>';
        }

        if ($session['logged-in'] === true) {
            $user  = $session->username;
            $level = $session->user_level;

            echo 'You are logged in as ' . $user . ' and your access level is ' . $level;
            echo '<br /><br />';
            echo '<a href="?page=logout">Logout</a>';
        } else {
            echo '<p>You need to log in.<br />';
            echo 'Normal user: user <em>demo</em> and pass <em>demo</em><br />';
            echo 'Administrator: user <em>admin</em> and pass <em>admin</em><br /><br />';
            echo '<form method="POST" action="?page=login">';

            echo '<input type="hidden" name="csrfToken" value="' . $session->getCsrfToken() . '" />';
            echo 'User: <input type="text" name="username" /><br /><br />';
            echo 'Pass: <input type="password" name="password" /><br /><br />';
            echo '<input type="submit" value="Login" />';

            echo '</form>';
        }

        break;
}