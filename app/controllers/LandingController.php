<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class LandingController extends Controller {
    public function index() {
        $this->call->view('landing/index');
    }
}
