<?php
/**
 *
 * @author Blake Nahin <blake@zseartcc.org>
 */

namespace App\Helpers;


class ClubHelper
{
    public static function isPD($clubid = null)
    {
        return \App\Club::where([
            ['id', "=", $clubid ?: getClubId()],
            ['join_code', '=', 'TCHRPD']
        ])->exists();
    }
    public static function settings($clubid = null) {
        return \App\Setting::where('club_id', $clubid ?: getClubId())->firstOrFail();
    }
}