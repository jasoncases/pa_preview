<?php

namespace Proaction\System\Views\Base;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Meta\Model\ViewModuleSubscription;
use Proaction\Domain\Users\Resource\ProactionUserFactory;
use Proaction\System\Resource\Data\Data;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Session\ClientSession;
use Proaction\System\Resource\Session\SystemSession;
use Proaction\System\Resource\Session\UserSession;
use Proaction\System\Resource\Status\StatusBox;

/**
 * Return arrays to be sent to the \view() calls.
 *
 * ViewBuilders are an attempt to remove complex business logic from the
 * Controllers and methods. Logic and manipulations can be handled in
 * ViewBuilders, either generic or specifc and can be extended to deal
 * with different data templates/layouts. The goal is skinny models and
 * skinny controllers.
 */
abstract class BaseViewBuilder implements BaseViewInterface
{

    // data passed to the builder from the controller for further manip.
    // example, ticket_id is sent so the ViewBuilder can pull the ticket
    // to do some editing before returning and sending to the \view
    protected $localData;

    // all sessions handlers are available for ViewBuilders as needed
    protected $sessionClient;
    protected $sessionUser;
    protected $sessionSystem;

    public function __construct()
    {
        $this->sessionClient = new ClientSession();
        $this->sessionUser = new UserSession();
        $this->sessionSystem = new SystemSession();
    }


    /**
     * Add data to a view.
     *
     * First array is passed directly to the view. The second is passed
     * to the ViewBuilder class as ::$localData, to allow for more
     * complex maniupations
     *
     * @param array $dataToPassToView   - an array of values to be rend-
     *                                    ered in the view with no mani-
     *                                    pulation
     * @param array $localBuilderData   - data sent to the viewbuilder
     *                                    that is needed to build the
     *                                    view data
     * @return array
     */
    public static function add($dataToPassToView = null, $localBuilderData = null)
    {
        return (new static)->get($dataToPassToView, $localBuilderData);
    }

    /**
     * Return data local to the current ViewBuilder. Allows the dev to
     * do more complex data manipulation so the controller doesn't get
     * messy with unecessary business logic.
     *
     * @return array
     */
    protected abstract function _getViewData();

    /**
     * Merge all (3)/(4) data streams together
     *
     * @param array $dataToPassToView
     * @param array $localBuilderData
     * @return array
     */
    private function get($dataToPassToView = null, $localBuilderData = null)
    {
        $this->localData = $localBuilderData;
        return array_merge(Data::getInstance()->get(), $this->_process($dataToPassToView));
    }

    /**
     * Undocumented function
     *
     * @param array $dataToPassToView
     * @return void
     */
    private function _process($dataToPassToView = [])
    {
        if (isset($_GET['debugViewdata'])) {
            Arr::pre(array_merge($this->_baseViewData(), $this->_getViewData(), $dataToPassToView ?? []));
            die('end preview. script killed');
        }
        return array_merge($this->_baseViewData(), $this->_getViewData(), $dataToPassToView ?? []);
    }

    /**
     * This is data that is required on all "layout" views. User login
     * info, user state, environment StatusBox, modules, etc
     *
     * @return array
     */
    protected function _baseViewData()
    {
        // get current user, OR create a NullUser obj
        $user = ProactionUserFactory::create();
        return [
            // pass the user object to the views
            'user' => $user,
            // this prints to a hidden input node that the User.ts class
            // uses to be able to have user data at page load, session
            // then confirms that the server info matches and removes
            // the node
            'currentUserInjected' => $user->getSerializedJSONDisplayProps(),
            // shortcuts for booleans in the views
            'loggedIn' => $user->isLoggedIn(),
            'clockedIn' => $user->isClockedIn(),
            'isAdmin' => $user->isAdministrator(),
            'isSuper' => $user->isSuperAdmin(),
            'statusBox' => StatusBox::getInstance(),
            // this was for rudimentary cache busting on css files early
            // can be removed, but remove the {{$rand}} from the blades
            'rand' => rand(0, 200000),
            // This is the current client subscribed modules
            'modules' => $this->_getClientModules(),
        ];
    }

    private function _getClientModules()
    {
        if (!$this->sessionClient->pluck('modules')) {
            $modules = ViewModuleSubscription::getByClientUid(ProactionClient::uid());
            $this->sessionClient->add('modules', $modules->toArray());
        }
        return $this->sessionClient->pluck('modules');
    }
}
