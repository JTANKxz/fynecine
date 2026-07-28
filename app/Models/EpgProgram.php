<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EpgProgram extends Model
{
    protected $fillable = ['epg_source_id', 'tv_channel_id', 'xmltv_channel_id', 'title', 'description', 'category', 'icon_url', 'starts_at', 'ends_at'];

    /**
     * XMLTV dates are saved as UTC. MySQL DATETIME does not retain a timezone,
     * so the default Eloquent cast would read them in APP_TIMEZONE and shift
     * the schedule a second time.
     */
    public function getStartsAtAttribute($value): ?Carbon
    {
        return $value ? Carbon::parse($value, 'UTC') : null;
    }

    public function getEndsAtAttribute($value): ?Carbon
    {
        return $value ? Carbon::parse($value, 'UTC') : null;
    }
}
