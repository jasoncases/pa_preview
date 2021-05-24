<?php

namespace Proaction\Domain\Payroll\Controller;

use Proaction\Service\Payroll\ShiftDetail;
use Proaction\System\Controller\BaseProactionController;

class ShiftDetailController extends BaseProactionController
{
    protected $_viewPath = 'payroll/detail/';
    protected $linkId = 9;

    public function index()
    {
    }
    public function store()
    {
    }
    public function create()
    {
    }
    public function show()
    {

        $shift = $this->_shift($this->props->id);
        $this->render(
            'show.html',
            [
                'meta' => $shift->getMeta(),
                'timeline' => $shift->getTimeline(),
            ]
        );
    }
    public function update()
    {
    }
    public function edit()
    {
    }
    public function destroy()
    {
    }

    private function _shift($id)
    {
        return new ShiftDetail($id);
    }
}
