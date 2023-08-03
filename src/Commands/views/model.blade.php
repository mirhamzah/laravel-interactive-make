@php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
@if (in_array('HasOne', $relationships))
use Illuminate\Database\Eloquent\Relations\HasOne;
@endif
@if (in_array('HasMany', $relationships))
use Illuminate\Database\Eloquent\Relations\HasMany;
@endif
@if (in_array('BelongsTo', $relationships))
use Illuminate\Database\Eloquent\Relations\BelongsTo;
@endif

class {{ $class }} extends Model
{
    use HasFactory;

@if (count($fillables))
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        '{!! implode("', '", array_column($fillables, 'name')) !!}'
    ];
@endif

@foreach ($relationships as $model => $type)
@switch($type)
    @case('HasOne')
    public function {{ strtolower($model) }}(): HasOne
    {
        return $this->hasOne({{ $model }}::class);
    }
    @break
 
    @case('HasMany')
    public function {{ strtolower($model) }}(): HasMany
    {
        return $this->hasMany({{ $model }}::class);
    }
    @break
 
    @case('BelongsTo')
    public function {{ strtolower($model) }}(): BelongsTo
    {
        return $this->belongsTo({{ $model }}::class);
    }
    @break

@endswitch

@endforeach

}
