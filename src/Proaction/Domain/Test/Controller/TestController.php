<?php

namespace Proaction\Domain\Test\Controller;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Employees\Model\EmployeePermissionView;
use Proaction\Domain\Employees\Resource\PendingBuilder;
use Proaction\Domain\Employees\Resource\PendingValidator;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Timesheets\Resource\ActiveShiftBuilder;
use Proaction\Domain\Timesheets\Resource\TimesheetAction;
use Proaction\Domain\Timesheets\Resource\TimestampUpdater;
use Proaction\Domain\Users\Model\ForcePasswordReset;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\Domain\Users\Service\Validation\UserIdentity;
use Proaction\System\Controller\BaseApiProactionController;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Cache\ProactionRedis;
use Proaction\System\Resource\Comms\Comms;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Regex\RegexHandler;
use Proaction\System\Resource\Session\VoiceSession;
use Proaction\System\Resource\Status\FlashAlert;
use Proaction\System\Resource\Status\Status;

class TestController extends BaseApiProactionController
{

    public function index()
    {

        $pin = 7878;

        $x = UserIdentity::validate(null, null, base64_encode($pin));

        Arr::pre($x);

        return (new Status())->error();

        if (isset($_GET['buster'])) {
            $cache = new Cache(ProactionClient::prefix(), ProactionRedis::getInstance());
            $cache->bustCache();
            $cache->process();
            Arr::pre($cache->get());
        }
    }
}
