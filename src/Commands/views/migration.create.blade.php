@php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{{ $table }}', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
@foreach ($fields as $field)
@switch($field['type'])
@case('string')
            $table->string('{{$field['name']}}', 50)->nullable();
@break
@default
            $table->{{ $field['type'] }}('{{ $field['name'] }}')->nullable();
@break
@endswitch
@endforeach

@foreach ($relationships as $key => $relationship)
@if ($relationship['type'] == 'HasOne')
            $table->unsignedBigInteger('{{ $relationship['field_name'] }}_id')->nullable();
            $table->foreign('{{ $relationship['field_name'] }}_id')->references('id')->on('{{ $relationship['table'] }}');
@endif
@endforeach

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{{ $table }}');
    }
};
