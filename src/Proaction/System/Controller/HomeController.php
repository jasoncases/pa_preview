<?php

namespace Proaction\System\Controller;

use Proaction\System\Views\HomeIndexView;

class HomeController extends BaseProactionController {

    protected $linkId = 1;

    public function index() {
        return view('System.Home.index', HomeIndexView::add());
    }

    public function landing() {
        return view('System.Home.landing', HomeIndexView::add());
    }

}
