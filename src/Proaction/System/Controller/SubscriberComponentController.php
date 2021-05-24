<?php

namespace Proaction\System\Controller;

use Illuminate\Http\Request;
use Proaction\Domain\Ticketing\Resource\RecordUpdater;

use Proaction\System\Resource\Status\Status;
use Proaction\System\Resource\Subscribers\ComponentQuery;
use Proaction\System\TicketBasedModules\GuestUser;

/**
 * route: /subscriber_component
 */
class SubscriberComponentController extends BaseProactionController {

    public function index(Request $req) {
        try {
            return (new Status())->aux([
                'suggestions' => ComponentQuery::query((array) $req->all())
                ])->echo();
        } catch (\Exception $e) {
            die(__CLASS__ . ' - ' . $e->getMessage());
        }
    }

    public function store(Request $req)
    {
        $subscriber_id = (new GuestUser($req->input('module'), $req->input('email')))->go();
        return (new Status())->aux(['subscriber_id' => $subscriber_id, 'email' => $req->input('email')])->echo();
    }

    public function update($id, Request $req)
    {
        try {
            (new RecordUpdater($req->input('module'), (array) $req->input('options')))->go();
        } catch (\Exception $e) {
            return (new Status())->aux(['msg' => $e->getMessage()])->error();
        }
    }
}
