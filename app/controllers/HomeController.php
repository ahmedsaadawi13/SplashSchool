<?php
// FILE: /app/controllers/HomeController.php

class HomeController extends Controller {
    public function index() {
        // Check if user is logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard/index');
        }

        $this->view('home/index');
    }
}
