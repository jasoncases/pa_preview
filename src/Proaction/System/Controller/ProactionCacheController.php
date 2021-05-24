<?php

namespace Proaction\System\Controller;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Cache\ProactionRedis;

/**
 * Cache controller builds the entire application state for the client.
 * 
 * As of right now, the cache is what controlls the users ability to 
 * use timeclocks. This is an unecessary coupling, but one that was done
 * for reasons of haste to get the cache up and running while trying to 
 * convert from the old long polling system.
 */
class ProactionCacheController extends StreamController
{

    protected function _getStream()
    {
        try {

            $clientPrefix = ProactionClient::prefix();
            $redisHandler = ProactionRedis::getInstance();
            $proactionCache = new Cache($clientPrefix, $redisHandler);

            $cache = $proactionCache->get();

            if (!empty($cache)) {
                $msg = json_encode($cache);
                echo "data: $msg\n\n";
                flush();
            }
        } catch (\Exception $e) {
            mail(
                ProactionUser::defaultAdminEmail(),
                'Error in the cache. Sending email: ',
                $e->getMessage() . "\n\n\n" . print_r(debug_print_backtrace(), true)
            );
        }
    }
}
