<?php

namespace Proaction\System\Controller;

use Illuminate\Http\Request;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Model\MetaGlobal;
use Proaction\System\Resource\Status\Status;

class SystemController extends BaseProactionController
{

    public function getGlobal(Request $req)
    {
        (new Status())->aux(
            MetaGlobal::get($req->input('key')) ?? []
        )->echo();
    }
    public function getGlobalBatch(Request $req)
    {
        (new Status())->aux(
            MetaGlobal::getBatch($req->input('key')) ?? []
        )->echo();
    }
}
