@php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
@if (in_array('HasOne', array_column($relationships, 'type')))
use Illuminate\Database\Eloquent\Relations\HasOne;
@endif
@if (in_array('HasMany', array_column($relationships, 'type')))
use Illuminate\Database\Eloquent\Relations\HasMany;
@endif
@if (in_array('BelongsTo', array_column($relationships, 'type')))
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

@foreach ($relationships as $name => $model)
@switch($model['type'])
    @case('HasOne')
    public function {{ strtolower($name) }}(): HasOne
    {
        return $this->hasOne({{ $name }}::class);
    }
    @break
 
    @case('HasMany')
    public function {{ strtolower($model['field_name_plural']) }}(): HasMany
    {
        return $this->hasMany({{ $name }}::class);
    }
    @break
 
    @case('BelongsTo')
    public function {{ strtolower($name) }}(): BelongsTo
    {
        return $this->belongsTo({{ $name }}::class);
    }
    @break

@endswitch

@endforeach

}
